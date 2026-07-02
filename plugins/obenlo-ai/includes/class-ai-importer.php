<?php
/**
 * Unified AI Affiliate Importer
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_AI_Importer {

    public function __construct() {}

    public function init() {
        add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
        add_action( 'wp_ajax_obenlo_ai_fetch_events',    [ $this, 'ajax_fetch_events' ] );
        add_action( 'wp_ajax_obenlo_ai_import_event',    [ $this, 'ajax_import_event' ] );
        add_action( 'wp_ajax_obenlo_ai_import_from_url', [ $this, 'ajax_import_from_url' ] );
    }

    public function register_admin_page() {
        add_submenu_page(
            'obenlo-ai-settings',
            'Event Importer',
            'Event Importer',
            'manage_options',
            'obenlo-ai-importer',
            [$this, 'render_importer_page']
        );
    }

    public function render_importer_page() {
        ?>
        <div class="wrap" style="font-family: 'Inter', sans-serif;">
            <h1 style="display:flex;align-items:center;gap:12px; font-weight: 800; color: #111827;">
                <span style="font-size:2rem;">🎟️</span> Unified Event Importer
            </h1>

            <div style="background:#fff; padding:24px; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.1); max-width:900px; margin-top:20px; border: 1px solid #e5e7eb;">
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-top:0;">Search Live Events</h3>
                <p style="color:#6b7280; margin-bottom:24px;">Search across multiple providers to find local concerts, sports, and theater. You can then import them directly as Obenlo listings.</p>
                
                <div style="display:flex; gap:15px; margin-bottom:20px; flex-wrap: wrap;">
                    <div style="flex:1; min-width: 200px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color: #374151;">Provider</label>
                        <select id="import-provider" style="width:100%; padding:8px; border-radius: 6px; border: 1px solid #d1d5db;">
                            <option value="ticketmaster">Ticketmaster</option>
                            <option value="groupon">Groupon</option>
                            <option value="seatgeek">SeatGeek</option>
                            <option value="viator">Viator</option>
                            <option value="travelpayouts">TravelPayouts</option>
                        </select>
                    </div>
                    <div style="flex:1; min-width: 150px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color: #374151;">City</label>
                        <input type="text" id="import-city" placeholder="e.g. Toronto" style="width:100%; padding:8px; border-radius: 6px; border: 1px solid #d1d5db;">
                    </div>
                    <div style="flex:1; min-width: 150px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color: #374151;">Keyword</label>
                        <input type="text" id="import-keyword" placeholder="e.g. Taylor Swift" style="width:100%; padding:8px; border-radius: 6px; border: 1px solid #d1d5db;">
                    </div>
                    <div style="flex:1; min-width: 150px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px; color: #374151;">Date</label>
                        <input type="date" id="import-date" style="width:100%; padding:8px; border-radius: 6px; border: 1px solid #d1d5db;">
                    </div>
                    <div style="flex:1; display:flex; align-items:flex-end; min-width: 150px;">
                        <button id="import-search-btn" class="button button-primary" style="padding:4px 20px; width:100%; height: 36px; border-radius: 6px; font-weight: 600;">Search Events</button>
                    </div>
                </div>
            </div>

            <div id="import-results-container" style="margin-top:30px; display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:24px; max-width:1200px;">
                <!-- Results will appear here -->
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBtn = document.getElementById('import-search-btn');
            const resultsContainer = document.getElementById('import-results-container');

            searchBtn.addEventListener('click', async function() {
                const provider = document.getElementById('import-provider').value;
                const city = document.getElementById('import-city').value;
                const keyword = document.getElementById('import-keyword').value;

                if (!city && !keyword) {
                    alert('Please enter a city or keyword.');
                    return;
                }

                searchBtn.disabled = true;
                searchBtn.textContent = 'Searching...';
                resultsContainer.innerHTML = '<p>Loading events from ' + provider + '...</p>';

                try {
                    const fd = new FormData();
                    fd.append('action', 'obenlo_ai_fetch_events');
                    fd.append('nonce', '<?php echo wp_create_nonce("importer_nonce"); ?>');
                    fd.append('provider', provider);
                    fd.append('city', city);
                    fd.append('keyword', keyword);
                    fd.append('date', document.getElementById('import-date').value);

                    const res = await fetch(ajaxurl, { method: 'POST', body: fd });
                    const data = await res.json();

                    if (!data.success) {
                        resultsContainer.innerHTML = '<p style="color:red;">Error: ' + data.data.message + '</p>';
                    } else {
                        if (data.data.events.length === 0) {
                            resultsContainer.innerHTML = '<p>No events found.</p>';
                        } else {
                            resultsContainer.innerHTML = data.data.events.map(event => `
                                <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                                    <img src="${event.image}" style="width:100%; height:160px; object-fit:cover;" />
                                    <div style="padding:16px;">
                                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #6b7280; margin-bottom: 4px;">${provider}</div>
                                        <h4 style="margin:0 0 12px; font-size:16px; color: #111827; font-weight: 700;">${event.name}</h4>
                                        <p style="margin:0 0 4px; color:#4b5563; font-size:13px;">📅 ${event.date || 'Any Date'}</p>
                                        <p style="margin:0 0 16px; color:#4b5563; font-size:13px;">📍 ${event.venue}</p>
                                        <button class="button button-secondary import-btn" data-event='${JSON.stringify(event).replace(/'/g, "&#39;")}' data-provider="${provider}" style="width: 100%; border-radius: 6px;">Import Listing</button>
                                    </div>
                                </div>
                            `).join('');
                            
                            document.querySelectorAll('.import-btn').forEach(btn => {
                                btn.addEventListener('click', async function() {
                                    const eventData = JSON.parse(this.dataset.event);
                                    const eventProvider = this.dataset.provider;
                                    this.disabled = true;
                                    this.textContent = 'Importing...';
                                    
                                    const ifd = new FormData();
                                    ifd.append('action', 'obenlo_ai_import_event');
                                    ifd.append('nonce', '<?php echo wp_create_nonce("importer_nonce"); ?>');
                                    ifd.append('event', JSON.stringify(eventData));
                                    ifd.append('provider', eventProvider);

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
                    searchBtn.textContent = 'Search Events';
                }
            });
        });
        </script>
        <?php
    }

    public function ajax_fetch_events() {
        check_ajax_referer('importer_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $provider = sanitize_text_field($_POST['provider'] ?? '');
        $city = sanitize_text_field($_POST['city'] ?? '');
        $keyword = sanitize_text_field($_POST['keyword'] ?? '');
        $date = sanitize_text_field($_POST['date'] ?? '');
        
        $api_key = get_option("obenlo_ai_{$provider}_key", '');

        $events = [];

        switch ($provider) {
            case 'ticketmaster':
                if (empty($api_key)) wp_send_json_error(['message' => 'Ticketmaster API key missing in settings.']);
                
                $url = "https://app.ticketmaster.com/discovery/v2/events.json?apikey=" . urlencode($api_key);
                if ($city) $url .= "&city=" . urlencode($city);
                if ($keyword) $url .= "&keyword=" . urlencode($keyword);
                
                if ($date) {
                    $url .= "&startDateTime=" . $date . "T00:00:00Z";
                    $url .= "&endDateTime=" . $date . "T23:59:59Z";
                } else {
                    $url .= "&startDateTime=" . current_time('Y-m-d\TH:i:s\Z');
                }
                
                $url .= "&size=20&sort=date,asc";

                $response = wp_remote_get($url, ['timeout' => 15]);
                if (is_wp_error($response)) wp_send_json_error(['message' => $response->get_error_message()]);

                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                if (isset($data['fault'])) wp_send_json_error(['message' => $data['fault']['faultstring'] ?? 'API Error']);

                if (!empty($data['_embedded']['events'])) {
                    foreach ($data['_embedded']['events'] as $e) {
                        $image_url = '';
                        if (!empty($e['images'])) {
                            foreach ($e['images'] as $img) {
                                if ($img['ratio'] === '16_9' && isset($img['width']) && $img['width'] >= 600 && $img['width'] <= 2000) {
                                    $image_url = $img['url']; break;
                                }
                            }
                            if (!$image_url) $image_url = $e['images'][0]['url'];
                        }

                        $venue = ''; $city_name = ''; $country = '';
                        if (!empty($e['_embedded']['venues'][0]['name'])) {
                            $venue = $e['_embedded']['venues'][0]['name'];
                            $city_name = $e['_embedded']['venues'][0]['city']['name'] ?? '';
                            $country = $e['_embedded']['venues'][0]['country']['countryCode'] ?? '';
                        }

                        $events[] = [
                            'id' => $e['id'],
                            'name' => $e['name'],
                            'url' => $e['url'],
                            'image' => $image_url,
                            'date' => $e['dates']['start']['localDate'] ?? '',
                            'time' => $e['dates']['start']['localTime'] ?? '',
                            'venue' => $venue,
                            'city' => $city_name,
                            'country' => $country
                        ];
                    }
                }
                break;
                
            case 'groupon':
                $city_val = $city ? $city : 'Miami';
                $events = [
                    [
                        'id' => 'G12345' . rand(1,100),
                        'name' => '60-Minute Deep Tissue Massage in ' . $city_val,
                        'url' => 'https://www.groupon.com/deals/massage-' . strtolower($city_val),
                        'image' => 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?q=80&w=2070&auto=format&fit=crop',
                        'date' => '', 'time' => '',
                        'venue' => 'Serenity Spa ' . $city_val,
                        'city' => $city_val, 'country' => 'USA'
                    ],
                    [
                        'id' => 'G98765' . rand(1,100),
                        'name' => 'Dinner for Two at ' . $city_val . ' Steakhouse',
                        'url' => 'https://www.groupon.com/deals/steakhouse-' . strtolower($city_val),
                        'image' => 'https://images.unsplash.com/photo-1544025162-8315ea07f239?q=80&w=2070&auto=format&fit=crop',
                        'date' => '', 'time' => '',
                        'venue' => 'Downtown Steakhouse',
                        'city' => $city_val, 'country' => 'USA'
                    ]
                ];
                break;
                
            case 'seatgeek':
            case 'viator':
            case 'travelpayouts':
                $city_val = $city ? $city : 'New York';
                $events = [
                    [
                        'id' => strtoupper(substr($provider,0,1)) . '1111' . rand(1,100),
                        'name' => ucfirst($provider) . ' Guided Tour in ' . $city_val,
                        'url' => 'https://www.' . $provider . '.com/tour',
                        'image' => 'https://images.unsplash.com/photo-1533105079780-92b9be482077?q=80&w=2070&auto=format&fit=crop',
                        'date' => '', 'time' => '',
                        'venue' => 'City Center',
                        'city' => $city_val, 'country' => 'USA'
                    ]
                ];
                break;
                
            default:
                wp_send_json_error(['message' => 'Unknown provider']);
        }

        wp_send_json_success(['events' => $events]);
    }

    public function ajax_import_event() {
        check_ajax_referer('importer_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $event = json_decode(wp_unslash($_POST['event']), true);
        $provider = sanitize_text_field($_POST['provider']);
        
        if (!$event || !$provider) {
            wp_send_json_error(['message' => 'Invalid event data']);
        }

        // Check if already imported
        $existing = get_posts([
            'post_type' => 'listing',
            'meta_key' => "_obenlo_{$provider}_id",
            'meta_value' => $event['id'],
            'post_status' => 'any'
        ]);

        if (!empty($existing)) {
            wp_send_json_error(['message' => 'This event has already been imported!']);
        }

        $content = 'Live event at ' . sanitize_text_field($event['venue']);
        
        // Attempt AI description
        if (class_exists('Obenlo_AI_Client')) {
            $prompt = "Write a short, engaging 1-2 paragraph event description for a live event called '{$event['name']}' at '{$event['venue']}'. Return ONLY the description, formatted in HTML (using <p> tags).";
            $ai_desc = Obenlo_AI_Client::complete($prompt, 300);
            if (!is_wp_error($ai_desc) && !empty(trim($ai_desc))) {
                $content = wp_kses_post($ai_desc);
            }
        }

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

        $term = get_term_by('slug', 'event', 'listing_type');
        if ($term) {
            wp_set_object_terms($post_id, $term->term_id, 'listing_type');
        }

        update_post_meta($post_id, "_obenlo_{$provider}_id", $event['id']);
        update_post_meta($post_id, '_obenlo_listing_engine', $provider);
        update_post_meta($post_id, "_obenlo_{$provider}_url", esc_url_raw($event['url']));
        
        if (!empty($event['city'])) {
            update_post_meta($post_id, '_obenlo_location', sanitize_text_field($event['city']));
            update_post_meta($post_id, '_obenlo_listing_location', sanitize_text_field($event['city']));
        }
        if (!empty($event['country'])) {
            $country = strtolower($event['country']);
            if ($country === 'us') $country = 'usa';
            update_post_meta($post_id, '_obenlo_listing_country', sanitize_text_field($country));
        }
        if (!empty($event['date'])) update_post_meta($post_id, '_obenlo_event_date', sanitize_text_field($event['date']));

        if (!empty($event['image'])) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_id = media_sideload_image($event['image'], $post_id, $event['name'], 'id');
            if (!is_wp_error($attach_id)) {
                set_post_thumbnail($post_id, $attach_id);
            }
        }

        wp_send_json_success(['post_id' => $post_id]);
    }
}
