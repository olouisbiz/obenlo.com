<?php
/**
 * Frontend Host Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class Obenlo_Booking_Frontend_Dashboard
{

    public function init()
    {
        add_shortcode('obenlo_host_dashboard', array($this, 'render_dashboard'));
        add_action('admin_post_nopriv_obenlo_dashboard_save_listing', array($this, 'handle_save_listing'));
        add_action('admin_post_obenlo_dashboard_save_listing', array($this, 'handle_save_listing'));
        add_action('admin_post_obenlo_dashboard_save_storefront', array($this, 'handle_save_storefront'));
        add_action('admin_post_obenlo_dashboard_booking_action', array($this, 'handle_booking_action'));
        add_action('admin_post_obenlo_dashboard_save_availability', array($this, 'handle_save_availability'));
        add_action('admin_post_obenlo_dashboard_delete_listing', array($this, 'handle_delete_listing'));
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

        $user_id = get_current_user_id();
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'overview';
        $listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;

        ob_start();
?>
        <div class="obenlo-dashboard-container">
            <style>
                .obenlo-dashboard-container { display: flex; min-height: 800px; background: #fff; font-family: 'Inter', sans-serif; gap: 0; }
                .dashboard-sidebar { width: 260px; background: #f9f9f9; border-right: 1px solid #eee; padding: 40px 20px; display: flex; flex-direction: column; gap: 5px; }
                .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; text-decoration: none; color: #555; font-weight: 600; border-radius: 12px; transition: all 0.2s; font-size: 0.95rem; }
                .sidebar-link:hover { background: #f0f0f0; color: #e61e4d; }
                .sidebar-link.active { background: #e61e4d; color: #fff; box-shadow: 0 4px 12px rgba(230,30,77,0.2); }
                .sidebar-link svg { width: 20px; height: 20px; }
                
                .dashboard-content { flex-grow: 1; padding: 40px 50px; background: #fff; }
                .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
                .dashboard-title { font-size: 2rem; font-weight: 800; color: #222; margin: 0; }
                
                .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 50px; }
                .stat-card { background: #fff; border: 1px solid #eee; padding: 30px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); text-align: left; transition: transform 0.3s; }
                .stat-card:hover { transform: translateY(-5px); }
                .stat-value { display: block; font-size: 2.2rem; font-weight: 800; color: #222; margin-bottom: 5px; }
                .stat-label { color: #888; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1.2px; }
                
                .admin-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #eee; margin-top: 20px; }
                .admin-table th { background: #fcfcfc; padding: 20px; text-align: left; font-weight: 700; color: #444; border-bottom: 2px solid #f5f5f5; font-size: 0.9rem; }
                .admin-table td { padding: 20px; border-bottom: 1px solid #f5f5f5; color: #666; font-size: 0.95rem; vertical-align: middle; }
                .admin-table tr:last-child td { border-bottom: none; }
                
                .badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
                .badge-success { background: #ecfdf5; color: #10b981; }
                .badge-warning { background: #fff7ed; color: #f97316; }
                .badge-danger { background: #fef2f2; color: #ef4444; }
                .badge-info { background: #eff6ff; color: #3b82f6; }
                
                .btn-primary { background: #e61e4d; color: #fff; border: none; padding: 12px 25px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
                .btn-primary:hover { opacity: 0.9; transform: scale(1.02); }
                .form-section { background: #fff; border: 1px solid #eee; padding: 35px; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-top: 30px; }
                
                @media (max-width: 900px) {
                    .obenlo-dashboard-container { flex-direction: column; }
                    .dashboard-sidebar { 
                        width: 100%; 
                        border-right: none; 
                        border-bottom: 1px solid #eee; 
                        padding: 10px 15px; 
                        overflow-x: auto; 
                        overflow-y: hidden; 
                        flex-direction: row; 
                        flex-wrap: nowrap; 
                        gap: 10px; 
                        -webkit-overflow-scrolling: touch;
                        scrollbar-width: none;
                    }
                    .dashboard-sidebar::-webkit-scrollbar { display: none; }
                    .dashboard-sidebar a { 
                        padding: 8px 15px; 
                        font-size: 0.85rem; 
                        white-space: nowrap;
                        flex-shrink: 0;
                    }
                    .dashboard-content { padding: 20px 15px; overflow-x: hidden; width: 100%; box-sizing: border-box; }
                    .stats-grid { grid-template-columns: 1fr; gap: 15px; }
                    
                    /* Responsive Grid System */
                    .grid-row { flex-direction: column !important; gap: 15px !important; }
                    .grid-col-1-2, .grid-col-1-3, .grid-col-2-3 { width: 100% !important; flex: none !important; }
                    
                    /* Form Section Refinement */
                    .form-section { padding: 25px 20px !important; }
                    
                    /* Restore visibility for previously hidden elements */
                    .mobile-hide { display: table-cell !important; }
                    th.mobile-hide { display: table-cell !important; }
                }
            </style>

            <!-- Sidebar Navigation -->
            <div class="dashboard-sidebar">
                <a href="?action=overview" class="sidebar-link <?php echo $action === 'overview' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span>Overview</span>
                </a>
                <a href="?action=list" class="sidebar-link <?php echo($action === 'list' || $action === 'edit') ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span>My Listings</span>
                </a>
                <a href="?action=bookings" class="sidebar-link <?php echo $action === 'bookings' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>Bookings</span>
                </a>
                <a href="?action=storefront" class="sidebar-link <?php echo $action === 'storefront' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>Storefront</span>
                </a>
                <a href="?action=reviews" class="sidebar-link <?php echo $action === 'reviews' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    <span>Reviews</span>
                </a>
                <a href="?action=messages" class="sidebar-link <?php echo $action === 'messages' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span>Messages</span>
                </a>
                <a href="?action=availability" class="sidebar-link <?php echo $action === 'availability' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Availability</span>
                </a>
                <a href="?action=payouts" class="sidebar-link <?php echo $action === 'payouts' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    <span>Payout Settings</span>
                </a>
                <a href="?action=support" class="sidebar-link <?php echo $action === 'support' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span>Help Center</span>
                </a>

                <div style="margin-top:auto; padding-top:20px; border-top:1px solid #eee;">
                    <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" class="sidebar-link" style="opacity:0.8;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        <span>View Storefront</span>
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="dashboard-content">
                <?php
        // Flash Messages & Errors
        if (isset($_GET['message']) && $_GET['message'] === 'saved') {
            echo '<div class="badge badge-success" style="margin-bottom:30px; display:block; text-align:center; padding:15px; border-radius:12px; font-weight:700;">✓ Action completed successfully!</div>';
        }

        if (isset($_GET['obenlo_error'])) {
            $error_code = sanitize_text_field($_GET['obenlo_error']);
            $error_msg = 'An unexpected error occurred. Please try again.';
            
            switch($error_code) {

                case 'unauthorized':
                    $error_msg = 'Unauthorized: You do not have permission to perform this action.';
                    break;
                case 'security_failed':
                    $error_msg = 'Security check failed. Please refresh the page and try again.';
                    break;
                case 'invalid_booking':
                    $error_msg = 'Invalid booking or listing reference.';
                    break;
                case 'invalid_data':
                    $error_msg = 'Missing required information for this action.';
                    break;
                case 'invalid_listing':
                    $error_msg = 'Invalid listing reference.';
                    break;
                case 'capacity_exceeded':
                    $error_msg = 'Guest count exceeds capacity.';
                    break;
                case 'host_away':
                    $error_msg = 'Selected dates are unavailable (Host vacation).';
                    break;
                case 'day_unavailable':
                    $error_msg = 'Host is not available on this day.';
                    break;
                case 'time_unavailable':
                    $error_msg = 'Selected time is outside operating hours.';
                    break;
                case 'already_booked':
                    $error_msg = 'These dates/times are already booked.';
                    break;
                case 'booking_error':
                    $error_msg = 'Error creating booking. Please try again.';
                    break;
                case 'invalid_payment':
                    $error_msg = 'Invalid payment method selected.';
                    break;
            }
            
            echo '<div style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding:15px 20px; border-radius:12px; margin-bottom:30px; font-weight:700; display:flex; align-items:center; gap:10px; border-left: 4px solid #ef4444;">';
            echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px; height:20px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
            echo '<span>' . esc_html($error_msg) . '</span>';
            echo '</div>';
        }


        if ($action === 'overview') {
            $this->render_overview_tab();
        }
        elseif ($action === 'add' || $action === 'edit') {
            $this->render_listing_form($listing_id);
        }
        elseif ($action === 'bookings') {
            $this->render_bookings_list();
        }
        elseif ($action === 'storefront') {
            $this->render_storefront_form();
        }
        elseif ($action === 'reviews') {
            $this->render_reviews_list();
        }
        elseif ($action === 'messages') {
            echo '<div class="dashboard-header"><h2 class="dashboard-title">Inbox</h2></div>';
            echo do_shortcode('[obenlo_messages_page]');
        }
        elseif ($action === 'availability') {
            $this->render_availability_tab();
        }
        elseif ($action === 'payouts') {
            $this->render_payout_tab();
        }
        elseif ($action === 'verification') {
            $this->render_verification_tab();
        }
        elseif ($action === 'support') {
            $this->render_support_section();
        }
        else {
            $this->render_listings_list();
        }
