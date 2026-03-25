<?php
/**
 * Main Index Template
 * Fallback template for posts/pages when no more specific template exists.
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main default-index-page">
    <div class="container">

        <?php if (is_home() && !is_front_page()) : ?>
            <header class="page-header">
                <span class="section-badge">Latest Posts</span>
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>

        <?php if (have_posts()) : ?>
            <div class="listing-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('listing-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <div class="listing-card__image">
                                    <?php the_post_thumbnail('large'); ?>
                                </div>
                            </a>
                        <?php endif; ?>

                        <div class="listing-card__content">
                            <div class="listing-card__top">
                                <span class="listing-chip">
                                    <?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Post'); ?>
                                </span>
                            </div>

                            <h2 class="listing-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <div class="listing-card__excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                Read More
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="pagination-wrap">
                <?php the_posts_pagination(); ?>
            </div>

        <?php else : ?>
            <div class="empty-state">
                <h2>Nothing found</h2>
                <p>There is no content to display yet.</p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>