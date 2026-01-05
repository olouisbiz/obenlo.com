<?php
/**
 * Template Name: Host Studio Dashboard
 * Location: /obenlo-theme/templates/dashboard-host.php
 */

get_header();
$host_id = get_current_user_id();
$stats = get_obenlo_host_stats($host_id);
?>

<div class="obenlo-container" style="padding: 40px; max-width: 1200px; margin: auto;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; font-weight: 800;">Studio <span style="color: #6366f1;">Insights</span></h1>
        <div style="background: #f8fafc; padding: 10px 20px; border-radius: 50px; border: 1px solid #e2e8f0;">
            <strong>Total Earned:</strong> $<?php echo number_format($stats['total_revenue'], 2); ?>
        </div>
    </header>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <div style="background: white; border-radius: 15px; border: 1px solid #e2e8f0; padding: 25px;">
            <h3>Recent Bookings</h3>
            <hr>
            <?php if (!empty($stats['recent_bookings'])): ?>
                <?php foreach ($stats['recent_bookings'] as $booking): ?>
                    <div style="padding: 15px 0; border-bottom: 1px solid #f1f5f9;">
                        <div style="display: flex; justify-content: space-between;">
                            <strong><?php echo esc_html($booking['customer_name']); ?></strong>
                            <span>$<?php echo number_format($booking['amount'], 2); ?></span>
                        </div>
                        <small style="color: #64748b;"><?php echo esc_html($booking['booking_dates']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No bookings yet. Your listings are live and waiting!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>