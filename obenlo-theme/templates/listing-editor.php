<?php
/** Template Name: Listing Editor **/
if (!is_user_logged_in() || !is_obenlo_host()) { wp_redirect(home_url()); exit; }
get_header(); ?>
<main class="max-w-4xl mx-auto px-6 py-20 flex-grow">
    <div class="text-center mb-16">
        <h1 class="text-5xl font-black tracking-tighter mb-4 uppercase">Asset Studio<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Create Marketplace Inventory</p>
    </div>
    <form class="space-y-8 bg-white p-12 rounded-[3rem] shadow-2xl shadow-slate-100 border border-slate-50">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Listing Title</label>
            <input type="text" placeholder="e.g. Premium Marketplace Asset" class="w-full bg-slate-50 border-none rounded-2xl p-5 font-bold">
        </div>
        <button class="w-full bg-slate-900 text-white py-6 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all">Publish to Marketplace</button>
    </form>
</main>
<?php get_footer(); ?>
