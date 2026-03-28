<?php
/**
 * Template Name: Community
 */

get_header(); ?>

<div class="static-page-header" style="background: linear-gradient(135deg, #222 0%, #111 100%); padding: 100px 20px; text-align: center; color: #fff;">
    <div style="font-size: 3rem; margin-bottom: 20px;">🌍🤝✨</div>
    <h1 style="font-size: 3.5rem; margin-bottom: 20px;"><?php esc_html_e( 'The Obenlo Community', 'obenlo' ); ?></h1>
    <p style="font-size: 1.3rem; max-width: 800px; margin: 0 auto; color: #ccc;"><?php esc_html_e( 'Connect with travelers, share your hosting tips, and discover stories from our global network of explorers.', 'obenlo' ); ?></p>
</div>

<div class="static-page-content" style="max-width: 1200px; margin: 0 auto; padding: 80px 20px;">
    
    <!-- Host Spotlights -->
    <section style="margin-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <div>
                <h2 style="font-size: 2.2rem; margin-bottom: 15px;"><?php esc_html_e( 'Host Spotlights', 'obenlo' ); ?></h2>
                <p style="color: #666; font-size: 1.1rem;"><?php esc_html_e( 'Meet the people opening their doors and sharing their passions.', 'obenlo' ); ?></p>
            </div>
            <a href="<?php echo home_url('/blog'); ?>" style="color: var(--obenlo-primary); font-weight: bold; text-decoration: none;">Read all stories &rarr;</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div style="border: 1px solid #eee; border-radius: 20px; overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="background: #f7f7f7; height: 200px; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🎨</div>
                <div style="padding: 25px;">
                    <div style="color: var(--obenlo-primary); font-weight: bold; font-size: 0.85rem; margin-bottom: 10px; text-transform: uppercase;">Experience Host</div>
                    <h3 style="margin-bottom: 10px; font-size: 1.3rem;">Hosting a Pottery Masterclass in the City</h3>
                    <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px;">Learn how Sarah turned her weekend hobby into a fully booked experience on Obenlo.</p>
                    <a href="<?php echo home_url('/blog'); ?>" style="color: #222; font-weight: bold; text-decoration: none;">Read Story</a>
                </div>
            </div>
            
            <div style="border: 1px solid #eee; border-radius: 20px; overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="background: #f7f7f7; height: 200px; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🏡</div>
                <div style="padding: 25px;">
                    <div style="color: var(--obenlo-primary); font-weight: bold; font-size: 0.85rem; margin-bottom: 10px; text-transform: uppercase;">Stay Host</div>
                    <h3 style="margin-bottom: 10px; font-size: 1.3rem;">From Empty Nest to Global Guest Book</h3>
                    <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px;">How a retired couple found a new calling by sharing their historic farmhouse.</p>
                    <a href="<?php echo home_url('/blog'); ?>" style="color: #222; font-weight: bold; text-decoration: none;">Read Story</a>
                </div>
            </div>

            <div style="border: 1px solid #eee; border-radius: 20px; overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="background: #f7f7f7; height: 200px; display: flex; align-items: center; justify-content: center; font-size: 3rem;">🛠️</div>
                <div style="padding: 25px;">
                    <div style="color: var(--obenlo-primary); font-weight: bold; font-size: 0.85rem; margin-bottom: 10px; text-transform: uppercase;">Service Host</div>
                    <h3 style="margin-bottom: 10px; font-size: 1.3rem;">Building a Freelance Business with Obenlo</h3>
                    <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 20px;">Discover how local guides and professionals are scaling their services securely.</p>
                    <a href="<?php echo home_url('/blog'); ?>" style="color: #222; font-weight: bold; text-decoration: none;">Read Story</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Forum / Connection Teaser -->
    <section style="background: var(--obenlo-primary); color: #fff; border-radius: 30px; padding: 60px; text-align: center; margin-bottom: 80px;">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;"><?php esc_html_e( 'The Community Forum is launching soon', 'obenlo' ); ?></h2>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto 30px; opacity: 0.9;">
            <?php esc_html_e( 'We are building a dedicated space for you to ask questions, share itineraries, and collaborate on best practices with hosts and guests worldwide.', 'obenlo' ); ?>
        </p>
        <button style="background: #fff; color: var(--obenlo-primary); border: none; padding: 16px 40px; border-radius: 12px; font-weight: bold; font-size: 1.1rem; cursor: pointer; opacity: 0.5;">
            Forum Coming 2026
        </button>
    </section>

    <!-- Socials & Events -->
    <section style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <div style="background: #f7f7f7; padding: 50px; border-radius: 30px;">
            <div style="font-size: 2.5rem; margin-bottom: 20px;">📅</div>
            <h3 style="font-size: 1.8rem; margin-bottom: 15px;"><?php esc_html_e( 'Local Meetups', 'obenlo' ); ?></h3>
            <p style="color: #666; font-size: 1.05rem; line-height: 1.6; margin-bottom: 25px;">
                <?php esc_html_e( 'Join us at our upcoming host events and traveler mixers in major cities. Stay tuned to the blog for the next Obenlo meetup near you.', 'obenlo' ); ?>
            </p>
            <a href="<?php echo home_url('/blog'); ?>" style="display: inline-block; background: #222; color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold;">Check Schedule</a>
        </div>
        
        <div style="background: #f7f7f7; padding: 50px; border-radius: 30px;">
            <div style="font-size: 2.5rem; margin-bottom: 20px;">💬</div>
            <h3 style="font-size: 1.8rem; margin-bottom: 15px;"><?php esc_html_e( 'Follow our Journey', 'obenlo' ); ?></h3>
            <p style="color: #666; font-size: 1.05rem; line-height: 1.6; margin-bottom: 25px;">
                <?php esc_html_e( 'We regularly feature amazing stays, thrilling experiences, and top-rated hosts on our social channels. Tag us to be featured!', 'obenlo' ); ?>
            </p>
            <div style="display: flex; gap: 20px;">
                <a href="https://www.instagram.com/obenlobooking" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; background: var(--obenlo-primary); color: #fff; text-decoration: none; border-radius: 50%; font-weight: bold;">Ig</a>
                <a href="https://www.facebook.com/obenlobooking" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; background: #1877F2; color: #fff; text-decoration: none; border-radius: 50%; font-weight: bold;">Fb</a>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>
