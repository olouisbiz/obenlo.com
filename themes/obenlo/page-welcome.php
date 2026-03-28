<?php
/**
 * Template Name: Welcome Landing Page
 * A premium onboarding experience for Obenlo Hosts and Guests.
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$name = $current_user->display_name ?: $current_user->user_login;
$role = in_array('host', (array) $current_user->roles) ? 'host' : 'guest';
$avatar_url = get_avatar_url($current_user->ID, ['size' => 120]);
?>

<style>
    :root {
        --primary: #e61e4d;
        --secondary: #008489;
        --bg-gradient: linear-gradient(135deg, #fff 0%, #f9f9f9 100%);
        --accent-gradient: linear-gradient(135deg, #ff385c 0%, #e61e4d 100%);
        --glass: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    .welcome-page {
        background: var(--bg-gradient);
        min-height: 90vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .welcome-container {
        max-width: 900px;
        width: 100%;
        text-align: center;
    }

    /* Hero Section */
    .welcome-hero {
        margin-bottom: 50px;
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .welcome-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin: 0 auto 25px;
        border: 4px solid white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        object-fit: cover;
    }

    .welcome-hero h1 {
        font-size: 3rem;
        font-weight: 900;
        color: #1a1a1a;
        margin: 0 0 10px;
        letter-spacing: -0.03em;
    }

    .welcome-hero p {
        font-size: 1.25rem;
        color: #666;
        font-weight: 500;
    }

    /* Actions Grid */
    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 25px;
        margin-top: 40px;
        animation: fadeIn 1s ease-out 0.2s both;
    }

    .action-card {
        background: var(--glass);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 28px;
        padding: 35px 25px;
        text-decoration: none;
        color: #1a1a1a;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 10px 40px rgba(0,0,0,0.04);
    }

    .action-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.08);
        border-color: var(--primary);
    }

    .action-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        background: #fdf2f4;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: var(--primary);
        transition: all 0.3s;
    }

    .action-card:hover .action-icon {
        background: var(--primary);
        color: white;
        transform: scale(1.1) rotate(5deg);
    }

    .action-card h3 {
        font-size: 1.25rem;
        font-weight: 800;
        margin: 0 0 8px;
    }

    .action-card p {
        font-size: 0.95rem;
        color: #666;
        margin: 0;
        line-height: 1.5;
    }

    /* Footer / Skip */
    .welcome-footer {
        margin-top: 60px;
        animation: fadeIn 1s ease-out 0.5s both;
    }

    .skip-link {
        color: #999;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.2s;
    }

    .skip-link:hover {
        color: var(--primary);
        background: rgba(230, 30, 77, 0.05);
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(40px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @media (max-width: 768px) {
        .welcome-hero h1 { font-size: 2.2rem; }
        .welcome-hero p { font-size: 1.1rem; }
        .welcome-page { padding: 40px 15px; }
    }
</style>

<div class="welcome-page">
    <div class="welcome-container">
        <!-- Hero -->
        <div class="welcome-hero">
            <img src="<?php echo esc_url($avatar_url); ?>" alt="" class="welcome-avatar">
            <h1>Welcome back, <?php echo esc_html($name); ?>!</h1>
            <p>Ready for your next adventure on Obenlo?</p>
        </div>

        <!-- Role Based Actions -->
        <div class="actions-grid">
            <?php if ($role === 'host') : ?>
                <!-- Host Actions -->
                <a href="<?php echo home_url('/host-dashboard'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <h3>Host Dashboard</h3>
                    <p>Manage your properties, bookings, and earnings and more.</p>
                </a>
                <a href="<?php echo home_url('/host-dashboard/?action=add'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </div>
                    <h3>Create Listing</h3>
                    <p>Add a new stay, experience, or service to the platform.</p>
                </a>
                <a href="<?php echo home_url('/host-dashboard/?action=verification'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <h3>Identity Verification</h3>
                    <p>Verify your profile to build trust with Obenlo guests.</p>
                </a>
            <?php else : ?>
                <!-- Guest Actions -->
                <a href="<?php echo home_url('/listings'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <h3>Explore Stays</h3>
                    <p>Find your next perfect getaway from our curated listings.</p>
                </a>
                <a href="<?php echo home_url('/account/?tab=trips'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <h3>My Adventures</h3>
                    <p>View your upcoming bookings and past experiences.</p>
                </a>
                <a href="<?php echo home_url('/account'); ?>" class="action-card">
                    <div class="action-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <h3>Complete Profile</h3>
                    <p>Update your photo and info to personalize your journey.</p>
                </a>
            <?php endif; ?>
        </div>

        <div class="welcome-footer">
            <a href="<?php echo $role === 'host' ? home_url('/host-dashboard') : home_url('/'); ?>" class="skip-link">
                Skip and go to dashboard →
            </a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
