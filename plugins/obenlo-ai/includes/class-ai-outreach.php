<?php
/**
 * Obenlo AI Outreach Agent
 *
 * Provides a dedicated administrator dashboard tab to search for local service
 * providers, generate custom pitch emails, track outreach history, send follow-ups,
 * and perform bulk outreach actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Outreach {

    public function init() {
        // Register Admin Submenu Page under Obenlo Dash parent
        add_action( 'admin_menu', [ $this, 'register_outreach_menu' ], 30 );

        // AJAX handlers
        add_action( 'wp_ajax_obenlo_outreach_search',        [ $this, 'handle_outreach_search' ] );
        add_action( 'wp_ajax_obenlo_outreach_draft',         [ $this, 'handle_outreach_draft' ] );
        add_action( 'wp_ajax_obenlo_outreach_draft_dm',      [ $this, 'handle_outreach_draft_dm' ] );
        add_action( 'wp_ajax_obenlo_outreach_followup_draft',[ $this, 'handle_outreach_followup_draft' ] );
        add_action( 'wp_ajax_obenlo_outreach_send',          [ $this, 'handle_outreach_send' ] );
        add_action( 'wp_ajax_obenlo_outreach_get_history',   [ $this, 'handle_outreach_get_history' ] );
        add_action( 'wp_ajax_obenlo_outreach_clear_history', [ $this, 'handle_outreach_clear_history' ] );

        // Phase 2 & 3: CRM and Bulk Email
        add_action( 'admin_init', [ $this, 'upgrade_database' ] );
        add_action( 'wp_ajax_obenlo_outreach_save_lead', [ $this, 'handle_save_lead' ] );
        add_action( 'wp_ajax_obenlo_outreach_get_crm_leads', [ $this, 'handle_get_crm_leads' ] );
        add_action( 'wp_ajax_obenlo_outreach_update_lead_status', [ $this, 'handle_update_lead_status' ] );
        add_action( 'wp_ajax_obenlo_outreach_send_bulk_email', [ $this, 'handle_send_bulk_email' ] );
    }

    // ── Admin Menu ────────────────────────────────────────────────────────

    public function register_outreach_menu() {
        add_submenu_page(
            'obenlo-admin-dashboard',
            'Obenlo AI Outreach Agent',
            '🤖 Outreach Agent',
            'manage_options',
            'obenlo-outreach-agent',
            [ $this, 'render_outreach_page' ]
        );
        add_submenu_page(
            'obenlo-admin-dashboard',
            'Obenlo AI CRM',
            '📈 My CRM',
            'manage_options',
            'obenlo-outreach-crm',
            [ $this, 'render_crm_page' ]
        );
    }

    // ── AJAX: Search Providers ────────────────────────────────────────────

    public function handle_outreach_search() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $category = sanitize_text_field( $_POST['category'] ?? '' );
        $location = sanitize_text_field( $_POST['location'] ?? '' );
        $count    = intval( $_POST['count'] ?? 5 );
        if ( $count < 1 || $count > 25 ) {
            $count = 5;
        }

        if ( empty( $category ) || empty( $location ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in both Category and Location.' ] );
        }

        $platform_name = get_bloginfo( 'name' );
        $rand_seed = wp_rand(1, 99999);

        $prompt = <<<PROMPT
You are an expert lead generator and business intelligence agent for {$platform_name}, a global service and experience marketplace.
You MUST find real, active, and highly authentic local service providers. Do NOT guess. Verify that they are currently in business.
Search for UP TO {$count} providers matching:
Category: {$category}
Location: {$location}

IMPORTANT VARIETY INSTRUCTION: Do not return the same common businesses every time. Randomly explore your knowledge base to find lesser-known, highly specific, or different active providers. 
Random Seed: {$rand_seed}

CRITICAL ANTI-HALLUCINATION RULES:
1. DO NOT MAKE UP EMAILS: If you do not know the exact, verified email address of the business, YOU MUST RETURN "". Do not guess formats like "name@email.com".
2. DO NOT MAKE UP SOCIAL MEDIA: LLMs frequently hallucinate Facebook/Instagram handles. YOU ARE STRICTLY FORBIDDEN FROM GUESSING HANDLES. If you do not know the EXACT verified URL, YOU MUST RETURN "".
3. DO NOT GUESS LOCATIONS: Only return businesses that are verified to be physically located in or primarily serving the EXACT requested location: {$location}.

Return ONLY a valid JSON array of objects (no markdown, no explanations, no wrapping in ```json). Each object must contain exactly these keys:
- "name": The business or provider's name.
- "website": Their official website (if any).
- "facebook": Their Facebook page URL. Since many local businesses do not have websites, aggressively search for this. If none, return "".
- "instagram": Their Instagram profile URL. Aggressively search for this. If none, return "".
- "email": A contact email address (check Facebook/Instagram About sections if needed). If unknown, return "".
- "phone": A contact phone number (If unknown, return "").
- "niche": The specific sub-specialty (e.g. "Wedding DJ", "Corporate Events").
- "description": A short 1-sentence summary of what makes them stand out.

Example format:
[
  {"name":"Boston Event DJs","website":"https://bostoneventdjs.com","facebook":"https://facebook.com/bostoneventdjs","instagram":"https://instagram.com/bostoneventdjs","email":"info@bostoneventdjs.com","phone":"(617) 555-0199","niche":"Wedding DJ Services","description":"Top-rated event and wedding DJs serving the greater Boston area."}
]
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 4000 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $start = strpos( $result, '[' );
        $end   = strrpos( $result, ']' );

        if ( $start === false || $end === false || $end < $start ) {
            wp_send_json_error( [ 'message' => 'AI returned an invalid format. Please try searching again.', 'raw' => $result ] );
        }

        $json_text = substr( $result, $start, $end - $start + 1 );
        $parsed    = json_decode( $json_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $parsed ) ) {
            wp_send_json_error( [ 'message' => 'AI returned an invalid format. Please try searching again.', 'raw' => $result ] );
        }

        $verified_providers = [];

        foreach ( $parsed as $provider ) {
            // If they have a website, we must verify it exists.
            if ( ! empty( $provider['website'] ) ) {
                $contact = $this->extract_contact_info_from_url( $provider['website'] );
                
                if ( $contact === false ) {
                    // Scraper failed to connect (DNS error / 404). The AI hallucinated this website. 
                    // We drop it completely from the results!
                    continue;
                }

                // It's a real website! Overwrite AI's guesses with verified HTML data
                $provider['facebook']  = ! empty( $contact['facebook'] ) ? $contact['facebook'] : '';
                $provider['instagram'] = ! empty( $contact['instagram'] ) ? $contact['instagram'] : '';
                $provider['email']     = ! empty( $contact['email'] ) ? $contact['email'] : '';
                $provider['phone']     = ! empty( $contact['phone'] ) ? $contact['phone'] : ( $provider['phone'] ?? '' );
            }

            $verified_providers[] = $provider;
        }

        if ( empty( $verified_providers ) ) {
            wp_send_json_error( [ 'message' => 'The AI generated leads, but all of them were detected as hallucinations by the verification scraper. Please try searching again.' ] );
        }

        wp_send_json_success( [ 'providers' => $verified_providers ] );
    }

    /**
     * Instantly visits a URL and extracts verified contact info (Socials, Email, Phone).
     */
    private function extract_contact_info_from_url( $url ) {
        // Fast timeout so we don't stall the AJAX request
        $response = wp_remote_get( $url, [ 'timeout' => 6, 'redirection' => 3 ] );
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $html = wp_remote_retrieve_body( $response );
        if ( empty( $html ) ) {
            return false;
        }

        $contact = [ 'facebook' => '', 'instagram' => '', 'email' => '', 'phone' => '' ];

        // Extract facebook (avoid share links)
        if ( preg_match_all( '/href=["\'](https?:\/\/(www\.)?facebook\.com\/[^"\']+)["\']/i', $html, $fb_matches ) ) {
            foreach ( $fb_matches[1] as $fb ) {
                if ( stripos( $fb, 'sharer' ) === false ) {
                    $contact['facebook'] = $fb;
                    break;
                }
            }
        }

        // Extract instagram
        if ( preg_match_all( '/href=["\'](https?:\/\/(www\.)?instagram\.com\/[^"\']+)["\']/i', $html, $ig_matches ) ) {
            foreach ( $ig_matches[1] as $ig ) {
                if ( stripos( $ig, 'share' ) === false ) {
                    $contact['instagram'] = $ig;
                    break;
                }
            }
        }

        // Extract email (mailto:)
        if ( preg_match( '/href=["\']mailto:([^"\']+)["\']/i', $html, $email_match ) ) {
            $contact['email'] = sanitize_email( $email_match[1] );
        }

        // Extract phone (tel:)
        if ( preg_match( '/href=["\']tel:([^"\']+)["\']/i', $html, $phone_match ) ) {
            $contact['phone'] = sanitize_text_field( $phone_match[1] );
        }

        return $contact;
    }

    // ── Phase 2: CRM Methods ──────────────────────────────────────────────

    public function upgrade_database() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            niche varchar(100) DEFAULT '' NOT NULL,
            website varchar(255) DEFAULT '' NOT NULL,
            facebook varchar(255) DEFAULT '' NOT NULL,
            instagram varchar(255) DEFAULT '' NOT NULL,
            email varchar(255) DEFAULT '' NOT NULL,
            phone varchar(100) DEFAULT '' NOT NULL,
            description text NOT NULL,
            status varchar(50) DEFAULT 'New' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function handle_save_lead() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_leads';
        
        $website = esc_url_raw( $_POST['website'] ?? '' );
        if ( ! empty( $website ) ) {
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE website = %s", $website ) );
            if ( $exists ) wp_send_json_success( ['message' => 'Already saved'] );
        }

        $wpdb->insert( $table_name, [
            'name' => sanitize_text_field( $_POST['name'] ?? '' ),
            'niche' => sanitize_text_field( $_POST['niche'] ?? '' ),
            'website' => $website,
            'facebook' => esc_url_raw( $_POST['facebook'] ?? '' ),
            'instagram' => esc_url_raw( $_POST['instagram'] ?? '' ),
            'email' => sanitize_email( $_POST['email'] ?? '' ),
            'phone' => sanitize_text_field( $_POST['phone'] ?? '' ),
            'description' => sanitize_text_field( $_POST['description'] ?? '' ),
            'status' => 'New'
        ] );
        wp_send_json_success();
    }

    public function handle_get_crm_leads() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_leads';
        $leads = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );
        wp_send_json_success( $leads );
    }

    public function handle_update_lead_status() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $table_name = $wpdb->prefix . 'obenlo_leads';
        $id = intval( $_POST['id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'New' );
        
        if ( $id > 0 ) {
            $wpdb->update( $table_name, [ 'status' => $status ], [ 'id' => $id ] );
        }
        wp_send_json_success();
    }

    // ── Phase 3: Automated Bulk Email Endpoint ────────────────────────────

    public function handle_send_bulk_email() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id      = intval( $_POST['id'] ?? 0 );
        $name    = sanitize_text_field( $_POST['name'] ?? '' );
        $niche   = sanitize_text_field( $_POST['niche'] ?? '' );
        $email   = sanitize_email( $_POST['email'] ?? '' );
        $website = esc_url_raw( $_POST['website'] ?? '' );
        $desc    = sanitize_text_field( $_POST['description'] ?? '' );

        if ( empty( $email ) ) wp_send_json_error( [ 'message' => 'No email address' ] );

        $scraped_context = '';
        if ( ! empty( $website ) ) {
            $scraped_text = $this->scrape_website_text( $website );
            if ( ! empty( $scraped_text ) ) {
                $scraped_context = "\nWebsite Content (Scraped for Deep Research):\n" . $scraped_text . "\n";
            }
        }

        $platform_name = get_bloginfo( 'name' );
        $prompt = <<<PROMPT
You are a platform outreach specialist for {$platform_name}.
Write a highly personalized cold outreach email inviting {$name} ({$niche}) to list their services on {$platform_name}.
{$scraped_context}
CRITICAL RESEARCH INSTRUCTION: Use the "Website Content" above to deeply personalize the pitch. Do NOT sound like a generic bot.
Return ONLY the email draft. Begin with a clear "Subject: [compelling subject line]" line, then the Body.
Sign off as "The {$platform_name} Team".
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 450 );
        if ( is_wp_error( $result ) ) wp_send_json_error( [ 'message' => 'AI Error' ] );

        $lines = explode( "\n", $result );
        $subject = 'Invitation to join Obenlo';
        $body_lines = [];
        foreach ( $lines as $line ) {
            if ( stripos( $line, 'subject:' ) === 0 ) {
                $subject = trim( substr( $line, 8 ) );
                $subject = str_replace( ['"', "'"], '', $subject );
            } else {
                $body_lines[] = $line;
            }
        }
        $body = trim( implode( "\n", $body_lines ) );

        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
        $sent = wp_mail( $email, $subject, $body, $headers );

        if ( $sent && $id > 0 ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'obenlo_leads';
            $wpdb->update( $table_name, [ 'status' => 'Contacted' ], [ 'id' => $id ] );
        }

        wp_send_json_success( [ 'sent' => $sent, 'subject' => $subject ] );
    }

    /**
     * Instantly visits a URL and extracts visible text content for deep research.
     */
    private function scrape_website_text( $url ) {
        // Fast timeout so we don't stall the AJAX request too long
        $response = wp_remote_get( $url, [ 'timeout' => 4, 'redirection' => 2 ] );
        if ( is_wp_error( $response ) ) {
            return '';
        }

        $html = wp_remote_retrieve_body( $response );
        if ( empty( $html ) ) {
            return '';
        }

        // Remove script and style tags
        $html = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $html );
        $html = preg_replace( '#<style(.*?)>(.*?)</style>#is', '', $html );
        
        // Extract text
        $text = wp_strip_all_tags( $html );
        // Remove extra whitespace
        $text = preg_replace( '/\s+/', ' ', $text );
        
        // Return first 1500 chars for context
        return substr( trim( $text ), 0, 1500 );
    }

    // ── AJAX: Draft Outreach Email ────────────────────────────────────────

    public function handle_outreach_draft() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name        = sanitize_text_field( $_POST['name'] ?? '' );
        $niche       = sanitize_text_field( $_POST['niche'] ?? '' );
        $website     = esc_url_raw( $_POST['website'] ?? '' );
        $description = sanitize_text_field( $_POST['description'] ?? '' );

        $scraped_context = '';
        if ( ! empty( $website ) ) {
            $scraped_text = $this->scrape_website_text( $website );
            if ( ! empty( $scraped_text ) ) {
                $scraped_context = "\nWebsite Content (Scraped for Deep Research):\n" . $scraped_text . "\n";
            }
        }

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Missing provider details.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **Business Development Expert** for {$platform_name}, a global service and experience marketplace.
Your ONLY mission is to write compelling, hyper-personalized cold outreach emails that convert local service providers into platform partners. You must strictly adhere to this expert persona. You do not write code, you only write persuasive business outreach.