?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    private function render_overview_tab()
    {
        $user_id = get_current_user_id();

        // Stats Calculation
        $listings_count = count(get_posts(array('post_type' => 'listing', 'author' => $user_id, 'post_parent' => 0, 'posts_per_page' => -1, 'suppress_filters' => false)));

        $bookings = get_posts(array(
            'post_type' => 'booking',
            'posts_per_page' => -1,
            'meta_query' => array(array('key' => '_obenlo_host_id', 'value' => $user_id))
        ));
        $total_earnings = 0;
        foreach ($bookings as $booking) {
            if (get_post_meta($booking->ID, '_obenlo_booking_status', true) === 'completed') {
                $total_earnings += floatval(get_post_meta($booking->ID, '_obenlo_total_price', true));
            }
        }

        $reviews = get_comments(array(
            'post_author__not_in' => array($user_id),
            'post_id__in' => get_posts(array('post_type' => 'listing', 'author' => $user_id, 'fields' => 'ids', 'posts_per_page' => -1, 'suppress_filters' => false)),
            'status' => 'approve',
            'parent' => 0
        ));
        $avg_rating = 0;
        if (!empty($reviews)) {
            $total_rating = 0;
            foreach ($reviews as $review) {
                $total_rating += intval(get_comment_meta($review->comment_ID, '_obenlo_rating', true));
            }
            $avg_rating = round($total_rating / count($reviews), 1);
        }

        $verification_status = Obenlo_Booking_Host_Verification::get_status($user_id);
        $status_label = ucfirst($verification_status);
        $status_class = 'badge-warning';
        if ($verification_status === 'verified')
            $status_class = 'badge-success';
        if ($verification_status === 'rejected')
            $status_class = 'badge-danger';

?>
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h2 class="dashboard-title">Dashboard Overview</h2>
                <div style="margin-top:10px; display:flex; align-items:center; gap:10px;">
                    <span class="badge <?php echo $status_class; ?>">Account Status: <?php echo esc_html($status_label); ?></span>
                    <?php if ($verification_status !== 'verified'): ?>
                        <a href="<?php echo home_url('/host-dashboard?action=verification'); ?>" style="font-size:0.85rem; color:#e61e4d; font-weight:700; text-decoration:none;">Complete Verification →</a>
                    <?php
        else: ?>
                        <a href="<?php echo home_url('/host-onboarding?step=3&force=1'); ?>" style="font-size:0.85rem; color:#666; font-weight:600; text-decoration:none;">Update Payouts</a>
                    <?php
        endif; ?>
                </div>
            </div>
            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" style="color:#e61e4d; text-decoration:none; font-weight:700; font-size:0.9rem; display:flex; align-items:center; gap:6px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Preview My Storefront
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Earnings</span>
                <span class="stat-value">$<?php echo number_format($total_earnings, 2); ?></span>
                <span style="font-size:0.8rem; color:#10b981; font-weight:600;">Completed Bookings</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Listings</span>
                <span class="stat-value"><?php echo $listings_count; ?></span>
                <span style="font-size:0.8rem; color:#3b82f6; font-weight:600;">Live on Obenlo</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Bookings</span>
                <span class="stat-value"><?php echo count($bookings); ?></span>
                <span style="font-size:0.8rem; color:#f97316; font-weight:600;">Lifetime volume</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Avg. Rating</span>
                <span class="stat-value"><?php echo $avg_rating ?: 'N/A'; ?> ★</span>
                <span style="font-size:0.8rem; color:#eab308; font-weight:600;">Host Reputation</span>
            </div>
        </div>

        <div class="dashboard-grid-layout" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:30px;">
            <div class="form-section" style="margin-top:0;">
                <h4 style="margin-top:0; margin-bottom:20px;">Recent Bookings</h4>
                <?php $this->render_bookings_list(5); ?>
            </div>
            <div class="form-section" style="margin-top:0;">
                <h4 style="margin-top:0; margin-bottom:20px;">Performance</h4>
                <p style="color:#666; font-size:0.9rem;">Your response rate and booking conversion stats will appear here as your hosting history grows.</p>
            </div>
        </div>
        <?php
    }

    private function render_verification_tab() {
        $user_id = get_current_user_id();
        $status = Obenlo_Booking_Host_Verification::get_status($user_id);
        
        echo '<div class="dashboard-header"><h2 class="dashboard-title">Identity Verification</h2></div>';
        
        if ($status === 'verified') {
            echo '<div style="background:#dcfce7; color:#166534; padding:20px; border-radius:12px; font-weight:500;">✓ Your identity has been successfully verified. Thank you for building trust in the Obenlo community.</div>';
            return;
        }
        
        if ($status === 'pending') {
            echo '<div style="background:#fef9c3; color:#854d0e; padding:20px; border-radius:12px; font-weight:500;">⏳ Your verification document is currently under review by our team. Approval usually takes 24-48 hours.</div>';
            return;
        }
        
        if ($status === 'rejected') {
            echo '<div style="background:#fee2e2; color:#991b1b; padding:20px; border-radius:12px; margin-bottom:20px; font-weight:500;">⚠️ Your previous verification attempt was rejected. Please ensure the document is clear, legible, and a valid government-issued ID.</div>';
        }
        
        echo '<div style="background:#fff; padding:30px; border-radius:16px; border:1px solid #ddd; max-width:600px;">';
        echo '<h3 style="margin-top:0;">Upload Identification</h3>';
        echo '<p style="color:#666; font-size:0.95em;">To keep our community safe, we require all hosts to upload a valid government-issued ID (Passport, Driver\'s License, or National ID Card) before accepting bookings.</p>';
        echo '<div style="margin: 25px 0;">';
        echo '<input type="file" id="id_document_input" accept="image/jpeg,image/png,application/pdf" style="display:block; width:100%; padding:15px; border:2px dashed #ddd; border-radius:8px; cursor:pointer;">';
        echo '<small style="color:#888; display:block; margin-top:8px;">Max file size: 5MB. Formats: JPG, PNG, PDF</small>';
        echo '</div>';
        echo '<button id="submit_verification" class="btn-primary" style="width:100%; border:none; padding:15px; border-radius:10px; cursor:pointer;">Submit Document for Review</button>';
        echo '</div>';
        
        echo "<script>
        document.getElementById('submit_verification').addEventListener('click', function() {
            const fileInput = document.getElementById('id_document_input');
            if (!fileInput.files[0]) { alert('Please select a file to upload.'); return; }
            
            this.textContent = 'Uploading...';
            this.style.opacity = '0.7';
            this.disabled = true;

            const formData = new FormData();
            formData.append('action', 'obenlo_upload_id');
            formData.append('security', '" . wp_create_nonce("obenlo_onboarding_nonce") . "');
            formData.append('id_document', fileInput.files[0]);
            
            fetch('" . admin_url('admin-ajax.php') . "', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) { 
                    window.location.reload(); 
                } else { 
                    alert(res.data || 'Upload failed. Please try again.'); 
                    this.textContent = 'Submit Document for Review'; 
                    this.style.opacity = '1';
                    this.disabled = false;
                }
            })
            .catch(err => {
                alert('Connection error during upload.');
                this.textContent = 'Submit Document for Review'; 
                this.style.opacity = '1';
                this.disabled = false;
            });
        });
        </script>";
    }

    private function render_listings_list()
    {
        $user_id = get_current_user_id();
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'posts_per_page' => -1,
            'post_parent' => 0,
            'suppress_filters' => false, // Ensure sandbox isolation is applied
        );
        $listings = get_posts($args);

?>
        <div class="dashboard-header">
            <h2 class="dashboard-title">My Listings</h2>
            <a href="?action=add" class="btn-primary">+ Add New Listing</a>
        </div>

        <?php if (empty($listings)): ?>
            <div class="form-section" style="text-align:center; padding: 60px;">
                <p style="color:#666; font-size:1.1rem;">You haven't created any listings yet.</p>
                <a href="?action=add" class="btn-primary" style="margin-top:20px;">Create Your First Listing</a>
            </div>
        <?php
        else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Units/Sessions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $listing):
                $type_terms = wp_get_post_terms($listing->ID, 'listing_type', array('fields' => 'names'));
                $type_display = !empty($type_terms) ? $type_terms[0] : 'Uncategorized';

                $children = get_posts(array(
                    'post_type' => 'listing',
                    'post_parent' => $listing->ID,
                    'posts_per_page' => -1,
                    'suppress_filters' => false,
                ));
