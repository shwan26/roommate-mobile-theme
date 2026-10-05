<?php
/**
 * 404 Template - Page not found
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main error-404-page">
    <div class="container">
        <div class="error-404">
            <p class="error-404__code">404</p>
            <h1 class="error-404__title"><?php esc_html_e('Page not found', 'roommate-mobile-theme'); ?></h1>
            <p class="error-404__text">
                <?php esc_html_e('Sorry, the page you are looking for does not exist or may have been moved.', 'roommate-mobile-theme'); ?>
            </p>

            <div class="error-404__actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                    <?php esc_html_e('Back to Home', 'roommate-mobile-theme'); ?>
                </a>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