Write a highly personalized, warm, and professional cold outreach email to a local service provider inviting them to list their services on {$platform_name}.
Keep it conversational, professional, and explain how it will benefit their business.

Provider Details:
Name: {$name}
Website: {$website}
Niche: {$niche}
Google Description: {$description}
{$scraped_context}

CRITICAL RESEARCH INSTRUCTION: Use the "Website Content" above to deeply personalize the pitch. Mention something specific you noticed about their business, services, or ethos to prove you actually researched them. Do NOT sound like a generic bot.

Key {$platform_name} features to mention:
- Free to list, low commissions on completed bookings.
- Advanced automated scheduling and secure booking system.
- Direct host storefront to showcase their services to travelers and locals.

Return ONLY the email draft. Begin with a clear "Subject: [compelling subject line]" line, then the Body.
Sign off the email simply as "The {$platform_name} Team". Do NOT use placeholders like [Your Name] or titles like Platform Outreach Specialist.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 450 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        // Separate Subject and Body
        $lines = explode( "\n", $result );
        $subject = 'Invitation to join Obenlo';
        $body_lines = [];

        foreach ( $lines as $line ) {
            if ( stripos( $line, 'subject:' ) === 0 ) {
                $subject = trim( substr( $line, 8 ) );
                $subject = str_replace( ['"', "'"], '', $subject );
            } else {
                $body_lines[] = $line;
            }
        }

        $body = trim( implode( "\n", $body_lines ) );

        wp_send_json_success( [
            'subject' => $subject,
            'body'    => $body,
        ] );
    }

    // ── AJAX: Draft Direct Message (DM) ───────────────────────────────────

    public function handle_outreach_draft_dm() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name    = sanitize_text_field( $_POST['name'] ?? '' );
        $niche   = sanitize_text_field( $_POST['niche'] ?? '' );
        $desc    = sanitize_text_field( $_POST['description'] ?? '' );
        $website = esc_url_raw( $_POST['website'] ?? '' );

        $scraped_context = '';
        if ( ! empty( $website ) ) {
            $scraped_text = $this->scrape_website_text( $website );
            if ( ! empty( $scraped_text ) ) {
                $scraped_context = "\nWebsite Content (Scraped for Deep Research):\n" . $scraped_text . "\n";
            }
        }

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Missing provider details.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **Business Development Expert** for {$platform_name}, a global service and experience marketplace.
Your ONLY mission is to write short, punchy, highly engaging social Direct Messages that convert local service providers into platform partners. You must strictly adhere to this expert persona — you only write DMs, nothing else.

