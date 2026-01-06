<?php
/**
 * Template Name: Explorer Grid
 */
get_header(); ?>

<main class="max-w-7xl mx-auto px-6 py-12">
    <header class="mb-12">
        <h1 class="text-6xl font-black text-slate-900 tracking-tighter mb-4">Discovery<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-medium uppercase tracking-widest text-xs">Explore Nomad-Ready SES Assets</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php
        $query = new WP_Query(['post_type' => 'obenlo_ses', 'posts_per_page' => 12]);
        if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            
            <a href="<?php the_permalink(); ?>" class="group block">
                <div class="relative aspect-[4/5] overflow-hidden rounded-[2.5rem] bg-slate-100 mb-6 shadow-xl shadow-slate-200/50 transition-transform duration-500 group-hover:scale-[0.98]">
                    <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['class' => 'object-cover w-full h-full']); endif; ?>
                    <div class="absolute top-6 right-6 bg-white/90 backdrop-blur-md px-4 py-2 rounded-2xl shadow-sm">
                        <span class="text-xs font-black text-slate-900 uppercase">Featured</span>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-1 group-hover:text-indigo-600 transition-colors"><?php the_title(); ?></h3>
                <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider"><?php echo get_post_meta(get_the_ID(), 'obenlo_location', true); ?></p>
            </a>

        <?php endwhile; wp_reset_postdata(); endif; ?>
    </div>
</main>

<?php get_footer(); ?>
