<?php
/**
 * Template part for displaying a host card
 */

if ( ! isset( $host ) ) {
    return;
}

$avatar_url = get_avatar_url( $host->ID, array( 'size' => 120 ) );
$display_name = $host->display_name;
$registered_date = date( 'Y', strtotime( $host->user_registered ) );
$author_url = get_author_posts_url( $host->ID );
?>

<div class="host-card" style="background: #fff; border: 1px solid #ddd; border-radius: 24px; padding: 30px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)';" onclick="window.location='<?php echo esc_url( $author_url ); ?>';">
    <div style="position: relative; display: inline-block; margin-bottom: 15px;">
        <img src="<?php echo esc_url( $avatar_url ); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--obenlo-primary);">
        <div style="position: absolute; bottom: 0; right: 0; background: var(--obenlo-primary); color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 2px solid #fff;">
            ★
        </div>
    </div>
    <h3 style="margin: 0 0 5px; font-size: 1.2rem; font-weight: 600;"><?php echo esc_html( $display_name ); ?></h3>
    <p style="color: #717171; font-size: 0.9rem; margin: 0 0 20px;"><?php printf( esc_html__( 'Hosted since %s', 'obenlo' ), esc_html( $registered_date ) ); ?></p>
    <a href="<?php echo esc_url( $author_url ); ?>" style="display: inline-block; padding: 10px 20px; background: #222; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9';" onmouseout="this.style.opacity='1';"><?php esc_html_e( 'View Profile', 'obenlo' ); ?></a>
</div>
