<?php
/**
 * The header for our theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <?php
    $seo_title = wp_title('|', false, 'right') . get_bloginfo('name');
    $seo_desc = get_bloginfo('description');
    $seo_image = esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile.png');
    
    if ( is_singular() ) {
        global $post;
        $seo_title = get_the_title() . ' | ' . get_bloginfo('name');
        if ( has_excerpt() ) {
            $seo_desc = wp_strip_all_tags( get_the_excerpt() );
        } else {
            $seo_desc = wp_trim_words( wp_strip_all_tags( $post->post_content ), 20, '...' );
        }
        if ( has_post_thumbnail() ) {
            $seo_image = get_the_post_thumbnail_url(null, 'large');
        }
    }
    // Specific check for listing type taxonomy archive
    if ( is_tax('listing_type') ) {
        $term = get_queried_object();
        $seo_title = $term->name . ' | ' . get_bloginfo('name');
        if ( !empty($term->description) ) {
            $seo_desc = wp_strip_all_tags($term->description);
        } else {
            $seo_desc = 'Find the best local ' . strtolower($term->name) . ' professionals on ' . get_bloginfo('name') . '.';
        }
    }
    ?>
    <!-- SEO & Legitimacy Tags -->
    <meta name="description" content="<?php echo esc_attr($seo_desc); ?>">
    <link rel="canonical" href="<?php echo esc_url(home_url(add_query_arg(array(), $wp->request))); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo is_singular() ? 'article' : 'website'; ?>">
    <meta property="og:url" content="<?php echo esc_url(home_url(add_query_arg(array(), $wp->request))); ?>">
    <meta property="og:title" content="<?php echo esc_attr($seo_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($seo_desc); ?>">
    <meta property="og:image" content="<?php echo esc_url($seo_image); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo esc_url(home_url(add_query_arg(array(), $wp->request))); ?>">
    <meta property="twitter:title" content="<?php echo esc_attr($seo_title); ?>">
    <meta property="twitter:description" content="<?php echo esc_attr($seo_desc); ?>">
    <meta property="twitter:image" content="<?php echo esc_url($seo_image); ?>">

    <?php
    // Retrieve Dynamic Tracking Options from Obenlo Settings
    $ga_id = get_option('obenlo_google_analytics_id', '');
    $meta_pixel_id = get_option('obenlo_meta_pixel_id', '');
    ?>
    <!-- Site Verification Placeholders - Replace with your actual codes -->
    <meta name="google-site-verification" content="pTHwfkbZtkCHLS8aWdQ3MsKXKB8cIJxcNc2ZGmIBLEU">
    <meta name="msvalidate.01" content="YOUR_BING_VERIFICATION_CODE">

    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-social-profile.png'); ?>">
    
    <!-- Travelpayouts Main White Label Code -->
    <script nowprocket data-noptimize="1" data-cfasync="false" data-wpfc-render="false" seraph-accel-crit="1" data-no-defer="1">
      (function () {
            var script = document.createElement("script");
            script.async = 1;
            script.type = "module";
            script.src = "https://tpwdgt.com/wl_web/main.js?wl_id=15668";
            document.head.appendChild(script);
          })();
    </script>

    <!-- Travelpayouts Search Query Sync -->
    <script>
      (function() {
        window.addEventListener('load', function() {
          const params = new URLSearchParams(window.location.search);
          const query = params.get('s');
          if (query && document.getElementById('tpwl-search')) {
            // Setting the global search value in the widget 
            // This assumes the widget script is loaded and watching for changes
            const checkWidget = setInterval(() => {
              const searchInput = document.querySelector('.tp-search-input'); // Standard TP class
              if (searchInput) {
                searchInput.value = query;
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                clearInterval(checkWidget);
              }
            }, 500);
            setTimeout(() => clearInterval(checkWidget), 5000); // Stop after 5s
          }
        });
      })();
    </script>

    <?php wp_head(); ?>

    <?php if ($ga_id): ?>
    <!-- Google Analytics 4 (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga_id); ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?php echo esc_js($ga_id); ?>');
    </script>
    <?php endif; ?>
    
    <?php if ($meta_pixel_id): ?>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo esc_js($meta_pixel_id); ?>');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=<?php echo esc_attr($meta_pixel_id); ?>&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <?php endif; ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="header-inner">
        
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" style="display: block; text-decoration: none;">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/obenlo-logo-no-background.svg'); ?>" alt="<?php bloginfo('name'); ?>" style="height: 38px; width: auto; display: block;">
            </a>

        </div>

        <div class="header-search-nav">
            <form role="search" method="get" class="smart-search-bar" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="text" name="s" class="search-input" placeholder="Search listings, categories, locations or hosts..." value="<?php echo get_search_query(); ?>">
                <button type="submit" class="search-icon-btn">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;fill:none;height:12px;width:12px;stroke:currentColor;stroke-width:5.33333;overflow:visible"><g fill="none"><path d="m13 24c6.0751322 0 11-4.9248678 11-11 0-6.07513225-4.9248678-11-11-11-6.07513225 0-11 4.92486775-11 11 0 6.0751322 4.92486775 11 11 11zm8-3 9 9"></path></g></svg>
                </button>
            </form>
        </div>


        <div class="header-user-menu" id="headerUserMenu">
            <?php if (is_user_logged_in()):
    $user = wp_get_current_user();
    $is_host = in_array('host', (array)$user->roles) || in_array('administrator', (array)$user->roles);
?>
                <?php if (!$is_host): ?>
                    <a href="https://travel.obenlo.com" class="become-host-link" target="_blank"><?php esc_html_e('Travel', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/become-a-host')); ?>" class="become-host-link"><?php esc_html_e('Offer a Service', 'obenlo-booking'); ?></a>
                <?php
    else: ?>
                    <a href="https://travel.obenlo.com" class="become-host-link" target="_blank"><?php esc_html_e('Travel', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/host-dashboard')); ?>" class="become-host-link"><?php esc_html_e('Switch to hosting', 'obenlo-booking'); ?></a>
                <?php
    endif; ?>
                
                <button class="user-dropdown-btn" onclick="document.getElementById('headerUserMenu').classList.toggle('active')">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;fill:none;height:16px;width:16px;stroke:currentColor;stroke-width:3;overflow:visible"><g fill="none"><path d="M2 16h28M2 24h28M2 8h28"></path></g></svg>
                    <div class="user-avatar">
                        <?php echo get_avatar($user->ID, 30); ?>
                    </div>
                </button>

                <div class="user-dropdown-menu">
                    <div style="padding: 12px 20px; font-weight: bold;">Hi, <?php echo esc_html($user->display_name); ?></div>
                    <div class="menu-divider"></div>
                    <a href="<?php echo esc_url(home_url('/account?tab=trips')); ?>"><?php esc_html_e('Trips', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/wishlists')); ?>"><?php esc_html_e('Wishlists', 'obenlo-booking'); ?></a>
                    <div class="menu-divider"></div>
                    <?php if ($is_host): ?>
                        <a href="<?php echo esc_url(home_url('/host-dashboard')); ?>"><?php esc_html_e('Host Dashboard', 'obenlo-booking'); ?></a>
                    <?php
    endif; ?>
                    <?php if (current_user_can('administrator')): ?>
                        <a href="<?php echo esc_url(home_url('/site-admin')); ?>" style="color: #e61e4d; font-weight: bold;"><?php esc_html_e('Site Admin', 'obenlo-booking'); ?></a>
                    <?php
    endif; ?>
                    <a href="<?php echo esc_url(home_url('/account')); ?>"><?php esc_html_e('Account', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/messages')); ?>"><?php esc_html_e('Messages', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/support')); ?>"><?php esc_html_e('Support / Dispute', 'obenlo-booking'); ?></a>
                    <a href="https://travel.obenlo.com" target="_blank" style="color: #e61e4d; font-weight: bold;"><?php esc_html_e('Global Stays & Flights', 'obenlo-booking'); ?></a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"><?php esc_html_e('Log out', 'obenlo-booking'); ?></a>
                </div>

            <?php
else: ?>
                
                <a href="https://travel.obenlo.com" class="become-host-link" target="_blank"><?php esc_html_e('Travel', 'obenlo-booking'); ?></a>
                <a href="<?php echo esc_url(home_url('/become-a-host')); ?>" class="become-host-link"><?php esc_html_e('Offer a Service', 'obenlo-booking'); ?></a>
                
                <button class="user-dropdown-btn" onclick="document.getElementById('headerUserMenu').classList.toggle('active')">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;fill:none;height:16px;width:16px;stroke:currentColor;stroke-width:3;overflow:visible"><g fill="none"><path d="M2 16h28M2 24h28M2 8h28"></path></g></svg>
                    <div class="user-avatar" style="background:#222;">
                        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;height:100%;width:100%;fill:currentColor"><path d="m16 .7c-8.437 0-15.3 6.863-15.3 15.3s6.863 15.3 15.3 15.3 15.3-6.863 15.3-15.3-6.863-15.3-15.3-15.3zm0 28c-4.021 0-7.605-1.884-9.933-4.81a12.425 12.425 0 0 1 6.451-4.4 6.507 6.507 0 0 1 -3.018-5.49c0-3.584 2.916-6.5 6.5-6.5s6.5 2.916 6.5 6.5a6.513 6.513 0 0 1 -3.019 5.491 12.42 12.42 0 0 1 6.452 4.4c-2.328 2.925-5.912 4.809-9.933 4.809z"></path></g></svg>
                    </div>
                </button>

                <div class="user-dropdown-menu">
                    <a href="<?php echo esc_url(home_url('/login')); ?>" style="font-weight: bold;"><?php esc_html_e('Log in', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/login#signup')); ?>"><?php esc_html_e('Sign up', 'obenlo-booking'); ?></a>
                    <div class="menu-divider"></div>
                    <a href="<?php echo esc_url(home_url('/become-a-host')); ?>"><?php esc_html_e('Offer a Service', 'obenlo-booking'); ?></a>
                    <a href="https://travel.obenlo.com" target="_blank" style="color: #e61e4d; font-weight: bold;"><?php esc_html_e('Global Stays & Flights', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/support')); ?>"><?php esc_html_e('Help / Support', 'obenlo-booking'); ?></a>
                </div>

            <?php
endif; ?>
        </div>

    </div>
</header>

<div class="site-content">
