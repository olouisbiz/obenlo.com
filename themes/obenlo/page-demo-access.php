<?php
/**
 * Template Name: Demo Access Page
 */

get_header();
?>

<div class="obenlo-demo-access-container" style="min-height: 80vh; padding: 100px 20px; background: #fff; font-family: 'Inter', sans-serif;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center;">
        
        <div style="display: inline-block; padding: 12px 24px; background: #fff1f4; color: #e61e4d; border-radius: 100px; font-weight: 700; font-size: 0.9rem; margin-bottom: 30px; letter-spacing: 0.5px; text-transform: uppercase;">
            Host Experience Demo
        </div>
        
        <h1 style="font-size: 3.5rem; font-weight: 900; color: #222; margin-bottom: 25px; line-height: 1.1; letter-spacing: -2px;">
            Explore the Obenlo <span style="color: #e61e4d;">Dashboard.</span>
        </h1>
        
        <p style="font-size: 1.25rem; color: #666; max-width: 600px; margin: 0 auto 50px; line-height: 1.6;">
            Step inside our comprehensive hosting platform. Test drive our listing tools, manage simulated bookings, and experience exactly how Obenlo powers professional hosts.
        </p>

        <div style="background: #f9f9f9; border: 1px solid #eee; border-radius: 24px; padding: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px; margin-bottom: 50px; text-align: left;">
            <div>
                <h3 style="margin-top: 0; font-weight: 800; color: #222;">How it works</h3>
                <ul style="padding-left: 0; list-style: none; color: #555; line-height: 1.8;">
                    <li style="margin-bottom: 12px; display: flex; gap: 10px;">
                        <span style="color: #e61e4d; font-weight: bold;">01.</span>
                        <span>Log in using the experimental credentials below.</span>
                    </li>
                    <li style="margin-bottom: 12px; display: flex; gap: 10px;">
                        <span style="color: #e61e4d; font-weight: bold;">02.</span>
                        <span>Changes you make are private to your session.</span>
                    </li>
                    <li style="display: flex; gap: 10px;">
                        <span style="color: #e61e4d; font-weight: bold;">03.</span>
                        <span>The sandbox resets automatically when you logout.</span>
                    </li>
                </ul>
            </div>
            <div style="background: #fff; padding: 30px; border-radius: 16px; border: 1px solid #eee; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <h4 style="margin-top: 0; margin-bottom: 20px; font-weight: 800; color: #222; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;">Access Credentials</h4>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.8rem; color: #888; font-weight: 600; margin-bottom: 4px;">Username</label>
                    <div style="font-family: monospace; font-size: 1.1rem; color: #222; font-weight: 700; background: #f5f5f5; padding: 10px; border-radius: 8px;">demo</div>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 0.8rem; color: #888; font-weight: 600; margin-bottom: 4px;">Password</label>
                    <div style="font-family: monospace; font-size: 1.1rem; color: #222; font-weight: 700; background: #f5f5f5; padding: 10px; border-radius: 8px;">demo123</div>
                </div>

                <a href="<?php echo home_url('/login?autofill=demo'); ?>" style="display: block; background: #e61e4d; color: white; text-align: center; padding: 16px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 1rem; transition: transform 0.2s;">
                    Launch Demo Environment →
                </a>

                <?php if (isset($_GET['debug_session'])) : ?>
                <div style="margin-top: 20px; font-size: 0.7rem; color: #ccc; text-align: center;">
                    Session: <?php echo esc_html(Obenlo_Booking_Demo_Sandbox::get_session_id()); ?><br>
                    Is Active: <?php echo Obenlo_Booking_Demo_Sandbox::is_active() ? 'YES' : 'NO'; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p style="color: #999; font-size: 0.9rem;">
            Ready to start for real? <a href="<?php echo home_url('/become-a-host'); ?>" style="color: #222; font-weight: 700; text-decoration: none; border-bottom: 2px solid #e61e4d;">Create your official host account</a>
        </p>

    </div>
</div>

<?php get_footer(); ?>
