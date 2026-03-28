<?php
/**
 * Frontend Experience Logic - Hiding WordPress Backend - Obenlo
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Frontend_Experience
{

    public function init()
    {
        // Disable admin bar for non-administrators
        add_action('after_setup_theme', array($this, 'hide_admin_bar'));

        // Redirect non-admins from /wp-admin/
        add_action('admin_init', array($this, 'restrict_admin_access'));

        // Custom Login Redirection
        add_action('init', array($this, 'redirect_to_custom_login'));

        // Bespoke Form Handlers
        add_action('admin_post_nopriv_obenlo_bespoke_login', array($this, 'handle_bespoke_login'));
        add_action('admin_post_obenlo_bespoke_login', array($this, 'handle_bespoke_login'));

        add_action('admin_post_nopriv_obenlo_bespoke_register', array($this, 'handle_bespoke_register'));
        add_action('admin_post_obenlo_bespoke_register', array($this, 'handle_bespoke_register'));
        
        // Custom Login Branding (for wp-login.php if accessed)
        add_action('login_enqueue_scripts', array($this, 'custom_login_branding'));
        add_filter('login_headerurl', array($this, 'custom_login_url'));
        add_filter('login_headertext', array($this, 'custom_login_title'));
    }

    /**
     * Redirect wp-login.php to /login
     */
    public function redirect_to_custom_login()
    {
        global $pagenow;
        if ('wp-login.php' == $pagenow && !isset($_POST['wp-submit'])) {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

            // Handle main login redirect
            if ($action === 'login') {
                wp_safe_redirect(home_url('/login'));
                exit;
            }

            // Handle lost password redirect (GET only to avoid breaking form submission)
            if ($action === 'lostpassword' && $_SERVER['REQUEST_METHOD'] === 'GET') {
                wp_safe_redirect(home_url('/login#forgot'));
                exit;
            }
        }
    }

    /**
     * Handle custom login submission
     */
    public function handle_bespoke_login()
    {
        $creds = array(
            'user_login' => sanitize_text_field($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember' => isset($_POST['rememberme'])
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            wp_safe_redirect(add_query_arg('login_error', '1', home_url('/login')));
        }
        else {
            // Check role and redirect accordingly
            if (in_array('host', (array)$user->roles)) {
                wp_safe_redirect(home_url('/host-dashboard'));
            }
            else {
                wp_safe_redirect(home_url('/account'));
            }
        }
        exit;
    }

    /**
     * Handle custom registration submission
     */
    public function handle_bespoke_register()
    {
        $username = sanitize_user($_POST['user_login']);
        $email = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_safe_redirect(add_query_arg('reg_error', '1', home_url('/login#signup')));
        }
        else {
            // Log them in immediately
            wp_set_auth_cookie($user_id);

            // Assign role based on selection
            $user = new WP_User($user_id);
            $requested_role = isset($_POST['user_role']) ? sanitize_text_field($_POST['user_role']) : 'guest';

            if ($requested_role === 'host') {
                $user->set_role('host');
                update_user_meta($user_id, '_obenlo_new_user', '1');
                wp_safe_redirect(home_url('/host-onboarding'));
            }
            else {
                $user->set_role('guest');
                update_user_meta($user_id, '_obenlo_new_user', '1');
                wp_safe_redirect(home_url('/account'));
            }
        }
        exit;
    }

    /**
     * Disable the admin bar for anyone who is not an administrator
     */
    public function hide_admin_bar()
    {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }

    /**
     * Redirect non-admins away from the backend
     */
    public function restrict_admin_access()
    {
        // Allow AJAX and admin-post handlers
        if ((defined('DOING_AJAX') && DOING_AJAX) || strpos($_SERVER['PHP_SELF'], 'admin-post.php') !== false) {
            return;
        }

        if (!current_user_can('administrator')) {
            $user = wp_get_current_user();
            if (in_array('host', (array)$user->roles)) {
                wp_safe_redirect(home_url('/host-dashboard'));
            }
            else {
                wp_safe_redirect(home_url('/account'));
            }
            exit;
        }
    }

    /**
     * Render a premium welcome modal for new users
     */
    public static function render_welcome_modal()
    {
        $user_id = get_current_user_id();
        if (!$user_id || get_user_meta($user_id, '_obenlo_new_user', true) !== '1') {
            return;
        }

        $user = get_userdata($user_id);
        $first_name = $user ? $user->first_name : '';
        $brand_name = get_option('obenlo_brand_name', 'Obenlo');
        $primary_color = get_option('obenlo_primary_color', '#e61e4d');

        // Clear the flag immediately to prevent re-shows on refresh
        delete_user_meta($user_id, '_obenlo_new_user');
?>
        <div id="obenlo-welcome-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:99999; display:flex; align-items:center; justify-content:center; animation:fadeIn 0.5s ease;">
            <style>
                @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
                @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
                .welcome-card { background: #fff; width: 90%; max-width: 550px; border-radius: 32px; padding: 50px; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.3); animation: slideUp 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; }
                .welcome-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 8px; background: linear-gradient(90deg, <?php echo $primary_color; ?>, #ff5a5f); }
                .welcome-icon { width: 80px; height: 80px; background: #fffcfc; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; border: 1px solid #f0f0f0; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
                .welcome-icon svg { width: 40px; height: 40px; color: <?php echo $primary_color; ?>; }
                .welcome-btn { background: <?php echo $primary_color; ?>; color: #fff; padding: 16px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; transition: all 0.3s; margin-top: 30px; box-shadow: 0 10px 20px rgba(230, 30, 77, 0.2); }
                .welcome-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px rgba(230, 30, 77, 0.3); }
            </style>
            <div class="welcome-card">
                <div class="welcome-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.84-8.84 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </div>
                <h2 style="font-size: 2.2rem; font-weight: 900; color: #222; margin-bottom: 15px;"><?php printf(__('Welcome to %s!', 'obenlo-booking'), esc_html($brand_name)); ?></h2>
                <?php if ($first_name): ?>
                    <p style="font-size: 1.2rem; color: <?php echo $primary_color; ?>; font-weight: 700; margin-bottom: 20px;"><?php printf(__('Hello, %s!', 'obenlo-booking'), esc_html($first_name)); ?></p>
                <?php endif; ?>
                <p style="font-size: 1.05rem; color: #666; line-height: 1.6;"><?php printf(__('We\'re thrilled to have you here. Whether you\'re hosting or traveling, %s is your place for unique shared experiences.', 'obenlo-booking'), esc_html($brand_name)); ?></p>
                <button class="welcome-btn" onclick="document.getElementById('obenlo-welcome-overlay').style.display='none'"><?php echo __('Let\'s Get Started', 'obenlo-booking'); ?></button>
            </div>
        </div>
<?php
    }

    /**
     * Render success/error notices for dashboard actions
     */
    public static function obenlo_render_dashboard_notices()
    {
        $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
        $error = isset($_GET['obenlo_error']) ? sanitize_text_field($_GET['obenlo_error']) : '';
        
        if ($message === 'saved' || (isset($_GET['obenlo_message']) && $_GET['obenlo_message'] == '1')) {
            echo '<div style="background:#ecfdf5; color:#059669; padding:20px; border-radius:16px; margin-bottom:30px; font-weight:700; display:flex; align-items:center; gap:12px; border:1px solid #d1fae5; animation:slideUp 0.4s ease;">';
            echo '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            echo '<span>' . __('Success! Your changes have been saved.', 'obenlo-booking') . '</span>';
            echo '</div>';
        }

        if ($error) {
            $error_msg = __('An error occurred. Please try again.', 'obenlo-booking');
            switch($error) {
                case 'unauthorized': $error_msg = __('You do not have permission for this.', 'obenlo-booking'); break;
                case 'security_failed': $error_msg = __('Security check failed. Refresh and try again.', 'obenlo-booking'); break;
                case 'invalid_data': $error_msg = __('Missing required information.', 'obenlo-booking'); break;
            }
            echo '<div style="background:#fef2f2; color:#dc2626; padding:20px; border-radius:16px; margin-bottom:30px; font-weight:700; display:flex; align-items:center; gap:12px; border:1px solid #fee2e2; animation:slideUp 0.4s ease;">';
            echo '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
            echo '<span>' . esc_html($error_msg) . '</span>';
            echo '</div>';
        }
    }

    /**
     * Change WP login logo to Brand style
     */
    public function custom_login_branding()
    {
        $brand_name    = get_option('obenlo_brand_name', 'Obenlo');
        $primary_color = get_option('obenlo_primary_color', '#e61e4d');
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: none !important;
                height: auto !important;
                width: auto !important;
                text-indent: 0 !important;
                font-size: 2.5rem !important;
                font-weight: 700 !important;
                color: <?php echo esc_attr($primary_color); ?> !important;
                margin-bottom: 20px !important;
                display: block !important;
            }
            #login h1 a::after {
                content: '<?php echo esc_js($brand_name); ?>';
            }
            body.login {
                background: #fff !important;
            }
            .login form {
                border: 1px solid #eee !important;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
                border-radius: 12px !important;
            }
            .wp-core-ui .button-primary {
                background: <?php echo esc_attr($primary_color); ?> !important;
                border-color: <?php echo esc_attr($primary_color); ?> !important;
                text-shadow: none !important;
                box-shadow: none !important;
                font-weight: bold !important;
                padding: 5px 20px !important;
            }
            #nav, #backtoblog {
                text-align: center !important;
            }
            #nav a, #backtoblog a {
                color: #666 !important;
                font-size: 0.9em !important;
            }
        </style>
        <?php
    }

    public function custom_login_url()
    {
        return home_url();
    }

    public function custom_login_title()
    {
        return get_bloginfo('name');
    }
}
