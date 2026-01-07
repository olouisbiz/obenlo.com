<?php
/** Obenlo Marketplace Master Header **/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-white text-slate-900 flex flex-col min-h-screen font-outfit'); ?>>
<nav class="sticky top-0 z-[100] bg-white/95 backdrop-blur-md border-b border-slate-100 h-20 flex items-center">
    <div class="max-w-7xl mx-auto px-6 w-full flex justify-between items-center">
        <a href="<?php echo home_url(); ?>" class="text-2xl font-black tracking-tighter uppercase">Obenlo<span class="text-indigo-600">.</span></a>
        <div class="hidden md:flex gap-8 items-center text-[10px] font-black uppercase tracking-widest text-slate-400">
            <a href="<?php echo home_url('/listings'); ?>" class="hover:text-indigo-600 transition-colors">Marketplace</a>
            <?php if (is_user_logged_in()): ?>
                <a href="<?php echo home_url('/host-console'); ?>" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl">Host Console</a>
            <?php else: ?>
                <a href="<?php echo home_url('/login-hub'); ?>" class="text-indigo-600">Partner Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div id="obenlo-marketplace-root" class="flex-grow">
