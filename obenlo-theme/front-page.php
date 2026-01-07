<?php get_header(); ?>

<main>
    <section class="min-h-[80vh] flex items-center justify-center py-32 px-6">
        <div class="max-w-5xl mx-auto text-center">
            <p class="text-indigo-600 font-black uppercase tracking-[0.4em] text-[10px] mb-6">Professional Marketplace Infrastructure</p>
            <h1 class="text-7xl md:text-[10rem] font-black text-slate-900 tracking-tighter leading-[0.85] mb-10 uppercase">
                Marketplace<br/>Redefined<span class="text-indigo-600">.</span>
            </h1>
            <p class="text-slate-400 text-lg font-bold uppercase tracking-tight mb-14 max-w-xl mx-auto leading-relaxed">
                Obenlo.com is a high-performance booking platform. A secure gateway for premium stays, experiences, and specialized services.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-6">
                <a href="<?php echo home_url('/listings'); ?>" class="bg-slate-900 text-white px-12 py-7 rounded-2xl font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-indigo-600 transition-all">Browse Marketplace</a>
                
                <a href="<?php echo home_url('/host-console'); ?>" class="bg-white border border-slate-200 text-slate-900 px-12 py-7 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors">Become a Host</a>
            </div>
        </div>
    </section>

    <section class="py-24 bg-slate-50 border-y border-slate-100 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12">
            <div>
                <span class="text-indigo-600 font-black text-2xl tracking-tighter uppercase block mb-4">01. Secure.</span>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-4">Transactional Integrity</p>
                <p class="text-slate-900 font-bold text-sm leading-relaxed">Every booking is backed by Stripe-integrated security ensuring traveler protection and host reliability.</p>
            </div>
            <div>
                <span class="text-indigo-600 font-black text-2xl tracking-tighter uppercase block mb-4">02. Split.</span>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-4">5% Platform Logic</p>
                <p class="text-slate-900 font-bold text-sm leading-relaxed">Our transparent hybrid marketplace model ensures hosts retain 95% of their revenue with automated payouts.</p>
            </div>
            <div>
                <span class="text-indigo-600 font-black text-2xl tracking-tighter uppercase block mb-4">03. Verified.</span>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mb-4">Elite Assets Only</p>
                <p class="text-slate-900 font-bold text-sm leading-relaxed">Obenlo curates premium stays and professional services tailored for high-performance lifestyles.</p>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
