<?php
/**
 * Frontend Experience Logic - Hiding WordPress Backend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Obenlo_Booking_Frontend_Experience {

    public function init() {
        // Disable admin bar for non-administrators
        add_action( 'after_setup_theme', array( $this, 'hide_admin_bar' ) );

        // Redirect non-admins from /wp-admin/
        add_action( 'admin_init', array( $this, 'restrict_admin_access' ) );

        // Custom Login Redirection
        add_action( 'init', array( $this, 'redirect_to_custom_login' ) );

        // Bespoke Form Handlers
        add_action( 'admin_post_nopriv_obenlo_bespoke_login', array( $this, 'handle_bespoke_login' ) );
        add_action( 'admin_post_nopriv_obenlo_bespoke_register', array( $this, 'handle_bespoke_register' ) );
    }

    /**
     * Redirect wp-login.php to /login
     */
    public function redirect_to_custom_login() {
        global $pagenow;
        if ( 'wp-login.php' == $pagenow && !isset($_POST['wp-submit']) ) {
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

            // Handle main login redirect
            if ( $action === 'login' ) {
                wp_safe_redirect( home_url( '/login' ) );
                exit;
            }
            
            // Handle lost password redirect (GET only to avoid breaking form submission)
            if ( $action === 'lostpassword' && $_SERVER['REQUEST_METHOD'] === 'GET' ) {
                wp_safe_redirect( home_url( '/login#forgot' ) );
                exit;
            }
        }
    }

    /**
     * Handle custom login submission
     */
    public function handle_bespoke_login() {
        if ( !isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'obenlo_login') ) {
            wp_die('Security check failed');
        }

        $creds = array(
            'user_login'    => sanitize_text_field($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember'      => isset($_POST['rememberme'])
        );

        $user = wp_signon( $creds, false );

        if ( is_wp_error($user) ) {
            wp_safe_redirect( add_query_arg( 'login_error', '1', home_url('/login') ) );
        } else {
            wp_safe_redirect( home_url('/host-dashboard') );
        }
        exit;
    }

    /**
     * Handle custom registration submission
     */
    public function handle_bespoke_register() {
        if ( !isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'obenlo_register') ) {
            wp_die('Security check failed');
        }

        $username = sanitize_user($_POST['user_login']);
        $email    = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error($user_id) ) {
            wp_safe_redirect( add_query_arg( 'reg_error', '1', home_url('/login#signup') ) );
        } else {
            // Log them in immediately
            wp_set_auth_cookie( $user_id );
            // Assign role
            $user = new WP_User( $user_id );
            $user->set_role( 'guest' );
            
            wp_safe_redirect( home_url('/host-dashboard') );
        }
        exit;
    }

    /**
     * Disable the admin bar for anyone who is not an administrator
     */
    public function hide_admin_bar() {
        if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
            show_admin_bar( false );
        }
    }

    /**
     * Redirect non-admins away from the backend
     */
    public function restrict_admin_access() {
        // Allow AJAX and admin-post handlers
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || strpos( $_SERVER['PHP_SELF'], 'admin-post.php' ) !== false ) {
            return;
        }

        if ( ! current_user_can( 'administrator' ) ) {
            wp_safe_redirect( home_url( '/host-dashboard' ) );
            exit;
        }
    }

    /**
     * Change WP login logo to Obenlo style (minimalist version)
     */
    public function custom_login_branding() {
        ?>
        <style type="text/css">
            #login h1 a, .login h1 a {
                background-image: none !important;
                height: auto !important;
                width: auto !important;
                text-indent: 0 !important;
                font-size: 2.5rem !important;
                font-weight: 700 !important;
                color: #e61e4d !important;
                margin-bottom: 20px !important;
                display: block !important;
            }
            #login h1 a::after {
                content: 'Obenlo';
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
                background: #e61e4d !important;
                border-color: #e61e4d !important;
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

    public function custom_login_url() {
        return home_url();
    }

    public function custom_login_title() {
        return get_bloginfo( 'name' );
    }
}
