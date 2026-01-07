<?php
/** Template Name: Profile Editor **/
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }
get_header(); ?>
<main class="max-w-3xl mx-auto px-6 py-20 flex-grow">
    <div class="mb-12">
        <h1 class="text-5xl font-black tracking-tighter uppercase">Settings<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Manage your marketplace identity</p>
    </div>
    <form class="bg-white p-12 rounded-[3rem] shadow-2xl shadow-slate-100 border border-slate-50 space-y-8">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Display Name</label>
            <input type="text" value="<?php echo wp_get_current_user()->display_name; ?>" class="w-full bg-slate-50 border-none rounded-2xl p-5 font-bold">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">Public Email</label>
            <input type="email" value="<?php echo wp_get_current_user()->user_email; ?>" class="w-full bg-slate-50 border-none rounded-2xl p-5 font-bold">
        </div>
        <button class="w-full bg-slate-900 text-white py-6 rounded-3xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
            Update Profile
        </button>
    </form>
</main>
<?php get_footer(); ?>