?>
                        <tr>
                            <td data-label="Listing">
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <?php if (has_post_thumbnail($listing->ID)): ?>
                                        <img src="<?php echo get_the_post_thumbnail_url($listing->ID, 'thumbnail'); ?>" style="width:40px; height:40px; border-radius:8px; object-fit:cover;">
                                    <?php endif; ?>
                                    <span style="font-weight:700; color:#222; text-align: left;"><?php echo get_the_title($listing->ID); ?></span>
                                </div>
                            </td>
                            <td data-label="Category"><span class="badge badge-info"><?php echo esc_html($type_display); ?></span></td>
                            <td data-label="Status"><span class="badge badge-success"><?php echo ucfirst($listing->post_status); ?></span></td>
                            <td data-label="Units">
                                <span style="font-weight:600; color:#444;"><?php echo count($children); ?> units</span>
                                <a href="?action=add&parent_id=<?php echo $listing->ID; ?>" style="display:block; font-size:0.75rem; color:#e61e4d; text-decoration:none;">+ Add unit</a>
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <a href="?action=edit&listing_id=<?php echo $listing->ID; ?>" style="background:#f0f0f0; color:#222; padding:6px 12px; border-radius:8px; font-weight:700; text-decoration:none; font-size:0.8rem;">Edit</a>
                                    <a href="<?php echo get_permalink($listing->ID); ?>" target="_blank" style="color:#1d9bf0; font-weight:700; text-decoration:none; font-size:0.8rem;">View</a>
                                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                        <input type="hidden" name="action" value="obenlo_dashboard_delete_listing">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing->ID; ?>">
                                        <?php wp_nonce_field('obenlo_delete_listing_' . $listing->ID); ?>
                                        <button type="submit" style="background:none; border:none; color:#e61e4d; font-weight:700; font-size:0.8rem; cursor:pointer; padding:0;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php if (!empty($children)): ?>
                            <?php foreach ($children as $child): ?>
                                <tr style="background:#fafafa;">
                                    <td data-label="Listing" style="padding-left:30px; font-size:0.85rem; color:#666;">└─ <?php echo get_the_title($child->ID); ?></td>
                                    <td></td>
                                    <td data-label="Status"><span class="badge badge-success" style="opacity:0.6; font-size:0.7rem;"><?php echo ucfirst($child->post_status); ?></span></td>
                                    <td></td>
                                    <td data-label="Actions">
                                        <div style="display:flex; gap:12px; align-items:center;">
                                            <a href="?action=edit&listing_id=<?php echo $child->ID; ?>" style="color:#666; font-size:0.8rem; font-weight:600; text-decoration:none;">Edit Unit</a>
                                            <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this unit?');">
                                                <input type="hidden" name="action" value="obenlo_dashboard_delete_listing">
                                                <input type="hidden" name="listing_id" value="<?php echo $child->ID; ?>">
                                                <?php wp_nonce_field('obenlo_delete_listing_' . $child->ID); ?>
                                                <button type="submit" style="background:none; border:none; color:#999; font-size:0.8rem; cursor:pointer; padding:0;">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php
            endforeach; ?>
                </tbody>
            </table>
        <?php
        endif;
    }

    private function render_bookings_list($limit = -1)
    {
        $user_id = get_current_user_id();

        $args = array(
            'post_type' => 'booking',
            'posts_per_page' => $limit,
            'meta_query' => array(
                    array(
                    'key' => '_obenlo_host_id',
                    'value' => $user_id,
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $args['suppress_filters'] = false;
        $bookings = get_posts($args);

        if ($limit === -1): ?>
            <div class="dashboard-header">
                <h2 class="dashboard-title">My Bookings</h2>
            </div>

            <!-- ── Confirmation Code Search ── -->
            <div style="margin-bottom:24px; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <div style="position:relative; flex:1; min-width:260px; max-width:420px;">
                    <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#aaa;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" id="booking-code-search" placeholder="Search by confirmation code or last 4 digits…" style="width:100%; padding:11px 14px 11px 40px; border:2px solid #eee; border-radius:12px; font-size:0.92rem; outline:none; transition:border-color 0.2s; box-sizing:border-box;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#eee'">
                </div>
                <span id="booking-search-count" style="font-size:0.85rem; color:#888;"></span>
                <button onclick="document.getElementById('booking-code-search').value=''; filterBookings();" style="background:none; border:1px solid #eee; border-radius:10px; padding:10px 16px; color:#666; cursor:pointer; font-size:0.85rem;">Clear</button>
            </div>
        <?php
        endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="form-section">
                <p style="color:#666; font-size:1rem;">You have no bookings yet.</p>
            </div>
        <?php
        else: ?>
            <table class="admin-table" id="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Listing</th>
                        <th>Dates / Details</th>
                        <th>Guest</th>
                        <th>Total</th>
                        <th>Status</th>
                        <?php if ($limit === -1): ?><th>Confirmation Code</th><th>Actions</th><?php
            endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking):
                $listing_id = get_post_meta($booking->ID, '_obenlo_listing_id', true);
                $listing_title = $listing_id ? get_the_title($listing_id) : 'Unknown Listing';
                $start_date = get_post_meta($booking->ID, '_obenlo_start_date', true);
                $end_date = get_post_meta($booking->ID, '_obenlo_end_date', true);
                $guests = get_post_meta($booking->ID, '_obenlo_guests', true);
                $total = get_post_meta($booking->ID, '_obenlo_total_price', true);
                $status = get_post_meta($booking->ID, '_obenlo_booking_status', true);
                $conf_code = get_post_meta($booking->ID, '_obenlo_confirmation_code', true);
                $guest_id = get_post_meta($booking->ID, '_obenlo_guest_id', true);
                $checked_in = get_post_meta($booking->ID, '_obenlo_checked_in', true) === 'yes';

                $guest_user = get_user_by('id', $booking->post_author);
                $guest_info = $guest_user ? $guest_user->display_name : 'Guest #' . $booking->post_author;

                $status_badge = 'badge-info';
                if ($status === 'confirmed' || $status === 'approved' || $status === 'completed')
                    $status_badge = 'badge-success';
                if ($status === 'declined' || $status === 'cancelled')
                    $status_badge = 'badge-danger';
                if ($status === 'pending_payment')
                    $status_badge = 'badge-warning';
?>
                        <tr>
                            <td data-label="Booking ID"><span style="font-family:monospace; color:#888;">#<?php echo $booking->ID; ?></span></td>
                            <td data-label="Listing"><strong><?php echo esc_html($listing_title); ?></strong></td>
                            <td data-label="Details">
                                <div><?php echo esc_html($start_date); ?><?php echo $end_date ? ' → ' . esc_html($end_date) : ''; ?></div>
                                <div style="font-size:0.75rem; color:#888;"><?php echo esc_html($guests); ?> guests</div>
                            </td>
                             <td data-label="Guest">
                                <div style="display:flex; align-items:center; gap:10px; justify-content: flex-end;">
                                    <strong><?php echo esc_html($guest_info); ?></strong>
                                    <?php if ($guest_user) : ?>
                                        <button onclick="window.obenloStartChatWith(<?php echo $guest_user->ID; ?>, '<?php echo esc_js($guest_user->display_name); ?>', '<?php echo esc_url(get_avatar_url($guest_user->ID)); ?>')" style="padding:5px; border:1px solid #eee; background:#f9f9f9; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition: all 0.2s;" title="Message Guest">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px; height:14px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($guest_id) : ?>
                                    <div class="guest-id-val" style="font-size:0.75rem; color:#888; font-family:monospace; margin-top:2px;">ID: <?php echo esc_html($guest_id); ?></div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Total"><span style="font-weight:700; color:#222;">$<?php echo number_format(floatval($total), 2); ?></span></td>
                            <td data-label="Status"><span class="badge <?php echo $status_badge; ?>"><?php echo esc_html(str_replace('_', ' ', $status)); ?></span></td>
                             <?php if ($limit === -1): ?>
                            <td data-label="Conf. Code">
                                <?php if ($conf_code): ?>
                                    <code class="conf-code-val" style="background:#f9f9f9; border:1px solid #eee; padding:4px 8px; border-radius:6px; font-family:monospace; font-weight:700; color:#e61e4d; font-size:0.85rem;"><?php echo esc_html($conf_code); ?></code>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>

                                <?php if ($checked_in) : ?>
                                    <div style="margin-top:8px;"><span class="badge badge-success" style="font-size:0.7rem; padding:3px 8px;">✓ CHECKED IN</span></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:10px;">
                                    <?php
                    if (!in_array($status, ['declined', 'cancelled', 'completed'])) {
                        $approve_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=approve'), 'booking_action_' . $booking->ID);
                        $decline_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=decline'), 'booking_action_' . $booking->ID);
                        $complete_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=complete'), 'booking_action_' . $booking->ID);

                        if (!in_array($status, ['approved', 'confirmed'])) {
                            echo '<a href="' . esc_url($approve_url) . '" onclick="return confirm(\'Approve this booking?\')" style="color:#10b981; font-weight:700; text-decoration:none;">Approve</a>';
                        }
                        else {
                            echo '<a href="' . esc_url($complete_url) . '" onclick="return confirm(\'Mark as completed?\')" style="color:#3b82f6; font-weight:700; text-decoration:none;">Complete</a>';
                        }
                        
                        // Check-in Action
                        if (($status === 'approved' || $status === 'confirmed' || $status === 'completed') && !$checked_in) {
                            $checkin_url = wp_nonce_url(admin_url('admin-post.php?action=obenlo_dashboard_booking_action&booking_id=' . $booking->ID . '&do_action=checkin'), 'booking_action_' . $booking->ID);
                            echo '<a href="' . esc_url($checkin_url) . '" onclick="return confirm(\'Check in this guest?\')" style="background:#e61e4d; color:#fff; padding:4px 10px; border-radius:8px; font-weight:700; text-decoration:none; font-size:0.8rem;">Check In</a>';
                        }

                        echo '<a href="' . esc_url($decline_url) . '" onclick="return confirm(\'Decline this booking?\')" style="color:#ef4444; font-weight:700; text-decoration:none;">Decline</a>';
                    }
                    else {
                        echo '<span style="color:#ccc; font-size:0.85rem;">No actions</span>';
                    }
?>
                                </div>
                            </td>
                            <?php
                endif; ?>
                        </tr>
                    <?php
            endforeach; ?>
                </tbody>
            </table>

            <script>
            function filterBookings() {
                var input = document.getElementById('booking-code-search');
                var filter = input.value.toUpperCase();
                var table = document.getElementById('bookings-table');
                var tr = table.getElementsByTagName('tr');
                var count = 0;

                for (var i = 1; i < tr.length; i++) {
                    var confCode = tr[i].querySelector('.conf-code-val') ? tr[i].querySelector('.conf-code-val').textContent : '';
                    var guestId = tr[i].querySelector('.guest-id-val') ? tr[i].querySelector('.guest-id-val').textContent : '';
                    
                    if (confCode.toUpperCase().indexOf(filter) > -1 || guestId.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        count++;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
                
                var countDisplay = document.getElementById('booking-search-count');
                if (filter === "") {
                    countDisplay.textContent = "";
                } else {
                    countDisplay.textContent = "Found " + count + " booking(s)";
                }
            }
            document.getElementById('booking-code-search').addEventListener('keyup', filterBookings);
            </script>
        <?php
        endif;
    }

    private function render_reviews_list()
    {
        $user_id = get_current_user_id();

        // Get all listings for this host
        $args = array(
            'post_type' => 'listing',
            'author' => $user_id,
            'fields' => 'ids',
            'posts_per_page' => -1
        );
        $listing_ids = get_posts($args);

        echo '<h3>My Reviews</h3>';

        if (empty($listing_ids)) {
            echo '<p>You have no listings yet, so no reviews can be shown.</p>';
            return;
        }

        $comments = get_comments(array(
            'post_author__not_in' => array($user_id), // Exclude host's own replies
            'post_id__in' => $listing_ids,
            'status' => 'approve',
            'parent' => 0 // Only top-level reviews
        ));

        if (empty($comments)) {
            echo '<p>You have not received any reviews yet.</p>';
        }
        else {
            echo '<div class="reviews-list" style="display:flex; flex-direction:column; gap:20px;">';
            foreach ($comments as $comment) {
                $rating = get_comment_meta($comment->comment_ID, '_obenlo_rating', true);
                $listing_title = get_the_title($comment->comment_post_ID);
                $listing_url = get_permalink($comment->comment_post_ID);

                echo '<div class="review-item" style="border:1px solid #eee; padding:20px; border-radius:12px; background:#fff;">';
                echo '<div style="display:flex; justify-content:space-between; margin-bottom:10px;">';
                echo '<div><strong>' . esc_html($comment->comment_author) . '</strong> on <a href="' . esc_url($listing_url) . '">' . esc_html($listing_title) . '</a></div>';
                echo '<div style="font-size:0.85em; color:#666;">' . get_comment_date('', $comment->comment_ID) . '</div>';
                echo '</div>';

                if ($rating) {
                    $stars = str_repeat('★', intval($rating)) . str_repeat('☆', 5 - intval($rating));
                    echo '<div style="color:#FFD700; font-size:1.2em; margin-bottom:10px;">' . $stars . ' (' . intval($rating) . '/5)</div>';
                }

                echo '<div style="line-height:1.6; color:#333;">' . wpautop(esc_html($comment->comment_content)) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
    }

    private function render_listing_form($listing_id = 0)
    {
        $title = '';
        $content = '';
        $price = '';
        $capacity = '';
        $addons = array();
        $location = '';
        $policy_type = 'global';
        $policy_cancel = '';
        $policy_refund = '';
        $policy_other = '';
        $parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : 0;
        $selected_type = '';
        $virtual_link = '';
        $event_is_fixed = 'no';
        $event_date = '';
        $event_start_time = '';
        $event_end_time = '';
        $event_location_type = 'virtual';

        if ($listing_id > 0) {
            $post = get_post($listing_id);
            if ($post && $post->post_author == get_current_user_id()) {
                $title = get_the_title($listing_id);
                // Use the same filter logic for content
                $sandboxed_content = get_post_meta($listing_id, '_obenlo_sandboxed_content', true);
                $content = $sandboxed_content ? $sandboxed_content : $post->post_content;
                $parent_id = $post->post_parent;
                $price = get_post_meta($listing_id, '_obenlo_price', true);
                $capacity = get_post_meta($listing_id, '_obenlo_capacity', true);
                $location = get_post_meta($listing_id, '_obenlo_location', true);
                $virtual_link = get_post_meta($listing_id, '_obenlo_virtual_link', true);
                $event_is_fixed = get_post_meta($listing_id, '_obenlo_event_is_fixed', true) ?: 'no';
                $event_date = get_post_meta($listing_id, '_obenlo_event_date', true);
                $event_start_time = get_post_meta($listing_id, '_obenlo_event_start_time', true);
                $event_end_time = get_post_meta($listing_id, '_obenlo_event_end_time', true);
                $event_location_type = get_post_meta($listing_id, '_obenlo_event_location_type', true) ?: 'virtual';

                $addons_json = get_post_meta($listing_id, '_obenlo_addons_structured', true);
                if (!empty($addons_json)) {
                    $decoded = json_decode($addons_json, true);
                    if (is_array($decoded))
                        $addons = $decoded;
                }

                $pricing_model = get_post_meta($listing_id, '_obenlo_pricing_model', true) ?: 'per_night';
                $duration_val = get_post_meta($listing_id, '_obenlo_duration_val', true);
                $duration_unit = get_post_meta($listing_id, '_obenlo_duration_unit', true) ?: 'hours';
                $requires_slots = get_post_meta($listing_id, '_obenlo_requires_slots', true) ?: 'no';

                $type_terms = wp_get_post_terms($listing_id, 'listing_type');
                if (!empty($type_terms) && !is_wp_error($type_terms)) {
                    $selected_type = $type_terms[0]->term_id;
                }

                $policy_type = get_post_meta($listing_id, '_obenlo_policy_type', true) ?: 'global';
                $policy_cancel = get_post_meta($listing_id, '_obenlo_policy_cancel', true);
                $policy_refund = get_post_meta($listing_id, '_obenlo_policy_refund', true);
                $policy_other = get_post_meta($listing_id, '_obenlo_policy_other', true);
            }
            else {
                echo '<p>Invalid listing.</p>';
                return;
            }
        }
        else {
            // Default blank values for new listings
            $pricing_model = 'per_night';
            $duration_val = '';
            $duration_unit = 'hours';
            $requires_slots = 'no';
        }

        $is_child = ($parent_id > 0);
        $parent_post = null;
        if ($is_child) {
            $parent_post = get_post($parent_id);
            // Inherit type from parent
            $parent_terms = wp_get_post_terms($parent_id, 'listing_type');
            if (!empty($parent_terms) && !is_wp_error($parent_terms)) {
                $selected_type = $parent_terms[0]->term_id;
            }
        }

        $form_action = esc_url(admin_url('admin-post.php'));
        
        // Contextual Labels
        $title_label = $is_child ? 'Unit / Session Name' : 'Business / Property Name';
        $desc_label = $is_child ? 'About this Unit / Session' : 'About your Business / Property';
        $media_label = $is_child ? 'Unit Specific Photos' : 'Primary Property Photos';
        $media_hint = $is_child ? 'Upload up to 3 photos' : 'Upload up to 10 photos';
        $media_limit = $is_child ? 3 : 10;

        // Derive category flag for dynamic headings
        $category_flag = 'default';
        if ($selected_type) {
            $type_term = get_term($selected_type, 'listing_type');
            if ($type_term && !is_wp_error($type_term)) {
                $slug = $type_term->slug;
                $name_lower = strtolower($type_term->name);
                if (strpos($name_lower, 'stay') !== false || in_array($slug, ['hotel', 'guest-house']))
                    $category_flag = 'stay';
                elseif (strpos($name_lower, 'experience') !== false || strpos($name_lower, 'tour') !== false)
                    $category_flag = 'experience';
                elseif (in_array($slug, ['event', 'show']) || strpos($name_lower, 'event') !== false)
                    $category_flag = 'event';
                elseif (strpos($name_lower, 'service') !== false || in_array($slug, ['chauffeur', 'cook', 'barbershop', 'hairdresser', 'concierge', 'personal-assistant', 'babysitter', 'dogsitter']))
                    $category_flag = 'service';
            }
        }

?>
        <div class="dashboard-header">
            <h2 class="dashboard-title"><?php echo($listing_id ? 'Edit ' . ($is_child ? 'Unit/Session' : 'Listing') : 'Add New ' . ($is_child ? 'Unit/Session' : 'Listing')); ?></h2>
            <a href="?action=list" style="color:#666; font-weight:700; text-decoration:none;">← Back to Listings</a>
        </div>

        <div style="max-width:800px;">
            <?php if ($is_child): ?>
                <p style="background: #eff6ff; color: #1e40af; padding: 15px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem;">
                    Adding a specific bookable unit to: <strong><?php echo esc_html($parent_post->post_title); ?></strong>
                </p>
            <?php
        else: ?>
                <p style="color:#666; font-size:0.95rem; margin-bottom:30px;">
                    Create the main property, experience, or service. (You will add specific bookable rooms/sessions later).
                </p>
            <?php
        endif; ?>

            <form action="<?php echo $form_action; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="obenlo_dashboard_save_listing">
                <input type="hidden" name="listing_id" value="<?php echo esc_attr($listing_id); ?>">
                <?php if ($is_child): ?>
                    <input type="hidden" name="parent_id" value="<?php echo esc_attr($parent_id > 0 ? $parent_id : filter_input(INPUT_GET, 'parent_id', FILTER_SANITIZE_NUMBER_INT)); ?>">
                    <input type="hidden" name="listing_type" value="<?php echo esc_attr($selected_type); ?>">
                <?php endif; ?>
                
                <?php 
                $is_demo_edit = ($listing_id > 0 && get_post_meta($listing_id, '_obenlo_is_demo', true) === 'yes');
                $is_demo_create = (isset($_GET['demo']) && $_GET['demo'] == '1');
                if (($is_demo_edit || $is_demo_create) && current_user_can('administrator')): ?>
                    <input type="hidden" name="is_demo" value="1">
                    <div style="background:#fff1f3; color:#e61e4d; padding:20px; border-radius:12px; margin-bottom:30px; border:1px solid #fecdd3;">
                        <h4 style="margin-top:0; margin-bottom:10px; color:#e61e4d;">🛠️ Demo Listing Configuration</h4>
                        <p style="margin-top:0; margin-bottom:20px; font-size:0.9rem;">You are configuring a Demo Listing. Specify the simulated Host details below.</p>
                        
                        <div class="grid-row" style="margin-bottom:15px;">
                            <div style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Host Name</label>
                                <input type="text" name="_obenlo_demo_host_name" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_name', true)); ?>" style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Host Tagline</label>
                                <input type="text" name="_obenlo_demo_host_tagline" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_tagline', true)); ?>" style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;">
                            </div>
                        </div>
                        <div class="grid-row" style="margin-bottom:15px;">
                            <div style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Instagram (e.g. @obenlo)</label>
                                <input type="text" name="_obenlo_demo_host_instagram" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_instagram', true)); ?>" placeholder="@username" style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;">
                            </div>
                            <div style="flex:1;">
                                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Facebook URL</label>
                                <input type="text" name="_obenlo_demo_host_facebook" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_facebook', true)); ?>" placeholder="https://facebook.com/..." style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;">
                            </div>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Host Location</label>
                            <input type="text" name="_obenlo_demo_host_location" value="<?php echo esc_attr(get_post_meta($listing_id, '_obenlo_demo_host_location', true)); ?>" style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:5px;">Demo Host Bio</label>
                            <textarea name="_obenlo_demo_host_bio" rows="3" style="width:100%; padding:10px; border:1px solid #fecdd3; border-radius:8px;"><?php echo esc_textarea(get_post_meta($listing_id, '_obenlo_demo_host_bio', true)); ?></textarea>
                        </div>
                    </div>
                <?php endif; ?>

                <?php wp_nonce_field('dashboard_save_listing', 'dashboard_listing_nonce'); ?>

                <!-- Basic Information -->
                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Basic Information</h4>
                    
                    <div style="margin-bottom:20px;">
                        <label id="listing_title_label" style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo $title_label; ?></label>
                        <input type="text" name="listing_title" value="<?php echo esc_attr($title); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; transition:border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>


                    <?php if (!$is_child): ?>
                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Category</label>
                            <select name="listing_type" id="smart_listing_type" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                <option value="">-- Select a Category --</option>
                                <?php
            $types = get_terms(array('taxonomy' => 'listing_type', 'hide_empty' => false));
            if (!is_wp_error($types)) {
                foreach ($types as $type) {
                    $selected = ($selected_type == $type->term_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($type->term_id) . '" data-slug="' . esc_attr($type->slug) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
                }
            }
?>
                            </select>
                        </div>

                        <div style="margin-bottom:20px;" id="generic_location_wrapper">
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Location</label>
                            <input type="text" name="listing_location" value="<?php echo esc_attr($location); ?>" placeholder="e.g. Tulum, Mexico" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom:0;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;"><?php echo $desc_label; ?></label>
                        <textarea name="listing_content" rows="6" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;" placeholder="<?php echo $is_child ? 'Describe this specific unit, room, or session...' : 'Describe your overall business, property, or service group...'; ?>"><?php echo esc_textarea($content); ?></textarea>
                    </div>

                    <!-- Event Specific Configuration -->
                    <div id="event_config_wrapper" style="margin-top:20px; display:none; padding:20px; background:#f9f9f9; border-radius:12px; border:1px solid #eee;">
                        <h4 style="margin-top:0; margin-bottom:15px; color:#333;">Event Schedule & Location</h4>
                        
                        <div style="margin-bottom:15px;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700;">
                                <input type="checkbox" name="event_is_fixed" value="yes" id="event_is_fixed_toggle" <?php checked($event_is_fixed, 'yes'); ?>>
                                Specific Scheduled Time (e.g., Monday 8 April, 4pm-10pm)
                            </label>
                        </div>

                        <div id="fixed_time_fields" style="display:<?php echo ($event_is_fixed === 'yes') ? 'block' : 'none'; ?>; margin-bottom:20px;">
                            <div class="grid-row">
                                <div class="grid-col-1-3">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;">Event Date</label>
                                    <input type="date" name="event_date" value="<?php echo esc_attr($event_date); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                                <div class="grid-col-1-3">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;">Start Time</label>
                                    <input type="time" name="event_start_time" value="<?php echo esc_attr($event_start_time); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                                <div class="grid-col-1-3">
                                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#666; margin-bottom:5px;">End Time</label>
                                    <input type="time" name="event_end_time" value="<?php echo esc_attr($event_end_time); ?>" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Event Type</label>
                            <select name="event_location_type" id="event_location_type_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                <option value="virtual" <?php selected($event_location_type, 'virtual'); ?>>Virtual (Zoom, Google Meet, etc.)</option>
                                <option value="in_person" <?php selected($event_location_type, 'in_person'); ?>>In-Person (Physical Address)</option>
                            </select>
                        </div>

                        <div id="virtual_link_wrapper" style="display:<?php echo ($event_location_type === 'virtual') ? 'block' : 'none'; ?>;">
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Virtual Meeting Link</label>
                            <input type="url" name="virtual_link" value="<?php echo esc_url($virtual_link); ?>" placeholder="https://zoom.us/j/..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>

                        <div id="in_person_address_wrapper" style="display:<?php echo ($event_location_type === 'in_person') ? 'block' : 'none'; ?>;">
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Event Address</label>
                            <input type="text" name="listing_event_address" value="<?php echo esc_attr($location); ?>" placeholder="Enter physical address..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                    </div>
                </div>

                <!-- Pricing, Model & Capacity -->
                <?php if ($is_child): ?>
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Pricing & Booking Rules</h4>
                        
                        <div class="grid-row" style="margin-bottom:20px;">
                            <div class="grid-col-1-2">
                                <label id="listing_price_label" style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Base Price</label>
                                <div style="position:relative;">
                                    <span style="position:absolute; left:12px; top:12px; color:#888;">$</span>
                                    <input type="number" step="0.01" name="listing_price" value="<?php echo esc_attr($price); ?>" required style="width:100%; padding:12px 12px 12px 30px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;">
                                </div>
                            </div>
                            <div id="pricing_model_wrapper" class="grid-col-1-2">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Pricing Model</label>
                                <select name="pricing_model" id="pricing_model" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                    <option value="per_night" <?php selected($pricing_model, 'per_night'); ?>>Per Night</option>
                                    <option value="per_day" <?php selected($pricing_model, 'per_day'); ?>>Per Day</option>
                                    <option value="per_hour" <?php selected($pricing_model, 'per_hour'); ?>>Per Hour</option>
                                    <option value="per_session" <?php selected($pricing_model, 'per_session'); ?>>Per Session / Appointment</option>
                                    <option value="per_person" <?php selected($pricing_model, 'per_person'); ?>>Per Person</option>
                                    <option value="flat_fee" <?php selected($pricing_model, 'flat_fee'); ?>>Flat Fee</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-row" style="margin-bottom:20px;">
                            <div id="capacity_wrapper" class="grid-col-1-2">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Max Capacity (Guests/Tickets)</label>
                                <input type="number" name="listing_capacity" value="<?php echo esc_attr($capacity); ?>" placeholder="Leave blank if not applicable" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;">
                            </div>
                            <div id="duration_wrapper" class="grid-col-1-2" style="display:flex; gap:10px;">
                                <div style="flex:1;">
                                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Duration</label>
                                    <input type="number" step="0.5" name="duration_val" value="<?php echo esc_attr($duration_val); ?>" placeholder="e.g. 1.5" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; box-sizing:border-box;">
                                </div>
                                <div style="flex:1;">
                                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Unit</label>
                                    <select name="duration_unit" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                        <option value="hours" <?php selected($duration_unit, 'hours'); ?>>Hours</option>
                                        <option value="minutes" <?php selected($duration_unit, 'minutes'); ?>>Minutes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="requires_slots_wrapper" style="margin-top:10px;">
                            <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                                <input type="checkbox" name="requires_slots" value="yes" <?php checked($requires_slots, 'yes'); ?> style="width:18px; height:18px;">
                                <span style="font-weight:700; color:#444;">Requires Booking Time Slots</span>
                            </label>
                            <p style="font-size:0.85rem; color:#666; margin-top:5px; margin-left:28px;">Check this if you want the calendar to automatically show available time slots during your business hours (e.g. for Haircuts, Spa treatments).</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-section" style="background:#f0f9ff; border:1px solid #bae6fd; padding:20px; border-radius:12px; margin-bottom:30px;">
                        <div style="display:flex; gap:15px; align-items:center;">
                            <div style="font-size:1.5rem;">ℹ️</div>
                            <div>
                                <h4 style="margin:0 0 5px 0; color:#0369a1;">Business Profile Mode</h4>
                                <p style="margin:0; font-size:0.9rem; color:#0c4a6e;">This is your main profile. You will add bookable units, sessions, or events (with their own pricing) after saving this profile.</p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="listing_price" value="0">
                <?php endif; ?>



                <?php 
                $amenity_title = 'Amenities';
                if (in_array($category_flag, ['experience', 'event', 'show'])) {
                    $amenity_title = 'What\'s Included';
                }
                ?>
                <!-- Amenities -->
                <div class="form-section">
                    <h4 id="amenities_heading" style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo esc_html($amenity_title); ?></h4>
                    <div id="amenities-container">
                        <?php
        $current_amenities = wp_get_post_terms($listing_id, 'listing_amenity', array('fields' => 'names'));
        if (is_wp_error($current_amenities))
            $current_amenities = array();
        if (!empty($current_amenities)):
            foreach ($current_amenities as $amenity_name): ?>
                                <div class="amenity-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                    <input type="text" name="listing_amenities_repeater[]" value="<?php echo esc_attr($amenity_name); ?>" placeholder="e.g. WiFi or Textbook" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                    <button type="button" class="remove-amenity-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                                </div>
                            <?php
            endforeach; ?>
                        <?php
        endif; ?>
                    </div>
                    <button type="button" id="add-amenity-btn" style="background:#f9f9f9; border:1px dashed #ccc; color:#666; width:100%; padding:12px; border-radius:10px; cursor:pointer; font-weight:600; transition:all 0.2s;" onmouseover="this.style.borderColor='#e61e4d';this.style.color='#e61e4d'" onmouseout="this.style.borderColor='#ccc';this.style.color='#666'">+ Add New</button>
                    
                    <template id="amenity-template">
                        <div class="amenity-row" style="display:flex; gap:10px; margin-bottom:12px;">
                            <input type="text" name="listing_amenities_repeater[]" value="" placeholder="e.g. WiFi or Textbook" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                            <button type="button" class="remove-amenity-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                        </div>
                    </template>
                </div>

                <?php if ($is_child): ?>
                    <!-- Addons -->
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Add-ons (Optional Upsells)</h4>
                        <div id="addons-container">
                            <?php if (!empty($addons)):
                foreach ($addons as $addon): ?>
                                    <div class="addon-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                        <input type="text" name="addon_names[]" value="<?php echo esc_attr($addon['name']); ?>" placeholder="Addon (e.g. Breakfast)" style="flex:2; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                        <input type="number" step="0.01" name="addon_prices[]" value="<?php echo esc_attr($addon['price']); ?>" placeholder="$" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                        <button type="button" class="remove-addon-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                                    </div>
                                <?php
                endforeach; ?>
                            <?php
            endif; ?>
                        </div>
                        <button type="button" id="add-addon-btn" style="background:#f9f9f9; border:1px dashed #ccc; color:#666; width:100%; padding:12px; border-radius:10px; cursor:pointer; font-weight:600;">+ Add New Addon</button>
                        
                        <template id="addon-template">
                            <div class="addon-row" style="display:flex; gap:10px; margin-bottom:12px;">
                                <input type="text" name="addon_names[]" value="" placeholder="Addon (e.g. Breakfast)" style="flex:2; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <input type="number" step="0.01" name="addon_prices[]" value="" placeholder="$" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                                <button type="button" class="remove-addon-btn" style="background:#fef2f2; color:#ef4444; border:none; border-radius:8px; padding:0 15px; cursor:pointer; font-weight:800;">&times;</button>
                            </div>
                        </template>
                    </div>
                <?php
        endif; ?>

                <!-- Media -->
                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;"><?php echo $media_label; ?></h4>
                    
                    <?php if ($listing_id > 0):
            $images = get_attached_media('image', $listing_id);
            $curr_count = count($images);
            $thumb_id = get_post_thumbnail_id($listing_id);
            // ... (rest of logic)
?>
                            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap:15px; margin-bottom:20px;">
                                <?php foreach ($images as $img_id => $img):
                    $img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                    $is_featured = ($img_id == $thumb_id);
?>
                                    <div style="position:relative; aspect-ratio:1; border-radius:12px; overflow:hidden; border:2px solid <?php echo $is_featured ? '#e61e4d' : '#eee'; ?>;">
                                        <img src="<?php echo esc_url($img_url); ?>" style="width:100%; height:100%; object-fit:cover;">
                                        <?php if ($is_featured): ?>
                                            <div style="position:absolute; top:5px; left:5px; background:#e61e4d; color:#fff; font-size:8px; font-weight:800; padding:2px 6px; border-radius:20px;">COVER</div>
                                        <?php endif; ?>
                                        <label style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.6); color:#fff; font-size:10px; text-align:center; padding:3px; cursor:pointer;">
                                            <input type="checkbox" name="delete_images[]" value="<?php echo esc_attr($img_id); ?>"> Remove
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    
                    <div style="background:#fcfcfc; border:2px dashed #eee; padding:30px; border-radius:15px; text-align:center;">
                        <input type="file" name="listing_images[]" multiple accept="image/*" style="display:none;" id="listing_file_input" data-limit="<?php echo $media_limit; ?>">
                        <label for="listing_file_input" style="cursor:pointer; color:#888;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px; height:40px; margin-bottom:10px; color:#ccc;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <div style="font-weight:700; color:#444;">Click to upload photos</div>
                            <div style="font-size:0.8rem; color:#e61e4d;"><?php echo $media_hint; ?></div>
                        </label>
                    </div>
                </div>

                <?php if (!$is_child): ?>
                    <!-- Policies -->
                    <div class="form-section">
                        <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Policies & Rules</h4>
                        
                        <div style="margin-bottom:20px;">
                            <select name="policy_type" id="policy_type_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; background:#fff;">
                                <option value="global" <?php selected($policy_type, 'global'); ?>>Use Obenlo Global Policies (Standard)</option>
                                <option value="custom" <?php selected($policy_type, 'custom'); ?>>Set Custom Policies</option>
                            </select>
                        </div>
                        
                        <div id="custom_policies_fields" style="display:<?php echo($policy_type === 'custom') ? 'block' : 'none'; ?>;">
                            <div style="margin-bottom:20px;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Cancellation Policy</label>
                                <textarea name="policy_cancel" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_cancel); ?></textarea>
                            </div>
                            <div style="margin-bottom:20px;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Refund Policy</label>
                                <textarea name="policy_refund" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_refund); ?></textarea>
                            </div>
                            <div style="margin-bottom:0;">
                                <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">House Rules / Other</label>
                                <textarea name="policy_other" rows="3" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($policy_other); ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-top:40px; margin-bottom:100px;">
                    <button type="submit" class="btn-primary" style="padding:15px 40px; font-size:1.1rem; width:100%;">Save <?php echo($is_child ? 'Unit' : 'Listing'); ?> Now</button>
                    <p style="text-align:center; color:#888; font-size:0.85rem; margin-top:15px;">By saving, you agree to Obenlo's hosting standard and quality guidelines.</p>
                </div>
            </form>
        </div>
        <?php
        // Inject JavaScript for dynamic Category logic
        $this->render_form_javascript($is_child, $selected_type);
    }

    private function render_form_javascript($is_child, $selected_type)
    {
        // We will output a small script to adjust labels based on category
        // In a real WP environment, we'd enqueue this, but for the shortcode MVP this is clean enough.
        $types = get_terms(array('taxonomy' => 'listing_type', 'hide_empty' => false));
        $type_map = array();
        $slug_map = array();

        if (!is_wp_error($types)) {
            foreach ($types as $type) {
                // Very simple heuristic to map term IDs to normalized names
                $name_lower = strtolower($type->name);
                $slug = $type->slug;
                if (strpos($name_lower, 'stay') !== false || in_array($slug, ['hotel', 'guest-house']))
                    $type_map[$type->term_id] = 'stay';
                elseif (strpos($name_lower, 'experience') !== false || strpos($name_lower, 'tour') !== false)
                    $type_map[$type->term_id] = 'experience';
                elseif (in_array($slug, ['event', 'show']) || strpos($name_lower, 'event') !== false)
                    $type_map[$type->term_id] = 'event';
                elseif (strpos($name_lower, 'service') !== false || in_array($slug, ['chauffeur', 'cook', 'barbershop', 'hairdresser', 'concierge', 'personal-assistant', 'babysitter', 'dogsitter']))
                    $type_map[$type->term_id] = 'service';
                else
                    $type_map[$type->term_id] = 'default';
                
                $slug_map[$type->term_id] = $slug;
            }
        }

        $init_type_id = $selected_type ? $selected_type : '';
        $type_map_json = json_encode($type_map);
?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var typeMap = <?php echo json_encode($type_map); ?>;
            var slugMap = <?php echo json_encode($slug_map); ?>;
            var isChild = <?php echo $is_child ? 'true' : 'false'; ?>;
            var initTypeId = '<?php echo esc_js($init_type_id); ?>';
            
            var typeSelect = document.querySelector('select[name="listing_type"]');
            var priceLabel = document.getElementById('listing_price_label');
            
            var capInput = document.querySelector('input[name="listing_capacity"]');
            var capContainer = capInput ? capInput.closest('div') : null;
            if(!capContainer) capContainer = document.getElementById('capacity_wrapper');
            var capLabel = capContainer ? capContainer.querySelector('label') : null;

            var pricingModel = document.getElementById('pricing_model');
            var durationWrapper = document.getElementById('duration_wrapper');
            var slotsWrapper = document.getElementById('requires_slots_wrapper');
            var eventConfigWrapper = document.getElementById('event_config_wrapper');
            var genericLocationWrapper = document.getElementById('generic_location_wrapper');
            
            function updateFormLogic(typeId) {
                var category = typeMap[typeId] || 'default';
                var slug = slugMap[typeId] || '';
                
                // Reset defaults
                if (capContainer) capContainer.style.display = 'block';
                if (durationWrapper) durationWrapper.style.display = 'flex';
                if (slotsWrapper) slotsWrapper.style.display = 'block';
                if (eventConfigWrapper) eventConfigWrapper.style.display = 'none';
                if (genericLocationWrapper) genericLocationWrapper.style.display = 'block';

                var amenHeading = document.getElementById('amenities_heading');

                // Reset Pricing Model Options
                if (pricingModel) {
                    Array.from(pricingModel.options).forEach(opt => {
                        opt.hidden = false;
                        opt.disabled = false;
                    });
                }

                if (category === 'stay') {
                    if (priceLabel) priceLabel.innerText = 'Price (Per Night)';
                    if (capLabel) capLabel.innerText = 'Capacity/Max Guests';
                    if (pricingModel) {
                        pricingModel.value = 'per_night';
                        Array.from(pricingModel.options).forEach(opt => {
                            if (!['per_night', 'per_day', 'flat_fee'].includes(opt.value)) {
                                opt.hidden = true;
                                opt.disabled = true;
                            }
                        });
                    }
                    if (durationWrapper) durationWrapper.style.display = 'none';
                    if (slotsWrapper) slotsWrapper.style.display = 'none';
                    if (amenHeading) amenHeading.innerText = 'Amenities';
                } else if (category === 'event' || category === 'experience') {
                    if (priceLabel) priceLabel.innerText = category === 'event' ? 'Price (Per Ticket)' : 'Price (Per Person/Ticket)';
                    if (capLabel) capLabel.innerText = category === 'event' ? 'Total Tickets Available' : 'Max Tickets/Participants';
                    if (pricingModel) {
                        pricingModel.value = 'per_person';
                        Array.from(pricingModel.options).forEach(opt => {
                            if (!['per_person', 'flat_fee'].includes(opt.value)) {
                                opt.hidden = true;
                                opt.disabled = true;
                            }
                        });
                    }
                    if (slotsWrapper) slotsWrapper.style.display = 'none';
                    if (eventConfigWrapper) eventConfigWrapper.style.display = 'block';
                    if (genericLocationWrapper) genericLocationWrapper.style.display = 'none';
                    if (amenHeading) amenHeading.innerText = 'What\'s Included';
                } else if (category === 'service') {
                    if (priceLabel) priceLabel.innerText = 'Price (Per Hour/Session)';
                    if (capLabel) capLabel.innerText = 'Max Clients per Slot';
                    if (pricingModel) {
                        pricingModel.value = 'per_session';
                        Array.from(pricingModel.options).forEach(opt => {
                            if (!['per_session', 'per_hour', 'flat_fee'].includes(opt.value)) {
                                opt.hidden = true;
                                opt.disabled = true;
                            }
                        });
                        // Special cases for services
                        if(['babysitter', 'dogsitter', 'chauffeur'].includes(slug)) {
                            pricingModel.value = 'per_hour';
                        }
                    }
                    if (amenHeading) amenHeading.innerText = 'Amenities';
                } else {
                    if (priceLabel) priceLabel.innerText = 'Price (Base)';
                    if (capLabel) capLabel.innerText = 'Capacity/Max Guests';
                    if (amenHeading) amenHeading.innerText = 'Amenities';
                }

                // Call location toggles within footer JS context
                updateEventLocationToggles();
            }

            function updateEventLocationToggles() {
                var eventLocationSelect = document.getElementById('event_location_type_select');
                var vLinkWrapper = document.getElementById('virtual_link_wrapper');
                var inPersWrapper = document.getElementById('in_person_address_wrapper');
                if(eventLocationSelect && vLinkWrapper && inPersWrapper) {
                    vLinkWrapper.style.display = eventLocationSelect.value === 'virtual' ? 'block' : 'none';
                    inPersWrapper.style.display = eventLocationSelect.value === 'in_person' ? 'block' : 'none';
                }
            }
            
            if (typeSelect) {
                typeSelect.addEventListener('change', function(e) {
                    updateFormLogic(e.target.value);
                });
            }
            
            // Initialize
            if (initTypeId) {
                updateFormLogic(initTypeId);
            } else if (typeSelect && typeSelect.value) {
                updateFormLogic(typeSelect.value);
            }
            
            // Addons Repeater Logic
            var addAddonBtn = document.getElementById('add-addon-btn');
            var addonsContainer = document.getElementById('addons-container');
            var addonTemplate = document.getElementById('addon-template');
            
            if(addAddonBtn && addonsContainer && addonTemplate) {
                addAddonBtn.addEventListener('click', function() {
                    var clone = addonTemplate.content.cloneNode(true);
                    addonsContainer.appendChild(clone);
                });
                
                addonsContainer.addEventListener('click', function(e) {
                    if(e.target.classList.contains('remove-addon-btn')) {
                        e.target.closest('.addon-row').remove();
                    }
                });
            }

            // Amenities Repeater Logic
            var addAmenityBtn = document.getElementById('add-amenity-btn');
            var amenitiesContainer = document.getElementById('amenities-container');
            var amenityTemplate = document.getElementById('amenity-template');
            
            if(addAmenityBtn && amenitiesContainer && amenityTemplate) {
                addAmenityBtn.addEventListener('click', function() {
                    var clone = amenityTemplate.content.cloneNode(true);
                    amenitiesContainer.appendChild(clone);
                });
                
                amenitiesContainer.addEventListener('click', function(e) {
                    if(e.target.classList.contains('remove-amenity-btn')) {
                        e.target.closest('.amenity-row').remove();
                    }
                });
            }

            // Policies Logic
            var policySelect = document.getElementById('policy_type_select');
            var customFields = document.getElementById('custom_policies_fields');
            if(policySelect && customFields) {
                policySelect.addEventListener('change', function(e) {
                    if(e.target.value === 'custom') {
                        customFields.style.display = 'block';
                    } else {
                        customFields.style.display = 'none';
                    }
                });
            }

            // Event Configuration JS
            var eventFixedToggle = document.getElementById('event_is_fixed_toggle');
            var fixedTimeFields = document.getElementById('fixed_time_fields');
            if(eventFixedToggle && fixedTimeFields) {
                eventFixedToggle.addEventListener('change', function() {
                    fixedTimeFields.style.display = this.checked ? 'block' : 'none';
                });
            }

            var eventLocationSelect = document.getElementById('event_location_type_select');
            var vLinkWrapper = document.getElementById('virtual_link_wrapper');
            var inPersWrapper = document.getElementById('in_person_address_wrapper');
            if(eventLocationSelect && vLinkWrapper && inPersWrapper) {
                eventLocationSelect.addEventListener('change', function() {
                    vLinkWrapper.style.display = this.value === 'virtual' ? 'block' : 'none';
                    inPersWrapper.style.display = this.value === 'in_person' ? 'block' : 'none';
                });
            }
        });
        </script>
        <?php
    }

    public function render_storefront_form()
    {
        $user_id = get_current_user_id();
        $store_name = get_user_meta($user_id, 'obenlo_store_name', true);
        $store_desc = get_user_meta($user_id, 'obenlo_store_description', true);
        $store_location = get_user_meta($user_id, 'obenlo_store_location', true);
        $store_logo_id = get_user_meta($user_id, 'obenlo_store_logo', true);
        $store_banner_id = get_user_meta($user_id, 'obenlo_store_banner', true);
        $store_tagline = get_user_meta($user_id, 'obenlo_store_tagline', true);
        $store_video = get_user_meta($user_id, 'obenlo_store_video', true);
        $social_insta = get_user_meta($user_id, 'obenlo_instagram', true);
        $social_fb = get_user_meta($user_id, 'obenlo_facebook', true);
        $store_specialties = get_user_meta($user_id, 'obenlo_specialties', true);

?>
        <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h2 class="dashboard-title">Storefront Settings</h2>
            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>" target="_blank" class="btn-primary" style="padding:10px 20px; font-size:0.9rem; display:flex; align-items:center; gap:8px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px; height:16px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                View Live Storefront
            </a>
        </div>

        <div style="max-width:800px;">
            <p style="color:#666; font-size:0.95rem; margin-bottom:30px;">
                Customize how your host profile appears to guests. A professional storefront builds trust and increases bookings.
            </p>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="obenlo_dashboard_save_storefront">
                <?php wp_nonce_field('dashboard_save_storefront', 'dashboard_storefront_nonce'); ?>

                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Public Profile</h4>
                    
                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Host / Store Name</label>
                        <input type="text" name="store_name" value="<?php echo esc_attr($store_name); ?>" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Host Location</label>
                        <input type="text" name="store_location" value="<?php echo esc_attr($store_location); ?>" placeholder="e.g. New York, NY" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Tagline (Catchy Hook)</label>
                        <input type="text" name="store_tagline" value="<?php echo esc_attr($store_tagline); ?>" placeholder="e.g. Luxury Haircare in the heart of Paris" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Host Specialties</label>
                        <input type="text" name="store_specialties" value="<?php echo esc_attr($store_specialties); ?>" placeholder="e.g. Organic, Pet Friendly, Multilingual" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        <p style="font-size:0.8rem; color:#888; margin-top:5px;">Separate your specialties with commas.</p>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Description / Bio</label>
                        <textarea name="store_description" rows="5" placeholder="Tell guests about yourself or your hospitality business..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;"><?php echo esc_textarea($store_desc); ?></textarea>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Featured Video (YouTube/Vimeo)</label>
                        <input type="url" name="store_video" value="<?php echo esc_attr($store_video); ?>" placeholder="https://www.youtube.com/watch?v=..." style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        <p style="font-size:0.8rem; color:#888; margin-top:5px;">Share a welcoming video with your future guests.</p>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Instagram Profile</label>
                            <input type="text" name="social_insta" value="<?php echo esc_attr($social_insta); ?>" placeholder="@youraccount" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:700; margin-bottom:8px; color:#444;">Facebook Page</label>
                            <input type="text" name="social_fb" value="<?php echo esc_attr($social_fb); ?>" placeholder="facebook.com/yourpage" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h4 style="margin-top:0; margin-bottom:25px; border-bottom:1px solid #f5f5f5; padding-bottom:15px;">Branding & Identity</h4>
                    
                    <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px;">
                         <div>
                            <label style="display:block; font-weight:700; margin-bottom:12px; color:#444;">Host Logo</label>
                            <?php if ($store_logo_id): ?>
                                <div style="margin-bottom:15px; position:relative; width:120px; height:120px;">
                                    <img src="<?php echo esc_url(wp_get_attachment_image_url($store_logo_id, 'thumbnail')); ?>" style="width:100%; height:100%; border-radius:50%; object-fit:cover; border:3px solid #eee;">
                                    <label style="display:block; margin-top:10px; color:#ef4444; font-size:0.8rem; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" name="remove_logo" value="1"> Remove
                                    </label>
                                </div>
                            <?php
        endif; ?>
                            <input type="file" name="store_logo" accept="image/*" style="font-size:0.8rem;">
                         </div>

                         <div>
                            <label style="display:block; font-weight:700; margin-bottom:12px; color:#444;">Store Banner</label>
                            <?php if ($store_banner_id): ?>
                                <div style="margin-bottom:15px;">
                                    <img src="<?php echo esc_url(wp_get_attachment_image_url($store_banner_id, 'medium')); ?>" style="width:100%; height:100px; border-radius:12px; object-fit:cover; border:1px solid #eee;">
                                    <label style="display:block; margin-top:10px; color:#ef4444; font-size:0.8rem; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" name="remove_banner" value="1"> Remove
                                    </label>
                                </div>
                            <?php
        endif; ?>
                            <input type="file" name="store_banner" accept="image/*" style="font-size:0.8rem;">
                         </div>
                    </div>
                </div>

                <div style="margin-top:40px; margin-bottom:100px;">
                    <button type="submit" class="btn-primary" style="padding:15px 40px; font-size:1.1rem; width:100%;">Update My Storefront</button>
                </div>
            </form>
        </div>
        <?php
    }

    public function handle_save_listing()
    {
        if (!isset($_POST['dashboard_listing_nonce']) || !wp_verify_nonce($_POST['dashboard_listing_nonce'], 'dashboard_save_listing')) {
            $this->redirect_with_error('security_failed');
        }

        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $user = wp_get_current_user();

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        $title = sanitize_text_field($_POST['listing_title']);
        $content = wp_kses_post(wp_unslash($_POST['listing_content']));
        $parent_id = 0;
        if (isset($_POST['parent_id'])) {
            $parent_id = intval($_POST['parent_id']);
        } elseif (isset($_GET['parent_id'])) {
            $parent_id = intval($_GET['parent_id']);
        }

        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'listing',
            'post_author' => get_current_user_id(),
            'post_parent' => $parent_id
        );


        if ($listing_id > 0) {
            // Verify ownership
            $existing_post = get_post($listing_id);
            if ($existing_post->post_author != get_current_user_id() && !current_user_can('administrator')) {
                $this->redirect_with_error('unauthorized');
            }

            $post_data['ID'] = $listing_id;
            $new_post_id = wp_update_post($post_data);
        }
        else {
            $new_post_id = wp_insert_post($post_data);
        }

        if ($new_post_id && !is_wp_error($new_post_id)) {
            // Meta Fields
            if (isset($_POST['listing_price'])) {
                update_post_meta($new_post_id, '_obenlo_price', sanitize_text_field($_POST['listing_price']));
            }
            if (isset($_POST['listing_capacity'])) {
                update_post_meta($new_post_id, '_obenlo_capacity', sanitize_text_field($_POST['listing_capacity']));
            }
            if (isset($_POST['listing_location']) && !empty($_POST['listing_location'])) {
                update_post_meta($new_post_id, '_obenlo_location', sanitize_text_field($_POST['listing_location']));
            } elseif (isset($_POST['listing_event_address']) && !empty($_POST['listing_event_address'])) {
                update_post_meta($new_post_id, '_obenlo_location', sanitize_text_field($_POST['listing_event_address']));
            } elseif ($parent_id > 0) {
                // Inherit from parent if no specific address is provided for the child
                $parent_location = get_post_meta($parent_id, '_obenlo_location', true);
                if ($parent_location) {
                    update_post_meta($new_post_id, '_obenlo_location', $parent_location);
                }
            }
            if (isset($_POST['virtual_link'])) {
                update_post_meta($new_post_id, '_obenlo_virtual_link', esc_url_raw($_POST['virtual_link']));
            }



            // Fixed Event Scheduling
            update_post_meta($new_post_id, '_obenlo_event_is_fixed', isset($_POST['event_is_fixed']) ? 'yes' : 'no');
            if (isset($_POST['event_date'])) {
                update_post_meta($new_post_id, '_obenlo_event_date', sanitize_text_field($_POST['event_date']));
            }
            if (isset($_POST['event_start_time'])) {
                update_post_meta($new_post_id, '_obenlo_event_start_time', sanitize_text_field($_POST['event_start_time']));
            }
            if (isset($_POST['event_end_time'])) {
                update_post_meta($new_post_id, '_obenlo_event_end_time', sanitize_text_field($_POST['event_end_time']));
            }
            if (isset($_POST['event_location_type'])) {
                update_post_meta($new_post_id, '_obenlo_event_location_type', sanitize_text_field($_POST['event_location_type']));
            }

            // New Booking Meta Fields
            if (isset($_POST['pricing_model'])) {
                update_post_meta($new_post_id, '_obenlo_pricing_model', sanitize_text_field($_POST['pricing_model']));
            }
            if (isset($_POST['duration_val'])) {
                update_post_meta($new_post_id, '_obenlo_duration_val', sanitize_text_field($_POST['duration_val']));
            }
            if (isset($_POST['duration_unit'])) {
                update_post_meta($new_post_id, '_obenlo_duration_unit', sanitize_text_field($_POST['duration_unit']));
            }

            $req_slots = isset($_POST['requires_slots']) && $_POST['requires_slots'] === 'yes' ? 'yes' : 'no';
            update_post_meta($new_post_id, '_obenlo_requires_slots', $req_slots);

            // Demo Configuration
            if (isset($_POST['is_demo']) && $_POST['is_demo'] === '1' && current_user_can('administrator')) {
                update_post_meta($new_post_id, '_obenlo_is_demo', 'yes');
                
                if (isset($_POST['_obenlo_demo_host_name'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_name', sanitize_text_field($_POST['_obenlo_demo_host_name']));
                }
                if (isset($_POST['_obenlo_demo_host_bio'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_bio', sanitize_textarea_field(wp_unslash($_POST['_obenlo_demo_host_bio'])));
                }
                if (isset($_POST['_obenlo_demo_host_location'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_location', sanitize_text_field($_POST['_obenlo_demo_host_location']));
                }
                if (isset($_POST['_obenlo_demo_host_tagline'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_tagline', sanitize_text_field($_POST['_obenlo_demo_host_tagline']));
                }
                if (isset($_POST['_obenlo_demo_host_instagram'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_instagram', sanitize_text_field($_POST['_obenlo_demo_host_instagram']));
                }
                if (isset($_POST['_obenlo_demo_host_facebook'])) {
                    update_post_meta($new_post_id, '_obenlo_demo_host_facebook', esc_url_raw($_POST['_obenlo_demo_host_facebook']));
                }
            }

            // Policies (Parent Only)
            if ($parent_id == 0) {
                if (isset($_POST['policy_type'])) {
                    update_post_meta($new_post_id, '_obenlo_policy_type', sanitize_text_field($_POST['policy_type']));
                }
                if (isset($_POST['policy_cancel'])) {
                    update_post_meta($new_post_id, '_obenlo_policy_cancel', sanitize_textarea_field(wp_unslash($_POST['policy_cancel'])));
                }
                if (isset($_POST['policy_refund'])) {
                    update_post_meta($new_post_id, '_obenlo_policy_refund', sanitize_textarea_field(wp_unslash($_POST['policy_refund'])));
                }
                if (isset($_POST['policy_other'])) {
                    update_post_meta($new_post_id, '_obenlo_policy_other', sanitize_textarea_field(wp_unslash($_POST['policy_other'])));
                }
            }

            // Structured Addons Repeater
            $structured_addons = array();
            if (isset($_POST['addon_names']) && isset($_POST['addon_prices'])) {
                $names = $_POST['addon_names']; // We aren't doing heavy sanitization inside loop to avoid escaping issues
                $prices = $_POST['addon_prices'];
                for ($i = 0; $i < count($names); $i++) {
                    $name = sanitize_text_field(wp_unslash($names[$i]));
                    $price = sanitize_text_field(wp_unslash($prices[$i]));
                    // Only save if name exists
                    if (!empty($name)) {
                        $structured_addons[] = array(
                            'name' => $name,
                            'price' => $price
                        );
                    }
                }
            }
            update_post_meta($new_post_id, '_obenlo_addons_structured', wp_json_encode($structured_addons));

            // Term: Type (Only parent dictates category really, but child saves implicitly)
            if (isset($_POST['listing_type']) && !empty($_POST['listing_type'])) {
                $term_id = intval($_POST['listing_type']);
                wp_set_post_terms($new_post_id, array($term_id), 'listing_type');
            }

            // Term: Amenities (Repeater strings - Only parent)
            if ($parent_id == 0) {
                $selected_amenities = array();
                if (isset($_POST['listing_amenities_repeater']) && is_array($_POST['listing_amenities_repeater'])) {
                    foreach ($_POST['listing_amenities_repeater'] as $amenity_val) {
                        $term_name = sanitize_text_field(wp_unslash($amenity_val));
                        $term_name = trim($term_name);
                        if (!empty($term_name)) {
                            $term = term_exists($term_name, 'listing_amenity');
                            if (!$term) {
                                $term = wp_insert_term($term_name, 'listing_amenity');
                            }
                            if (!is_wp_error($term) && isset($term['term_id'])) {
                                $selected_amenities[] = intval($term['term_id']);
                            }
                        }
                    }
                }
                // Set all amenities
                wp_set_post_terms($new_post_id, $selected_amenities, 'listing_amenity');
            }

            // --- Images Processing ---

            // Delete marked images
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $del_id) {
                    // Check if it's the featured image and remove it
                    if (get_post_thumbnail_id($new_post_id) == $del_id) {
                        delete_post_thumbnail($new_post_id);
                    }
                    wp_delete_attachment(intval($del_id), true);
                }
            }
            // Ensure there's a cover image if images remain and featured image was deleted
            $remaining = get_attached_media('image', $new_post_id);
            if (!has_post_thumbnail($new_post_id) && count($remaining) > 0) {
                set_post_thumbnail($new_post_id, array_key_first($remaining));
            }

            // Handle new image uploads
            if (isset($_FILES['listing_images']) && !empty($_FILES['listing_images']['name'][0])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $files = $_FILES['listing_images'];
                $count = count($files['name']);

                // Get current images count to enforce limit
                $current_images = get_attached_media('image', $new_post_id);
                $current_count = count($current_images);

                for ($i = 0; $i < $count; $i++) {
                    if ($files['name'][$i]) {
                        if ($current_count >= 10)
                            break; // Limit to 10 images

                        $file = array(
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i]
                        );
                        $_FILES = array("upload_file" => $file);
                        $attachment_id = media_handle_upload("upload_file", $new_post_id);

                        if (!is_wp_error($attachment_id)) {
                            $current_count++;
                            // Set first uploaded image as featured if none exists
                            if (!has_post_thumbnail($new_post_id)) {
                                set_post_thumbnail($new_post_id, $attachment_id);
                            }
                        }
                    }
                }
            }
        }

        $redirect_url = remove_query_arg(array('action', 'parent_id', 'message'), wp_get_referer());
        $redirect_url = add_query_arg('obenlo_modal', 'listing_saved', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    public function handle_save_storefront()
    {
        if (!isset($_POST['dashboard_storefront_nonce']) || !wp_verify_nonce($_POST['dashboard_storefront_nonce'], 'dashboard_save_storefront')) {
            $this->redirect_with_error('security_failed');
        }

        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $user = wp_get_current_user();
        $user_id = get_current_user_id();

        // Save text meta
        if (isset($_POST['store_name'])) {
            $store_name = sanitize_text_field($_POST['store_name']);
            update_user_meta($user_id, 'obenlo_store_name', $store_name);

            // Sync user slug (nicename) with store name for clean URLs
            $new_slug = sanitize_title($store_name);
            if (!empty($new_slug)) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'user_nicename' => $new_slug
                ));
            }
        }
        if (isset($_POST['store_description'])) {
            update_user_meta($user_id, 'obenlo_store_description', sanitize_textarea_field(wp_unslash($_POST['store_description'])));
        }
        if (isset($_POST['store_location'])) {
            update_user_meta($user_id, 'obenlo_store_location', sanitize_text_field($_POST['store_location']));
        }
        if (isset($_POST['store_tagline'])) {
            update_user_meta($user_id, 'obenlo_store_tagline', sanitize_text_field($_POST['store_tagline']));
        }
        if (isset($_POST['store_video'])) {
            update_user_meta($user_id, 'obenlo_store_video', esc_url_raw($_POST['store_video']));
        }
        if (isset($_POST['social_insta'])) {
            update_user_meta($user_id, 'obenlo_instagram', sanitize_text_field($_POST['social_insta']));
        }
        if (isset($_POST['social_fb'])) {
            update_user_meta($user_id, 'obenlo_facebook', sanitize_text_field($_POST['social_fb']));
        }
        if (isset($_POST['store_specialties'])) {
            update_user_meta($user_id, 'obenlo_specialties', sanitize_text_field($_POST['store_specialties']));
        }

        // Process removals
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
            $old_logo = get_user_meta($user_id, 'obenlo_store_logo', true);
            if ($old_logo) {
                wp_delete_attachment($old_logo, true);
            }
            delete_user_meta($user_id, 'obenlo_store_logo');
        }
        if (isset($_POST['remove_banner']) && $_POST['remove_banner'] == '1') {
            $old_banner = get_user_meta($user_id, 'obenlo_store_banner', true);
            if ($old_banner) {
                wp_delete_attachment($old_banner, true);
            }
            delete_user_meta($user_id, 'obenlo_store_banner');
        }

        // Process new uploads
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        if (isset($_FILES['store_logo']) && !empty($_FILES['store_logo']['name'])) {
            $attachment_id = media_handle_upload('store_logo', 0); // 0 = no post attached
            if (!is_wp_error($attachment_id)) {
                update_user_meta($user_id, 'obenlo_store_logo', $attachment_id);
            }
        }
        if (isset($_FILES['store_banner']) && !empty($_FILES['store_banner']['name'])) {
            $attachment_id = media_handle_upload('store_banner', 0);
            if (!is_wp_error($attachment_id)) {
                update_user_meta($user_id, 'obenlo_store_banner', $attachment_id);
            }
        }

        $redirect_url = remove_query_arg(array('message'), wp_get_referer());
        $redirect_url = add_query_arg('message', 'saved', $redirect_url);
        // Make sure we stay on storefront tab
        $redirect_url = add_query_arg('action', 'storefront', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }

    public function handle_booking_action()
    {
        if (!is_user_logged_in() || !(current_user_can('host') || current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }

        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $do_action = isset($_GET['do_action']) ? sanitize_text_field($_GET['do_action']) : '';

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'booking_action_' . $booking_id)) {
            $this->redirect_with_error('security_failed');
        }

        $booking = get_post($booking_id);
        if (!$booking || $booking->post_type !== 'booking') {
            $this->redirect_with_error('invalid_booking');
        }

        // Verify host owns this booking's listing
        $host_id = get_post_meta($booking_id, '_obenlo_host_id', true);
        if ($host_id != get_current_user_id() && !current_user_can('administrator')) {
            $this->redirect_with_error('unauthorized');
        }

        if ($do_action === 'approve') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'approved');
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_confirmed');
        }
        elseif ($do_action === 'complete') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'completed');
            // Trigger Platform Fee Calculation
            $payments = new Obenlo_Booking_Payments();
            $payments->calculate_platform_fee($booking_id);

            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_completed');
        }
        elseif ($do_action === 'decline') {
            update_post_meta($booking_id, '_obenlo_booking_status', 'declined');
            Obenlo_Booking_Notifications::notify_booking_event($booking_id, 'booking_cancelled');
        }
        elseif ($do_action === 'checkin') {
            // Verify if already checked in
            if (get_post_meta($booking_id, '_obenlo_checked_in', true) !== 'yes') {
                update_post_meta($booking_id, '_obenlo_checked_in', 'yes');
            }
        }

        $redirect_url = remove_query_arg(array('booking_id', 'do_action', '_wpnonce'), wp_get_referer());
        wp_safe_redirect($redirect_url);
        exit;
    }

    private function render_support_section()
    {
        $user_id = get_current_user_id();
        $tickets = Obenlo_Booking_Communication::get_user_tickets($user_id);

?>
        <div class="dashboard-header">
            <h2 class="dashboard-title">Support & Assistance</h2>
        </div>

        <?php if (isset($_GET['ticket_sent'])): ?>
            <div style="background:#ecfdf5; color:#065f46; padding:15px 20px; border-radius:12px; margin-bottom:30px; border:1px solid #a7f3d0; font-weight:600;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:20px; height:20px; display:inline-block; vertical-align:middle; margin-right:8px;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                Ticket submitted successfully! Our team will review it and get back to you.
            </div>
        <?php
        endif; ?>

        <div class="dashboard-grid-layout" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:40px; align-items: start;">
            
            <!-- Create Ticket -->
            <div class="form-section" style="margin-bottom:0; background:#fcfcfc;">
                <h4 style="margin-top:0; margin-bottom:20px;">Open New Ticket</h4>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                    <input type="hidden" name="action" value="obenlo_submit_ticket">
                    <input type="hidden" name="ticket_type" value="support">
                    <?php wp_nonce_field('submit_ticket', 'ticket_nonce'); ?>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; font-size:0.9rem;">Subject</label>
                        <input type="text" name="ticket_title" placeholder="How can we help?" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-weight:700; margin-bottom:8px; font-size:0.9rem;">Message Detail</label>
                        <textarea name="ticket_content" placeholder="Describe your issue or question..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; height:120px;"></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%; padding:12px;">Create Ticket</button>
                </form>
            </div>

            <!-- Ticket List -->
            <div>
                <h4 style="margin-top:0; margin-bottom:20px;">Support History</h4>
                <?php if (empty($tickets)): ?>
                    <div style="background:#fff; border:1px dashed #ddd; padding:40px; border-radius:15px; text-align:center; color:#888;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:40px; height:40px; margin-bottom:15px; opacity:0.3;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <p>No support history found.</p>
                    </div>
                <?php
        else: ?>
                    <div style="display:flex; flex-direction:column; gap:15px;">
                        <?php foreach ($tickets as $ticket):
                $status = get_post_meta($ticket->ID, '_obenlo_ticket_status', true);
                $status_class = 'badge-info';
                if ($status === 'closed' || $status === 'resolved')
                    $status_class = 'badge-success';
                if ($status === 'open')
                    $status_class = 'badge-warning';
?>
                            <div style="background:#fff; border:1px solid #eee; padding:20px; border-radius:15px; transition:transform 0.2s, box-shadow 0.2s; cursor:pointer;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                                    <h5 style="margin:0; font-size:1.05rem;"><?php echo esc_html($ticket->post_title); ?></h5>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo esc_html(strtoupper($status)); ?></span>
                                </div>
                                <div style="font-size:0.9rem; color:#666; margin-bottom:15px; line-height:1.5;">
                                    <?php echo wp_trim_words($ticket->post_content, 20); ?>
                                </div>
                                <div style="border-top:1px solid #f5f5f5; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:0.75rem; color:#aaa;">Last updated: <?php echo get_the_modified_date('', $ticket->ID); ?></span>
                                    <a href="<?php echo esc_url(add_query_arg('ticket_id', $ticket->ID, home_url('/support'))); ?>" style="color:#e61e4d; font-weight:700; text-decoration:none; font-size:0.9rem;">View Conversation →</a>
                                </div>
                            </div>
                        <?php
            endforeach; ?>
                    </div>
                <?php
        endif; ?>
            </div>

        </div>
    <?php
    }

    private function render_payout_tab()
    {
        echo '<div class="dashboard-header"><h2 class="dashboard-title">Payout Settings</h2></div>';
        echo '<div class="form-section">';
        echo '<p style="margin-bottom:20px; color:#666;">Manage how you would like to receive your earnings from Obenlo.</p>';

        $user_id = get_current_user_id();
        $current_method = get_user_meta($user_id, 'obenlo_payout_method', true);
        $current_details = get_user_meta($user_id, 'obenlo_payout_details', true);
        $methods = Obenlo_Booking_Payout_Manager::get_methods();

?>
        <form id="payout-settings-form">
            <?php wp_nonce_field('obenlo_payout_nonce', 'security'); ?>
            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; margin-bottom:8px;">Payout Method</label>
                <select name="payout_method" id="payout_method_select" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                    <option value="">Select Method...</option>
                    <?php foreach ($methods as $key => $method): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($current_method, $key); ?>><?php echo esc_html($method['label']); ?></option>
                    <?php
        endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:25px;" id="payout_details_wrapper">
                <label style="display:block; font-weight:700; margin-bottom:8px;">Payment Details</label>
                <input type="text" name="payout_details" value="<?php echo esc_attr($current_details); ?>" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px;">
                <p style="font-size:0.8rem; color:#888; mt-2;" id="method_hint"></p>
            </div>
            <button type="submit" class="btn-primary" id="save-payout-btn">Save Payout Preferences</button>
            <div id="payout-msg" style="margin-top:15px; font-weight:600;"></div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('payout-settings-form');
            var select = document.getElementById('payout_method_select');
            var hint = document.getElementById('method_hint');
            var methods = <?php echo json_encode($methods); ?>;

            function updateHint() {
                var method = select.value;
                if(methods[method]) {
                    hint.innerText = 'Example: ' + methods[method].placeholder;
                    document.querySelector('input[name="payout_details"]').placeholder = methods[method].placeholder;
                    document.querySelector('input[name="payout_details"]').type = methods[method].field || 'text';
                }
            }
            select.addEventListener('change', updateHint);
            updateHint();

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var btn = document.getElementById('save-payout-btn');
                var msg = document.getElementById('payout-msg');
                btn.disabled = true;
                btn.innerText = 'Saving...';
                
                var formData = new FormData(form);
                formData.append('action', 'obenlo_save_payout_settings');

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        msg.style.color = '#10b981';
                        msg.innerText = '✓ ' + data.data.message;
                    } else {
                        msg.style.color = '#ef4444';
                        msg.innerText = '❌ ' + data.data;
                    }
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerText = 'Save Payout Preferences';
                });
            });
        });
        </script>
        <?php
        echo '</div>';
    }

    private function render_availability_tab()
    {
        $user_id = get_current_user_id();
        $business_hours = get_user_meta($user_id, '_obenlo_business_hours', true);
        if (!is_array($business_hours)) {
            // Default 9-5 Mon-Fri
            $business_hours = array(
                'monday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'tuesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'wednesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'thursday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'friday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'saturday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
                'sunday' => array('active' => 'no', 'start' => '09:00', 'end' => '17:00'),
            );
        }
        $vacation_blocks = get_user_meta($user_id, '_obenlo_vacation_blocks', true);
        if (!is_array($vacation_blocks)) {
            $vacation_blocks = array();
        }

        echo '<div class="dashboard-header"><h2 class="dashboard-title">Availability Settings</h2></div>';

        // Output Form
?>
        <div class="form-section">
            <p style="margin-bottom:20px; color:#666;">Set your default weekly business hours and block out specific dates for vacations or maintenance.</p>
            
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="obenlo_dashboard_save_availability">
                <?php wp_nonce_field('save_availability', 'availability_nonce'); ?>
                
                <h3 style="margin-bottom: 20px;">Business Hours</h3>
                <div style="display:flex; flex-direction:column; gap:15px; margin-bottom: 40px;">
                    <?php
        $days = array('monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday');
        foreach ($days as $key => $label) {
            $active = isset($business_hours[$key]['active']) && $business_hours[$key]['active'] === 'yes';
            $start = isset($business_hours[$key]['start']) ? $business_hours[$key]['start'] : '09:00';
            $end = isset($business_hours[$key]['end']) ? $business_hours[$key]['end'] : '17:00';
?>
                        <div class="grid-row" style="display:flex; align-items:center; gap:20px; padding:15px; border:1px solid #eee; border-radius:10px;">
                            <label style="width:120px; font-weight:bold; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="hours[<?php echo $key; ?>][active]" value="yes" <?php checked($active); ?> style="accent-color:#e61e4d;">
                                <?php echo $label; ?>
                            </label>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <input type="time" name="hours[<?php echo $key; ?>][start]" value="<?php echo esc_attr($start); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                                <span>to</span>
                                <input type="time" name="hours[<?php echo $key; ?>][end]" value="<?php echo esc_attr($end); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                            </div>
                        </div>
                        <?php
        }
?>
                </div>

                <h3 style="margin-bottom: 20px;">Vacation / Blocked Dates</h3>
                <div id="vacation-blocks" style="display:flex; flex-direction:column; gap:15px; margin-bottom: 20px;">
                    <?php
        if (!empty($vacation_blocks)) {
            foreach ($vacation_blocks as $idx => $block) {
?>
                            <div class="vacation-block-row grid-row" style="display:flex; align-items:center; gap:15px; padding:15px; border:1px dashed #ccc; border-radius:10px; background:#fafafa;">
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:bold;">Start Date</label>
                                    <input type="date" name="vacation[<?php echo $idx; ?>][start]" value="<?php echo esc_attr($block['start']); ?>" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:bold;">End Date</label>
                                    <input type="date" name="vacation[<?php echo $idx; ?>][end]" value="<?php echo esc_attr($block['end']); ?>" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.8rem; font-weight:bold;">Reason (Optional)</label>
                                    <input type="text" name="vacation[<?php echo $idx; ?>][reason]" value="<?php echo esc_attr($block['reason']); ?>" placeholder="e.g. Renovation" style="padding:8px; border:1px solid #ccc; border-radius:6px; width:200px;">
                                </div>
                                <button type="button" class="remove-block-btn" style="align-self:flex-end; padding:9px 15px; background:#fff; border:1px solid #ef4444; color:#ef4444; border-radius:6px; cursor:pointer;">Remove</button>
                            </div>
                            <?php
            }
        }
?>
                </div>
                <button type="button" id="add-vacation-block" style="padding:10px 20px; background:#f0f0f0; border:1px solid #ccc; border-radius:8px; font-weight:bold; cursor:pointer; margin-bottom:40px;">+ Add Blocked Date Range</button>
                <div style="clear:both;"></div>
                
                <button type="submit" class="btn-primary">Save Availability Settings</button>
            </form>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var container = document.getElementById('vacation-blocks');
                var addBtn = document.getElementById('add-vacation-block');
                var blockCount = <?php echo count($vacation_blocks); ?>;
                var template = `
                    <div class="vacation-block-row grid-row" style="display:flex; align-items:center; gap:15px; padding:15px; border:1px dashed #ccc; border-radius:10px; background:#fafafa; margin-top:15px;">
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:bold;">Start Date</label>
                            <input type="date" name="vacation[{idx}][start]" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:bold;">End Date</label>
                            <input type="date" name="vacation[{idx}][end]" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:0.8rem; font-weight:bold;">Reason (Optional)</label>
                            <input type="text" name="vacation[{idx}][reason]" placeholder="e.g. Renovation" style="padding:8px; border:1px solid #ccc; border-radius:6px; width:200px;">
                        </div>
                        <button type="button" class="remove-block-btn" style="align-self:flex-end; padding:9px 15px; background:#fff; border:1px solid #ef4444; color:#ef4444; border-radius:6px; cursor:pointer;">Remove</button>
                    </div>
                `;

                addBtn.addEventListener('click', function() {
                    container.insertAdjacentHTML('beforeend', template.replace(/{idx}/g, blockCount));
                    blockCount++;
                });

                container.addEventListener('click', function(e) {
                    if(e.target.classList.contains('remove-block-btn')) {
                        e.target.closest('.vacation-block-row').remove();
                    }
                });
            });
        </script>
        <?php
    }

    public function handle_save_availability()
    {
        if (!isset($_POST['availability_nonce']) || !wp_verify_nonce($_POST['availability_nonce'], 'save_availability')) {
            $this->redirect_with_error('security_failed');
        }
        if (!is_user_logged_in()) {
            $this->redirect_with_error('unauthorized');
        }

        $user = wp_get_current_user();
        if ($user->user_login === 'demo') {
            // $this->redirect_with_error('demo_restricted');
        }

        $user_id = get_current_user_id();

        // Save business hours
        $hours = isset($_POST['hours']) ? (array)$_POST['hours'] : array();
        // Sanitize
        $sanitized_hours = array();
        foreach ($hours as $day => $data) {
            $sanitized_hours[sanitize_key($day)] = array(
                'active' => isset($data['active']) && $data['active'] === 'yes' ? 'yes' : 'no',
                'start' => sanitize_text_field($data['start']),
                'end' => sanitize_text_field($data['end'])
            );
        }
        update_user_meta($user_id, '_obenlo_business_hours', $sanitized_hours);

        // Save vacation blocks
        $vacations = isset($_POST['vacation']) ? (array)$_POST['vacation'] : array();
        $sanitized_vacations = array();
        foreach ($vacations as $v) {
            if (!empty($v['start']) && !empty($v['end'])) {
                $sanitized_vacations[] = array(
                    'start' => sanitize_text_field($v['start']),
                    'end' => sanitize_text_field($v['end']),
                    'reason' => sanitize_text_field($v['reason'])
                );
            }
        }
        update_user_meta($user_id, '_obenlo_vacation_blocks', $sanitized_vacations);

        wp_safe_redirect(add_query_arg(array('action' => 'availability', 'message' => 'saved'), home_url('/host-dashboard')));
        exit;
    }

    /**
     * Handle listing deletion from the dashboard
     */
    public function handle_delete_listing() {
        if (!is_user_logged_in()) {
            $this->redirect_with_error('unauthorized');
        }

        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        if (!$listing_id) {
            $this->redirect_with_error('invalid_listing');
        }

        // Security check
        check_admin_referer('obenlo_delete_listing_' . $listing_id);

        $post = get_post($listing_id);
        if (!$post || ($post->post_author != get_current_user_id() && !current_user_can('administrator'))) {
            $this->redirect_with_error('unauthorized');
        }



        // Delete children first if it's a parent
        $children = get_posts(array(
            'post_type' => 'listing',
            'post_parent' => $listing_id,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));

        foreach ($children as $child_id) {
            wp_delete_post($child_id, true);
        }

        // Delete the listing
        wp_delete_post($listing_id, true);

        wp_safe_redirect(add_query_arg(array('action' => 'list', 'message' => 'deleted'), home_url('/host-dashboard')));
        exit;
    }

    /**
     * Redirect back to referer with a whitelisted error code.
     */
    private function redirect_with_error($error_code) {
        $redirect_url = remove_query_arg(array('obenlo_error', 'message'), wp_get_referer());
        $redirect_url = add_query_arg('obenlo_error', $error_code, $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
}
