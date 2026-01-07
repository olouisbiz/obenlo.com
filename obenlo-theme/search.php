<?php
/**
 * Marketplace Search Results
 */
get_header(); ?>
<main class="max-w-7xl mx-auto px-6 py-20 flex-grow">
    <div class="mb-16">
        <h1 class="text-5xl font-black tracking-tighter uppercase">Search Results<span class="text-indigo-600">.</span></h1>
        <p class="text-slate-400 font-bold uppercase tracking-widest text-[10px] mt-2">Query: <?php echo get_search_query(); ?></p>
    </div>

    <?php if (have_posts()) : ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <?php while (have_posts()) : the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="group block">
                    <div class="aspect-[4/5] bg-slate-100 rounded-[3rem] mb-6 overflow-hidden shadow-xl shadow-slate-100">
                        <?php the_post_thumbnail('large', ['class' => 'w-full h-full object-cover']); ?>
                    </div>
                    <h3 class="text-xl font-black"><?php the_title(); ?></h3>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <div class="py-20 text-center bg-slate-50 rounded-[3rem] border border-slate-100">
            <p class="font-bold text-slate-400 uppercase tracking-widest">No matching assets found.</p>
        </div>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
