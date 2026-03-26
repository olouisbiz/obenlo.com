<?php
/**
 * Template Name: Host Onboarding
 */

get_header();

$user_id = get_current_user_id();
if ( ! $user_id ) {
    wp_redirect( home_url('/login') );
    exit;
}

$status = Obenlo_Booking_Host_Verification::get_status( $user_id );
$current_step = isset( $_GET['step'] ) ? intval( $_GET['step'] ) : 1;

// If already verified, redirect to dashboard
if ( $status === 'verified' && ! isset( $_GET['force'] ) ) {
    wp_redirect( home_url('/host-dashboard') );
    exit;
}
?>

<div class="onboarding-wrapper" style="max-width: 800px; margin: 40px auto; padding: 25px; background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box;">
    
    <div class="onboarding-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: clamp(1.8rem, 5vw, 2.5rem); margin-bottom: 10px; color: #222;"><?php esc_html_e( 'Host Onboarding', 'obenlo' ); ?></h1>
        <p style="color: #666; font-size: clamp(1rem, 3vw, 1.1rem);"><?php esc_html_e( 'Complete these steps to start hosting on Obenlo.', 'obenlo' ); ?></p>
        
        <!-- Progress Bar -->
        <div class="progress-container" style="display: flex; justify-content: space-between; margin-top: 30px; position: relative; max-width: 280px; margin-left: auto; margin-right: auto;">
            <div style="position: absolute; top: 15px; left: 0; width: 100%; height: 2px; background: #eee; z-index: 1;"></div>
            <div style="position: absolute; top: 15px; left: 0; width: <?php echo ($current_step - 1) * 100; ?>%; height: 2px; background: #e61e4d; z-index: 2; transition: width 0.3s;"></div>
            
            <div class="step-dot <?php echo $current_step >= 1 ? 'active' : ''; ?>" style="z-index: 3; background: <?php echo $current_step >= 1 ? '#e61e4d' : '#fff'; ?>; border: 2px solid <?php echo $current_step >= 1 ? '#e61e4d' : '#eee'; ?>; color: <?php echo $current_step >= 1 ? '#fff' : '#999'; ?>; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">1</div>
            <div class="step-dot <?php echo $current_step >= 2 ? 'active' : ''; ?>" style="z-index: 3; background: <?php echo $current_step >= 2 ? '#e61e4d' : '#fff'; ?>; border: 2px solid <?php echo $current_step >= 2 ? '#e61e4d' : '#eee'; ?>; color: <?php echo $current_step >= 2 ? '#fff' : '#999'; ?>; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">2</div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.8rem; font-weight: 700; color: #717171; max-width: 320px; margin-left: auto; margin-right: auto;">
            <span style="<?php echo $current_step == 1 ? 'color: #e61e4d;' : ''; ?>"><?php esc_html_e( 'Basic Info', 'obenlo' ); ?></span>
            <span style="<?php echo $current_step == 2 ? 'color: #e61e4d;' : ''; ?>"><?php esc_html_e( 'Payouts', 'obenlo' ); ?></span>
        </div>
    </div>

    <div class="onboarding-content">
        <?php if ( $current_step === 1 ) : ?>
            <div class="step-view">
                <h2><?php esc_html_e( 'Confirm your account details', 'obenlo' ); ?></h2>
                <p><?php esc_html_e( 'Ensure your public profile information is accurate.', 'obenlo' ); ?></p>
                <form id="onboarding-step-1" style="margin-top: 30px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;"><?php esc_html_e( 'Legal Name', 'obenlo' ); ?></label>
                        <input type="text" value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;"><?php esc_html_e( 'Main Phone Number', 'obenlo' ); ?></label>
                        <input type="tel" placeholder="+1 XXX XXX XXXX" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px;">
                    </div>
                    <a href="?step=2" class="cta-button" style="display: block; text-align: center; background: #e61e4d; color: #fff; padding: 15px; border-radius: 12px; text-decoration: none; font-weight: bold;"><?php esc_html_e( 'Next: Payout Setup', 'obenlo' ); ?></a>
                </form>
            </div>

        <?php elseif ( $current_step === 2 ) : ?>
            <div class="step-view">
                <h2><?php esc_html_e( 'Step 2: Payout Method', 'obenlo' ); ?></h2>
                <p><?php esc_html_e( 'Select how you want to receive your earnings.', 'obenlo' ); ?></p>

                <div class="payout-selector" style="margin-top: 30px; display: grid; gap: 15px;">
                    <?php 
                    $methods = Obenlo_Booking_Payout_Manager::get_methods();
                    foreach ( $methods as $key => $method ) : ?>
                        <label style="display: flex; align-items: center; gap: 15px; padding: 15px 20px; border: 1px solid #ddd; border-radius: 12px; cursor: pointer; transition: border-color 0.2s;">
                            <input type="radio" name="payout_method" value="<?php echo esc_attr($key); ?>" style="width: 20px; height: 20px;">
                            <div style="flex: 1;">
                                <div style="font-weight: bold;"><?php echo esc_html($method['label']); ?></div>
                                <div style="font-size: 0.85rem; color: #717171;"><?php echo esc_html($method['placeholder']); ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="payout-details-form" style="display: none; margin-top: 30px; padding: 25px; background: #f9f9f9; border-radius: 15px;">
                    <label id="payout-detail-label" style="display: block; font-weight: bold; margin-bottom: 10px;"></label>
                    <input type="text" id="payout_detail_input" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px;">
                </div>

                <div style="margin-top: 40px;">
                    <button id="save-onboarding" style="width: 100%; padding: 18px; background: #e61e4d; color: #fff; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(230,30,77,0.3);"><?php esc_html_e( 'Finish & Go to Dashboard', 'obenlo' ); ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const payoutOptions = document.querySelectorAll('input[name="payout_method"]');
    const detailsForm = document.getElementById('payout-details-form');
    const detailLabel = document.getElementById('payout-detail-label');
    const detailInput = document.getElementById('payout_detail_input');

    payoutOptions.forEach(opt => {
        opt.addEventListener('change', function() {
            const method = this.value;
            const methods = <?php echo json_encode(Obenlo_Booking_Payout_Manager::get_methods()); ?>;
            const current = methods[method];

            detailsForm.style.display = 'block';
            detailLabel.innerHTML = current.label + ' ' + (current.field === 'email' ? 'Email Address' : 'Information');
            detailInput.placeholder = current.placeholder;
            detailInput.type = current.field;
        });
    });

    // Handle File Upload AJAX
    const idInput = document.getElementById('id_document_input');
    if (idInput) {
        idInput.addEventListener('change', function() {
            const file = this.files[0];
            const formData = new FormData();
            formData.append('action', 'obenlo_upload_id');
            formData.append('security', '<?php echo wp_create_nonce("obenlo_onboarding_nonce"); ?>');
            formData.append('id_document', file);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.data);
                }
            });
        });
    }

    // Handle Final Save AJAX
    const saveBtn = document.getElementById('save-onboarding');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const method = document.querySelector('input[name="payout_method"]:checked')?.value;
            const details = detailInput.value;

            if (!method || !details) {
                alert('Please complete all fields.');
                return;
            }

            const formData = new URLSearchParams();
            formData.append('action', 'obenlo_save_payout_settings');
            formData.append('security', '<?php echo wp_create_nonce("obenlo_payout_nonce"); ?>');
            formData.append('payout_method', method);
            formData.append('payout_details', details);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.location.href = '<?php echo home_url('/host-dashboard'); ?>';
                } else {
                    alert(res.data);
                }
            });
        });
    }
});
</script>

<?php get_footer(); ?>
