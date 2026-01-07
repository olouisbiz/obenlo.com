<?php
/**
 * Template Name: Login Hub
 */
if (is_user_logged_in()) { wp_redirect(home_url('/explore')); exit; }
get_header(); ?>

<div class="flex-grow flex items-center justify-center py-20 px-6">
    <div class="w-full max-w-md bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200 border border-slate-50">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-black text-slate-900 tracking-tighter">Welcome Back<span class="text-indigo-600">.</span></h2>
            <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Access the Obenlo Network</p>
        </div>

        <?php wp_login_form([
            'redirect' => home_url('/explore'),
            'label_username' => 'Identity',
            'label_password' => 'Passcode',
            'remember' => true
        ]); ?>
        
        <div class="mt-8 pt-8 border-t border-slate-50 text-center">
            <a href="<?php echo wp_registration_url(); ?>" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-slate-900 transition-colors">Create Nomadic Account</a>
        </div>
    </div>
</div>

<?php get_footer(); ?>
