<?php $user = wp_get_current_user(); $user_id = $user->ID; ?>
            <div class="obenlo-testimony-section">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 8px;">Obenlo Love</h2>
                <p style="color:#666; font-size:1.05rem; margin-bottom:30px;">We'd love to hear about your experience with the platform. Your feedback helps us grow and improve!</p>

                <?php if (isset($_GET['message']) && $_GET['message'] === 'saved'): ?>
                    <div style="background: #ecfdf5; color: #065f46; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; border: 1px solid #a7f3d0;">
                        ❤️ Thank you! Your testimony has been submitted for moderation.
                    </div>
                <?php endif; ?>

                <div style="background: #fff; border: 1px solid #eee; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 40px;">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; font-weight: 800;">Write a Testimony</h3>
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
                        <input type="hidden" name="action" value="obenlo_save_testimony">
                        <?php wp_nonce_field('save_testimony', 'testimony_nonce'); ?>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Headline</label>
                            <input type="text" name="testimony_title" placeholder="e.g. Best booking experience ever!" required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Your Experience</label>
                            <textarea name="testimony_content" rows="4" placeholder="Tell us what you love about Obenlo..." required style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s; resize: vertical;" onfocus="this.style.borderColor='#e61e4d'" onblur="this.style.borderColor='#ddd'"></textarea>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #444;">Overall Rating</label>
                            <div class="star-rating" style="display: flex; gap: 10px; font-size: 2rem; color: #ddd; cursor: pointer;">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <span class="star" data-value="<?php echo $i; ?>" style="transition: color 0.2s;" onclick="setRating(<?php echo $i; ?>)" onmouseover="highlightStars(<?php echo $i; ?>)" onmouseout="resetStars()">★</span>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="testimony_rating" id="testimony_rating" value="5">
                        </div>

                        <button type="submit" style="background: #e61e4d; color: #fff; border: none; padding: 14px 30px; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 10px;" onmouseover="this.style.background='#d91945'" onmouseout="this.style.background='#e61e4d'">
                            Submit Testimony
                        </button>
                    </form>
                </div>

                <script>
                    let currentRating = 5;
                    const stars = document.querySelectorAll('.star');
                    const ratingInput = document.getElementById('testimony_rating');

                    function setRating(val) {
                        currentRating = val;
                        ratingInput.value = val;
                        updateStars(val);
                    }

                    function highlightStars(val) {
                        updateStars(val);
                    }

                    function resetStars() {
                        updateStars(currentRating);
                    }

                    function updateStars(val) {
                        stars.forEach((star, index) => {
                            if (index < val) {
                                star.style.color = '#f59e0b';
                            } else {
                                star.style.color = '#ddd';
                            }
                        });
                    }

                    // Initial state
                    updateStars(5);
                </script>

                <?php
                $user_testimonies = get_posts(array(
                    'post_type' => 'testimony',
                    'author' => get_current_user_id(),
                    'post_status' => array('pending', 'publish', 'draft'),
                    'posts_per_page' => -1
                ));
                ?>

                <?php if (!empty($user_testimonies)): ?>
                    <h3 style="margin-bottom: 20px; font-size: 1.3rem; font-weight: 800;">Your Past Testimonies</h3>
                    <div style="display: grid; gap: 15px;">
                        <?php foreach ($user_testimonies as $post): 
                            $rating = get_post_meta($post->ID, '_obenlo_testimony_rating', true);
                            $status = $post->post_status;
                            $status_label = ($status === 'publish') ? 'Approved & Live' : (($status === 'pending') ? 'Pending Review' : 'Draft');
                            $status_color = ($status === 'publish') ? '#10b981' : (($status === 'pending') ? '#f59e0b' : '#666');
                        ?>
                            <div style="background: #fff; border: 1px solid #eee; border-radius: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                                <div>
                                    <div style="display: flex; gap: 4px; color: #f59e0b; margin-bottom: 5px;">
                                        <?php for($i=1; $i<=5; $i++) echo ($i <= $rating ? '★' : '☆'); ?>
                                    </div>
                                    <h4 style="margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;"><?php echo esc_html($post->post_title); ?></h4>
                                    <p style="margin: 0; color: #666; font-size: 0.95rem;"><?php echo esc_html($post->post_content); ?></p>
                                </div>
                                <div style="text-align: right; flex-shrink: 0;">
                                    <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: <?php echo $status_color; ?>;"><?php echo esc_html($status_label); ?></span>
                                    <div style="font-size: 0.8rem; color: #aaa; margin-top: 4px;"><?php echo get_the_date('', $post->ID); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
