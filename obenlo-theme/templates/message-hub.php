<?php
/** Template Name: Signal Hub **/
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
    <h1 class="text-6xl font-black tracking-tighter uppercase mb-12">Signals<span class="text-indigo-600">.</span></h1>
    <div class="grid grid-cols-12 gap-8 bg-slate-50 rounded-[3rem] border border-slate-100 min-h-[500px] overflow-hidden">
        <aside class="col-span-4 bg-white border-r border-slate-100 p-8">
            <span class="text-[10px] font-black uppercase text-slate-300 tracking-widest">Recent Inquiries</span>
        </aside>
        <section class="col-span-8 p-12 flex items-center justify-center text-slate-300 italic">
            Select a thread to view marketplace signals.
        </section>
    </div>
</main>
<?php get_footer(); ?>
