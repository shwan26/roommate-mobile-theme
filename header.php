<?php
/**
 * Theme Header
 */

defined('ABSPATH') || exit;
$is_room_active = is_post_type_archive('room') || is_singular('room') || is_page(['post-a-room', 'edit-room']);
$is_roommate_active = is_post_type_archive('roommate') || is_singular('roommate') || is_page(['post-a-roommate', 'edit-roommate']);
$is_dashboard_active = is_page('dashboard');
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
                        <span class="site-logo-text">Bkkroomie</span>
                        <?php
                    }
                    ?>
                </a>
            </div>

            <nav class="site-nav desktop-nav" aria-label="<?php esc_attr_e('Primary Menu', 'roommate-mobile-theme'); ?>">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'menu primary-menu',
                    ));
                }
                ?>
            </nav>

            <div class="site-header__actions">
                <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn <?php echo $is_room_active ? 'btn-primary' : 'btn-secondary'; ?> header-btn">
                    Browse Rooms
                </a>

                <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn <?php echo $is_roommate_active ? 'btn-primary' : 'btn-secondary'; ?> header-btn">
                    Browse Roommates
                </a>

                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn <?php echo $is_dashboard_active ? 'btn-primary' : 'btn-outline'; ?> header-btn">
                        Dashboard
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline header-btn">
                        Login / Sign Up
                    </a>
                <?php endif; ?>

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
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'menu mobile-menu-list',
                    ));
                }
                ?>

                <div class="mobile-menu-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn <?php echo $is_room_active ? 'btn-primary' : 'btn-secondary'; ?> mobile-menu-btn">
                        Browse Rooms
                    </a>

                    <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn <?php echo $is_roommate_active ? 'btn-primary' : 'btn-secondary'; ?> mobile-menu-btn">
                        Browse Roommates
                    </a>

                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn <?php echo $is_dashboard_active ? 'btn-primary' : 'btn-outline'; ?> mobile-menu-btn">
                            Dashboard
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline mobile-menu-btn">
                            Login / Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>