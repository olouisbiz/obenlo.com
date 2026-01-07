<?php
/** Template Name: Buyer Portal **/
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
    <h1 class="text-6xl font-black tracking-tighter uppercase mb-12">My Orders<span class="text-indigo-600">.</span></h1>
    <div class="bg-slate-50 border border-slate-100 rounded-[3rem] p-20 text-center">
        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mb-8">No transaction history found</p>
        <a href="<?php echo home_url('/listings'); ?>" class="inline-block bg-indigo-600 text-white px-10 py-4 rounded-xl font-black uppercase tracking-widest text-[10px]">Explore Marketplace</a>
    </div>
</main>
<?php get_footer(); ?>
