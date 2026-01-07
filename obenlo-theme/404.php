<?php
/**
 * 404 Error - Asset Not Found
 */
get_header(); ?>
<main class="flex-grow flex items-center justify-center py-32 px-6">
    <div class="text-center">
        <h1 class="text-9xl font-black text-slate-100 tracking-tighter leading-none mb-4">404</h1>
        <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tighter mb-8">Asset Not Located<span class="text-indigo-600">.</span></h2>
        <a href="<?php echo home_url('/listings'); ?>" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-2xl">Return to Marketplace</a>
    </div>
</main>
<?php get_footer(); ?>
