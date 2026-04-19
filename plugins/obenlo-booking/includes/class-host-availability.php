<?php
/**
 * Host Availability Module
 * Single Responsibility: Availability settings UI + save handler.
 */

if (!defined('ABSPATH')) exit;

class Obenlo_Host_Availability
{
    public function init()
    {
        add_action('admin_post_obenlo_dashboard_save_availability', array($this, 'handle_save_availability'));
    }

    private function redirect_with_error($error_code) {
        obenlo_redirect_with_error($error_code);
    }

    public function render_availability_tab()
    {
        $user_id        = get_current_user_id();
        $business_hours = get_user_meta($user_id, '_obenlo_business_hours', true);

        if (!is_array($business_hours)) {
            $business_hours = array(
                'monday'    => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'tuesday'   => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'wednesday' => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'thursday'  => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'friday'    => array('active' => 'yes', 'start' => '09:00', 'end' => '17:00'),
                'saturday'  => array('active' => 'no',  'start' => '09:00', 'end' => '17:00'),
                'sunday'    => array('active' => 'no',  'start' => '09:00', 'end' => '17:00'),
            );
        }

        $vacation_blocks = get_user_meta($user_id, '_obenlo_vacation_blocks', true);
        if (!is_array($vacation_blocks)) $vacation_blocks = array();

        echo '<div class="dashboard-header"><h2 class="dashboard-title">' . __('Availability Settings', 'obenlo') . '</h2></div>';
        ?>
        <div class="form-section">
            <p style="margin-bottom:20px; color:#666;"><?php echo __('Set your default weekly business hours and block out specific dates for vacations or maintenance.', 'obenlo'); ?></p>

            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="obenlo_dashboard_save_availability">
                <?php wp_nonce_field('save_availability', 'availability_nonce'); ?>

                <h3 style="margin-bottom:20px;"><?php echo __('Business Hours', 'obenlo'); ?></h3>
                <div style="display:flex; flex-direction:column; gap:15px; margin-bottom:40px;">
                    <?php
                    $days = array('monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday');
                    foreach ($days as $key => $label):
                        $active = isset($business_hours[$key]['active']) && $business_hours[$key]['active'] === 'yes';
                        $start  = isset($business_hours[$key]['start']) ? $business_hours[$key]['start'] : '09:00';
                        $end    = isset($business_hours[$key]['end'])   ? $business_hours[$key]['end']   : '17:00';
                    ?>
                        <div class="grid-row" style="display:flex; align-items:center; gap:20px; padding:15px; border:1px solid #eee; border-radius:10px;">
                            <label style="width:120px; font-weight:bold; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" name="hours[<?php echo $key; ?>][active]" value="yes" <?php checked($active); ?> style="accent-color:#e61e4d;">
                                <?php echo __($label, 'obenlo'); ?>
                            </label>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <input type="time" name="hours[<?php echo $key; ?>][start]" value="<?php echo esc_attr($start); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                                <span><?php echo __('to', 'obenlo'); ?></span>
                                <input type="time" name="hours[<?php echo $key; ?>][end]" value="<?php echo esc_attr($end); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h3 style="margin-bottom:20px;"><?php echo __('Vacation / Blocked Dates', 'obenlo'); ?></h3>
                <div id="vacation-blocks" style="display:flex; flex-direction:column; gap:15px; margin-bottom:20px;">
                    <?php foreach ($vacation_blocks as $idx => $block): ?>
                        <div class="vacation-block-row grid-row" style="display:flex; align-items:center; gap:15px; padding:15px; border:1px dashed #ccc; border-radius:10px; background:#fafafa;">
                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo __('Start Date', 'obenlo'); ?></label>
                                <input type="date" name="vacation[<?php echo $idx; ?>][start]" value="<?php echo esc_attr($block['start']); ?>" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo __('End Date', 'obenlo'); ?></label>
                                <input type="date" name="vacation[<?php echo $idx; ?>][end]" value="<?php echo esc_attr($block['end']); ?>" required style="padding:8px; border:1px solid #ccc; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo __('Reason (Optional)', 'obenlo'); ?></label>
                                <input type="text" name="vacation[<?php echo $idx; ?>][reason]" value="<?php echo esc_attr($block['reason']); ?>" placeholder="<?php echo esc_attr(__('e.g. Renovation', 'obenlo')); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px; width:200px;">
                            </div>
                            <button type="button" class="remove-block-btn" style="align-self:flex-end; padding:9px 15px; background:#fff; border:1px solid #ef4444; color:#ef4444; border-radius:6px; cursor:pointer;"><?php echo __('Remove', 'obenlo'); ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-vacation-block" style="padding:10px 20px; background:#f0f0f0; border:1px solid #ccc; border-radius:8px; font-weight:bold; cursor:pointer; margin-bottom:40px;"><?php echo __('+ Add Blocked Date Range', 'obenlo'); ?></button>
                <div style="clear:both;"></div>
                <button type="submit" class="btn-primary"><?php echo __('Save Availability Settings', 'obenlo'); ?></button>
            </form>
        </div>

        <script>
           document.addEventListener('DOMContentLoaded', function() {
               var container  = document.getElementById('vacation-blocks');
               var addBtn     = document.getElementById('add-vacation-block');
               var blockCount = <?php echo count($vacation_blocks); ?>;
               var template   = `
                   <div class="vacation-block-row grid-row" style="display:flex; align-items:center; gap:15px; padding:15px; border:1px dashed #ccc; border-radius:10px; background:#fafafa; margin-top:15px;">
                       <div><label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo esc_js(__('Start Date', 'obenlo')); ?></label><input type="date" name="vacation[{idx}][start]" required style="padding:8px; border:1px solid #ccc; border-radius:6px;"></div>
                       <div><label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo esc_js(__('End Date', 'obenlo')); ?></label><input type="date" name="vacation[{idx}][end]" required style="padding:8px; border:1px solid #ccc; border-radius:6px;"></div>
                       <div><label style="display:block; font-size:0.8rem; font-weight:bold;"><?php echo esc_js(__('Reason (Optional)', 'obenlo')); ?></label><input type="text" name="vacation[{idx}][reason]" placeholder="<?php echo esc_attr(__('e.g. Renovation', 'obenlo')); ?>" style="padding:8px; border:1px solid #ccc; border-radius:6px; width:200px;"></div>
                       <button type="button" class="remove-block-btn" style="align-self:flex-end; padding:9px 15px; background:#fff; border:1px solid #ef4444; color:#ef4444; border-radius:6px; cursor:pointer;"><?php echo esc_js(__('Remove', 'obenlo')); ?></button>
                   </div>`;
               addBtn.addEventListener('click', function() {
                   container.insertAdjacentHTML('beforeend', template.replace(/{idx}/g, blockCount));
                   blockCount++;
               });
               container.addEventListener('click', function(e) {
                   if(e.target.classList.contains('remove-block-btn')) e.target.closest('.vacation-block-row').remove();
               });
           });
        </script>
        <?php
    }

