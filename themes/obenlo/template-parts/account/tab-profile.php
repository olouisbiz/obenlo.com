<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 30px;">Personal Info</h2>
            
            <form method="POST" action="" style="max-width: 600px; background: #fff; padding: 30px; border: 1px solid #eee; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <input type="hidden" name="action" value="update_profile">
                <?php wp_nonce_field( 'update_user_profile', 'profile_nonce' ); ?>
                
                <div class="grid-row" style="margin-bottom: 20px;">
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">First Name</label>
                        <input type="text" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                    <div class="grid-col-1-2">
                        <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Unique Guest ID</label>
                    <div style="background: #f9fafb; padding: 12px 15px; border: 1px solid #eee; border-radius: 10px; font-family: monospace; font-weight: 800; color: #e61e4d; font-size: 1.1rem; letter-spacing: 1px;">
                        <?php echo esc_html( Obenlo_Booking_Payments::get_user_guest_id($user_id) ); ?>
                    </div>
                    <p style="font-size: 0.75rem; color: #888; margin-top: 6px;">Use this ID for check-ins at the door or when contacting support.</p>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #444;">Email Address</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 10px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                </div>

                <button type="submit" style="background: #222; color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#000'" onmouseout="this.style.background='#222'">
                    Save Changes
                </button>
            </form>

