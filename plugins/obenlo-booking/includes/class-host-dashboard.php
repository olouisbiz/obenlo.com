<?php
/**
 * Host Dashboard — Router & Shell
 * Single Responsibility: Shortcode registration, nav rendering, tab routing.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Dashboard
{
    public function init()
    {
        add_shortcode('obenlo_host_dashboard', array($this, 'render_dashboard'));
        add_action('init', array($this, 'handle_global_location_fix'));
    }

    public function handle_global_location_fix() {
        if (isset($_GET['obenlo_fix_locations']) && $_GET['obenlo_fix_locations'] === '1' && current_user_can('administrator')) {
            $child_listings = get_posts(array(
                'post_type' => 'listing',
                'post_parent__not_in' => array(0),
                'posts_per_page' => -1,
                'post_status' => 'any'
            ));
            $count = 0;
            foreach ($child_listings as $child) {
                $parent_id = $child->post_parent;
                $current_location = get_post_meta($child->ID, '_obenlo_location', true);
                if (empty($current_location)) {
                    $parent_location = get_post_meta($parent_id, '_obenlo_location', true);
                    if ($parent_location) {
                        update_post_meta($child->ID, '_obenlo_location', $parent_location);
                        $count++;
                    }
                }
            }
            wp_die("Success: Updated $count child listings with parent locations. <a href='" . home_url('/host-dashboard') . "'>Return to Dashboard</a>");
        }
    }

    public function render_dashboard()
    {
        if (!is_user_logged_in()) {
            return '<div style="padding: 100px 20px; text-align: center;"><p style="font-size: 1.2rem; color: #666;">Please log in to view the host dashboard.</p><a href="' . home_url('/login') . '" style="background: #e61e4d; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 20px;">Log In</a></div>';
        }

        $user = wp_get_current_user();
        if (!in_array('host', (array)$user->roles) && !in_array('administrator', (array)$user->roles)) {
            return '<div style="padding: 100px 20px; text-align: center;"><p style="font-size: 1.2rem; color: #666;">You do not have permission to view the host dashboard.</p></div>';
        }

        $is_host  = true;
        $user_id  = get_current_user_id();
        $action   = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'overview';
        $listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;

        ob_start();
        ?>
        <div class="obenlo-dashboard-container">
        <div class="obenlo-dashboard-container">
            <style>
                .obenlo-dashboard-container {
                    display: flex;
                    min-height: 800px;
                    background: #fafafa;
                    font-family: 'Inter', -apple-system, sans-serif;
                    gap: 0;
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
                    top: 72px;
                    height: calc(100vh - 72px);
                    box-sizing: border-box;
                }
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
                    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                    gap: 24px;
                    margin-bottom: 48px;
                }
                .stat-card {
                    background: #ffffff;
                    border: 1px solid rgba(0,0,0,0.04);
                    padding: 28px;
                    border-radius: var(--dash-radius-lg);
                    box-shadow: var(--dash-shadow-sm);
                    text-align: left;
                    transition: var(--dash-transition);
                    position: relative;
                    overflow: hidden;
                }
                .stat-card::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 4px;
                    height: 100%;
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
                .avatar-stack {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                .avatar-circle {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 2px solid #ffffff;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                }
                .badge {
                    padding: 6px 14px;
                    border-radius: var(--radius-pill);
                    font-size: 0.72rem;
                    font-weight: 800;
                    text-transform: uppercase;
                    letter-spacing: 0.8px;
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                }
                .badge::before {
                    content: '';
                    width: 6px;
                    height: 6px;
                    border-radius: 50%;
                    display: inline-block;
                }
                .badge-success { background: #f0fdf4; color: #166534; } .badge-success::before { background: #15803d; }
                .badge-warning { background: #fffbeb; color: #854d0e; } .badge-warning::before { background: #b45309; }
                .badge-danger { background: #fef2f2; color: #991b1b; } .badge-danger::before { background: #b91c1c; }
                .badge-info { background: #eff6ff; color: #1e40af; } .badge-info::before { background: #1d4ed8; }
                
                .btn-icon {
                    width: 36px;
                    height: 36px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #ffffff;
                    border: 1.5px solid rgba(0,0,0,0.08);
                    color: #52525b;
                    transition: var(--dash-transition);
                    cursor: pointer;
                }
                .btn-icon:hover {
                    background: #18181b;
                    color: #ffffff;
                    border-color: #18181b;
                    transform: translateY(-2px);
                }
                .btn-primary {
                    background: #18181b;
                    color: #ffffff;
                    border: none;
                    padding: 12px 24px;
                    border-radius: var(--dash-radius-md);
                    font-weight: 700;
                    cursor: pointer;
                    transition: var(--dash-transition);
                    text-decoration: none;
                    display: inline-block;
                    font-size: 0.92rem;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                }
                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
                    background: var(--dash-brand);
                }
                .btn-outline {
                    background: #ffffff;
                    color: #18181b;
                    border: 1.5px solid #18181b;
                    padding: 10px 20px;
                    border-radius: var(--dash-radius-md);
                    font-weight: 700;
                    cursor: pointer;
                    transition: var(--dash-transition);
                    text-decoration: none;
                    display: inline-block;
                    font-size: 0.88rem;
                }
                .btn-outline:hover {
                    background: #18181b;
                    color: #ffffff;
                }
                .search-container {
                    position: relative;
                    background: #ffffff;
                    border: 1.5px solid rgba(0,0,0,0.08);
                    border-radius: var(--dash-radius-md);
                    padding: 4px 12px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    transition: var(--dash-transition);
                    max-width: 400px;
                    width: 100%;
                }
                .search-container:focus-within {
                    border-color: #18181b;
                    box-shadow: var(--dash-shadow-md);
                }
                .search-input {
                    border: none;
                    background: transparent;
                    padding: 8px;
                    font-size: 0.92rem;
                    width: 100%;
                    outline: none;
                    font-weight: 500;
                    color: #18181b;
                }
                .form-section {
                    background: #ffffff;
                    border: 1px solid rgba(0,0,0,0.04);
                    border-radius: var(--dash-radius-lg);
                    padding: 32px;
                    margin-bottom: 28px;
                    box-shadow: var(--dash-shadow-sm);
                }
                .grid-row { display: flex; gap: 20px; align-items: flex-start; }
                .grid-col-1-2 { flex: 1; }
                .grid-col-1-3 { flex: 1; }
                
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
                    #obenlo-ai-listing-panel, #obenlo-ai-profile-fab, #obenlo-ai-profile-modal, .obenlo-lang-switcher {
                        bottom: 90px !important;
                    }
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
                        padding: 10px 15px;
                        flex-direction: row;
                        justify-content: flex-start;
                        overflow-x: auto;
                        gap: 15px;
                        z-index: 10000;
                        box-shadow: 0 -4px 16px rgba(0,0,0,0.04);
                        top: auto;
                        -webkit-overflow-scrolling: touch;
                    }
                    .dashboard-sidebar::-webkit-scrollbar {
                        display: none;
                    }
                    .sidebar-link {
                        flex-direction: column;
                        gap: 4px;
                        padding: 8px 4px;
                        min-width: 70px;
                        background: transparent !important;
                        color: #71717a;
                        border-radius: 0;
                        white-space: nowrap;
                        flex-shrink: 0;
                    }
                    .sidebar-link:hover {
                        transform: none;
                        background: transparent;
                    }
                    .sidebar-link span { display: block; font-size: 0.65rem; font-weight: 700; opacity: 0.9; }
                    .sidebar-link svg { width: 20px; height: 20px; }
                    .sidebar-link.active { color: var(--dash-brand); box-shadow: none; }
                    .dashboard-content { padding: 28px 16px; }
                    .dashboard-header { flex-direction: column; align-items: flex-start; gap: 16px; }
                    .admin-table, .admin-table tr, .admin-table td { display: block; width: 100%; }
                    .admin-table thead { display: none; }
                    .admin-table td {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 14px 16px;
                        text-align: right;
                        border-bottom: 1px solid rgba(0,0,0,0.03) !important;
                        font-size: 0.88rem;
                        min-height: 44px;
                        border-radius: 0 !important;
                    }
                    .admin-table td::before {
                        content: attr(data-label);
                        font-weight: 700;
                        color: #a1a1aa;
                        text-transform: uppercase;
                        font-size: 0.68rem;
                        margin-right: 12px;
                        text-align: left;
                        flex-shrink: 0;
                    }
                    .admin-table td:first-child {
                        padding-top: 20px;
                        border-top-left-radius: var(--dash-radius-md) !important;
                        border-top-right-radius: var(--dash-radius-md) !important;
                    }
                    .admin-table td:last-child {
                        border-bottom-left-radius: var(--dash-radius-md) !important;
                        border-bottom-right-radius: var(--dash-radius-md) !important;
                        border-bottom: 1px solid rgba(0,0,0,0.06);
                    }
                    .grid-row { flex-direction: column !important; align-items: flex-start !important; gap: 10px !important; }
                }
            </style>

            <!-- Sidebar Navigation -->
            <div class="dashboard-sidebar">
                <a href="?action=overview" class="sidebar-link <?php echo $action === 'overview' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span><?php echo __('Overview', 'obenlo'); ?></span>
                </a>

                <?php if ($is_host): ?>
                    <a href="?action=list" class="sidebar-link <?php echo($action === 'list' || $action === 'edit') ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span><?php echo __('My Listings', 'obenlo'); ?></span>
                    </a>
                    <a href="?action=bookings" class="sidebar-link <?php echo $action === 'bookings' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span><?php echo __('Bookings', 'obenlo'); ?></span>
                    </a>
                    <a href="?action=storefront" class="sidebar-link <?php echo $action === 'storefront' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span><?php echo __('Storefront', 'obenlo'); ?></span>
                    </a>
                    <a href="?action=reviews" class="sidebar-link <?php echo $action === 'reviews' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <span><?php echo __('Reviews', 'obenlo'); ?></span>
                    </a>
                    <a href="?action=refunds" class="sidebar-link <?php echo $action === 'refunds' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span><?php echo __('Refunds', 'obenlo'); ?></span>
                    </a>
                <?php endif; ?>

                <a href="?action=messages" class="sidebar-link <?php echo $action === 'messages' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span><?php echo __('Messages', 'obenlo'); ?></span>
                </a>

                <a href="?action=testimony" class="sidebar-link <?php echo $action === 'testimony' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l8.84-8.84 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <span><?php echo __('Obenlo Love', 'obenlo'); ?></span>
                </a>

                <?php if ($is_host): ?>
                    <a href="?action=availability" class="sidebar-link <?php echo $action === 'availability' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span><?php echo __('Availability', 'obenlo'); ?></span>
                    </a>
                    <a href="?action=payouts" class="sidebar-link <?php echo $action === 'payouts' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <span><?php echo __('Payout Settings', 'obenlo'); ?></span>
                    </a>
                <?php endif; ?>

                <a href="?action=support" class="sidebar-link <?php echo $action === 'support' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span><?php echo __('Help Center', 'obenlo'); ?></span>
                </a>

                <a href="?action=announcements" class="sidebar-link <?php echo $action === 'announcements' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                    <span><?php echo __('Announcements', 'obenlo'); ?></span>
                </a>

                <?php if ($is_host): ?>
                    <a href="?action=guide" class="sidebar-link <?php echo $action === 'guide' ? 'active' : ''; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path></svg>
                        <span><?php echo __('Host Guide', 'obenlo'); ?></span>
                    </a>
                <?php endif; ?>

                <div style="margin-top:auto; padding-top:20px; border-top:1px solid #eee;">
                    <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" class="sidebar-link" style="opacity:0.8;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        <span><?php echo __('View Storefront', 'obenlo'); ?></span>
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="dashboard-content">
                <?php
                // Flash Messages & Errors
                if (isset($_GET['message']) && $_GET['message'] === 'saved') {
                    echo '<div class="badge badge-success" style="margin-bottom:30px; display:block; text-align:center; padding:15px; border-radius:12px; font-weight:700;">' . __('✓ Action completed successfully!', 'obenlo') . '</div>';
                }

                if (isset($_GET['obenlo_error'])) {
                    $error_code = sanitize_text_field($_GET['obenlo_error']);
                    $msgs = array(
                        'unauthorized'    => __('Unauthorized: You do not have permission to perform this action.', 'obenlo'),
                        'security_failed' => __('Security check failed. Please refresh the page and try again.', 'obenlo'),
                        'invalid_booking' => __('Invalid booking or listing reference.', 'obenlo'),
                        'invalid_data'    => __('Missing required information for this action.', 'obenlo'),
                        'invalid_listing' => __('Invalid listing reference.', 'obenlo'),
                        'capacity_exceeded' => __('Guest count exceeds capacity.', 'obenlo'),
                        'host_away'       => __('Selected dates are unavailable (Host vacation).', 'obenlo'),
                        'day_unavailable' => __('Host is not available on this day.', 'obenlo'),
                        'time_unavailable'=> __('Selected time is outside operating hours.', 'obenlo'),
                        'already_booked'  => __('These dates/times are already booked.', 'obenlo'),
                        'booking_error'   => __('Error creating booking. Please try again.', 'obenlo'),
                        'invalid_payment' => __('Invalid payment method selected.', 'obenlo'),
                        'no_bookings'     => __('No bookings found for the selected date.', 'obenlo'),
                        'invalid_date'    => __('Please select a valid date for export.', 'obenlo'),
                    );
                    $error_msg = $msgs[$error_code] ?? __('An unexpected error occurred. Please try again.', 'obenlo');
                    echo '<div style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding:15px 20px; border-radius:12px; margin-bottom:30px; font-weight:700; display:flex; align-items:center; gap:10px; border-left: 4px solid #ef4444;">';
                    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px; height:20px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    echo '<span>' . esc_html($error_msg) . '</span></div>';
                }

                // Tab routing
                $listings_module   = new Obenlo_Host_Listings();
                $bookings_module   = new Obenlo_Host_Bookings();
                $reviews_module    = new Obenlo_Host_Reviews();
                $storefront_module = new Obenlo_Host_Storefront();
                $avail_module      = new Obenlo_Host_Availability();
                $payouts_module    = new Obenlo_Host_Payouts();
                $support_module    = new Obenlo_Host_Support();
                $trips_module      = new Obenlo_Host_Trips();
                $overview_module   = new Obenlo_Host_Overview();

                if ($action === 'overview') {
                    $overview_module->render_overview_tab();
                } elseif ($action === 'add' || $action === 'edit') {
                    $listings_module->render_listing_form($listing_id);
                } elseif ($action === 'list') {
                    $listings_module->render_listings_list();
                } elseif ($action === 'bookings') {
                    $bookings_module->render_bookings_list();
                } elseif ($action === 'storefront') {
                    $storefront_module->render_storefront_form();
                } elseif ($action === 'reviews') {
                    $reviews_module->render_reviews_list();
                } elseif ($action === 'refunds') {
                    $trips_module->render_refunds_tab();
                } elseif ($action === 'messages') {
                    echo '<div class="dashboard-header"><h2 class="dashboard-title">' . __('Inbox', 'obenlo') . '</h2></div>';
                    echo do_shortcode('[obenlo_messages_page]');
                } elseif ($action === 'availability') {
                    $avail_module->render_availability_tab();
                } elseif ($action === 'testimony') {
                    $trips_module->render_testimony_section();
                } elseif ($action === 'payouts') {
                    $payouts_module->render_payout_tab();
                } elseif ($action === 'verification') {
                    $overview_module->render_verification_tab();
                } elseif ($action === 'support') {
                    $support_module->render_support_section();
                } elseif ($action === 'announcements') {
                    echo '<div class="dashboard-header"><h2 class="dashboard-title">' . __('Announcements', 'obenlo') . '</h2></div>';
                    echo do_shortcode('[obenlo_broadcasts_page]');
                } elseif ($action === 'guide') {
                    $support_module->render_host_guide();
                } else {
                    $listings_module->render_listings_list();
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