    public function handle_save_availability()
    {
        if (!isset($_POST['availability_nonce']) || !wp_verify_nonce($_POST['availability_nonce'], 'save_availability')) {
            $this->redirect_with_error('security_failed');
        }
        if (!is_user_logged_in()) $this->redirect_with_error('unauthorized');

        $user_id = get_current_user_id();

        $hours = isset($_POST['hours']) ? (array)$_POST['hours'] : array();
        $sanitized_hours = array();
        foreach ($hours as $day => $data) {
            $sanitized_hours[sanitize_key($day)] = array(
                'active' => isset($data['active']) && $data['active'] === 'yes' ? 'yes' : 'no',
                'start'  => sanitize_text_field($data['start']),
                'end'    => sanitize_text_field($data['end']),
            );
        }
        update_user_meta($user_id, '_obenlo_business_hours', $sanitized_hours);

        $vacations = isset($_POST['vacation']) ? (array)$_POST['vacation'] : array();
        $sanitized_vacations = array();
        foreach ($vacations as $v) {
            if (!empty($v['start']) && !empty($v['end'])) {
                $sanitized_vacations[] = array(
                    'start'  => sanitize_text_field($v['start']),
                    'end'    => sanitize_text_field($v['end']),
                    'reason' => sanitize_text_field($v['reason'] ?? ''),
                );
            }
        }
        update_user_meta($user_id, '_obenlo_vacation_blocks', $sanitized_vacations);

        wp_safe_redirect(add_query_arg(array('action' => 'availability', 'message' => 'saved'), home_url('/host-dashboard')));
        exit;
    }
}