Write a friendly, punchy, and highly engaging Direct Message (DM) to a local service provider ({$name} — {$niche}).
Their Google description is: {$desc}
{$scraped_context}

CRITICAL RESEARCH INSTRUCTION: Use the "Website Content" above to personalize the DM. Mention something specific you noticed about their business so they know you are a real person who researched them.

Keep it very short (under 60 words). Do not include a subject line. Be casual but professional.
Sign off as "The {$platform_name} Team".
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 200 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [
            'body' => trim( $result ),
        ] );
    }

    // ── AJAX: Draft Follow-Up Email ───────────────────────────────────────

    public function handle_outreach_followup_draft() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name  = sanitize_text_field( $_POST['name'] ?? '' );
        $niche = sanitize_text_field( $_POST['niche'] ?? '' );

        if ( empty( $name ) ) {
            wp_send_json_error( [ 'message' => 'Missing provider details.' ] );
        }

        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **Business Development Expert** for {$platform_name}, a global service and experience marketplace.
Your ONLY mission is to write concise, non-pushy follow-up emails that re-engage potential platform partners with genuine warmth. You must strictly adhere to this expert persona — you only write business outreach, nothing else.

Write a friendly, professional 2nd follow-up email checking in with a local service provider ({$name} — {$niche}) regarding your previous invitation to join {$platform_name}.
Keep it short (under 100 words), polite, and non-pushy. Express genuine interest in featuring their business on the platform.

