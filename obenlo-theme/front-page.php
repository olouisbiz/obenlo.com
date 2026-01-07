<?php get_header(); ?>
<section class="flex-grow flex items-center justify-center py-32 px-6">
    <div class="max-w-5xl mx-auto text-center">
        <p class="text-indigo-600 font-black uppercase tracking-[0.4em] text-[10px] mb-6">Professional Booking Platform</p>
        <h1 class="text-7xl md:text-9xl font-black text-slate-900 tracking-tighter leading-none mb-10 uppercase">
            Marketplace<br/>Redefined<span class="text-indigo-600">.</span>
        </h1>
        <p class="text-slate-400 text-lg font-bold uppercase tracking-tight mb-14 max-w-xl mx-auto">
            Obenlo is the secure gateway for premium stays, experiences, and specialized host services.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <a href="<?php echo home_url('/listings'); ?>" class="bg-slate-900 text-white px-12 py-6 rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-indigo-600 transition-all">Browse Listings</a>
            <a href="<?php echo home_url('/login-hub'); ?>" class="bg-white border border-slate-200 text-slate-900 px-12 py-6 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors">Host Registration</a>
        </div>
    </div>
</section>
<?php get_footer(); ?>
