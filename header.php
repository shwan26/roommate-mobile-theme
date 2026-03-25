<?php
/**
 * Theme Header
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container">
        <div class="site-header__inner">

            <div class="site-branding">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-link">
                    <?php
                    if (function_exists('the_custom_logo') && has_custom_logo()) {
                        the_custom_logo();
                    } else {
                        ?>
                        <span class="site-logo-text"><?php bloginfo('name'); ?></span>
                        <?php
                    }
                    ?>
                </a>
            </div>

            <nav class="site-nav desktop-nav" aria-label="<?php esc_attr_e('Primary Menu', 'roommate-mobile-theme'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'menu primary-menu',
                    'fallback_cb'    => 'rmt_primary_menu_fallback',
                ));
                ?>
            </nav>

            <div class="site-header__actions">
                <a href="<?php echo esc_url(get_post_type_archive_link('have_room')); ?>" class="btn btn-secondary header-btn">
                    Have Room
                </a>
                <a href="<?php echo esc_url(get_post_type_archive_link('need_room')); ?>" class="btn btn-primary header-btn">
                    Need Room
                </a>

                <button
                    class="mobile-menu-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                    aria-label="<?php esc_attr_e('Open menu', 'roommate-mobile-theme'); ?>"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

        </div>
    </div>

    <div id="mobile-menu" class="mobile-menu" hidden>
        <div class="container">
            <nav class="mobile-nav" aria-label="<?php esc_attr_e('Mobile Menu', 'roommate-mobile-theme'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'menu mobile-menu-list',
                    'fallback_cb'    => 'rmt_primary_menu_fallback',
                ));
                ?>

                <div class="mobile-menu-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('have_room')); ?>" class="btn btn-secondary mobile-menu-btn">
                        Explore Have Room
                    </a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('need_room')); ?>" class="btn btn-primary mobile-menu-btn">
                        Explore Need Room
                    </a>
                </div>
            </nav>
        </div>
    </div>
</header>