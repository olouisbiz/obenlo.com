<?php
/**
 * travelpayouts Discovery API Importer
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_AI_travelpayouts {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('wp_ajax_obenlo_ai_fetch_travelpayouts', [$this, 'ajax_fetch_events']);
        add_action('wp_ajax_obenlo_ai_import_travelpayouts', [$this, 'ajax_import_event']);
    }

    public function register_admin_page() {
        add_submenu_page(
            'obenlo-ai-settings',
            'travelpayouts Import',
            'travelpayouts Import',
            'manage_options',
            'obenlo-ai-travelpayouts',
            [$this, 'render_importer_page']
        );
    }

    public function render_importer_page() {
        $api_key = get_option('obenlo_ai_travelpayouts_key', '');
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">🎟️</span> travelpayouts API Importer
            </h1>

            <?php if (empty($api_key)): ?>
                <div class="notice notice-error"><p><strong>API Key Missing:</strong> You must enter your travelpayouts API Key in the <a href="<?php echo admin_url('admin.php?page=obenlo-ai-settings'); ?>">AI Settings</a> before you can import events.</p></div>
                <?php return; ?>
            <?php endif; ?>

            <div style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.1); max-width:800px; margin-top:20px;">
                <h3>Search Live Events</h3>
                <p style="color:#6b7280; margin-bottom:20px;">Search the travelpayouts Discovery API to find local concerts, sports, and theater. You can then import them directly as Obenlo listings.</p>
                
                <div style="display:flex; gap:15px; margin-bottom:20px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">City</label>
                        <input type="text" id="travelpayouts-city" placeholder="e.g. Toronto" style="width:100%; padding:8px;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Keyword (Optional)</label>
                        <input type="text" id="travelpayouts-keyword" placeholder="e.g. Taylor Swift" style="width:100%; padding:8px;">
                    </div>
                    <div style="flex:1; display:flex; align-items:flex-end;">
                        <button id="travelpayouts-search-btn" class="button button-primary" style="padding:4px 20px;">Search travelpayouts</button>
                    </div>
                </div>
            </div>

            <div id="travelpayouts-results-container" style="margin-top:30px; display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:20px;">
                <!-- Results will appear here -->
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBtn = document.getElementById('travelpayouts-search-btn');
            const resultsContainer = document.getElementById('travelpayouts-results-container');

            searchBtn.addEventListener('click', async function() {
                const city = document.getElementById('travelpayouts-city').value;
                const keyword = document.getElementById('travelpayouts-keyword').value;

                if (!city && !keyword) {
                    alert('Please enter a city or keyword.');
                    return;
                }

                searchBtn.disabled = true;
                searchBtn.textContent = 'Searching...';
                resultsContainer.innerHTML = '<p>Loading events from travelpayouts...</p>';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_ai_fetch_travelpayouts');
                    fd.append('nonce', '<?php echo wp_create_nonce("tm_nonce"); ?>');
                    fd.append('city', city);
                    fd.append('keyword', keyword);

                    const res = await fetch(ajaxurl, { method: 'POST', body: fd });
                    const data = await res.json();

                    if (!data.success) {
                        resultsContainer.innerHTML = '<p style="color:red;">Error: ' + data.data.message + '</p>';
                    } else {
                        if (data.data.events.length === 0) {
                            resultsContainer.innerHTML = '<p>No events found.</p>';
                        } else {
                            resultsContainer.innerHTML = data.data.events.map(event => `
                                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                                    <img src="${event.image}" style="width:100%; height:150px; object-fit:cover;" />
                                    <div style="padding:15px;">
                                        <h4 style="margin:0 0 10px; font-size:16px;">${event.name}</h4>
                                        <p style="margin:0 0 5px; color:#6b7280; font-size:13px;">📅 ${event.date}</p>
                                        <p style="margin:0 0 15px; color:#6b7280; font-size:13px;">📍 ${event.venue}</p>
                                        <button class="button button-secondary travelpayouts-import-btn" data-event='${JSON.stringify(event).replace(/'/g, "&#39;")}'>Import Listing</button>
                                    </div>
                                </div>
                            `).join('');
                            
                            document.querySelectorAll('.travelpayouts-import-btn').forEach(btn => {
                                btn.addEventListener('click', async function() {
                                    const eventData = JSON.parse(this.dataset.event);
                                    this.disabled = true;
                                    this.textContent = 'Importing...';
                                    
                                    const ifd = new FormData();
                                    ifd.append('action', 'obenlo_ai_import_travelpayouts');
                                    ifd.append('nonce', '<?php echo wp_create_nonce("tm_nonce"); ?>');
                                    ifd.append('event', JSON.stringify(eventData));

                                    const iRes = await fetch(ajaxurl, { method: 'POST', body: ifd });
                                    const iData = await iRes.json();

                                    if (iData.success) {
                                        this.textContent = '✅ Imported';
                                        this.classList.remove('button-secondary');
                                        this.classList.add('button-primary');
                                    } else {
                                        this.textContent = '❌ Error';
                                        alert(iData.data.message);
                                    }
                                });
                            });
                        }
                    }
                } catch (e) {
                    resultsContainer.innerHTML = '<p style="color:red;">Fetch failed: ' + e.message + '</p>';
                } finally {
                    searchBtn.disabled = false;
                    searchBtn.textContent = 'Search travelpayouts';
                }
            });
        });
        </script>
        <?php
    }

    public function ajax_fetch_events() {
        check_ajax_referer('tm_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $api_key = get_option('obenlo_ai_tp_token', '');
        $marker = get_option('obenlo_ai_tp_marker', '');
        $city = sanitize_text_field($_POST['city'] ?? 'London');
        $keyword = sanitize_text_field($_POST['keyword'] ?? '');

        // Mocking the Travelpayouts / Booking API response for the prototype
        $events = [
            [
                'id' => 'H12345',
                'name' => 'The Grand ' . $city . ' Hotel & Spa',
                'url' => 'https://search.hotellook.com/?marker=' . $marker . '&destination=' . strtolower($city),
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=2070&auto=format&fit=crop',
                'date' => '',
                'time' => '',
                'venue' => 'Downtown ' . $city,
                'city' => $city,
                'country' => 'UK'
            ],
            [
                'id' => 'H98765',
                'name' => $city . ' Boutique Suites',
                'url' => 'https://search.hotellook.com/?marker=' . $marker . '&destination=' . strtolower($city),
                'image' => 'https://images.unsplash.com/photo-1522798514-97ceb8c4f1c8?q=80&w=2070&auto=format&fit=crop',
                'date' => '',
                'time' => '',
                'venue' => 'City Center',
                'city' => $city,
                'country' => 'UK'
            ]
        ];

        wp_send_json_success(['events' => $events]);
    }

    public function ajax_import_event() {
        check_ajax_referer('tm_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $event = json_decode(wp_unslash($_POST['event']), true);
        if (!$event) {
            wp_send_json_error(['message' => 'Invalid event data']);
        }

        // Check if already imported
        $existing = get_posts([
            'post_type' => 'listing',
            'meta_key' => '_obenlo_travelpayouts_id',
            'meta_value' => $event['id'],
            'post_status' => 'any'
        ]);

        if (!empty($existing)) {
            wp_send_json_error(['message' => 'This event has already been imported!']);
        }

        $content = 'Live event at ' . sanitize_text_field($event['venue']);
        
        // Attempt to generate a better description using AI
        if (class_exists('Obenlo_AI_Client')) {
            $prompt = "Write a short, engaging 1-2 paragraph event description for a live event called '{$event['name']}' at '{$event['venue']}'. Return ONLY the description, formatted in HTML (using <p> tags).";
            $ai_desc = Obenlo_AI_Client::complete($prompt, 300);
            if (!is_wp_error($ai_desc) && !empty(trim($ai_desc))) {
                $content = wp_kses_post($ai_desc);
            }
        }

        // Create the listing
        $post_id = wp_insert_post([
            'post_title' => sanitize_text_field($event['name']),
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'listing',
            'post_author' => get_current_user_id()
        ]);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Failed to create post.']);
        }

        // Assign to Stays category if it exists
        $term = get_term_by('slug', 'stay', 'listing_type');
        if ($term) {
            wp_set_object_terms($post_id, $term->term_id, 'listing_type');
        }

        // Set Meta Data
        update_post_meta($post_id, '_obenlo_travelpayouts_id', $event['id']);
        update_post_meta($post_id, '_obenlo_listing_engine', 'travelpayouts');
        
        $marker = esc_attr(get_option('obenlo_ai_tp_marker', ''));
        // Generate a standard Travelpayouts Search Widget
        $widget_code = sprintf(
            '<script async src="https://tp.media/content?currency=usd&promo_id=4132&shmarker=%s&campaign_id=121&target_host=search.hotellook.com&locale=en&limit=10&powered_by=true&destination=%s"></script>',
            $marker,
            esc_attr($event['city'])
        );
        update_post_meta($post_id, '_obenlo_travelpayouts_widget_code', wp_slash($widget_code));
        if (!empty($event['city'])) {
            update_post_meta($post_id, '_obenlo_listing_location', sanitize_text_field($event['city']));
        }
        if (!empty($event['country'])) {
            // travelpayouts uses 'US', Obenlo uses 'usa' by default for US
            $country = strtolower($event['country']);
            if ($country === 'us') $country = 'usa';
            update_post_meta($post_id, '_obenlo_listing_country', sanitize_text_field($country));
        }
        
        if ($event['date']) {
            update_post_meta($post_id, '_obenlo_event_date', sanitize_text_field($event['date']));
        }

        // Sideload Image
        if ($event['image']) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_id = media_sideload_image($event['image'], $post_id, $event['name'], 'id');
            if (!is_wp_error($attach_id)) {
                set_post_thumbnail($post_id, $attach_id);
            } else {
                error_log('travelpayouts Image Import Failed: ' . $attach_id->get_error_message() . ' URL: ' . $event['image']);
            }
        }

        wp_send_json_success(['post_id' => $post_id]);
    }
}



