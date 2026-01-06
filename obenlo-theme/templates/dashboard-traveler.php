<?php
/**
 * Template Name: Traveler Dashboard
 */
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }
get_header(); ?>

<main class="max-w-7xl mx-auto px-6 py-12 flex-grow">
    <div class="mb-12">
        <h1 class="text-6xl font-black text-slate-900 tracking-tighter">My Trips<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Your Nomadic History</p>
    </div>

    <div class="bg-slate-50 rounded-[3rem] p-20 text-center border border-slate-100">
        <div class="max-w-xs mx-auto">
            <p class="text-slate-400 font-semibold mb-6">You haven't booked any nomadic experiences yet.</p>
            <a href="<?php echo home_url('/explore'); ?>" class="inline-block bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-900 transition-all">Start Exploring</a>
        </div>
    </div>
</main>

<?php get_footer(); ?>
