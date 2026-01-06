<?php
/**
 * Obenlo Master Header
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; -webkit-font-smoothing: antialiased; }
        .nav-blur { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); }
    </style>
</head>
<body <?php body_class('bg-white text-slate-900 flex flex-col min-h-screen'); ?>>

<nav class="nav-blur sticky top-0 z-[100] border-b border-slate-100/80 h-20 flex items-center">
    <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
        <a href="<?php echo home_url(); ?>" class="text-2xl font-black tracking-tighter">
            Obenlo<span class="text-indigo-600">.</span>
        </a>
        <div class="flex gap-8 items-center">
            <a href="<?php echo home_url('/explore'); ?>" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors">Explorer</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo home_url('/dashboard-host'); ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest">Console</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login-hub'); ?>" class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div id="obenlo-app-root" class="flex-grow">
