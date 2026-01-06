<?php
/**
 * Template Name: SES Editor
 */
if (!is_user_logged_in() || !is_obenlo_host()) { wp_redirect(home_url()); exit; }
get_header(); ?>

<main class="max-w-4xl mx-auto px-6 py-20">
    <div class="text-center mb-16">
        <h1 class="text-5xl font-black text-slate-900 tracking-tighter mb-4">Studio Editor<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Define your nomadic experience</p>
    </div>

    <form class="space-y-10">
        <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-50">
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4">Asset Identity</label>
            <input type="text" placeholder="e.g. Minimalist Loft in Berlin" class="w-full text-2xl font-black border-none bg-slate-50 rounded-2xl p-6 focus:ring-2 focus:ring-indigo-600">
            
            <div class="grid grid-cols-2 gap-6 mt-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Category</label>
                    <select class="w-full bg-slate-50 border-none rounded-xl p-4 font-bold text-slate-900">
                        <option>Stay</option>
                        <option>Experience</option>
                        <option>Service</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Price / Night</label>
                    <input type="number" placeholder="0.00" class="w-full bg-slate-50 border-none rounded-xl p-4 font-bold text-slate-900">
                </div>
            </div>
        </div>

        <button class="w-full bg-slate-900 text-white py-6 rounded-3xl font-black uppercase tracking-[0.2em] text-xs hover:bg-indigo-600 transition-all shadow-2xl shadow-slate-200">
            Publish to Explorer
        </button>
    </form>
</main>

<?php get_footer(); ?>
