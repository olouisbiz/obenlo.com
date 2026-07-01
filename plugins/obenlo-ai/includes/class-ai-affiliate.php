<?php
/**
 * Obenlo AI Affiliate Importer
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_AI_Affiliate {

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_obenlo_ai_affiliate_search', [$this, 'handle_affiliate_search']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'obenlo-ai-settings',
            'Affiliate Importer',
            'Affiliate Importer',
            'manage_options',
            'obenlo-ai-affiliate',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <span style="font-size:2rem;">🌍</span> AI Affiliate Importer
            </h1>
            
            <div style="background:#fff; padding:30px; border-radius:16px; border:1px solid #e5e7eb; max-width:800px; box-shadow:0 2px 10px rgba(0,0,0,0.03);">
                <p style="font-size:1rem; color:#4b5563; margin-top:0; margin-bottom:20px; line-height:1.6;">
                    Since no official API keys (Viator/GetYourGuide) are configured yet, this tool uses <strong>Google Search AI</strong> to find external listings. 
                    It will automatically rewrite the descriptions for SEO and save them as Drafts using the Affiliate Booking Engine.
                </p>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#111;">What are you looking for?</label>
                    <input type="text" id="affiliate_search_query" placeholder="e.g., Top 3 snorkeling tours in Key West on Viator" style="width:100%; padding:14px; border-radius:10px; border:1px solid #d1d5db; font-size:1rem;">
                </div>

                <button id="btn_run_affiliate_search" style="background:linear-gradient(135deg, #7c3aed, #e61e4d); color:#fff; border:none; padding:14px 30px; border-radius:12px; font-weight:800; font-size:1rem; cursor:pointer; width:100%; box-shadow:0 4px 14px rgba(124,58,237,0.3);">
                    🔍 Find & Import Listings
                </button>

                <div id="affiliate_search_results" style="margin-top:30px; display:none;">
                    <h3 style="margin-bottom:15px; border-bottom:2px solid #f3f4f6; padding-bottom:10px;">Results Log</h3>
                    <div id="affiliate_search_log" style="background:#f9fafb; border:1px solid #e5e7eb; padding:20px; border-radius:10px; font-family:monospace; font-size:0.85rem; color:#374151; white-space:pre-wrap; max-height:400px; overflow-y:auto;"></div>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('btn_run_affiliate_search').addEventListener('click', async function() {
                var query = document.getElementById('affiliate_search_query').value.trim();
                if (!query) return alert('Please enter a search query.');

                var btn = this;
                var resultsDiv = document.getElementById('affiliate_search_results');
                var logDiv = document.getElementById('affiliate_search_log');

                btn.innerText = '⏳ Searching & Generating... this may take up to 30 seconds.';
                btn.style.opacity = '0.7';
                btn.disabled = true;
                resultsDiv.style.display = 'block';
                logDiv.innerHTML = 'Connecting to Google Search via Gemini API...\n';

                try {
                    var fd = new FormData();
                    fd.append('action', 'obenlo_ai_affiliate_search');
                    fd.append('query', query);
                    fd.append('nonce', '<?php echo wp_create_nonce("obenlo_ai_affiliate_nonce"); ?>');

                    var res = await fetch(ajaxurl, { method: 'POST', body: fd });
                    var data = await res.json();

                    if (data.success) {
                        logDiv.innerHTML += '\n' + data.data.log + '\n\n✅ Done! Check your Listings > Drafts.';
                    } else {
                        logDiv.innerHTML += '\n❌ Error: ' + (data.data?.message || 'Unknown error');
                    }
                } catch (e) {
                    logDiv.innerHTML += '\n❌ Request failed: ' + e.message;
                }

                btn.innerText = '🔍 Find & Import Listings';
                btn.style.opacity = '1';
                btn.disabled = false;
            });
        </script>
        <?php
    }

    public function handle_affiliate_search() {
        check_ajax_referer('obenlo_ai_affiliate_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $query = sanitize_text_field($_POST['query']);
        
        $prompt = "You are an AI Web Scraper. The user wants to find affiliate listings based on this query: '{$query}'.
        Use the Google Search tool to find 1 to 3 matching public listings (e.g., from Viator, TripAdvisor, or GetYourGuide).
        For each listing you find, you MUST rewrite the description to be unique, SEO-friendly, and engaging. DO NOT copy the exact original description.
        
        Return a JSON array of objects. Each object must have exactly these keys:
        - title: The title of the listing
        - url: The external booking URL where the user can buy it
        - price: The numeric starting price (just the number)
        - location: The city or region
        - description: Your newly rewritten, unique SEO description (at least 3 sentences)
        
        ONLY output valid JSON. No markdown wrappers. No explanations.";

        // Call the AI (Gemini has Google Search tool hardcoded in the client)
        $ai_response = Obenlo_AI_Client::complete($prompt, 2048);

        if (is_wp_error($ai_response)) {
            wp_send_json_error(['message' => $ai_response->get_error_message()]);
        }

        // Clean JSON
        $json_str = str_replace(['```json', '```'], '', $ai_response);
        $listings = json_decode(trim($json_str), true);

        if (!$listings || !is_array($listings)) {
            wp_send_json_error(['message' => 'Failed to parse AI response. Raw output: ' . $ai_response]);
        }

        $log = "";
        foreach ($listings as $item) {
            $title = sanitize_text_field($item['title'] ?? 'Untitled Affiliate Listing');
            $original_url = esc_url_raw($item['url'] ?? '');
            $price = floatval($item['price'] ?? 0);
            $location = sanitize_text_field($item['location'] ?? '');
            $desc = wp_kses_post($item['description'] ?? '');

            if (empty($original_url)) continue;

            $tp_marker = get_option('obenlo_ai_travelpayouts_marker', '');
            if (!empty($tp_marker)) {
                $p_arg = '';
                if (strpos($original_url, 'viator.com') !== false) {
                    $p_arg = '&p=' . esc_attr(get_option('obenlo_ai_tp_viator', ''));
                } elseif (strpos($original_url, 'tripadvisor.com') !== false) {
                    $p_arg = '&p=' . esc_attr(get_option('obenlo_ai_tp_tripadvisor', ''));
                } elseif (strpos($original_url, 'getyourguide.com') !== false) {
                    $p_arg = '&p=' . esc_attr(get_option('obenlo_ai_tp_gyg', ''));
                }
                
                // If we don't have a Program ID, TP will throw a "missing argument p" error. 
                // So we only generate a deep link if we successfully found a 'p' arg.
                if (!empty(trim($p_arg, '&p='))) {
                    $url = 'https://tp.media/r?marker=' . esc_attr($tp_marker) . $p_arg . '&u=' . urlencode($original_url);
                } else {
                    $url = $original_url;
                }
            } else {
                $url = $original_url;
            }

            $post_data = array(
                'post_title'   => $title,
                'post_content' => $desc,
                'post_status'  => 'draft',
                'post_type'    => 'listing',
            );

            $post_id = wp_insert_post($post_data);
            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_obenlo_price', $price);
                update_post_meta($post_id, '_obenlo_location', $location);
                // Assign the Affiliate engine!
                update_post_meta($post_id, '_obenlo_listing_engine', 'affiliate');
                update_post_meta($post_id, '_obenlo_affiliate_url', $url);

                $log .= "✅ Created Draft: {$title}\n   Price: \${$price}\n   URL: {$url}\n\n";
            }
        }

        if (empty($log)) {
            wp_send_json_error(['message' => 'No valid listings were found or created.']);
        }

        wp_send_json_success(['log' => $log]);
    }
}
