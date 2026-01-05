<?php
/**
 * Template: Single SES Listing
 * Location: /obenlo-theme/single-obenlo_listing.php
 */

get_header();
while (have_posts()) : the_post();
    $price = get_post_meta(get_the_ID(), '_obenlo_price', true) ?: '0';
?>

<div class="obenlo-listing-container" style="max-width: 1000px; margin: 50px auto; padding: 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
    
    <article>
        <h1 style="font-size: 2.5rem; margin-bottom: 20px;"><?php the_title(); ?></h1>
        <div class="listing-content" style="line-height: 1.8; color: #334155;">
            <?php the_content(); ?>
        </div>
    </article>

    <aside>
        <div style="position: sticky; top: 20px; border: 1px solid #e2e8f0; padding: 30px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">$<?php echo esc_html($price); ?> <span style="font-size: 0.9rem; color: #64748b;">/ person</span></h3>
            
            <button id="obenlo-book-now" 
                    data-id="<?php the_ID(); ?>" 
                    style="width: 100%; background: #6366f1; color: white; padding: 15px; border: none; border-radius: 12px; font-weight: bold; font-size: 1.1rem; cursor: pointer; margin-top: 20px;">
                Book Experience
            </button>
            
            <p style="font-size: 0.8rem; color: #94a3b8; text-align: center; margin-top: 15px;">Secure payment via Stripe</p>
        </div>
    </aside>

</div>

<?php endwhile; get_footer(); ?>