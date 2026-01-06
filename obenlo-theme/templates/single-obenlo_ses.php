<?php
/**
 * Template Name: Single SES Asset
 */
get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
        <div class="rounded-[3rem] overflow-hidden shadow-2xl shadow-slate-200 aspect-square bg-slate-100">
            <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); endif; ?>
        </div>

        <div class="pt-6">
            <p class="text-indigo-600 font-black uppercase tracking-[0.3em] text-[10px] mb-4">Verified SES Asset</p>
            <h1 class="text-6xl font-black text-slate-900 tracking-tighter mb-6"><?php the_title(); ?></h1>
            
            <div class="prose prose-slate prose-lg font-medium text-slate-500 mb-10">
                <?php the_content(); ?>
            </div>

            <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-slate-400 font-bold uppercase tracking-widest text-[10px]">Instant Booking</span>
                    <span class="text-2xl font-black text-slate-900">$<?php echo get_post_meta(get_the_ID(), 'obenlo_price', true); ?><span class="text-sm text-slate-400 font-bold">/night</span></span>
                </div>
                <a href="<?php echo home_url('/checkout?id=' . get_the_ID()); ?>" class="block w-full text-center bg-indigo-600 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-900 transition-all shadow-xl shadow-indigo-100">
                    Secure This Stay
                </a>
            </div>
        </div>
    </div>
</main>
<?php endwhile; endif; ?>

<?php get_footer(); ?>
