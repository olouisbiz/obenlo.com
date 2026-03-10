<?php
/**
 * The header for our theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e61e4d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Obenlo">
    <link rel="apple-touch-icon" href="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/icon-192.png'); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="icon" type="image/png" href="<?php echo esc_url(get_template_directory_uri() . '/assets/images/favicon-new.png'); ?>">
    <link rel="manifest" href="<?php echo esc_url(get_template_directory_uri() . '/manifest.json'); ?>">
    <?php wp_head(); ?>
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

        <div class="header-language-switcher" id="headerLangSwitcher" style="position: relative; margin-right: 15px;">
            <button class="lang-dropdown-btn" onclick="document.getElementById('headerLangSwitcher').classList.toggle('active')" style="background:none; border:none; cursor:pointer; padding:8px; border-radius:20px; display:flex; align-items:center; gap:5px; transition:background 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='none'">
                <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display:block;fill:none;height:16px;width:16px;stroke:currentColor;stroke-width:2;overflow:visible"><circle cx="16" cy="16" r="14"></circle><ellipse cx="16" cy="16" rx="6" ry="14"></ellipse><path d="M2 16h28"></path></svg>
            </button>
            <div id="headerLangMenu" class="user-dropdown-menu" style="min-width: 150px; right: 0; left: auto;">
                <a href="#" class="obenlo-lang-switch" data-lang="en" style="font-weight: <?php echo(!isset($_COOKIE['obenlo_lang']) || $_COOKIE['obenlo_lang'] == 'en') ? 'bold' : 'normal'; ?>">English</a>
                <a href="#" class="obenlo-lang-switch" data-lang="es" style="font-weight: <?php echo(isset($_COOKIE['obenlo_lang']) && $_COOKIE['obenlo_lang'] == 'es') ? 'bold' : 'normal'; ?>">Español</a>
                <a href="#" class="obenlo-lang-switch" data-lang="fr" style="font-weight: <?php echo(isset($_COOKIE['obenlo_lang']) && $_COOKIE['obenlo_lang'] == 'fr') ? 'bold' : 'normal'; ?>">Français</a>
            </div>
        </div>

        <div class="header-user-menu" id="headerUserMenu">
            <?php if (is_user_logged_in()):
    $user = wp_get_current_user();
    $is_host = in_array('host', (array)$user->roles) || in_array('administrator', (array)$user->roles);
?>
                <?php if (!$is_host): ?>
                    <a href="<?php echo esc_url(home_url('/become-a-host')); ?>" class="become-host-link"><?php esc_html_e('Obenlo your home', 'obenlo-booking'); ?></a>
                <?php
    else: ?>
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
                    <div class="menu-divider"></div>
                    <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"><?php esc_html_e('Log out', 'obenlo-booking'); ?></a>
                </div>

            <?php
else: ?>
                
                <a href="<?php echo esc_url(home_url('/become-a-host')); ?>" class="become-host-link"><?php esc_html_e('Obenlo your home', 'obenlo-booking'); ?></a>
                
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
                    <a href="<?php echo esc_url(home_url('/become-a-host')); ?>"><?php esc_html_e('Obenlo your home', 'obenlo-booking'); ?></a>
                    <a href="<?php echo esc_url(home_url('/support')); ?>"><?php esc_html_e('Help / Support', 'obenlo-booking'); ?></a>
                </div>

            <?php
endif; ?>
        </div>

    </div>
</header>

<div class="site-content">
