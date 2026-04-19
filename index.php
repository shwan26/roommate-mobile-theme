<?php
/**
 * Main Index Template
 * Fallback template for posts/pages when no more specific template exists.
 */

defined('ABSPATH') || exit;

get_header();

// Gather user data once if logged in
$is_logged_in      = is_user_logged_in();
$current_user      = $is_logged_in ? wp_get_current_user() : null;
$display_name      = $is_logged_in ? $current_user->display_name : '';
$user_id           = $is_logged_in ? $current_user->ID : 0;
$room_form_url     = home_url('/post-a-room/');
$roommate_form_url = home_url('/post-a-roommate/');
$profile_url       = home_url('/profile/');
?>

<main id="primary" class="site-main default-index-page">
    <div class="container">

        <?php // ── LOGGED-IN WELCOME BAR ──────────────────────────────────────── ?>
        <?php if ( $is_logged_in && is_front_page() ) : ?>

            <div class="index-welcome">

                <div class="index-welcome__greeting">
                    <a href="<?php echo esc_url( $profile_url ); ?>" class="index-welcome__avatar-link">
                        <?php echo get_avatar( $user_id, 40, '', $display_name, array( 'class' => 'index-welcome__avatar' ) ); ?>
                    </a>
                    <div class="index-welcome__text">
                        <p class="index-welcome__name">
                            <?php printf( esc_html__( 'Hi, %s', 'roommate-mobile-theme' ), esc_html( $display_name ) ); ?>
                        </p>
                        <a href="<?php echo esc_url( $profile_url ); ?>" class="index-welcome__profile-link">
                            <?php esc_html_e( 'View profile', 'roommate-mobile-theme' ); ?>
                        </a>
                    </div>
                </div>

                <div class="index-welcome__actions">
                    <p class="index-welcome__prompt">
                        <?php esc_html_e( "What's your situation?", 'roommate-mobile-theme' ); ?>
                    </p>
                    <div class="index-welcome__buttons">
                        <a href="<?php echo esc_url( $room_form_url ); ?>" class="listing-type-btn">
                            <span class="listing-type-btn__icon" aria-hidden="true">🏠</span>
                            <span class="listing-type-btn__label">
                                <?php esc_html_e( 'I have a room', 'roommate-mobile-theme' ); ?>
                            </span>
                        </a>
                        <a href="<?php echo esc_url( $roommate_form_url ); ?>" class="listing-type-btn">
                            <span class="listing-type-btn__icon" aria-hidden="true">🔍</span>
                            <span class="listing-type-btn__label">
                                <?php esc_html_e( "I need a room", 'roommate-mobile-theme' ); ?>
                            </span>
                        </a>
                    </div>
                </div>

            </div><!-- .index-welcome -->

        <?php endif; ?>


        <?php // ── BLOG HEADER (only on blog page, not front page) ──────────── ?>
        <?php if ( is_home() && ! is_front_page() ) : ?>
            <header class="page-header">
                <span class="section-badge">Latest Posts</span>
                <h1 class="page-title"><?php single_post_title(); ?></h1>
            </header>
        <?php endif; ?>


        <?php // ── MAIN LOOP ─────────────────────────────────────────────────── ?>
        <?php if ( have_posts() ) : ?>
            <div class="listing-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('listing-card'); ?>>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <div class="listing-card__image">
                                    <?php the_post_thumbnail('large'); ?>
                                </div>
                            </a>
                        <?php endif; ?>

                        <div class="listing-card__content">
                            <div class="listing-card__top">
                                <span class="listing-chip">
                                    <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? 'Post' ); ?>
                                </span>
                            </div>

                            <h2 class="listing-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <div class="listing-card__excerpt">
                                <?php the_excerpt(); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                <?php esc_html_e( 'Read More', 'roommate-mobile-theme' ); ?>
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
                <h2><?php esc_html_e( 'Nothing found', 'roommate-mobile-theme' ); ?></h2>
                <p><?php esc_html_e( 'There is no content to display yet.', 'roommate-mobile-theme' ); ?></p>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>