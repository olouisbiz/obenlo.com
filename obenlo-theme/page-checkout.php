<?php
/**
 * Template Name: Hybrid Checkout
 */
if (!is_user_logged_in()) { wp_redirect(home_url('/login-hub')); exit; }

get_header(); 

$asset_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$asset = get_post($asset_id);

if (!$asset || $asset->post_type !== 'obenlo_ses') {
    echo '<main class="py-20 text-center"><h1 class="text-2xl font-black">Asset Not Found</h1><a href="'.home_url('/explore').'" class="text-indigo-600 font-bold">Return to Explorer</a></main>';
    get_footer();
    exit;
}

$price = get_post_meta($asset_id, 'obenlo_price', true);
?>

<main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-16 items-start">
        
        <div class="lg:col-span-2">
            <h1 class="text-5xl font-black text-slate-900 tracking-tighter mb-10">Confirm & Pay<span class="text-indigo-600">.</span></h1>
            
            <div class="space-y-12">
                <section>
                    <h2 class="text-xl font-black text-slate-900 mb-4 uppercase tracking-tight">Your Trip</h2>
                    <div class="flex justify-between items-center p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Dates</p>
                            <p class="font-bold text-slate-900">Selection Pending</p>
                        </div>
                        <button class="text-indigo-600 font-black text-xs uppercase tracking-widest underline">Edit</button>
                    </div>
                </section>

                <section>
                    <h2 class="text-xl font-black text-slate-900 mb-4 uppercase tracking-tight">Payment Method</h2>
                    <div class="p-8 border-2 border-indigo-600 rounded-[2rem] bg-indigo-50/30 flex items-center gap-4">
                        <div class="w-12 h-8 bg-slate-900 rounded-md"></div>
                        <p class="font-bold text-slate-900 text-sm">Stripe Secure Checkout</p>
                    </div>
                </section>
            </div>
        </div>

        <aside class="sticky top-32">
            <div class="bg-white p-8 rounded-[3rem] shadow-2xl shadow-slate-200 border border-slate-50">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-100">
                        <?php echo get_the_post_thumbnail($asset_id, 'thumbnail', ['class' => 'object-cover w-full h-full']); ?>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Selected Asset</p>
                        <h3 class="font-black text-slate-900 leading-tight"><?php echo get_the_title($asset_id); ?></h3>
                    </div>
                </div>

                <div class="space-y-4 border-t border-slate-50 pt-6 mb-8">
                    <div class="flex justify-between text-sm font-medium text-slate-500">
                        <span>Rate / Night</span>
                        <span>$<?php echo number_format($price, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-sm font-medium text-slate-500">
                        <span>Obenlo Service Fee (5%)</span>
                        <span>$<?php echo number_format($price * 0.05, 2); ?></span>
                    </div>
                    <div class="flex justify-between pt-4 border-t border-slate-100">
                        <span class="font-black text-slate-900 uppercase text-xs tracking-widest">Total (USD)</span>
                        <span class="font-black text-2xl text-slate-900">$<?php echo number_format($price * 1.05, 2); ?></span>
                    </div>
                </div>

                <button id="obenlo-pay-btn" class="w-full bg-slate-900 text-white py-6 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
                    Confirm Reservation
                </button>
                
                <p class="text-[9px] text-center text-slate-400 font-bold uppercase tracking-tighter mt-6 px-4">
                    By clicking, you agree to the Obenlo Nomadic Terms & Host Rules.
                </p>
            </div>
        </aside>
    </div>
</main>

<?php get_footer(); ?>
