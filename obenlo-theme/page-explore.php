<?php
/** Template Name: Marketplace Explorer **/
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-20 flex-grow">
    <div class="flex justify-between items-end mb-16">
        <div>
            <h1 class="text-5xl font-black tracking-tighter uppercase">Marketplace<span class="text-indigo-600">.</span></h1>
            <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Verified Host Assets</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
        <?php
        $query = new WP_Query(['post_type' => 'obenlo_asset', 'posts_per_page' => 12]);
        if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="group block">
                <div class="aspect-[4/5] bg-slate-100 rounded-[3rem] mb-6 overflow-hidden shadow-xl shadow-slate-100 transition-all duration-500 group-hover:shadow-indigo-100 group-hover:scale-[0.98]">
                    <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); endif; ?>
                </div>
                <div class="flex justify-between items-center px-2">
                    <h3 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors"><?php the_title(); ?></h3>
                    <p class="font-black text-slate-900">$<?php echo get_post_meta(get_the_ID(), 'obenlo_price', true); ?></p>
                </div>
            </a>
        <?php endwhile; wp_reset_postdata(); endif; ?>
    </div>
</main>
<?php get_footer(); ?>
