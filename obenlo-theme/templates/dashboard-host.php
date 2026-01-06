<?php
/**
 * Template Name: Host Studio Console
 */
if (!is_user_logged_in() || !function_exists('is_obenlo_host') || !is_obenlo_host()) {
    wp_redirect(home_url('/login-hub')); exit;
}
get_header(); ?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="flex flex-col lg:flex-row justify-between items-end gap-6 mb-12">
        <div>
            <h1 class="text-6xl font-black text-slate-900 tracking-tighter">Studio Console<span class="text-indigo-600">.</span></h1>
            <p class="text-slate-400 font-bold uppercase tracking-[0.2em] text-[10px] mt-2">Manage your nomadic assets</p>
        </div>
        <a href="<?php echo home_url('/ses-editor'); ?>" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
            Create New Asset
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Net Earnings</p>
            <p class="text-4xl font-black text-slate-900">$0.00</p>
        </div>
        <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Active Stays</p>
            <p class="text-4xl font-black text-slate-900">0</p>
        </div>
        <div class="bg-slate-50 p-8 rounded-[2rem] border border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Signal Alerts</p>
            <p class="text-4xl font-black text-indigo-600">0</p>
        </div>
    </div>
</main>

<?php get_footer(); ?>
