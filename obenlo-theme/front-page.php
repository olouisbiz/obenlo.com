<?php get_header(); ?>

<section class="relative pt-32 pb-20 px-6">
    <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-7xl md:text-8xl font-black text-slate-900 tracking-tighter leading-none mb-8">
            The nomadic<br/>ecosystem<span class="text-indigo-600">.</span>
        </h1>
        <p class="max-w-2xl mx-auto text-slate-400 text-lg font-medium mb-12 uppercase tracking-tight">
            Seamlessly book stays, experiences, and nomadic services in one high-performance interface.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?php echo home_url('/explore'); ?>" class="bg-indigo-600 text-white px-12 py-6 rounded-[2rem] font-black text-xs uppercase tracking-widest shadow-2xl shadow-indigo-200 hover:scale-105 transition-transform">Start Exploring</a>
            <a href="<?php echo home_url('/login-hub?action=signup'); ?>" class="bg-white border border-slate-100 text-slate-900 px-12 py-6 rounded-[2rem] font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors">Become a Host</a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
