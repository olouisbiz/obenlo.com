<?php
/**
 * Template Name: Profile Editor
 * Path: /workspaces/obenlo.com/obenlo-theme/templates/profile-editor.php
 * Connections: dashboard-host, dashboard-traveler, login-hub
 */

if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login-hub'));
    exit;
}

$user = wp_get_current_user();
$phone = get_user_meta($user->ID, 'obenlo_phone', true);
$location = get_user_meta($user->ID, 'obenlo_location', true);

/** * ROLE WIRE: Redirection logic synchronized with master routing
 */
$is_host = function_exists('is_obenlo_host') ? is_obenlo_host() : in_array('obenlo_vendor', (array) $user->roles);
$dashboard_url = $is_host ? home_url('/dashboard-host') : home_url('/dashboard-traveler');

get_header(); ?>

<style>
    body { font-family: 'Outfit', sans-serif; background-color: #fcfcfd; -webkit-font-smoothing: antialiased; }
    .editor-card { background: #ffffff; border-radius: 3rem; border: 1px solid #f1f5f9; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.02); }
    .input-pill { background: #f8fafc; border: 2px solid transparent; transition: all 0.3s ease; }
    .input-pill:focus { border-color: #6366f1; background: #ffffff; outline: none; }
    .nav-btn { transition: all 0.3s ease; border: 1px solid #f1f5f9; }
    .nav-btn:hover { background: #f8fafc; transform: translateX(-4px); }
</style>

<main class="min-h-screen py-16 px-6 lg:px-12">
    <div class="max-w-3xl mx-auto">
        
        <nav class="flex justify-between items-center mb-12">
            <a href="<?php echo $dashboard_url; ?>" class="nav-btn bg-white px-6 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-4 h-4 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-900">Return to Console</span>
            </a>
            <a href="<?php echo home_url(); ?>" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 transition-colors">
                Obenlo Home
            </a>
        </nav>

        <header class="mb-12">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-600 mb-2">Account Parameters</p>
            <h1 class="text-6xl font-black text-slate-900 tracking-tighter italic">Edit Profile<span class="text-indigo-600">.</span></h1>
        </header>

        <section class="editor-card p-10 md:p-14">
            <h3 class="text-2xl font-black text-slate-900 mb-8 tracking-tight">Identity <span class="text-indigo-600">Management</span></h3>
            
            <form id="obenlo-profile-form" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">First Name</label>
                        <input type="text" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" 
                               class="input-pill w-full p-4 rounded-2xl font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" 
                               class="input-pill w-full p-4 rounded-2xl font-bold text-slate-700">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Global Phone</label>
                        <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>" placeholder="+1 (555) 000-0000"
                               class="input-pill w-full p-4 rounded-2xl font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">Base Location</label>
                        <input type="text" name="location" value="<?php echo esc_attr($location); ?>" placeholder="e.g. London, UK" 
                               class="input-pill w-full p-4 rounded-2xl font-bold text-slate-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 px-1">The Narrative (Bio)</label>
                    <textarea name="bio" rows="4" placeholder="Briefly describe your persona..."
                              class="input-pill w-full p-5 rounded-3xl font-bold text-slate-700 resize-none"><?php echo esc_textarea($user->description); ?></textarea>
                </div>

                <div class="pt-6 border-t border-slate-50">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-4 px-1">Security Update (Leave blank to keep current)</label>
                    <input type="password" name="new_password" placeholder="New Secure Phrase"
                           class="input-pill w-full p-4 rounded-2xl font-bold text-slate-700">
                </div>
                
                <div class="pt-4">
                    <button type="submit" id="profile-submit-btn" 
                            class="w-full bg-slate-900 text-white py-6 rounded-3xl font-black text-xl uppercase tracking-widest hover:bg-indigo-600 shadow-2xl transition-all active:scale-[0.98]">
                        Sync Profile
                    </button>
                </div>

                <div id="profile-feedback" class="text-center font-black text-[10px] uppercase tracking-widest hidden p-5 rounded-2xl"></div>
            </form>
        </section>

        <div class="mt-8 text-center">
            <a href="<?php echo wp_logout_url(home_url()); ?>" class="text-[10px] font-black text-slate-400 hover:text-red-500 uppercase tracking-widest transition-colors">
                Terminate Session
            </a>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('obenlo-profile-form');
    const feedback = document.getElementById('profile-feedback');
    const submitBtn = document.getElementById('profile-submit-btn');

    if(!profileForm) return;

    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        feedback.className = 'text-center font-black text-[10px] uppercase tracking-widest p-5 rounded-2xl bg-slate-50 text-slate-400';
        feedback.innerHTML = '<span class="animate-pulse">COMMITTING TO DATABASE...</span>';
        feedback.classList.remove('hidden');
        submitBtn.disabled = true;

        const formData = new FormData(profileForm);
        formData.append('action', 'obenlo_update_profile');
        formData.append('nonce', obenlo_data.nonce);

        fetch(obenlo_data.ajax_url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            feedback.classList.remove('bg-slate-50', 'text-slate-400');
            if (data.success) {
                feedback.classList.add('bg-emerald-50', 'text-emerald-600');
                feedback.innerHTML = '✓ Core Profile Synced Successfully';
            } else {
                feedback.classList.add('bg-red-50', 'text-red-600');
                feedback.innerHTML = '⚠️ Sync Error: ' + data.data;
            }
            submitBtn.disabled = false;
        })
        .catch(() => {
            feedback.innerHTML = '⚠️ Connectivity Loss';
            submitBtn.disabled = false;
        });
    });
});
</script>

<?php get_footer(); ?>