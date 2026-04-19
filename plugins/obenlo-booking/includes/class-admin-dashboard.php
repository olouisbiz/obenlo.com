<?php
/**
 * Site Admin Dashboard Logic (Router)
 * Coordinates modular tab rendering for the internal control console.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Admin_Dashboard
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_shortcode('obenlo_admin_dashboard', array($this, 'render_dashboard'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Obenlo Dash',
            'Obenlo Dash',
            'manage_options',
            'obenlo-admin-dashboard',
            array($this, 'render_dashboard_in_wp_admin'),
            'dashicons-chart-area',
            26
        );
    }

    public function render_dashboard_in_wp_admin()
    {
        echo '<div class="wrap">';
        echo $this->render_dashboard();
        echo '</div>';
    }

    public function render_dashboard()
    {
        if (!current_user_can('manage_support')) {
            return '<p>You do not have permission to access the Support Console.</p>';
        }

        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';

        ob_start();
?>
        <div class="obenlo-dashboard-container admin-mode" data-version="1.8.0">
            <style>
                .obenlo-dashboard-container { display: flex; min-height: 800px; background: #fff; font-family: 'Inter', sans-serif; gap: 0; margin-left: -20px; }
                .dashboard-sidebar { width: 260px; background: #fdfdfd; border-right: 1px solid #f0f0f0; padding: 40px 20px; display: flex; flex-direction: column; gap: 5px; position: sticky; top: 0; height: 100vh; z-index: 99; }
                #wpadminbar + #wpwrap .dashboard-sidebar { top: 32px; height: calc(100vh - 32px); }
                .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; text-decoration: none; color: #666; font-weight: 600; border-radius: 14px; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); font-size: 0.95rem; }
                .sidebar-link:hover { background: #f7f7f7; color: #222; }
                .sidebar-link.active { background: #e61e4d; color: #fff; box-shadow: 0 10px 20px rgba(230,30,77,0.1); }
                .sidebar-link svg { width: 20px; height: 20px; stroke-width: 2.2; }
                
                .dashboard-content { flex-grow: 1; padding: 50px 60px; background: #fff; max-width: 1400px; margin: 0 auto; width: 100%; box-sizing: border-box; }
                .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
                .dashboard-title { font-size: 2.4rem; font-weight: 800; color: #222; margin: 0; letter-spacing: -0.5px; }

                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
                .stat-card { background: #fff; border: 1px solid #eee; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; }
                .stat-value { display: block; font-size: 2.5em; font-weight: 800; color: #e61e4d; margin-bottom: 5px; }
                .stat-label { color: #666; font-weight: 600; text-transform: uppercase; font-size: 0.8em; letter-spacing: 1px; }
                
                .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; background: transparent; margin-top: 10px; }
                .admin-table td { background: #fff; padding: 15px; color: #444; font-size: 0.95rem; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; }
                .admin-table td:first-child { border-left: 1px solid #f0f0f0; border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
                .admin-table td:last-child { border-right: 1px solid #f0f0f0; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }
            </style>

            <div class="dashboard-sidebar">
                <?php
                $base_url = is_admin() ? '?page=obenlo-admin-dashboard&tab=' : '?tab=';
                $nav_items = array(
                    'overview'       => array('label' => 'Overview',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>'),
                    'listings'       => array('label' => 'Listings',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>'),
                    'users'          => array('label' => 'Users',          'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>'),
                    'verifications'  => array('label' => 'Verifications',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>'),
                    'bookings'       => array('label' => 'Bookings',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'),
                    'payments'       => array('label' => 'Payments',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>'),
                    'reviews'        => array('label' => 'Reviews',        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>'),
                    'testimonies'    => array('label' => 'Testimonies',    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.84-8.84 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>'),
                    'refunds'        => array('label' => 'Refunds',        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>'),
                    'messaging'      => array('label' => 'Messaging',      'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>'),
                    'broadcast'      => array('label' => 'Broadcast',      'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>'),
                    'support'        => array('label' => 'Support',        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>'),
                    'settings'       => array('label' => 'Settings',       'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>'),
                    'translation'    => array('label' => 'Translation',    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>'),
                    'demo_manager'   => array('label' => 'Demo Manager',   'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>'),
                    'payouts'        => array('label' => 'Payouts',        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>'),
                );

                foreach ($nav_items as $id => $item) {
                    $active = ($tab === $id) ? 'active' : '';
                    echo "<a href='{$base_url}{$id}' class='sidebar-link {$active}'>{$item['icon']}<span>{$item['label']}</span></a>";
                }
                ?>
            </div>

            <div class="dashboard-content">
                <?php
                switch ($tab) {
                    case 'listings':      (new Obenlo_Admin_Listings())->render_listings_tab(); break;
                    case 'users':         (new Obenlo_Admin_Users())->render_users_tab(); break;
                    case 'verifications': (new Obenlo_Admin_Verifications())->render_verifications_tab(); break;
                    case 'reviews':       (new Obenlo_Admin_Verifications())->render_reviews_tab(); break;
                    case 'testimonies':   (new Obenlo_Admin_Verifications())->render_testimonies_tab(); break;
                    case 'bookings':      (new Obenlo_Admin_Bookings())->render_bookings_tab(); break;
                    case 'payments':      (new Obenlo_Admin_Payments())->render_payments_tab(); break;
                    case 'payouts':       (new Obenlo_Admin_Payments())->render_payout_management_tab(); break;
                    case 'refunds':       (new Obenlo_Admin_Payments())->render_refunds_tab(); break;
                    case 'settings':      (new Obenlo_Admin_Settings())->render_settings_tab(); break;
                    case 'translation':   (new Obenlo_Admin_Settings())->render_translation_tab(); break;
                    case 'messaging':     (new Obenlo_Admin_Messages())->render_messaging_oversight_tab(); break;
                    case 'broadcast':     (new Obenlo_Admin_Messages())->render_broadcast_tab(); break;
                    case 'support':       (new Obenlo_Admin_Messages())->render_communication_tab(); break;
                    case 'demo_manager':  (new Obenlo_Admin_Demo_Manager())->render_demo_manager_tab(); break;
                    case 'edit_host':     (new Obenlo_Admin_Users())->render_edit_host_tab(); break;
                    case 'manage_availability': (new Obenlo_Admin_Users())->render_manage_availability_tab(); break;
                    default:              (new Obenlo_Admin_Overview())->render_overview_tab(); break;
                }
                ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