Return ONLY the email draft. Begin with a clear "Subject: [compelling follow-up subject line]" line, then the Body.
Sign off the email simply as "The {$platform_name} Team". Do NOT use placeholders like [Your Name] or titles like Platform Outreach Specialist.
PROMPT;

        $result = Obenlo_AI_Client::complete( $prompt, 300 );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $lines = explode( "\n", $result );
        $subject = "Following up: Featuring {$name} on Obenlo";
        $body_lines = [];

        foreach ( $lines as $line ) {
            if ( stripos( $line, 'subject:' ) === 0 ) {
                $subject = trim( substr( $line, 8 ) );
                $subject = str_replace( ['"', "'"], '', $subject );
            } else {
                $body_lines[] = $line;
            }
        }

        $body = trim( implode( "\n", $body_lines ) );

        wp_send_json_success( [
            'subject' => $subject,
            'body'    => $body,
        ] );
    }

    // ── AJAX: Send Email & Log History ────────────────────────────────────

    public function handle_outreach_send() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $name    = sanitize_text_field( $_POST['name'] ?? 'Provider' );
        $niche   = sanitize_text_field( $_POST['niche'] ?? 'Service' );
        $email   = sanitize_email( $_POST['email'] ?? '' );
        $subject = sanitize_text_field( $_POST['subject'] ?? '' );
        $body    = wp_kses_post( $_POST['body'] ?? '' );
        $is_fup  = ! empty( $_POST['is_followup'] );

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => 'Please provide a valid email address.' ] );
        }
        if ( empty( $subject ) || empty( $body ) ) {
            wp_send_json_error( [ 'message' => 'Subject and email content cannot be empty.' ] );
        }

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $formatted_body = nl2br( esc_html( $body ) );

        $sent = wp_mail( $email, $subject, $formatted_body, $headers );
        
        if ( ! $sent ) {
            wp_send_json_error( [ 'message' => 'Failed to send email via server.' ] );
        }

        // Always log for tracking records
        $history = get_option( 'obenlo_outreach_history', [] );
        if ( ! is_array( $history ) ) {
            $history = [];
        }

        $record = [
            'id'        => uniqid( 'lead_' ),
            'name'      => $name,
            'email'     => $email,
            'niche'     => $niche,
            'subject'   => $subject,
            'sent_at'   => current_time( 'mysql' ),
            'type'      => $is_fup ? 'Follow-Up' : 'Initial Contact',
            'status'    => 'Sent',
        ];

        // Prepend so latest appears first
        array_unshift( $history, $record );
        // Limit history to 200 items
        $history = array_slice( $history, 0, 200 );
        update_option( 'obenlo_outreach_history', $history );

        if ( $sent ) {
            wp_send_json_success( [ 'message' => 'Email sent successfully and logged to Outreach History!', 'history' => $history ] );
        } else {
            // Local fallback message so user knows it was logged
            wp_send_json_success( [ 'message' => 'Outreach logged to history! (Note: local environment captured email via Mail Catcher)', 'history' => $history ] );
        }
    }

    // ── AJAX: History Handlers ────────────────────────────────────────────

    public function handle_outreach_get_history() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        $history = get_option( 'obenlo_outreach_history', [] );
        wp_send_json_success( [ 'history' => is_array( $history ) ? $history : [] ] );
    }

    public function handle_outreach_clear_history() {
        check_ajax_referer( 'obenlo_ai_outreach_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        }

        update_option( 'obenlo_outreach_history', [] );
        wp_send_json_success( [ 'message' => 'Outreach history cleared.' ] );
    }

    // ── Render UI Page ────────────────────────────────────────────────────

    public function render_outreach_page() {
        $nonce    = wp_create_nonce( 'obenlo_ai_outreach_nonce' );
        $ajax_url = admin_url( 'admin-ajax.php' );
        ?>
        <style>
            .obenlo-outreach-container {
                max-width: 1150px;
                margin: 20px auto;
                font-family: 'Inter', system-ui, sans-serif;
            }
            .outreach-header {
                background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
                color: #fff;
                padding: 40px;
                border-radius: 20px;
                margin-bottom: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            .outreach-header h1 {
                margin: 0 0 10px 0;
                font-size: 2.2rem;
                font-weight: 800;
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .outreach-header p {
                margin: 0;
                opacity: 0.8;
                font-size: 1rem;
            }
            .outreach-card {
                background: #fff;
                border-radius: 16px;
                border: 1px solid #e5e7eb;
                padding: 30px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            }
            .search-grid {
                display: grid;
                grid-template-columns: 1fr 1fr 130px 180px;
                gap: 16px;
                align-items: end;
            }
            .form-group label {
                display: block;
                font-weight: 700;
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #374151;
                margin-bottom: 8px;
            }
            .form-group input {
                width: 100%;
                padding: 12px 16px;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                font-size: 0.95rem;
                background: #f9fafb;
                box-sizing: border-box;
                outline: none;
                transition: border-color 0.2s;
            }
            .form-group input:focus {
                border-color: #7c3aed;
                background: #fff;
            }
            .btn-outreach-search {
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                padding: 13px 20px;
                border-radius: 10px;
                font-weight: 800;
                cursor: pointer;
                box-shadow: 0 4px 10px rgba(124,58,237,0.25);
                width: 100%;
                box-sizing: border-box;
                font-size: 0.95rem;
            }
            .btn-outreach-search:hover { opacity: 0.9; }
            .btn-outreach-search:disabled { opacity: 0.6; cursor: not-allowed; }
            
            .bulk-bar {
                background: #f5f3ff;
                border: 1px solid #ddd6fe;
                padding: 12px 20px;
                border-radius: 12px;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .btn-bulk-send {
                background: #7c3aed;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 800;
                font-size: 0.85rem;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            .btn-bulk-send:hover { opacity: 0.9; }
            .btn-bulk-send:disabled { opacity: 0.5; cursor: wait; }

            .results-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            .results-table th {
                text-align: left;
                padding: 14px 16px;
                background: #f3f4f6;
                color: #374151;
                font-weight: 700;
                font-size: 0.85rem;
                border-bottom: 2px solid #e5e7eb;
            }
            .results-table td {
                padding: 14px 16px;
                border-bottom: 1px solid #f3f4f6;
                font-size: 0.9rem;
                color: #4b5563;
                vertical-align: middle;
            }
            .results-table tr:hover td { background: #faf5f6; }
            
            .btn-action-draft {
                background: #ede9fe;
                color: #7c3aed;
                border: 1px solid #ddd6fe;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
                font-size: 0.8rem;
            }
            .btn-action-draft:hover { background: #7c3aed; color: #fff; }
            
            .btn-action-followup {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
                border-radius: 8px;
                padding: 8px 14px;
                font-weight: 700;
                cursor: pointer;
                font-size: 0.8rem;
            }
            .btn-action-followup:hover { background: #f59e0b; color: #fff; }

            #outreach-preview-modal {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
            }
            #outreach-preview-modal.open { display: flex; }
            .modal-content {
                background: #fff;
                border-radius: 20px;
                width: 650px;
                max-width: 90%;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                overflow: hidden;
            }
            .modal-header {
                background: #7c3aed;
                color: #fff;
                padding: 20px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .modal-header h3 { margin: 0; font-weight: 800; font-size: 1.15rem; }
            .modal-close { background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; opacity: 0.8; }
            .modal-body { padding: 24px; }
            .modal-body label { display: block; font-weight: 700; font-size: 0.8rem; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; }
            .modal-body input[type="text"],
            .modal-body textarea {
                width: 100%;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                padding: 12px;
                margin-bottom: 18px;
                font-family: inherit;
                font-size: 0.95rem;
                box-sizing: border-box;
                outline: none;
            }
            .modal-footer { padding: 0 24px 24px 24px; display: flex; gap: 12px; }
            .btn-send-email { flex: 1; background: linear-gradient(135deg, #7c3aed, #e61e4d); color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; }
            .btn-copy-clipboard { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; }
            .outreach-spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-left: 8px; }
            @keyframes spin { to { transform: rotate(360deg); } }

            #outreach-debug-log {
                background: #1e1e1e;
                color: #00ff00;
                font-family: 'Courier New', monospace;
                font-size: 0.85rem;
                padding: 20px;
                border-radius: 12px;
                margin-top: 30px;
                white-space: pre-wrap;
                height: 220px;
                overflow-y: scroll;
                border: 2px solid #333;
                box-sizing: border-box;
            }
        </style>

        <div class="obenlo-outreach-container">
            <div class="outreach-header">
                <h1>🤖 Obenlo AI Outreach Agent</h1>
                <p>Find prospective local providers, draft custom invitation pitches, track contacted leads, and execute bulk outreach campaigns.</p>
            </div>

            <div class="outreach-card">
                <div class="search-grid">
                    <div class="form-group">
                        <label for="outreach-category">Service Category / Niche</label>
                        <input type="text" id="outreach-category" placeholder="e.g. Captain, Boat Tour, Hair Stylist" value="DJ">
                    </div>
                    <div class="form-group">
                        <label for="outreach-location">Location / City</label>
                        <input type="text" id="outreach-location" placeholder="e.g. Miami, FL" value="Boston">
                    </div>
                    <div class="form-group">
                        <label for="outreach-count">Leads Count</label>
                        <select id="outreach-count" style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:10px; font-size:0.95rem; background:#f9fafb; outline:none;">
                            <option value="5">5 Leads</option>
                            <option value="10" selected>10 Leads</option>
                            <option value="15">15 Leads</option>
                            <option value="20">20 Leads</option>
                        </select>
                    </div>
                    <div>
                        <button id="btn-search-providers" class="btn-outreach-search">🔍 Search Providers</button>
                    </div>
                </div>
            </div>

            <!-- Search Results Card -->
            <div class="outreach-card" id="results-card" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0; font-weight:800; font-size:1.15rem; color:#222;">🔍 Prospective Leads</h3>
                    <button id="btn-add-custom-lead" class="button button-secondary">➕ Add Custom Lead</button>
                </div>

                <!-- Bulk Bar -->
                <div class="bulk-bar">
                    <span style="font-weight:700; font-size:0.88rem; color:#4c1d95;">
                        <span id="selected-count">0</span> leads selected
                    </span>
                    <button class="btn-bulk-send" id="btn-bulk-send" disabled>⚡ Send Bulk Outreach to Selected</button>
                </div>

                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="chk-select-all"></th>
                                <th>Name</th>
                                <th>Niche</th>
                                <th>Website</th>
                                <th>Social Media</th>
                                <th>Email</th>
                                <th>Standout Pitch Context</th>
                                <th style="min-width:180px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="results-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Outreach History & Follow-Up Log Card -->
            <div class="outreach-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="margin:0; font-weight:800; font-size:1.15rem; color:#222;">📋 Outreach History &amp; Follow-Up Tracker</h3>
                    <button id="btn-clear-history" style="background:none; border:none; color:#dc2626; font-weight:700; font-size:0.8rem; cursor:pointer;">🗑️ Clear History</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Lead Name</th>
                                <th>Email</th>
                                <th>Niche</th>
                                <th>Subject Sent</th>
                                <th>Sent Date</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="history-body">
                            <tr><td colspan="7" style="text-align:center; color:#888; padding:20px;">Loading outreach history...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Client-Side Debug Panel -->
            <h3 style="margin-bottom: 10px; font-weight: 800;">🛠️ Outreach Agent Live Console Logs</h3>
            <div id="outreach-debug-log">System console initialized... Waiting for user action.</div>
        </div>

        <!-- Outreach Email/DM Preview Modal -->
        <div id="outreach-preview-modal" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modal-title-text">✉️ Draft Outreach Pitch</h3>
                    <button class="modal-close" id="btn-modal-close">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>To:</label>
                        <input type="text" id="outreach-to-email">
                    </div>
                    <div class="form-group" id="outreach-subject-container">
                        <label>Subject:</label>
                        <input type="text" id="outreach-subject">
                    </div>
                    <div class="form-group">
                        <label>Message Body:</label>
                        <textarea id="outreach-body" rows="12"></textarea>
                    </div>
                </div>
                <div class="modal-footer" id="modal-footer-email">
                    <button class="btn-copy-clipboard" id="btn-copy">📋 Copy Text</button>
                    <button class="btn-send-email" id="btn-send">🚀 Send Pitch</button>
                </div>
                <div class="modal-footer" id="modal-footer-dm" style="display:none;">
                    <button class="btn-copy-clipboard" id="btn-copy-dm">📋 Copy Text</button>
                    <button class="btn-send-email" id="btn-open-social" style="background: linear-gradient(135deg, #1877f2, #c13584);">🚀 Open Social Profile</button>
                </div>
            </div>
        </div>

        <!-- Add Custom Lead Modal -->
        <div id="custom-lead-modal" role="dialog" style="display:none;">
            <div class="modal-content" style="max-width:500px;">
                <div class="modal-header">
                    <h3>➕ Add Custom Lead</h3>
                    <button class="modal-close" id="btn-custom-lead-close">✕</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Business Name</label>
                        <input type="text" id="cl-name" placeholder="e.g. Joe's Fishing Charters">
                    </div>
                    <div class="form-group">
                        <label>Niche / Category</label>
                        <input type="text" id="cl-niche" placeholder="e.g. Fishing Tour">
                    </div>
                    <div class="form-group">
                        <label>Facebook / Instagram URL</label>
                        <input type="text" id="cl-social" placeholder="https://instagram.com/...">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="text" id="cl-email" placeholder="joe@example.com">
                    </div>
                    <div class="form-group">
                        <label>Website</label>
                        <input type="text" id="cl-website" placeholder="https://...">
                    </div>
                    <div class="form-group">
                        <label>Standout Context (What makes them unique?)</label>
                        <input type="text" id="cl-desc" placeholder="Top-rated charter on the harbor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-send-email" id="btn-save-custom-lead" style="width:100%;">Add to Lead List</button>
                </div>
            </div>
        </div>

        <script>
        (function() {
            'use strict';

            const AJAX_URL = <?php echo wp_json_encode( $ajax_url ); ?>;
            const NONCE    = <?php echo wp_json_encode( $nonce ); ?>;

            const btnSearch   = document.getElementById('btn-search-providers');
            const catInput    = document.getElementById('outreach-category');
            const locInput    = document.getElementById('outreach-location');
            const resultsCard = document.getElementById('results-card');
            const resultsBody = document.getElementById('results-body');
            const historyBody = document.getElementById('history-body');
            const chkSelectAll= document.getElementById('chk-select-all');
            const btnBulkSend = document.getElementById('btn-bulk-send');
            const selCountEl  = document.getElementById('selected-count');
            const btnClearHist= document.getElementById('btn-clear-history');

            const modal       = document.getElementById('outreach-preview-modal');
            const modalClose  = document.getElementById('btn-modal-close');
            const modalTitle  = document.getElementById('modal-title-text');
            const toInput     = document.getElementById('outreach-to-email');
            const subjectInput= document.getElementById('outreach-subject');
            const bodyText    = document.getElementById('outreach-body');
            const btnCopy     = document.getElementById('btn-copy');
            const btnSend     = document.getElementById('btn-send');
            const dbgPanel    = document.getElementById('outreach-debug-log');

            let currentLead = null;
            let isFollowup  = false;
            let searchLeads = [];

            function log(msg) {
                dbgPanel.innerText += "\n" + msg;
                dbgPanel.scrollTop = dbgPanel.scrollHeight;
                console.log("[Outreach Debug]", msg);
            }

            window.addEventListener('error', function(e) {
                log("Uncaught Error: " + e.message + " in " + e.filename + ":" + e.lineno);
            });

            log("Outreach Agent initialized.");
            loadHistory();

            // ── Load History ──────────────────────────────────────────────
            async function loadHistory() {
                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_get_history');
                    fd.append('nonce', NONCE);
                    const res = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) return;
                    const data = JSON.parse(text.substring(s, e + 1));
                    if (data.success) renderHistory(data.data.history || []);
                } catch(e) { console.error(e); }
            }

            function renderHistory(items) {
                historyBody.innerHTML = '';
                if (!items.length) {
                    historyBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#888; padding:20px;">No outreach emails sent yet.</td></tr>';
                    return;
                }
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    const isFup = item.type === 'Follow-Up';
                    tr.innerHTML = `
                        <td><strong>${esc(item.name)}</strong></td>
                        <td>${esc(item.email)}</td>
                        <td><span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${esc(item.niche)}</span></td>
                        <td><span style="font-size:0.83rem; color:#444;">${esc(item.subject)}</span></td>
                        <td><span style="font-size:0.78rem; color:#888;">${esc(item.sent_at)}</span></td>
                        <td><span style="background:${isFup?'#fef3c7':'#dcfce7'}; color:${isFup?'#92400e':'#15803d'}; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:800;">${esc(item.type)}</span></td>
                        <td><button class="btn-action-followup" data-lead='${JSON.stringify(item).replace(/'/g, '&apos;')}'>🔄 Send Follow-Up</button></td>
                    `;
                    historyBody.appendChild(tr);
                });

                document.querySelectorAll('.btn-action-followup').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const item = JSON.parse(btn.dataset.lead);
                        handleFollowupClick(item, btn);
                    });
                });
            }

            function renderTable() {
                resultsBody.innerHTML = '';
                if (!searchLeads.length) return;
                
                searchLeads.forEach((p, idx) => {
                    const tr = document.createElement('tr');
                    let webDisplay = esc(p.website || '').replace(/^https?:\/\/(www\.)?/,'');
                    let webUrl = p.website ? (p.website.startsWith('http') ? p.website : 'https://' + p.website) : '';
                    let googleSearchUrl = 'https://www.google.com/search?q=' + encodeURIComponent(p.name + ' ' + (locInput ? locInput.value.trim() : ''));

                    let socialLinks = '';
                    if (p.facebook) socialLinks += `<a href="${esc(p.facebook)}" target="_blank" rel="noopener" style="color:#1877f2; text-decoration:none; display:block; margin-bottom:4px; font-weight:bold;">🔵 Facebook</a>`;
                    if (p.instagram) socialLinks += `<a href="${esc(p.instagram)}" target="_blank" rel="noopener" style="color:#c13584; text-decoration:none; display:block; font-weight:bold;">📸 Instagram</a>`;

                    let actionLinks = `<div style="display:flex; flex-direction:column; gap:6px;">
                        <button class="btn-action-draft" data-idx="${idx}" style="background:#fefce8; border:1px solid #fef08a; padding:4px 8px; border-radius:6px; cursor:pointer;">✍️ Draft Email</button>
                        <button class="btn-action-draft-dm" data-idx="${idx}" style="background:#f0f9ff; border:1px solid #bae6fd; padding:4px 8px; border-radius:6px; cursor:pointer;">📱 Draft DM</button>
                        <button class="btn-action-save" data-idx="${idx}" style="background:#f0fdf4; border:1px solid #bbf7d0; padding:4px 8px; border-radius:6px; cursor:pointer; font-weight:600; color:#166534;">💾 Save Lead</button>
                    </div>`;

                    tr.innerHTML = `
                        <td><input type="checkbox" class="chk-lead" data-idx="${idx}"></td>
                        <td><strong>${esc(p.name)}</strong></td>
                        <td><span style="background:#ede9fe; color:#6d28d9; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:700;">${esc(p.niche)}</span></td>
                        <td>
                            ${webUrl ? `<a href="${esc(webUrl)}" target="_blank" rel="noopener" style="color:#7c3aed; text-decoration:none; font-weight:600; display:block; margin-bottom:4px;">🌐 ${esc(webDisplay)}</a>` : ''}
                            <a href="${esc(googleSearchUrl)}" target="_blank" rel="noopener" style="color:#2563eb; text-decoration:none; font-size:0.75rem; font-weight:700; background:#eff6ff; padding:2px 6px; border-radius:4px; display:inline-block;">🔍 Verify Google</a>
                        </td>
                        <td>${socialLinks}</td>
                        <td>${esc(p.email)}</td>
                        <td><span style="font-size:0.82rem; color:#6b7280;">${esc(p.description)}</span></td>
                        <td>${actionLinks}</td>
                    `;
                    resultsBody.appendChild(tr);
                });

                document.querySelectorAll('.btn-action-draft').forEach(btn => {
                    btn.addEventListener('click', () => {
                        handleDraftClick(searchLeads[parseInt(btn.dataset.idx)], btn);
                    });
                });
                
                document.querySelectorAll('.btn-action-draft-dm').forEach(btn => {
                    btn.addEventListener('click', () => {
                        handleDraftDMClick(searchLeads[parseInt(btn.dataset.idx)], btn);
                    });
                });

                document.querySelectorAll('.btn-action-save').forEach(btn => {
                    btn.addEventListener('click', () => {
                        handleSaveLeadClick(searchLeads[parseInt(btn.dataset.idx)], btn);
                    });
                });

                document.querySelectorAll('.chk-lead').forEach(chk => {
                    chk.addEventListener('change', updateBulkState);
                });

                resultsCard.style.display = 'block';
            }

            // ── Search Providers ──────────────────────────────────────────
            btnSearch.addEventListener('click', async () => {
                const cat   = catInput.value.trim(), loc = locInput.value.trim();
                const count = document.getElementById('outreach-count').value;
                log("Search clicked: " + cat + " in " + loc + " (Count: " + count + ")");
                if (!cat || !loc) return;

                btnSearch.disabled = true;
                btnSearch.innerHTML = 'Searching<span class="outreach-spinner"></span>';
                resultsCard.style.display = 'none';
                resultsBody.innerHTML = '';
                searchLeads = [];
                updateBulkState();

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_search');
                    fd.append('nonce', NONCE);
                    fd.append('category', cat);
                    fd.append('location', loc);
                    fd.append('count', count);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    if (s === -1 || e === -1) throw new Error("Invalid response");
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success && data.data.providers) {
                        searchLeads = data.data.providers;
                        log("Found " + searchLeads.length + " leads.");
                        renderTable();
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to retrieve providers.'));
                    }
                } catch (e) {
                    log("Error: " + e.message);
                    alert('Search failed. Please try again.');
                } finally {
                    btnSearch.disabled = false;
                    btnSearch.textContent = '🔍 Search Providers';
                }
            });

            // ── Bulk Selection ─────────────────────────────────────────────
            chkSelectAll.addEventListener('change', () => {
                document.querySelectorAll('.chk-lead').forEach(chk => chk.checked = chkSelectAll.checked);
                updateBulkState();
            });

            function updateBulkState() {
                const checked = document.querySelectorAll('.chk-lead:checked');
                selCountEl.textContent = checked.length;
                btnBulkSend.disabled = checked.length === 0;
            }

            // ── Bulk Outreach Handler ──────────────────────────────────────
            btnBulkSend.addEventListener('click', async () => {
                const checked = Array.from(document.querySelectorAll('.chk-lead:checked'));
                if (!checked.length) return;
                if (!confirm(`Are you sure you want to generate and send personalized pitches to all ${checked.length} selected leads?`)) return;

                btnBulkSend.disabled = true;
                const origBtnText = btnBulkSend.textContent;

                for (let i = 0; i < checked.length; i++) {
                    const idx = parseInt(checked[i].dataset.idx);
                    const lead = searchLeads[idx];
                    btnBulkSend.innerHTML = `⏳ Sending ${i+1}/${checked.length}: ${esc(lead.name)}...`;
                    log(`Bulk (${i+1}/${checked.length}): Drafting and sending pitch to ${lead.name}...`);

                    try {
                        // Draft
                        const fd1 = new FormData();
                        fd1.append('action', 'obenlo_outreach_draft');
                        fd1.append('nonce', NONCE);
                        fd1.append('name', lead.name);
                        fd1.append('niche', lead.niche);
                        fd1.append('website', lead.website);
                        fd1.append('description', lead.description);

                        const res1 = await fetch(AJAX_URL, { method: 'POST', body: fd1 });
                        const text1 = await res1.text();
                        const s1 = text1.indexOf('{'), e1 = text1.lastIndexOf('}');
                        const data1 = JSON.parse(text1.substring(s1, e1 + 1));

                        if (data1.success) {
                            // Send
                            const fd2 = new FormData();
                            fd2.append('action', 'obenlo_outreach_send');
                            fd2.append('nonce', NONCE);
                            fd2.append('name', lead.name);
                            fd2.append('niche', lead.niche);
                            fd2.append('email', lead.email);
                            fd2.append('subject', data1.data.subject);
                            fd2.append('body', data1.data.body);

                            const res2 = await fetch(AJAX_URL, { method: 'POST', body: fd2 });
                            const text2 = await res2.text();
                            const s2 = text2.indexOf('{'), e2 = text2.lastIndexOf('}');
                            const data2 = JSON.parse(text2.substring(s2, e2 + 1));
                            if (data2.success && data2.data.history) {
                                renderHistory(data2.data.history);
                            }
                        }
                    } catch(err) {
                        log("Bulk error for " + lead.name + ": " + err.message);
                    }
                }

                btnBulkSend.disabled = false;
                btnBulkSend.textContent = origBtnText;
                alert('⚡ Bulk outreach campaign completed!');
                loadHistory();
            });

            // ── Single Draft Click ─────────────────────────────────────────
            async function handleDraftClick(lead, btn) {
                currentLead = lead;
                isFollowup = false;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                modalTitle.textContent = '✉️ Draft Outreach Pitch';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_draft');
                    fd.append('nonce', NONCE);
                    fd.append('name', lead.name);
                    fd.append('niche', lead.niche);
                    fd.append('website', lead.website);
                    fd.append('description', lead.description);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        toInput.value = lead.email;
                        document.getElementById('outreach-subject-container').style.display = 'block';
                        subjectInput.value = data.data.subject;
                        bodyText.value = data.data.body;
                        document.getElementById('modal-footer-email').style.display = 'flex';
                        document.getElementById('modal-footer-dm').style.display = 'none';
                        modal.classList.add('open');
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to draft email.'));
                    }
                } catch (e) {
                    alert('Error while drafting pitch.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            }

            // ── Single Draft DM Click ─────────────────────────────────────────
            async function handleDraftDMClick(lead, btn) {
                currentLead = lead;
                isFollowup = false;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                modalTitle.textContent = '📱 Draft DM Pitch';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_draft_dm');
                    fd.append('nonce', NONCE);
                    fd.append('name', lead.name);
                    fd.append('niche', lead.niche);
                    fd.append('website', lead.website);
                    fd.append('description', lead.description);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        toInput.value = lead.instagram || lead.facebook || 'No social URL found';
                        document.getElementById('outreach-subject-container').style.display = 'none';
                        subjectInput.value = '';
                        bodyText.value = data.data.body;
                        document.getElementById('modal-footer-email').style.display = 'none';
                        document.getElementById('modal-footer-dm').style.display = 'flex';
                        modal.classList.add('open');
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to draft DM.'));
                    }
                } catch (e) {
                    alert('Error while drafting DM.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            }

            // ── Save Lead Click ──────────────────────────────────────────────
            async function handleSaveLeadClick(lead, btn) {
                btn.disabled = true;
                const origText = btn.innerHTML;
                btn.innerHTML = '⏳ Saving...';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_save_lead');
                    fd.append('nonce', NONCE);
                    fd.append('name', lead.name || '');
                    fd.append('niche', lead.niche || '');
                    fd.append('email', lead.email || '');
                    fd.append('website', lead.website || '');
                    fd.append('facebook', lead.facebook || '');
                    fd.append('instagram', lead.instagram || '');
                    fd.append('description', lead.description || '');

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        btn.innerHTML = '✅ Saved';
                        btn.style.background = '#dcfce7';
                        btn.style.color = '#15803d';
                        btn.style.borderColor = '#86efac';
                        // Refresh CRM leads if needed
                        if (typeof loadCRMLeads === 'function') {
                            loadCRMLeads();
                        }
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to save lead.'));
                        btn.disabled = false;
                        btn.innerHTML = origText;
                    }
                } catch (e) {
                    alert('Error while saving lead.');
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            }

            // ── Follow-Up Click ───────────────────────────────────────────
            async function handleFollowupClick(item, btn) {
                currentLead = item;
                isFollowup = true;
                btn.disabled = true;
                const origText = btn.textContent;
                btn.textContent = '⏳ Drafting...';
                modalTitle.textContent = '🔄 Draft Follow-Up Pitch';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_followup_draft');
                    fd.append('nonce', NONCE);
                    fd.append('name', item.name);
                    fd.append('niche', item.niche);

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        toInput.value = item.email;
                        subjectInput.value = data.data.subject;
                        bodyText.value = data.data.body;
                        modal.classList.add('open');
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to draft follow-up.'));
                    }
                } catch (e) {
                    alert('Error while drafting follow-up.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            }

            modalClose.addEventListener('click', () => modal.classList.remove('open'));

            btnCopy.addEventListener('click', () => {
                bodyText.select();
                document.execCommand('copy');
                btnCopy.textContent = '📋 Copied!';
                setTimeout(() => { btnCopy.textContent = '📋 Copy Text'; }, 2000);
            });

            // ── Send Email Handler ────────────────────────────────────────
            btnSend.addEventListener('click', async () => {
                const to = toInput.value.trim(), subj = subjectInput.value.trim(), body = bodyText.value.trim();
                if (!to || !subj || !body) return;

                btnSend.disabled = true;
                btnSend.innerHTML = 'Sending<span class="outreach-spinner"></span>';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_send');
                    fd.append('nonce', NONCE);
                    fd.append('name', currentLead ? currentLead.name : 'Provider');
                    fd.append('niche', currentLead ? currentLead.niche : 'Service');
                    fd.append('email', to);
                    fd.append('subject', subj);
                    fd.append('body', body);
                    if (isFollowup) fd.append('is_followup', '1');

                    const res  = await fetch(AJAX_URL, { method: 'POST', body: fd });
                    const text = await res.text();
                    const s = text.indexOf('{'), e = text.lastIndexOf('}');
                    const data = JSON.parse(text.substring(s, e + 1));

                    if (data.success) {
                        alert(data.data.message || '🚀 Email processed successfully!');
                        modal.classList.remove('open');
                        if (data.data.history) renderHistory(data.data.history);
                    } else {
                        alert('Error: ' + (data.data?.message || 'Failed to send email.'));
                    }
                } catch (e) {
                    alert('Network error while sending email.');
                } finally {
                    btnSend.disabled = false;
                    btnSend.textContent = '🚀 Send Pitch';
                }
            });

            btnClearHist.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to clear outreach history?')) return;
                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_clear_history');
                    fd.append('nonce', NONCE);
                    await fetch(AJAX_URL, { method: 'POST', body: fd });
                    loadHistory();
                } catch(e) { console.error(e); }
            });

            // ── Modal Handlers for DM & Custom Leads ───────────────────────
            document.getElementById('btn-copy-dm').addEventListener('click', () => {
                bodyText.select();
                document.execCommand('copy');
                const btn = document.getElementById('btn-copy-dm');
                btn.textContent = '📋 Copied!';
                setTimeout(() => { btn.textContent = '📋 Copy Text'; }, 2000);
            });

            document.getElementById('btn-open-social').addEventListener('click', () => {
                bodyText.select();
                document.execCommand('copy');
                let socialUrl = toInput.value.trim();
                
                if (socialUrl && socialUrl.startsWith('http')) {
                    // Deep-link to Facebook Messenger
                    if (socialUrl.includes('facebook.com')) {
                        // Extract username and convert to m.me/username
                        let parts = socialUrl.split('facebook.com/');
                        if (parts.length > 1) {
                            let username = parts[1].replace(/\/$/, '');
                            socialUrl = 'https://m.me/' + username;
                        }
                    } 
                    // Deep-link to Instagram Direct (Works best on mobile)
                    else if (socialUrl.includes('instagram.com')) {
                        let parts = socialUrl.split('instagram.com/');
                        if (parts.length > 1) {
                            let username = parts[1].replace(/\/$/, '');
                            socialUrl = 'https://ig.me/m/' + username;
                        }
                    }

                    // Change button text to show it copied
                    const btn = document.getElementById('btn-open-social');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '📋 Copied! Opening Chat...';
                    setTimeout(() => { btn.innerHTML = originalText; }, 3000);

                    window.open(socialUrl, '_blank');
                    modal.classList.remove('open');
                } else {
                    alert('No valid social media URL found for this lead.');
                }
            });

            const customLeadModal = document.getElementById('custom-lead-modal');
            
            document.getElementById('btn-add-custom-lead').addEventListener('click', () => {
                customLeadModal.style.display = 'block';
                customLeadModal.classList.add('open');
            });
            
            document.getElementById('btn-custom-lead-close').addEventListener('click', () => {
                customLeadModal.style.display = 'none';
                customLeadModal.classList.remove('open');
            });

            document.getElementById('btn-save-custom-lead').addEventListener('click', () => {
                const name = document.getElementById('cl-name').value.trim();
                const niche = document.getElementById('cl-niche').value.trim();
                const social = document.getElementById('cl-social').value.trim();
                const email = document.getElementById('cl-email').value.trim();
                const website = document.getElementById('cl-website').value.trim();
                const desc = document.getElementById('cl-desc').value.trim();

                if (!name || !niche) {
                    alert('Please enter at least a Business Name and Niche.');
                    return;
                }

                const newLead = {
                    name: name,
                    niche: niche,
                    facebook: social && social.includes('facebook') ? social : '',
                    instagram: social && social.includes('instagram') ? social : (!social.includes('facebook') ? social : ''),
                    email: email,
                    website: website,
                    phone: '',
                    description: desc || 'Custom added lead.'
                };

                searchLeads.unshift(newLead); // add to top
                renderTable();
                
                customLeadModal.style.display = 'none';
                customLeadModal.classList.remove('open');
                
                document.getElementById('cl-name').value = '';
                document.getElementById('cl-niche').value = '';
                document.getElementById('cl-social').value = '';
                document.getElementById('cl-email').value = '';
                document.getElementById('cl-website').value = '';
                document.getElementById('cl-desc').value = '';
            });

            function esc(str) {
                if (!str) return '';
                return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
        })();
        </script>
        <?php
    }

    public function render_crm_page() {
        ?>
        <div class="wrap" style="font-family: 'Inter', sans-serif;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="font-size: 2rem; font-weight: 800; color: #111827;">📈 My CRM</h1>
                <button id="btn-launch-campaign" class="button button-primary button-large" style="background: #e61e4d; border-color: #e61e4d; border-radius: 8px; font-weight: 600; padding: 0 24px; box-shadow: 0 4px 6px rgba(230,30,77,0.2);">
                    🚀 Launch Automated Email Campaign
                </button>
            </div>

            <div style="background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">
                <table class="wp-list-table widefat fixed striped table-view-list" style="border: none;">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>
                            <th scope="col" style="font-weight: 700; color: #374151;">Provider Name</th>
                            <th scope="col" style="font-weight: 700; color: #374151;">Niche</th>
                            <th scope="col" style="font-weight: 700; color: #374151;">Contact</th>
                            <th scope="col" style="font-weight: 700; color: #374151;">Status</th>
                            <th scope="col" style="font-weight: 700; color: #374151;">Date Added</th>
                            <th scope="col" style="font-weight: 700; color: #374151; width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="crm-leads-table">
                        <tr><td colspan="7" style="text-align: center; padding: 20px;">Loading CRM leads...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Campaign Progress Modal -->
            <div id="campaign-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999999; align-items:center; justify-content:center;">
                <div style="background:#fff; padding:32px; border-radius:16px; width:400px; text-align:center; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);">
                    <h2 style="margin-top:0; font-size:1.5rem; font-weight:800; color:#111827;">🚀 Launching Campaign</h2>
                    <p style="color:#4b5563; margin-bottom:24px;">The AI is deeply researching each lead and firing off hyper-personalized emails. Please do not close this page.</p>
                    <div style="background:#f3f4f6; border-radius:8px; height:24px; width:100%; overflow:hidden; position:relative; margin-bottom:12px;">
                        <div id="campaign-progress-bar" style="background:#e61e4d; height:100%; width:0%; transition:width 0.3s ease;"></div>
                    </div>
                    <p id="campaign-status-text" style="font-weight:600; color:#111827;">0 / 0 Emails Sent</p>
                    <button id="btn-close-campaign" class="button" style="display:none; margin-top:16px; border-radius:6px;">Close</button>
                </div>
            </div>
        </div>

        <script>
            const CRM_AJAX_URL = "<?php echo admin_url('admin-ajax.php'); ?>";
            const CRM_NONCE = "<?php echo wp_create_nonce('obenlo_ai_outreach_nonce'); ?>";
            let crmLeads = [];

            function loadLeads() {
                const tbody = document.getElementById('crm-leads-table');
                if (!tbody) return;

                const fd = new FormData();
                fd.append('action', 'obenlo_outreach_get_crm_leads');
                fd.append('nonce', CRM_NONCE);

                fetch(CRM_AJAX_URL, { method: 'POST', body: fd })
                    .then(res => res.text())
                    .then(text => {
                        try {
                            const s = text.indexOf('{'), e = text.lastIndexOf('}');
                            if (s === -1 || e === -1) throw new Error('Invalid JSON response');
                            const data = JSON.parse(text.substring(s, e + 1));
                            if (data.success) {
                                crmLeads = data.data;
                                renderLeads();
                            } else {
                                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#ef4444;">Error loading leads: ' + (data.data?.message || 'Unknown error') + '</td></tr>';
                            }
                        } catch (err) {
                            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#ef4444;">Error parsing response. Please refresh the page.</td></tr>';
                            console.error('CRM leads parse error:', err, text);
                        }
                    })
                    .catch(err => {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px; color:#ef4444;">Network error loading leads. Please refresh the page.</td></tr>';
                        console.error('CRM leads fetch error:', err);
                    });
            }

            // Expose for external calls (e.g., from save lead)
            window.loadCRMLeads = loadLeads;

            function renderLeads() {
                const tbody = document.getElementById('crm-leads-table');
                tbody.innerHTML = '';
                if (crmLeads.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">No leads found. Go to the Outreach Agent to search and save leads!</td></tr>';
                    return;
                }

                crmLeads.forEach(lead => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <th scope="row" class="check-column"><input type="checkbox" class="lead-checkbox" value="${lead.id}"></th>
                        <td><strong>${lead.name}</strong></td>
                        <td>${lead.niche}</td>
                        <td>
                            ${lead.email ? \`<a href="mailto:${lead.email}">📧 Email</a><br>\` : ''}
                            ${lead.website ? \`<a href="${lead.website}" target="_blank">🌐 Website</a><br>\` : ''}
                            ${lead.phone ? \`📱 ${lead.phone}\` : ''}
                        </td>
                        <td>
                            <select onchange="updateLeadStatus(${lead.id}, this.value)" style="font-size:0.8rem; padding:2px 24px 2px 8px;">
                                <option value="New" ${lead.status==='New'?'selected':''}>New</option>
                                <option value="Contacted" ${lead.status==='Contacted'?'selected':''}>Contacted</option>
                                <option value="Replied" ${lead.status==='Replied'?'selected':''}>Replied</option>
                                <option value="Lost" ${lead.status==='Lost'?'selected':''}>Lost</option>
                            </select>
                        </td>
                        <td>${new Date(lead.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="button" onclick="alert('View details coming soon')">View</button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            window.updateLeadStatus = function(id, status) {
                const fd = new FormData();
                fd.append('action', 'obenlo_outreach_update_lead_status');
                fd.append('nonce', CRM_NONCE);
                fd.append('id', id);
                fd.append('status', status);
                fetch(CRM_AJAX_URL, { method: 'POST', body: fd }).then(() => loadLeads());
            };

            document.getElementById('cb-select-all-1')?.addEventListener('change', function(e) {
                document.querySelectorAll('.lead-checkbox').forEach(cb => cb.checked = e.target.checked);
            });

            // Bulk Campaign Logic
            document.getElementById('btn-launch-campaign')?.addEventListener('click', async function() {
                const selected = Array.from(document.querySelectorAll('.lead-checkbox:checked')).map(cb => parseInt(cb.value));
                if (selected.length === 0) {
                    alert('Please select at least one lead.');
                    return;
                }
                if (!confirm(`Are you sure you want to launch an autonomous campaign to ${selected.length} leads?`)) return;

                const modal = document.getElementById('campaign-modal');
                const bar = document.getElementById('campaign-progress-bar');
                const statusText = document.getElementById('campaign-status-text');
                const closeBtn = document.getElementById('btn-close-campaign');
                
                modal.style.display = 'flex';
                closeBtn.style.display = 'none';
                
                let successCount = 0;
                
                for (let i = 0; i < selected.length; i++) {
                    const leadId = selected[i];
                    const lead = crmLeads.find(l => parseInt(l.id) === leadId);
                    
                    if (!lead.email) {
                        console.log(`Skipping ${lead.name} (No Email)`);
                        continue;
                    }

                    statusText.textContent = `Drafting & Sending to ${lead.name}... (${i+1} / ${selected.length})`;
                    
                    const fd = new FormData();
                    fd.append('action', 'obenlo_outreach_send_bulk_email');
                    fd.append('nonce', CRM_NONCE);
                    fd.append('id', lead.id);
                    fd.append('name', lead.name);
                    fd.append('niche', lead.niche);
                    fd.append('email', lead.email);
                    fd.append('website', lead.website);
                    fd.append('description', lead.description);

                    try {
                        const res = await fetch(CRM_AJAX_URL, { method: 'POST', body: fd });
                        const text = await res.text();
                        const s = text.indexOf('{'), e = text.lastIndexOf('}');
                        const data = JSON.parse(text.substring(s, e + 1));
                        if (data.success && data.data.sent) {
                            successCount++;
                        }
                    } catch(e) { console.error(e); }

                    bar.style.width = Math.round(((i + 1) / selected.length) * 100) + '%';
                }

                statusText.textContent = `Campaign Complete! Sent ${successCount} emails.`;
                closeBtn.style.display = 'inline-block';
                closeBtn.onclick = () => {
                    modal.style.display = 'none';
                    loadLeads();
                };
            });

            document.addEventListener('DOMContentLoaded', () => {
                if(document.getElementById('crm-leads-table')) loadLeads();
            });
        </script>
        <?php
    }
}
