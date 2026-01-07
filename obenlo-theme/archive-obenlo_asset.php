<?php
/** * Archive Template for Marketplace Assets
 */
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-20 flex-grow">
    <div class="mb-16">
        <h1 class="text-5xl font-black tracking-tighter uppercase">Category Results<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Filter: <?php the_archive_title(); ?></p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="group block">
                <div class="aspect-[4/5] bg-slate-100 rounded-[3rem] mb-6 overflow-hidden shadow-xl shadow-slate-100 transition-all duration-500 group-hover:shadow-indigo-100">
                    <?php if (has_post_thumbnail()) : the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); endif; ?>
                </div>
                <h3 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors"><?php the_title(); ?></h3>
            </a>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php get_footer(); ?>
