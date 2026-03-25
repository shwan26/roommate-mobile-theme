<?php
/**
 * Theme Footer
 */

defined('ABSPATH') || exit;
?>

<footer class="site-footer">
    <div class="container">
        <div class="site-footer__inner">

            <div class="site-footer__brand">
                <h2 class="site-footer__title"><?php bloginfo('name'); ?></h2>
                <p class="site-footer__text">
                    <?php bloginfo('description'); ?>
                </p>
            </div>

            <div class="site-footer__nav">
                <h3 class="site-footer__heading"><?php esc_html_e('Quick Links', 'roommate-mobile-theme'); ?></h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'footer-menu',
                    'fallback_cb'    => false,
                ));
                ?>
            </div>

            <div class="site-footer__contact">
                <h3 class="site-footer__heading"><?php esc_html_e('About', 'roommate-mobile-theme'); ?></h3>
                <p class="site-footer__text">
                    <?php esc_html_e('Find roommates, discover rooms, and connect easily in one place.', 'roommate-mobile-theme'); ?>
                </p>
            </div>

        </div>

        <div class="site-footer__bottom">
            <p>
                &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>.
                <?php esc_html_e('All rights reserved.', 'roommate-mobile-theme'); ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>