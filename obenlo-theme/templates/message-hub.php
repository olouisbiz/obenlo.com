<?php
/**
 * Template Name: Signal Hub
 */
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }
get_header(); ?>

<main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
    <div class="mb-12">
        <h1 class="text-6xl font-black text-slate-900 tracking-tighter">Signal Hub<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Active Nomadic Encounters</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 min-h-[600px]">
        <div class="bg-slate-50 rounded-[2.5rem] p-6 border border-slate-100 overflow-y-auto">
            <div class="space-y-4">
                <?php 
                // Logic to fetch message threads would go here via Plugin Brain
                ?>
                <p class="text-[10px] font-black uppercase text-slate-300 text-center py-10 tracking-widest">No Active Signals</p>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-100 flex flex-col">
            <div class="flex-grow p-10">
                <p class="text-slate-300 italic font-medium text-center mt-20">Select a signal to start communicating.</p>
            </div>
            <div class="p-6 border-t border-slate-50">
                <div class="relative">
                    <input type="text" placeholder="Type your signal..." class="w-full bg-slate-50 border-none rounded-2xl py-5 px-6 font-medium text-slate-900 focus:ring-2 focus:ring-indigo-600 transition-all">
                    <button class="absolute right-3 top-3 bg-indigo-600 text-white px-6 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100">Send</button>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
