<?php
/**
 * Obenlo AI Admin Settings
 *
 * Adds an "Obenlo AI" tab inside the existing Obenlo Admin Dashboard.
 * Also registers a standalone admin menu page as a fallback.
 *
 * Settings stored:
 *  - obenlo_ai_provider        (gemini|openai)
 *  - obenlo_ai_gemini_key      (string)
 *  - obenlo_ai_gemini_model    (string)
 *  - obenlo_ai_openai_key      (string)
 *  - obenlo_ai_openai_model    (string)
 *  - obenlo_ai_enable_chat     (yes|no)
 *  - obenlo_ai_enable_listing  (yes|no)
 *  - obenlo_ai_enable_search   (yes|no)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_AI_Admin {

    public function init() {
        // Standalone admin page (fallback / direct access)
        add_action( 'admin_menu', [ $this, 'register_admin_page' ] );

        // Handle settings save
        add_action( 'admin_post_obenlo_ai_save_settings', [ $this, 'handle_save_settings' ] );

        // AJAX: Connection test
        add_action( 'wp_ajax_obenlo_ai_test_connection', [ $this, 'handle_test_connection' ] );

        // Hook into the Obenlo Admin Dashboard tabs system (if available)
        add_filter( 'obenlo_admin_dashboard_tabs', [ $this, 'register_dashboard_tab' ], 20 );
        add_action( 'obenlo_admin_tab_ai', [ $this, 'render_settings_panel' ] );
    }

    // ── Admin Menu ────────────────────────────────────────────────────────

    public function register_admin_page() {
        add_menu_page(
            'Obenlo Agent Settings',
            'Obenlo Agent',
            'manage_options',
            'obenlo-ai-settings',
            [ $this, 'render_standalone_page' ],
            'dashicons-superhero',
            30
        );

        add_submenu_page(
            'obenlo-ai-settings',
            'Settings',
            'Settings',
            'manage_options',
            'obenlo-ai-settings',
            [ $this, 'render_standalone_page' ]
        );
    }

    public function render_standalone_page() {
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:2rem;">🤖</span> Obenlo Agent Settings
            </h1>
            <?php $this->render_settings_panel(); ?>
        </div>
        <?php
    }

    // ── Dashboard Tab Hook ────────────────────────────────────────────────

    public function register_dashboard_tab( $tabs ) {
        $tabs['ai'] = '🤖 Obenlo Agent Settings';
        return $tabs;
    }

    // ── Settings Panel ────────────────────────────────────────────────────

    public function render_settings_panel() {
        $provider       = get_option( 'obenlo_ai_provider', 'gemini' );
        $gemini_key     = get_option( 'obenlo_ai_gemini_key', '' );
        $gemini_model   = get_option( 'obenlo_ai_gemini_model', 'gemini-2.0-flash' );
        $openai_key     = get_option( 'obenlo_ai_openai_key', '' );
        $openai_model   = get_option( 'obenlo_ai_openai_model', 'gpt-4o-mini' );
        $groq_key       = get_option( 'obenlo_ai_groq_key', '' );
        $groq_model     = get_option( 'obenlo_ai_groq_model', 'llama-3.3-70b-versatile' );
        $enable_chat    = get_option( 'obenlo_ai_enable_chat', 'yes' );
        $enable_listing = get_option( 'obenlo_ai_enable_listing', 'yes' );
        $enable_search  = get_option( 'obenlo_ai_enable_search', 'yes' );
        $tp_marker      = get_option( 'obenlo_ai_travelpayouts_marker', '' );
        $tp_viator      = get_option( 'obenlo_ai_tp_viator', '' );
        $tp_tripadvisor = get_option( 'obenlo_ai_tp_tripadvisor', '' );
        $tp_gyg         = get_option( 'obenlo_ai_tp_gyg', '' );

        $saved = isset( $_GET['ai_saved'] ) && $_GET['ai_saved'] === '1';
        $error = isset( $_GET['ai_error'] ) ? urldecode( $_GET['ai_error'] ) : '';
        ?>
        <style>
            .obenlo-ai-settings { max-width: 860px; }
            .obenlo-ai-settings .ai-card {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                padding: 28px;
                margin-bottom: 24px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            }
            .obenlo-ai-settings .ai-card h3 {
                margin: 0 0 20px;
                font-size: 1.05rem;
                display: flex;
                align-items: center;
                gap: 10px;
                color: #111;
                padding-bottom: 14px;
                border-bottom: 2px solid #f3f4f6;
            }
            .obenlo-ai-settings label.ai-label {
                display: block;
                font-weight: 700;
                font-size: 0.85rem;
                color: #374151;
                margin-bottom: 6px;
                text-transform: uppercase;
                letter-spacing: 0.4px;
            }
            .obenlo-ai-settings input[type="text"],
            .obenlo-ai-settings input[type="password"],
            .obenlo-ai-settings select {
                width: 100%;
                padding: 11px 14px;
                border: 1px solid #d1d5db;
                border-radius: 10px;
                font-size: 0.95rem;
                outline: none;
                transition: border-color 0.2s;
                background: #f9fafb;
                box-sizing: border-box;
            }
            .obenlo-ai-settings input:focus,
            .obenlo-ai-settings select:focus {
                border-color: #7c3aed;
                background: #fff;
            }
            .obenlo-ai-settings .form-row { margin-bottom: 20px; }
            .obenlo-ai-settings .form-hint {
                font-size: 0.78rem;
                color: #9ca3af;
                margin-top: 5px;
            }
            .obenlo-ai-settings .ai-toggle-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 0;
                border-bottom: 1px solid #f3f4f6;
            }
            .obenlo-ai-settings .ai-toggle-row:last-child { border-bottom: none; }
            .obenlo-ai-settings .ai-toggle-label { font-weight: 600; color: #111; }
            .obenlo-ai-settings .ai-toggle-desc { font-size: 0.82rem; color: #6b7280; margin-top: 2px; }
            .obenlo-ai-settings .ai-switch {
                position: relative;
                width: 46px;
                height: 24px;
                flex-shrink: 0;
            }
            .obenlo-ai-settings .ai-switch input { opacity: 0; width: 0; height: 0; }
            .obenlo-ai-settings .ai-slider {
                position: absolute;
                cursor: pointer;
                inset: 0;
                background: #d1d5db;
                border-radius: 24px;
                transition: background 0.3s;
            }
            .obenlo-ai-settings .ai-slider:before {
                content: "";
                position: absolute;
                width: 16px;
                height: 16px;
                left: 4px;
                bottom: 4px;
                background: #fff;
                border-radius: 50%;
                transition: transform 0.3s;
            }
            .obenlo-ai-settings .ai-switch input:checked + .ai-slider { background: #7c3aed; }
            .obenlo-ai-settings .ai-switch input:checked + .ai-slider:before { transform: translateX(22px); }
            .obenlo-ai-settings .btn-save {
                background: linear-gradient(135deg, #7c3aed, #e61e4d);
                color: #fff;
                border: none;
                padding: 14px 40px;
                border-radius: 12px;
                font-weight: 800;
                font-size: 1rem;
                cursor: pointer;
                width: 100%;
                box-shadow: 0 4px 14px rgba(124,58,237,0.3);
                transition: opacity 0.2s;
            }
            .obenlo-ai-settings .btn-save:hover { opacity: 0.9; }
            .obenlo-ai-settings .btn-test {
                background: #f3f4f6;
                color: #374151;
                border: 1px solid #d1d5db;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 700;
                font-size: 0.85rem;
                cursor: pointer;
                margin-top: 10px;
                transition: background 0.15s;
            }
            .obenlo-ai-settings .btn-test:hover { background: #e5e7eb; }
            .obenlo-ai-settings .notice-success {
                background: #d1fae5;
                border: 1px solid #6ee7b7;
                border-radius: 10px;
                padding: 14px 18px;
                color: #065f46;
                font-weight: 700;
                margin-bottom: 20px;
            }
            .obenlo-ai-settings .notice-error {
                background: #fee2e2;
                border: 1px solid #fca5a5;
                border-radius: 10px;
                padding: 14px 18px;
                color: #991b1b;
                font-weight: 700;
                margin-bottom: 20px;
            }
            .obenlo-ai-settings .provider-section { display: none; }
            .obenlo-ai-settings .provider-section.active { display: block; }
            .obenlo-ai-settings .badge-free {
                background: #d1fae5;
                color: #065f46;
                font-size: 0.7rem;
                font-weight: 800;
                padding: 2px 8px;
                border-radius: 4px;
                text-transform: uppercase;
            }
            .obenlo-ai-settings .badge-paid {
                background: #fef3c7;
                color: #92400e;
                font-size: 0.7rem;
                font-weight: 800;
                padding: 2px 8px;
                border-radius: 4px;
                text-transform: uppercase;
            }
        </style>

        <div class="obenlo-ai-settings">

            <?php if ( $saved ): ?>
                <div class="notice-success">✅ AI settings saved successfully.</div>
            <?php endif; ?>

            <?php if ( $error ): ?>
                <div class="notice-error">⚠️ <?php echo esc_html( $error ); ?></div>
            <?php endif; ?>

            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
                <input type="hidden" name="action" value="obenlo_ai_save_settings">
                <?php wp_nonce_field( 'obenlo_ai_save_settings', 'ai_settings_nonce' ); ?>

                <!-- Provider Selection -->
                <div class="ai-card">
                    <h3>⚡ AI Provider</h3>
                    <div class="form-row">
                        <label class="ai-label">Active Provider</label>
                        <select name="obenlo_ai_provider" id="obenlo-ai-provider-select">
                            <option value="gemini" <?php selected( $provider, 'gemini' ); ?>>
                                Google Gemini
                            </option>
                            <option value="groq" <?php selected( $provider, 'groq' ); ?>>
                                Groq (Llama 3 — Free &amp; Fast)
                            </option>
                            <option value="openai" <?php selected( $provider, 'openai' ); ?>>
                                OpenAI (GPT)
                            </option>
                        </select>
                        <p class="form-hint">Gemini 2.0 Flash is free and recommended. Switch to OpenAI anytime without code changes.</p>
                    </div>
                </div>

                <!-- Gemini Settings -->
                <div class="ai-card" id="obenlo-ai-gemini-section">
                    <h3>🟣 Google Gemini <span class="badge-free">Free Tier Available</span></h3>
                    <div class="form-row">
                        <label class="ai-label">Gemini API Key</label>
                        <input
                            type="password"
                            name="obenlo_ai_gemini_key"
                            value="<?php echo esc_attr( $gemini_key ); ?>"
                            placeholder="AIza..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your free key at <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">aistudio.google.com</a> — no billing required.</p>
                    </div>
                    <div class="form-row">
                        <label class="ai-label">Model</label>
                        <select name="obenlo_ai_gemini_model">
                            <option value="gemini-2.0-flash" <?php selected( $gemini_model, 'gemini-2.0-flash' ); ?>>gemini-2.0-flash (Recommended — 1,500 free req/day)</option>
                            <option value="gemini-2.0-flash-lite" <?php selected( $gemini_model, 'gemini-2.0-flash-lite' ); ?>>gemini-2.0-flash-lite (Fastest / Lightest)</option>
                            <option value="gemini-2.5-flash-preview-05-20" <?php selected( $gemini_model, 'gemini-2.5-flash-preview-05-20' ); ?>>gemini-2.5-flash-preview (Smartest — 500 free req/day)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-test" id="obenlo-ai-test-gemini">🔌 Test Gemini Connection</button>
                    <span id="obenlo-ai-test-gemini-result" style="margin-left:12px; font-size:0.85rem; font-weight:700;"></span>
                </div>

                <!-- Groq Settings -->
                <div class="ai-card" id="obenlo-ai-groq-section">
                    <h3>⚡ Groq <span class="badge-free">Free &amp; Fast</span></h3>
                    <div class="form-row">
                        <label class="ai-label">Groq API Key</label>
                        <input
                            type="password"
                            name="obenlo_ai_groq_key"
                            value="<?php echo esc_attr( $groq_key ); ?>"
                            placeholder="gsk_..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your free key at <a href="https://console.groq.com/keys" target="_blank" rel="noopener">console.groq.com</a> — 14,400 req/day free, no billing required.</p>
                    </div>
                    <div class="form-row">
                        <label class="ai-label">Model</label>
                        <select name="obenlo_ai_groq_model">
                            <option value="llama-3.3-70b-versatile" <?php selected( $groq_model, 'llama-3.3-70b-versatile' ); ?>>llama-3.3-70b-versatile (Recommended — Best Quality)</option>
                            <option value="llama-3.1-8b-instant" <?php selected( $groq_model, 'llama-3.1-8b-instant' ); ?>>llama-3.1-8b-instant (Fastest)</option>
                            <option value="mixtral-8x7b-32768" <?php selected( $groq_model, 'mixtral-8x7b-32768' ); ?>>mixtral-8x7b-32768 (Long context)</option>
                            <option value="gemma2-9b-it" <?php selected( $groq_model, 'gemma2-9b-it' ); ?>>gemma2-9b-it (Google Gemma 2)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-test" id="obenlo-ai-test-groq">🔌 Test Groq Connection</button>
                    <span id="obenlo-ai-test-groq-result" style="margin-left:12px; font-size:0.85rem; font-weight:700;"></span>
                </div>

                <!-- OpenAI Settings -->
                <div class="ai-card" id="obenlo-ai-openai-section">
                    <h3>🟢 OpenAI (GPT) <span class="badge-paid">Paid</span></h3>
                    <div class="form-row">
                        <label class="ai-label">OpenAI API Key</label>
                        <input
                            type="password"
                            name="obenlo_ai_openai_key"
                            value="<?php echo esc_attr( $openai_key ); ?>"
                            placeholder="sk-..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your key at <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com</a>.</p>
                    </div>
                    <div class="form-row">
                        <label class="ai-label">Model</label>
                        <select name="obenlo_ai_openai_model">
                            <option value="gpt-4o-mini" <?php selected( $openai_model, 'gpt-4o-mini' ); ?>>gpt-4o-mini (Fast & affordable)</option>
                            <option value="gpt-4o" <?php selected( $openai_model, 'gpt-4o' ); ?>>gpt-4o (Most capable)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-test" id="obenlo-ai-test-openai">🔌 Test OpenAI Connection</button>
                    <span id="obenlo-ai-test-openai-result" style="margin-left:12px; font-size:0.85rem; font-weight:700;"></span>
                </div>

                <!-- Affiliate Settings -->
                <div class="ai-card" id="obenlo-ai-affiliate-section">
                    <h3>🤝 Travelpayouts Affiliate</h3>
                    <div class="form-row">
                        <label class="ai-label">Travelpayouts Marker (Affiliate ID)</label>
                        <input
                            type="text"
                            name="obenlo_ai_travelpayouts_marker"
                            value="<?php echo esc_attr( $tp_marker ); ?>"
                            placeholder="e.g. 719345"
                        />
                        <p class="form-hint">Your unique Travelpayouts numeric ID. Required for tracking.</p>
                    </div>
                    <div style="display:flex; gap:15px;">
                        <div class="form-row" style="flex:1;">
                            <label class="ai-label">Viator Program ID (p)</label>
                            <input type="text" name="obenlo_ai_tp_viator" value="<?php echo esc_attr( $tp_viator ); ?>" placeholder="e.g. 4181">
                        </div>
                        <div class="form-row" style="flex:1;">
                            <label class="ai-label">TripAdvisor Program ID (p)</label>
                            <input type="text" name="obenlo_ai_tp_tripadvisor" value="<?php echo esc_attr( $tp_tripadvisor ); ?>" placeholder="e.g. 4232">
                        </div>
                        <div class="form-row" style="flex:1;">
                            <label class="ai-label">GetYourGuide Program ID (p)</label>
                            <input type="text" name="obenlo_ai_tp_gyg" value="<?php echo esc_attr( $tp_gyg ); ?>" placeholder="e.g. 3960">
                        </div>
                    </div>
                    <p class="form-hint">Travelpayouts requires a specific Program ID <code>(p)</code> for each network to generate deep links correctly.</p>
                </div>

                <!-- Ticketmaster API -->
                <div class="ai-card" id="obenlo-ai-ticketmaster-section">
                    <h3>🎟️ Ticketmaster API</h3>
                    <div class="form-row">
                        <label class="ai-label">Ticketmaster Developer API Key</label>
                        <input
                            type="password"
                            name="obenlo_ai_ticketmaster_key"
                            value="<?php echo esc_attr( get_option('obenlo_ai_ticketmaster_key', '') ); ?>"
                            placeholder="e.g. ABcdEfG12345..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your free key at <a href="https://developer.ticketmaster.com/" target="_blank" rel="noopener">developer.ticketmaster.com</a>. This is required to fetch and import live events.</p>
                    </div>
                </div>

                <!-- SeatGeek API -->
                <div class="ai-card" id="obenlo-ai-seatgeek-section">
                    <h3>🎟️ SeatGeek API</h3>
                    <div class="form-row">
                        <label class="ai-label">SeatGeek Client ID (API Key)</label>
                        <input
                            type="password"
                            name="obenlo_ai_seatgeek_key"
                            value="<?php echo esc_attr( get_option('obenlo_ai_seatgeek_key', '') ); ?>"
                            placeholder="e.g. MTIzNDU2Nzg5..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your free Client ID at <a href="https://seatgeek.com/account/develop" target="_blank" rel="noopener">seatgeek.com/account/develop</a>. Required to fetch indie events and sports.</p>
                    </div>
                </div>

                <!-- Viator API -->
                <div class="ai-card" id="obenlo-ai-viator-section">
                    <h3>🗺️ Viator API</h3>
                    <div class="form-row">
                        <label class="ai-label">Viator API Key</label>
                        <input
                            type="password"
                            name="obenlo_ai_viator_key"
                            value="<?php echo esc_attr( get_option('obenlo_ai_viator_key', '') ); ?>"
                            placeholder="e.g. 12345678-abcd-..."
                            autocomplete="new-password"
                        />
                        <p class="form-hint">Get your free API Key at <a href="https://partner.viator.com/" target="_blank" rel="noopener">partner.viator.com</a>. Required to fetch local tours and experiences.</p>
                    </div>
                </div>

                <!-- Travelpayouts API -->
                <div class="ai-card" id="obenlo-ai-travelpayouts-section">
                    <h3>🏨 Travelpayouts API (Hotels & Flights)</h3>
                    <div class="form-row">
                        <label class="ai-label">Travelpayouts Affiliate Marker (ID)</label>
                        <input type="text" name="obenlo_ai_tp_marker" value="<?php echo esc_attr( get_option('obenlo_ai_tp_marker', '') ); ?>" placeholder="e.g. 123456" class="ai-input" />
                        <label class="ai-label" style="margin-top:15px;">Travelpayouts API Token</label>
                        <input type="password" name="obenlo_ai_tp_token" value="<?php echo esc_attr( get_option('obenlo_ai_tp_token', '') ); ?>" placeholder="e.g. abcdef123456..." autocomplete="new-password" class="ai-input" />
                        <p class="form-hint">Get your Token and Marker at <a href="https://www.travelpayouts.com/" target="_blank" rel="noopener">travelpayouts.com</a>. Required for the Hotel Importer.</p>
                    </div>
                </div>

                <!-- Groupon / CJ Affiliate API -->
                <div class="ai-card" id="obenlo-ai-groupon-section">
                    <h3>💆 Groupon API (Local Deals)</h3>
                    <div class="form-row">
                        <label class="ai-label">CJ Affiliate Personal Access Token</label>
                        <input type="password" name="obenlo_ai_groupon_key" value="<?php echo esc_attr( get_option('obenlo_ai_groupon_key', '') ); ?>" placeholder="e.g. Bearer abcdef123456..." autocomplete="new-password" class="ai-input" />
                        <p class="form-hint">Get your API Token at <a href="https://developers.cj.com/" target="_blank" rel="noopener">developers.cj.com</a>. Required to fetch Groupon local deals and spas.</p>
                    </div>
                </div>

                <!-- Featured Widgets -->
                <div class="ai-card" id="obenlo-ai-widgets-section">
                    <h3>🎟️ Global Widgets</h3>
                    <div class="form-row">
                        <label class="ai-label">Front Page Viator Widget Code</label>
                        <textarea
                            name="obenlo_ai_front_page_viator"
                            rows="4"
                            placeholder="<div data-vi-partner-id=...></div><script async src=...></script>"
                            style="width:100%; padding:11px 14px; border:1px solid #d1d5db; border-radius:10px; font-size:0.95rem; font-family:monospace; outline:none; background:#f9fafb; box-sizing:border-box; margin-bottom: 20px;"
                        ><?php echo esc_textarea( get_option( 'obenlo_ai_front_page_viator', '' ) ); ?></textarea>
                        
                        <label class="ai-label">Front Page Travelpayouts Widget Code</label>
                        <textarea
                            name="obenlo_ai_front_page_travelpayouts"
                            rows="4"
                            placeholder="<script async src=...></script>"
                            style="width:100%; padding:11px 14px; border:1px solid #d1d5db; border-radius:10px; font-size:0.95rem; font-family:monospace; outline:none; background:#f9fafb; box-sizing:border-box;"
                        ><?php echo esc_textarea( get_option( 'obenlo_ai_front_page_travelpayouts', '' ) ); ?></textarea>
                        <p class="form-hint">Paste your Viator and/or Travelpayouts widget scripts here. They will automatically be displayed in featured sections on the Obenlo Front Page.</p>
                    </div>
                </div>

                <!-- Feature Toggles -->
                <div class="ai-card">
                    <h3>🎛️ Feature Toggles</h3>
                    <div class="ai-toggle-row">
                        <div>
                            <div class="ai-toggle-label">💬 AI Chat Reply Assistant</div>
                            <div class="ai-toggle-desc">Adds an "✨ AI Draft" button inside the host chat window to generate smart replies.</div>
                        </div>
                        <label class="ai-switch">
                            <input type="checkbox" name="obenlo_ai_enable_chat" value="yes" <?php checked( $enable_chat, 'yes' ); ?>>
                            <span class="ai-slider"></span>
                        </label>
                    </div>
                    <div class="ai-toggle-row">
                        <div>
                            <div class="ai-toggle-label">📝 AI Listing Optimizer</div>
                            <div class="ai-toggle-desc">Floating panel on listing forms to optimize descriptions, suggest titles, and generate SEO meta.</div>
                        </div>
                        <label class="ai-switch">
                            <input type="checkbox" name="obenlo_ai_enable_listing" value="yes" <?php checked( $enable_listing, 'yes' ); ?>>
                            <span class="ai-slider"></span>
                        </label>
                    </div>
                    <div class="ai-toggle-row">
                        <div>
                            <div class="ai-toggle-label">🔍 AI Natural Language Search</div>
                            <div class="ai-toggle-desc">Adds a floating "AI Search" button on the frontend — guests can search using natural language.</div>
                        </div>
                        <label class="ai-switch">
                            <input type="checkbox" name="obenlo_ai_enable_search" value="yes" <?php checked( $enable_search, 'yes' ); ?>>
                            <span class="ai-slider"></span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-save">💾 Save AI Settings</button>
            </form>
        </div>

        <script>
        (function () {
            const providerSelect = document.getElementById('obenlo-ai-provider-select');
            const geminiSection  = document.getElementById('obenlo-ai-gemini-section');
            const groqSection    = document.getElementById('obenlo-ai-groq-section');
            const openaiSection  = document.getElementById('obenlo-ai-openai-section');

            function toggleSections() {
                const v = providerSelect.value;
                geminiSection.style.display = (v === 'gemini') ? 'block' : 'none';
                groqSection.style.display   = (v === 'groq')   ? 'block' : 'none';
                openaiSection.style.display = (v === 'openai') ? 'block' : 'none';
            }

            providerSelect.addEventListener('change', toggleSections);
            toggleSections();

            // Test buttons — sends the key from the form so you can test before saving
            async function testConnection(provider, resultEl) {
                resultEl.textContent = '⏳ Testing...';
                resultEl.style.color = '#6b7280';

                // Read key & model directly from the form inputs
                let apiKey = '';
                let model  = '';
                if (provider === 'gemini') {
                    apiKey = document.querySelector('[name="obenlo_ai_gemini_key"]')?.value || '';
                    model  = document.querySelector('[name="obenlo_ai_gemini_model"]')?.value || '';
                } else if (provider === 'groq') {
                    apiKey = document.querySelector('[name="obenlo_ai_groq_key"]')?.value || '';
                    model  = document.querySelector('[name="obenlo_ai_groq_model"]')?.value || '';
                } else {
                    apiKey = document.querySelector('[name="obenlo_ai_openai_key"]')?.value || '';
                    model  = document.querySelector('[name="obenlo_ai_openai_model"]')?.value || '';
                }

                if (!apiKey) {
                    resultEl.textContent = '❌ Please enter an API key first.';
                    resultEl.style.color = '#dc2626';
                    return;
                }

                try {
                    const fd = new FormData();
                    fd.append('action',   'obenlo_ai_test_connection');
                    fd.append('nonce',    '<?php echo wp_create_nonce( "obenlo_ai_test_nonce" ); ?>');
                    fd.append('provider', provider);
                    fd.append('api_key',  apiKey);
                    fd.append('model',    model);

                    const res = await fetch('<?php echo admin_url( "admin-ajax.php" ); ?>', { method: 'POST', body: fd });
                    const rawText = await res.text();

                    let data;
                    try {
                        // Try to extract JSON if PHP notices are mixed in
                        const start = rawText.indexOf('{');
                        const end   = rawText.lastIndexOf('}');
                        if (start !== -1 && end !== -1) {
                            data = JSON.parse(rawText.substring(start, end + 1));
                        } else {
                            throw new Error('No JSON found');
                        }
                    } catch (jsonErr) {
                        resultEl.textContent = '❌ Bad server response: ' + rawText.substring(0, 120);
                        resultEl.style.color = '#dc2626';
                        return;
                    }

                    if (data.success) {
                        resultEl.textContent = '✅ Connected! Response: ' + (data.data?.response || 'OK');
                        resultEl.style.color = '#059669';
                    } else {
                        resultEl.textContent = '❌ ' + (data.data?.message || 'Failed');
                        resultEl.style.color = '#dc2626';
                    }
                } catch (e) {
                    resultEl.textContent = '❌ Fetch failed: ' + e.message;
                    resultEl.style.color = '#dc2626';
                }
            }

            document.getElementById('obenlo-ai-test-gemini').addEventListener('click', () => {
                testConnection('gemini', document.getElementById('obenlo-ai-test-gemini-result'));
            });
            document.getElementById('obenlo-ai-test-groq').addEventListener('click', () => {
                testConnection('groq', document.getElementById('obenlo-ai-test-groq-result'));
            });
            document.getElementById('obenlo-ai-test-openai').addEventListener('click', () => {
                testConnection('openai', document.getElementById('obenlo-ai-test-openai-result'));
            });
        })();
        </script>
        <?php
    }

    // ── Save Handler ──────────────────────────────────────────────────────

    public function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        check_admin_referer( 'obenlo_ai_save_settings', 'ai_settings_nonce' );

        // Redirect back to the Obenlo Admin Dashboard AI tab
        $referer  = wp_get_referer();
        $redirect = $referer ?: home_url( '/?tab=ai_settings' );
        // Normalize: ensure the tab is set to ai_settings on redirect
        $redirect = add_query_arg( 'ai_saved', '1', remove_query_arg( 'ai_saved', $redirect ) );

        update_option( 'obenlo_ai_provider',       sanitize_text_field( $_POST['obenlo_ai_provider'] ?? 'gemini' ) );
        update_option( 'obenlo_ai_gemini_model',   sanitize_text_field( $_POST['obenlo_ai_gemini_model'] ?? 'gemini-2.0-flash' ) );
        update_option( 'obenlo_ai_openai_model',   sanitize_text_field( $_POST['obenlo_ai_openai_model'] ?? 'gpt-4o-mini' ) );
        update_option( 'obenlo_ai_groq_model',     sanitize_text_field( $_POST['obenlo_ai_groq_model'] ?? 'llama-3.3-70b-versatile' ) );
        update_option( 'obenlo_ai_enable_chat',    isset( $_POST['obenlo_ai_enable_chat'] ) ? 'yes' : 'no' );
        update_option( 'obenlo_ai_enable_listing', isset( $_POST['obenlo_ai_enable_listing'] ) ? 'yes' : 'no' );
        update_option( 'obenlo_ai_enable_search',  isset( $_POST['obenlo_ai_enable_search'] ) ? 'yes' : 'no' );
        update_option( 'obenlo_ai_travelpayouts_marker', sanitize_text_field( $_POST['obenlo_ai_travelpayouts_marker'] ?? '' ) );
        update_option( 'obenlo_ai_tp_viator', sanitize_text_field( $_POST['obenlo_ai_tp_viator'] ?? '' ) );
        update_option( 'obenlo_ai_tp_tripadvisor', sanitize_text_field( $_POST['obenlo_ai_tp_tripadvisor'] ?? '' ) );
        update_option( 'obenlo_ai_tp_gyg', sanitize_text_field( $_POST['obenlo_ai_tp_gyg'] ?? '' ) );
        if ( isset( $_POST['obenlo_ai_front_page_viator'] ) ) {
            update_option( 'obenlo_ai_front_page_viator', wp_unslash( $_POST['obenlo_ai_front_page_viator'] ) );
        }
        if ( isset( $_POST['obenlo_ai_front_page_travelpayouts'] ) ) {
            update_option( 'obenlo_ai_front_page_travelpayouts', wp_unslash( $_POST['obenlo_ai_front_page_travelpayouts'] ) );
        }

        if ( ! empty( $_POST['obenlo_ai_gemini_key'] ) ) {
            update_option( 'obenlo_ai_gemini_key', sanitize_text_field( $_POST['obenlo_ai_gemini_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_openai_key'] ) ) {
            update_option( 'obenlo_ai_openai_key', sanitize_text_field( $_POST['obenlo_ai_openai_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_groq_key'] ) ) {
            update_option( 'obenlo_ai_groq_key', sanitize_text_field( $_POST['obenlo_ai_groq_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_ticketmaster_key'] ) ) {
            update_option( 'obenlo_ai_ticketmaster_key', sanitize_text_field( $_POST['obenlo_ai_ticketmaster_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_seatgeek_key'] ) ) {
            update_option( 'obenlo_ai_seatgeek_key', sanitize_text_field( $_POST['obenlo_ai_seatgeek_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_viator_key'] ) ) {
            update_option( 'obenlo_ai_viator_key', sanitize_text_field( $_POST['obenlo_ai_viator_key'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_tp_marker'] ) ) {
            update_option( 'obenlo_ai_tp_marker', sanitize_text_field( $_POST['obenlo_ai_tp_marker'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_tp_token'] ) ) {
            update_option( 'obenlo_ai_tp_token', sanitize_text_field( $_POST['obenlo_ai_tp_token'] ) );
        }
        if ( ! empty( $_POST['obenlo_ai_groupon_key'] ) ) {
            update_option( 'obenlo_ai_groupon_key', sanitize_text_field( $_POST['obenlo_ai_groupon_key'] ) );
        }

        wp_safe_redirect( $redirect );
        exit;
    }

    // ── AJAX: Test Connection ─────────────────────────────────────────────
    // Accepts the key from the form POST so users can test BEFORE saving.

    public function handle_test_connection() {
        check_ajax_referer( 'obenlo_ai_test_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
        }

        $provider = sanitize_text_field( $_POST['provider'] ?? 'gemini' );
        $api_key  = sanitize_text_field( $_POST['api_key'] ?? '' );
        $model    = sanitize_text_field( $_POST['model'] ?? '' );

        if ( empty( $api_key ) ) {
            wp_send_json_error( [ 'message' => 'No API key provided.' ] );
        }

        // Call the API directly with the provided key (no database read)
        if ( $provider === 'gemini' ) {
            $result = self::_test_gemini( $api_key, $model ?: 'gemini-2.0-flash' );
        } elseif ( $provider === 'groq' ) {
            $result = self::_test_groq( $api_key, $model ?: 'llama-3.3-70b-versatile' );
        } else {
            $result = self::_test_openai( $api_key, $model ?: 'gpt-4o-mini' );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success( [ 'response' => $result ] );
    }

    /**
     * Direct Gemini test — bypasses Obenlo_AI_Client to use the form-provided key.
     */
    private static function _test_gemini( string $api_key, string $model ) {
        $endpoint = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}";

        $body = wp_json_encode( [
            'contents' => [
                [ 'parts' => [ [ 'text' => 'Reply with only the word: CONNECTED' ] ] ],
            ],
            'generationConfig' => [ 'maxOutputTokens' => 10 ],
        ] );

        $response = wp_remote_post( $endpoint, [
            'headers'     => [ 'Content-Type' => 'application/json' ],
            'body'        => $body,
            'timeout'     => 15,
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'Gemini returned HTTP ' . $code;
            return new WP_Error( 'gemini_test_fail', $msg );
        }

        return trim( $data['candidates'][0]['content']['parts'][0]['text'] ?? 'OK' );
    }

    /**
     * Direct Groq test — OpenAI-compatible endpoint.
     */
    private static function _test_groq( string $api_key, string $model ) {
        $body = wp_json_encode( [
            'model'      => $model,
            'messages'   => [ [ 'role' => 'user', 'content' => 'Reply with only the word: CONNECTED' ] ],
            'max_tokens' => 10,
        ] );

        $response = wp_remote_post( 'https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'        => $body,
            'timeout'     => 15,
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'Groq returned HTTP ' . $code;
            return new WP_Error( 'groq_test_fail', $msg );
        }

        return trim( $data['choices'][0]['message']['content'] ?? 'OK' );
    }

    /**
     * Direct OpenAI test — bypasses Obenlo_AI_Client to use the form-provided key.
     */
    private static function _test_openai( string $api_key, string $model ) {
        $body = wp_json_encode( [
            'model'      => $model,
            'messages'   => [ [ 'role' => 'user', 'content' => 'Reply with only the word: CONNECTED' ] ],
            'max_tokens' => 10,
        ] );

        $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'body'        => $body,
            'timeout'     => 15,
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code !== 200 ) {
            $msg = $data['error']['message'] ?? 'OpenAI returned HTTP ' . $code;
            return new WP_Error( 'openai_test_fail', $msg );
        }

        return trim( $data['choices'][0]['message']['content'] ?? 'OK' );
    }
}
