<?php
/**
 * Template Name: Obenlo Checkout Hub
 * Logic: Validates the booking session, calculates totals, and initiates the Stripe Bridge.
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login-hub'));
    exit;
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$booking    = get_post($booking_id);

// Security: Ensure booking exists and belongs to the current user
if (!$booking || $booking->post_type !== 'obenlo_booking' || $booking->post_author != get_current_user_id()) {
    wp_die('Invalid Security Token or Session Expired.');
}

// Extract Asset Data
$asset_id   = get_post_meta($booking_id, '_booking_asset_id', true);
$price      = get_post_meta($booking_id, '_booking_total', true);
$type       = get_post_meta($asset_id, '_ses_type', true);
$location   = get_post_meta($asset_id, '_ses_location', true);

get_header(); ?>

<main class="obenlo-container py-12 lg:py-20 min-h-screen">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <div class="lg:col-span-7 animate-fade-in">
            <h1 class="text-4xl font-black mb-2 italic-title">Secure Checkout</h1>
            <p class="text-slate-400 mb-10">Verify your details and finalize your reservation on the Obenlo Network.</p>

            <div class="glass-card p-8 mb-8">
                <h3 class="text-lg font-bold mb-6">Payment Method</h3>
                
                <div id="payment-element" class="mb-6">
                    </div>

                <div id="payment-message" class="hidden p-4 mb-4 bg-red-50 text-red-600 rounded-xl text-sm font-medium"></div>

                <button id="submit-payment" class="btn-primary w-full py-5 text-lg group">
                    Confirm & Pay 
                    <span class="ml-2 opacity-50 group-hover:translate-x-1 transition-transform">→</span>
                </button>
            </div>

            <p class="text-center text-xs text-slate-400">
                Encrypted with 256-bit SSL. By clicking confirm, you agree to the Obenlo Guest Policy.
            </p>
        </div>

        <div class="lg:col-span-5">
            <div class="glass-card sticky top-10 overflow-hidden">
                <div class="h-48 w-full bg-slate-100 overflow-hidden">
                    <?php echo get_the_post_thumbnail($asset_id, 'large', ['class' => 'w-full h-full object-cover']); ?>
                </div>

                <div class="p-8">
                    <span class="text-[10px] uppercase tracking-widest font-black text-brand mb-2 block">
                        <?php echo esc_html($type); ?>
                    </span>
                    <h2 class="text-2xl font-bold mb-1"><?php echo get_the_title($asset_id); ?></h2>
                    <p class="text-slate-400 text-sm mb-6 flex items-center">
                        <span class="dashicons dashicons-location text-sm mr-1"></span>
                        <?php echo esc_html($location); ?>
                    </p>

                    <hr class="border-slate-50 mb-6">

                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Base Reservation</span>
                            <span class="font-semibold">$<?php echo number_format($price, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Network Service Fee (5%)</span>
                            <span class="font-semibold">$<?php echo number_format($price * 0.05, 2); ?></span>
                        </div>
                        <div class="flex justify-between text-xl font-black pt-4 border-t border-slate-50">
                            <span>Total</span>
                            <span>$<?php echo number_format($price * 1.05, 2); ?></span>
                        </div>
                    </div>

                    <div class="bg-indigo-50 p-4 rounded-2xl flex items-start gap-3">
                        <div class="bg-brand text-white p-1 rounded-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <p class="text-xs text-indigo-900 leading-relaxed italic">
                            Your payment is held in escrow until 24 hours after check-in for your safety.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe Bridge
    const stripe = Stripe('<?php echo get_option('obenlo_stripe_public_key'); ?>');
    
    // Additional logic for Stripe Elements will go here 
    // to link with your Stripe-engine.php module.
</script>

<?php get_footer(); ?>