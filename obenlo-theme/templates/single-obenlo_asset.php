<?php
/** Template Name: Single Asset View **/
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-16 flex-grow">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
        <div class="aspect-square bg-slate-100 rounded-[3rem] overflow-hidden shadow-2xl">
            <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
        </div>
        <div class="flex flex-col justify-center">
            <span class="text-indigo-600 font-black uppercase tracking-widest text-[10px] mb-4">Verified Listing</span>
            <h1 class="text-6xl font-black tracking-tighter mb-6"><?php the_title(); ?></h1>
            <div class="prose text-slate-500 mb-10"><?php the_content(); ?></div>
            <div class="p-8 bg-slate-50 rounded-[2.5rem] border border-slate-100">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-2xl font-black">$<?php echo get_post_meta(get_the_ID(), 'obenlo_price', true); ?></span>
                    <span class="text-slate-400 font-bold uppercase text-[10px]">Secure Transaction</span>
                </div>
                <a href="<?php echo home_url('/secure-checkout?id=' . get_the_ID()); ?>" class="block text-center bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-indigo-600 transition-all">Book Now</a>
            </div>
        </div>
    </div>
    <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>
