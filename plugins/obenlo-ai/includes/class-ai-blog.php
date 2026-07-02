<?php
/**
 * Obenlo AI Blog Generator
 *
 * Adds an admin interface to generate full blog posts using the Gemini API.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Blog {

    public function init() {
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
    }

    public function register_admin_menu() {
        add_submenu_page(
            'edit.php',
            'AI Blog Generator',
            'AI Blog Generator',
            'manage_options',
            'obenlo-ai-blog',
            [ $this, 'render_admin_page' ]
        );
    }

    public function render_admin_page() {
        $error = '';

        // Handle submission
        if ( isset( $_POST['obenlo_ai_blog_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['obenlo_ai_blog_nonce'] ) ), 'obenlo_ai_blog_action' ) ) {
            $topic = isset( $_POST['blog_topic'] ) ? sanitize_textarea_field( wp_unslash( $_POST['blog_topic'] ) ) : '';

            if ( empty( $topic ) ) {
                $error = 'Please enter a topic or idea.';
            } else {
                $result = $this->generate_and_draft_post( $topic );
                if ( is_wp_error( $result ) ) {
                    $error = $result->get_error_message();
                } else {
                    // Success! Redirect to the edit screen
                    $edit_url = admin_url( 'post.php?post=' . $result . '&action=edit' );
                    echo '<script>window.location.href="' . esc_url_raw( $edit_url ) . '";</script>';
                    exit;
                }
            }
        }

        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">✍️</span> Obenlo AI Blog Generator
            </h1>
            <p>Enter a topic, idea, or set of keywords below. The AI will research the topic on the web to ensure accuracy, write a fully formatted SEO-optimized blog post, and automatically save it as a draft for your review.</p>

            <?php if ( ! empty( $error ) ) : ?>
                <div class="notice notice-error is-dismissible">
                    <p><strong>Error:</strong> <?php echo esc_html( $error ); ?></p>
                </div>
            <?php endif; ?>

            <div style="background:#fff;border:1px solid #ccc;padding:20px;margin-top:20px;max-width:800px;border-radius:4px;">
                <form method="post" action="">
                    <?php wp_nonce_field( 'obenlo_ai_blog_action', 'obenlo_ai_blog_nonce' ); ?>
                    
                    <label for="blog_topic" style="font-weight:bold;display:block;margin-bottom:10px;">Blog Topic or Idea:</label>
                    <textarea name="blog_topic" id="blog_topic" rows="5" style="width:100%;font-size:16px;padding:10px;" placeholder="e.g. Top 5 Reasons to Hire a Local Handyman through Obenlo"></textarea>
                    
                    <p style="color:#666;font-size:13px;margin-bottom:20px;">The AI will use Google Search to verify facts before writing.</p>

                    <button type="submit" class="button button-primary button-hero" onclick="this.innerHTML='Generating (this may take a minute)...'; this.style.pointerEvents='none'; this.form.submit();">
                        Generate Blog Post
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    private function generate_and_draft_post( $topic ) {
        $platform_name = get_bloginfo( 'name' );

        $prompt = <<<PROMPT
You are the **SEO Content Expert** for {$platform_name}, a global service and experience marketplace.
Your ONLY mission is to research and write highly engaging, professional, and community-focused blog posts. You must strictly adhere to this expert persona. You do not write code, you only write top-tier SEO articles.
Your task is to write a highly engaging blog post about the following topic:
"{$topic}"

INSTRUCTIONS:
1. You MUST use your Google Search tool to research the topic on the live internet before writing to ensure all information is 100% accurate, verified, and up-to-date. Do not hallucinate facts.
2. The tone should be highly engaging, authoritative, and community-focused. Speak directly to the reader like a top-tier lifestyle and business blog.
3. CRITICAL: DO NOT use generic, robotic headers like "Introduction" or "Conclusion". Use creative, magazine-style headers.
4. CRITICAL: You MUST seamlessly weave {$platform_name} into the article. Position {$platform_name} as the ultimate solution for finding and booking these services locally. Mention that it is free to use and commission-based.
5. Use proper HTML formatting for the content (e.g., <h2>, <h3>, <p>, <ul>, <li>, <strong>). Do NOT wrap the content in Markdown code blocks (like ```html), just output raw text.
6. You MUST output your response using the exact tags below. Do not use JSON.

[TITLE]
Your Catchy SEO Title Here
[/TITLE]
[CONTENT]
<h2>Heading 1</h2>
<p>Paragraph 1...</p>
[/CONTENT]
PROMPT;

        $response = Obenlo_AI_Client::complete( $prompt, 6000 );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Parse using regex
        preg_match( '/\[TITLE\](.*?)\[\/TITLE\]/is', $response, $title_matches );
        preg_match( '/\[CONTENT\](.*?)\[\/CONTENT\]/is', $response, $content_matches );

        $title = ! empty( $title_matches[1] ) ? trim( $title_matches[1] ) : '';
        $content = ! empty( $content_matches[1] ) ? trim( $content_matches[1] ) : '';

        if ( empty( $title ) || empty( $content ) ) {
            return new WP_Error( 'parse_error', 'The AI generated an invalid format. Please try again. Raw output: ' . esc_html( substr( $response, 0, 200 ) ) . '...' );
        }

        // Create the post
        $post_data = [
            'post_title'   => sanitize_text_field( $title ),
            'post_content' => wp_kses_post( $content ),
            'post_status'  => 'draft',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id(),
        ];

        $post_id = wp_insert_post( $post_data, true );

        return $post_id;
    }
}
