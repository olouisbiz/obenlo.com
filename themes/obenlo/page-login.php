<?php
/**
 * Template Name: Login Page
 */

get_header();
?>

<div class="obenlo-auth-container" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: #f9f9f9;">
    <div class="auth-card" style="background: #fff; width: 100%; max-width: 450px; border-radius: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.08); overflow: hidden; position: relative; margin: 20px 0;">
        
        <!-- Toggle Tabs -->
        <div class="auth-tabs" style="display: flex; border-bottom: 1px solid #eee;">
            <button onclick="toggleAuth('login')" id="tab-login" style="flex: 1; padding: 20px; border: none; background: #fff; font-weight: bold; cursor: pointer; color: var(--obenlo-primary); border-bottom: 3px solid var(--obenlo-primary); transition: all 0.3s; font-size: 1.1rem;"><?php echo __('Log In', 'obenlo'); ?></button>
            <button onclick="toggleAuth('signup')" id="tab-signup" style="flex: 1; padding: 20px; border: none; background: #fff; font-weight: bold; cursor: pointer; color: #666; border-bottom: 3px solid transparent; transition: all 0.3s; font-size: 1.1rem;"><?php echo __('Sign Up', 'obenlo'); ?></button>
        </div>

        <div class="auth-form-body" style="padding: clamp(25px, 5vw, 40px);">
            <!-- Login Form -->
            <div id="form-login" class="auth-form">
                <h2 style="margin-bottom: 10px; font-size: clamp(1.5rem, 5vw, 1.8rem);"><?php echo __('Welcome back', 'obenlo'); ?></h2>
                <p style="color: #666; margin-bottom: 30px; font-size: 0.95rem;"><?php echo __('Log in to manage your bookings and listings.', 'obenlo'); ?></p>

                <?php if ( isset($_GET['login_error']) ) : ?>
                    <div style="background: #fff1f1; color: #d32f2f; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #ffcdd2;">
                        <?php echo __('Invalid username or password. Please try again.', 'obenlo'); ?>
                    </div>
                <?php endif; ?>


                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                    <input type="hidden" name="action" value="obenlo_bespoke_login">
                    <?php wp_nonce_field( 'obenlo_login', 'login_nonce' ); ?>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Username or Email', 'obenlo'); ?></label>
                        <input type="text" name="log" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Password', 'obenlo'); ?></label>
                        <input type="password" name="pwd" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                        <div style="text-align: right; margin-top: 10px;">
                            <a href="<?php echo wp_lostpassword_url(); ?>" style="color: var(--obenlo-primary); font-size: 0.85rem; text-decoration: none;"><?php echo __('Forgot password?', 'obenlo'); ?></a>
                        </div>
                    </div>

                    <button type="submit" style="width: 100%; background: var(--obenlo-primary); color: white; border: none; padding: 166px; border-radius: 12px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                        <?php echo __('Enter Obenlo', 'obenlo'); ?>
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div id="form-signup" class="auth-form" style="display: none;">
                <h2 style="margin-bottom: 10px; font-size: 1.8rem;"><?php echo __('Join Obenlo', 'obenlo'); ?></h2>
                <p style="color: #666; margin-bottom: 30px; font-size: 0.95rem;"><?php echo __('Create an account to start your journey.', 'obenlo'); ?></p>

                <?php if ( isset($_GET['reg_error']) ) : ?>
                    <div style="background: #fff1f1; color: #d32f2f; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #ffcdd2;">
                        <?php echo __('This username or email is already taken.', 'obenlo'); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
                    <input type="hidden" name="action" value="obenlo_bespoke_register">
                    <?php wp_nonce_field( 'obenlo_register', 'register_nonce' ); ?>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Username', 'obenlo'); ?></label>
                        <input type="text" name="user_login" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Email Address', 'obenlo'); ?></label>
                        <input type="email" name="user_email" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('I want to be a:', 'obenlo'); ?></label>
                        <div class="role-selection" style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px; border: 1px solid #ddd; border-radius: 12px; flex: 1; min-width: 140px;">
                                <input type="radio" name="user_role" value="guest" checked>
                                <span style="font-weight: 600;"><?php echo __('Guest', 'obenlo'); ?></span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px; border: 1px solid #ddd; border-radius: 12px; flex: 1; min-width: 140px;">
                                <input type="radio" name="user_role" value="host">
                                <span style="font-weight: 600;"><?php echo __('Host', 'obenlo'); ?></span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Password', 'obenlo'); ?></label>
                        <input type="password" name="user_pass" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                    </div>

                    <button type="submit" style="width: 100%; background: #222; color: white; border: none; padding: 16px; border-radius: 12px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                        <?php echo __('Create Account', 'obenlo'); ?>
                    </button>
                    
                    <p style="text-align: center; color: #666; font-size: 0.8rem; margin-top: 25px;">
                        <?php printf(__('By signing up, you agree to our <a href="%1$s" style="color: inherit;">Terms</a> and <a href="%2$s" style="color: inherit;">Privacy Policy</a>.', 'obenlo'), home_url('/terms'), home_url('/privacy')); ?>
                    </p>
                </form>
            </div>

            <!-- Forgot Password Form -->
            <div id="form-forgot" class="auth-form" style="display: none;">
                <h2 style="margin-bottom: 10px; font-size: 1.8rem;"><?php echo __('Reset Password', 'obenlo'); ?></h2>
                <p style="color: #666; margin-bottom: 30px; font-size: 0.95rem;"><?php echo __('Enter your email to receive a reset link.', 'obenlo'); ?></p>

                <form action="<?php echo esc_url( wp_lostpassword_url() ); ?>" method="POST">
                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: bold; color: #444;"><?php echo __('Email Address', 'obenlo'); ?></label>
                        <input type="text" name="user_login" id="user_login" required style="width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem;">
                    </div>

                    <button type="submit" style="width: 100%; background: var(--obenlo-primary); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: background 0.2s;">
                        <?php echo __('Send Reset Link', 'obenlo'); ?>
                    </button>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="javascript:void(0)" onclick="toggleAuth('login')" style="color: #666; font-size: 0.85rem; text-decoration: none;"><?php echo __('Back to Log In', 'obenlo'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAuth(mode) {
    const loginForm = document.getElementById('form-login');
    const signupForm = document.getElementById('form-signup');
    const forgotForm = document.getElementById('form-forgot');
    const loginTab = document.getElementById('tab-login');
    const signupTab = document.getElementById('tab-signup');
    const authTabs = document.querySelector('.auth-tabs');

    // Reset visibility
    loginForm.style.display = 'none';
    signupForm.style.display = 'none';
    forgotForm.style.display = 'none';
    authTabs.style.display = 'flex';

    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--obenlo-primary').trim() || '#e61e4d';

    if (mode === 'login') {
        loginForm.style.display = 'block';
        loginTab.style.color = primaryColor;
        loginTab.style.borderBottomColor = primaryColor;
        signupTab.style.color = '#666';
        signupTab.style.borderBottomColor = 'transparent';
    } else if (mode === 'signup') {
        signupForm.style.display = 'block';
        signupTab.style.color = primaryColor;
        signupTab.style.borderBottomColor = primaryColor;
        loginTab.style.color = '#666';
        loginTab.style.borderBottomColor = 'transparent';
    } else if (mode === 'forgot') {
        forgotForm.style.display = 'block';
        authTabs.style.display = 'none'; // Hide tabs for forgot password
    }
}

// Handle hash in URL for deep linking
window.addEventListener('load', function() {
    if (window.location.hash === '#signup') {
        toggleAuth('signup');
    } else if (window.location.hash === '#forgot') {
        toggleAuth('forgot');
    }
});
</script>

<?php get_footer(); ?>
