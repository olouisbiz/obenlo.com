<?php
/**
 * Obenlo Master Header - Responsive Hybrid App UI
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        @layer utilities {
            .nav-blur { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); }
        }
    </style>
</head>
<body <?php body_class('bg-white text-slate-900 flex flex-col min-h-screen'); ?>>

<nav class="nav-blur sticky top-0 z-[100] border-b border-slate-100/80 h-20 flex items-center">
    <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
        <a href="<?php echo home_url(); ?>" class="text-2xl font-black tracking-tighter transition-transform hover:scale-95">
            Obenlo<span class="text-indigo-600">.</span>
        </a>
        
        <div class="hidden md:flex gap-8 items-center">
            <a href="<?php echo home_url('/explore'); ?>" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors">Explorer</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo home_url('/dashboard-host'); ?>" class="bg-slate-900 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all">Console</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login-hub'); ?>" class="text-[10px] font-black uppercase tracking-widest text-indigo-600">Sign In</a>
            <?php endif; ?>
        </div>

        <button id="mobile-menu-toggle" class="md:hidden flex flex-col gap-1.5 p-2">
            <span class="w-6 h-0.5 bg-slate-900 rounded-full"></span>
            <span class="w-4 h-0.5 bg-slate-900 rounded-full ml-auto"></span>
        </button>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 z-[110] bg-white translate-x-full transition-transform duration-500 ease-in-out md:hidden">
    <div class="p-8 flex flex-col h-full">
        <div class="flex justify-between items-center mb-16">
            <span class="text-2xl font-black tracking-tighter">Obenlo<span class="text-indigo-600">.</span></span>
            <button id="mobile-menu-close" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Close</button>
        </div>
        <div class="flex flex-col gap-8">
            <a href="<?php echo home_url('/explore'); ?>" class="text-4xl font-black text-slate-900 tracking-tighter hover:text-indigo-600">Explore</a>
            <a href="<?php echo home_url('/messages'); ?>" class="text-4xl font-black text-slate-900 tracking-tighter hover:text-indigo-600">Signals</a>
            <a href="<?php echo home_url('/dashboard-traveler'); ?>" class="text-4xl font-black text-slate-900 tracking-tighter hover:text-indigo-600">My Trips</a>
        </div>
        <div class="mt-auto pt-10 border-t border-slate-50">
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="text-[10px] font-black uppercase tracking-widest text-red-500">Sign Out</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login-hub'); ?>" class="block text-center bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-[10px]">Access Platform</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const menu = document.getElementById('mobile-menu');
    document.getElementById('mobile-menu-toggle').addEventListener('click', () => menu.classList.remove('translate-x-full'));
    document.getElementById('mobile-menu-close').addEventListener('click', () => menu.classList.add('translate-x-full'));
</script>

<div id="obenlo-app-root" class="flex-grow">
