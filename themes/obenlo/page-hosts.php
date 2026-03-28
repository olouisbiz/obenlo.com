<?php
/**
 * Template Name: Host Directory
 * The template for displaying all hosts on the platform.
 */

get_header(); ?>

<style>
    :root {
        --primary-color: var(--obenlo-primary, #e61e4d);
        --text-dark: #222222;
        --text-muted: #717171;
        --bg-light: #f7f7f7;
    }

    .hosts-directory-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 60px 20px;
        font-family: 'Inter', -apple-system, system-ui, sans-serif;
    }

    .directory-header {
        text-align: center;
        margin-bottom: 80px;
    }

    .directory-header h1 {
        font-size: 3rem;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 15px;
        letter-spacing: -1px;
    }

    .directory-header p {
        font-size: 1.25rem;
        color: var(--text-muted);
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .hosts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
    }

    .host-card {
        background: #fff;
        border: 1px solid #ebebeb;
        border-radius: 24px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .host-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        border-color: #ddd;
    }

    .host-avatar-wrapper {
        position: relative;
        margin-bottom: 20px;
    }

    .host-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .verified-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #008489;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .host-name {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 5px 0;
    }

    .host-location {
        font-size: 0.95rem;
        color: var(--text-muted);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .host-bio {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.5;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
    }

    .host-stats {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding-top: 20px;
        margin-top: auto;
        border-top: 1px solid #f0f0f0;
        width: 100%;
    }

    .host-stat {
        font-size: 0.85rem;
        font-weight: 600;
        color: #444;
    }

    .view-host-btn {
        margin-top: 25px;
        background: var(--text-dark);
        color: #fff;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s;
        width: 100%;
    }

    .host-card:hover .view-host-btn {
        background: var(--primary-color);
    }

    @media (max-width: 768px) {
        .directory-header h1 { font-size: 2.2rem; }
        .directory-header p { font-size: 1.1rem; }
    }
</style>

<main class="hosts-directory-wrapper">
    <header class="directory-header">
        <h1>Meet our Hosts</h1>
        <p><?php echo sprintf( esc_html__( 'Discover the talented professionals and dedicated property owners making the %s community exceptional.', 'obenlo' ), esc_html( get_option('obenlo_brand_name', 'Obenlo') ) ); ?></p>
    </header>

    <div class="hosts-grid">
        <?php
        $args = array(
            'role'    => 'host',
            'orderby' => 'registered',
            'order'   => 'DESC',
            'number'  => 50
        );

        $host_query = new WP_User_Query($args);
        $hosts = $host_query->get_results();

        if (!empty($hosts)) {
            foreach ($hosts as $host) {
                $user_id = $host->ID;
                $store_name = get_user_meta($user_id, 'obenlo_store_name', true) ?: $host->display_name;
                $location = get_user_meta($user_id, 'obenlo_store_location', true) ?: 'Global';
                $bio = get_user_meta($user_id, 'obenlo_store_description', true) ?: $host->description;
                $logo_id = get_user_meta($user_id, 'obenlo_store_logo', true);
                $avatar_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : get_avatar_url($user_id);
                $profile_url = home_url('/' . $host->user_nicename . '/');
                
                // Average Rating (Mock for now, or real if class exists)
                $rating = '5.0';
                if (class_exists('Obenlo_Booking_Reviews')) {
                    $avg = Obenlo_Booking_Reviews::get_host_average_rating($user_id);
                    if ($avg > 0) $rating = number_format($avg, 1);
                }
                ?>
                <a href="<?php echo esc_url($profile_url); ?>" class="host-card">
                    <div class="host-avatar-wrapper">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($store_name); ?>" class="host-avatar">
                    </div>
                    
                    <h2 class="host-name"><?php echo esc_html($store_name); ?></h2>
                    
                    <div class="host-location">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <?php echo esc_html($location); ?>
                    </div>

                    <?php if($bio): ?>
                        <p class="host-bio"><?php echo esc_html($bio); ?></p>
                    <?php endif; ?>

                    <?php echo Obenlo_Booking_Badges::render_badges_html($user_id, 'directory'); ?>

                    <div class="view-host-btn">View Storefront</div>
                </a>
                <?php
            }
        } else {
            // Fallback to Admins if no hosts exist yet (for testing)
            $admin_args = array('role' => 'administrator');
            $admin_query = new WP_User_Query($admin_args);
            $admins = $admin_query->get_results();
            
            foreach ($admins as $admin) {
                // Reuse same card logic or simple message
                echo '<p style="text-align:center; grid-column:1/-1;">No public hosts listed yet. Check back soon!</p>';
                break;
            }
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>
