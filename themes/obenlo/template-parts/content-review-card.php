<?php
/**
 * Template part for displaying a review card
 */

if ( ! isset( $review ) ) {
    return;
}

$rating = isset( $review['rating'] ) ? $review['rating'] : 5;
$comment = isset( $review['comment'] ) ? $review['comment'] : '';
$author = isset( $review['author'] ) ? $review['author'] : __( 'Guest', 'obenlo' );
$date = isset( $review['date'] ) ? $review['date'] : __( 'Recently', 'obenlo' );
?>

<div class="review-card" style="background: #fff; border: 1px solid #ddd; border-radius: 16px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)';">
    <div style="display: flex; gap: 2px; color: var(--obenlo-primary); margin-bottom: 15px; font-size: 0.9rem;">
        <?php for( $i = 0; $i < 5; $i++ ): ?>
            <span><?php echo $i < $rating ? '★' : '☆'; ?></span>
        <?php endfor; ?>
    </div>
    <blockquote style="margin: 0 0 20px; padding: 0; color: #484848; font-style: italic; font-size: 1rem; line-height: 1.6;">
        "<?php echo esc_html( wp_trim_words( $comment, 25 ) ); ?>"
    </blockquote>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; background: #f7f7f7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #717171; font-size: 0.9rem;">
            <?php echo strtoupper( substr( $author, 0, 1 ) ); ?>
        </div>
        <div>
            <div style="font-weight: 600; font-size: 0.95rem;"><?php echo esc_html( $author ); ?></div>
            <div style="color: #717171; font-size: 0.85rem;"><?php echo esc_html( $date ); ?></div>
        </div>
    </div>
</div>
