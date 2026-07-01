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
                .obenlo-dashboard-container {
                    display: flex;
                    min-height: 800px;
                    background: #fafafa;
                    font-family: 'Inter', -apple-system, sans-serif;
                    gap: 0;
                    margin-left: -20px;
                    --dash-brand: #e61e4d;
                    --dash-brand-dark: #b5143a;
                    --dash-radius-sm: 8px;
                    --dash-radius-md: 14px;
                    --dash-radius-lg: 24px;
                    --dash-shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
                    --dash-shadow-md: 0 8px 30px rgba(0,0,0,0.06);
                    --dash-transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                }
                .dashboard-sidebar {
                    width: 260px;
                    background: #ffffff;
                    border-right: 1px solid rgba(0,0,0,0.06);
                    padding: 40px 20px;
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    position: sticky;
                    top: 0;
                    height: 100vh;
                    z-index: 99;
                    box-sizing: border-box;
                }
                #wpadminbar + #wpwrap .dashboard-sidebar { top: 32px; height: calc(100vh - 32px); }
                .sidebar-link {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px 18px;
                    text-decoration: none;
                    color: #52525b;
                    font-weight: 600;
                    border-radius: var(--dash-radius-md);
                    transition: var(--dash-transition);
                    font-size: 0.92rem;
                }
                .sidebar-link:hover {
                    background: #f4f4f5;
                    color: #18181b;
                    transform: translateX(4px);
                }
                .sidebar-link.active {
                    background: linear-gradient(135deg, var(--dash-brand), var(--dash-brand-dark));
                    color: #ffffff;
                    box-shadow: 0 8px 20px rgba(230,30,77,0.25);
                }
                .sidebar-link svg {
                    width: 18px;
                    height: 18px;
                    stroke-width: 2.2;
                    transition: transform var(--dash-transition);
                }
                .sidebar-link.active svg {
                    transform: scale(1.1);
                }
                
                .dashboard-content {
                    flex-grow: 1;
                    padding: 48px 56px;
                    background: #fafafa;
                    max-width: 1400px;
                    margin: 0 auto;
                    width: 100%;
                    box-sizing: border-box;
                }
                .dashboard-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 36px;
                }
                .dashboard-title {
                    font-size: 2.1rem;
                    font-weight: 800;
                    color: #18181b;
                    margin: 0;
                    letter-spacing: -0.5px;
                }

                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 24px;
                    margin-bottom: 48px;
                }
                .stat-card {
                    background: #ffffff;
                    border: 1px solid rgba(0,0,0,0.04);
                    padding: 24px;
                    border-radius: var(--dash-radius-md);
                    box-shadow: var(--dash-shadow-sm);
                    text-align: center;
                    transition: var(--dash-transition);
                    position: relative;
                    overflow: hidden;
                }
                .stat-card::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 4px;
                    background: var(--dash-brand);
                    opacity: 0;
                    transition: opacity var(--dash-transition);
                }
                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--dash-shadow-md);
                    border-color: rgba(0,0,0,0.08);
                }
                .stat-card:hover::after {
                    opacity: 1;
                }
                .stat-value {
                    display: block;
                    font-size: 2.2rem;
                    font-weight: 850;
                    color: #18181b;
                    margin-bottom: 6px;
                    line-height: 1;
                    letter-spacing: -0.5px;
                }
                .stat-label {
                    color: #a1a1aa;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 0.72rem;
                    letter-spacing: 1.2px;
                }
                
                .admin-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0 10px;
                    background: transparent;
                    margin-top: 10px;
                }
                .admin-table th {
                    background: transparent;
                    padding: 12px 24px;
                    text-align: left;
                    font-weight: 700;
                    color: #a1a1aa;
                    text-transform: uppercase;
                    font-size: 0.72rem;
                    letter-spacing: 1px;
                }
                .admin-table td {
                    background: #ffffff;
                    padding: 20px 24px;
                    color: #3f3f46;
                    font-size: 0.92rem;
                    vertical-align: middle;
                    border-top: 1px solid rgba(0,0,0,0.03);
                    border-bottom: 1px solid rgba(0,0,0,0.03);
                    transition: var(--dash-transition);
                }
                .admin-table td:first-child {
                    border-left: 1px solid rgba(0,0,0,0.03);
                    border-top-left-radius: var(--dash-radius-md);
                    border-bottom-left-radius: var(--dash-radius-md);
                }
                .admin-table td:last-child {
                    border-right: 1px solid rgba(0,0,0,0.03);
                    border-top-right-radius: var(--dash-radius-md);
                    border-bottom-right-radius: var(--dash-radius-md);
                }
                .admin-table tr:hover td {
                    background: #fafafa;
                    border-color: rgba(0,0,0,0.08);
                }
                
                @media (max-width: 1024px) {
                    .dashboard-sidebar { width: 80px; padding: 40px 10px; }
                    .sidebar-link span { display: none; }
                    .sidebar-link { justify-content: center; padding: 14px; }
                    .dashboard-content { padding: 40px 24px; }
                }
                @media (max-width: 768px) {
                    body { padding-bottom: 100px !important; }
                    .site-footer { margin-bottom: 90px !important; }
                    .obenlo-dashboard-container { flex-direction: column; padding-bottom: 20px; }
                    .dashboard-sidebar {
                        width: 100%;
                        height: auto;
                        position: fixed;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        border-right: none;
                        border-top: 1px solid rgba(0,0,0,0.08);
                        background: rgba(255, 255, 255, 0.92);
                        backdrop-filter: blur(20px);
                        -webkit-backdrop-filter: blur(20px);
                        padding: 10px 5px;
                        flex-direction: row;
                        justify-content: flex-start;
                        overflow-x: auto;
                        gap: 0;
                        z-index: 10000;
                        box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
                        top: auto;
                    }
                    .sidebar-link {
                        flex-direction: column;
                        gap: 2px;
                        padding: 8px 12px;
                        min-width: 65px;
                        background: transparent !important;
                        box-shadow: none !important;
                    }
                    .sidebar-link.active {
                        color: var(--dash-brand);
                        background: transparent !important;
                    }
                    .sidebar-link svg {
                        width: 22px;
                        height: 22px;
                        margin-bottom: 2px;
                    }
                    .sidebar-link span {
                        display: block;
                        font-size: 0.65rem;
                        font-weight: 700;
                    }
                }
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
                    'ai_settings'    => array('label' => '🤖 AI Settings',  'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>'),
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
                    case 'ai_settings':   if (class_exists('Obenlo_AI_Admin')) { (new Obenlo_AI_Admin())->render_settings_panel(); } else { echo '<p>⚠️ The Obenlo AI plugin is not active. Please activate it in <a href="' . admin_url('plugins.php') . '">Plugins</a>.</p>'; } break;
                    default:              (new Obenlo_Admin_Overview())->render_overview_tab(); break;
                }
                ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
