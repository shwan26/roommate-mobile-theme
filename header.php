<?php
/**
 * Theme Header
 */

defined('ABSPATH') || exit;

$request_path        = function_exists('rmt_get_frontend_request_path') ? rmt_get_frontend_request_path() : '';
$is_room_active      = is_post_type_archive('room') || is_singular('room') || is_page(array('post-a-room', 'edit-room')) || $request_path === 'post-a-room';
$is_roommate_active  = is_post_type_archive('roommate') || is_singular('roommate') || is_page(array('post-a-roommate', 'edit-roommate')) || $request_path === 'post-a-roommate';
$is_dashboard_active = is_page(array('dashboard', 'edit-profile', 'messages'));
$is_browse_active    = is_post_type_archive(array('room', 'roommate')) || is_singular(array('room', 'roommate'));
$is_post_active      = is_page(array('post-a-room', 'post-a-roommate', 'edit-room', 'edit-roommate')) || in_array($request_path, array('post-a-room', 'post-a-roommate'), true);

$custom_logo_id = get_theme_mod('custom_logo');
$theme_logo_url = get_template_directory_uri() . '/assets/images/bkkroomie-logo.png';
$theme_logo_path = get_template_directory() . '/assets/images/bkkroomie-logo.png';
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
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo-link" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <?php
                    if ($custom_logo_id) {
                        echo wp_get_attachment_image(
                            $custom_logo_id,
                            'full',
                            false,
                            array(
                                'class'    => 'site-logo-img',
                                'alt'      => esc_attr(get_bloginfo('name')),
                                'loading'  => 'eager',
                                'decoding' => 'async',
                            )
                        );
                    } elseif (file_exists($theme_logo_path)) {
                        ?>
                        <img
                            src="<?php echo esc_url($theme_logo_url); ?>"
                            alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                            class="site-logo-img"
                            loading="eager"
                            decoding="async"
                        >
                        <?php
                    } else {
                        ?>
                        <span class="site-logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
                        <?php
                    }
                    ?>
                </a>
            </div>

            <div class="site-header__actions">
                <nav class="header-nav desktop-nav" aria-label="<?php esc_attr_e('Primary Menu', 'roommate-mobile-theme'); ?>">
                    <ul class="header-nav__list">
                        <li class="header-dropdown <?php echo $is_browse_active ? 'is-active' : ''; ?>" data-header-dropdown>
                            <button class="header-dropdown__toggle" type="button" aria-expanded="false" aria-controls="browse-dropdown">
                                <span>Browse</span>
                                <span class="header-dropdown__chevron" aria-hidden="true"></span>
                            </button>
                            <ul id="browse-dropdown" class="header-dropdown__menu" hidden>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">Browse Roommates</a></li>
                                <li><a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>">Browse Rooms</a></li>
                            </ul>
                        </li>
                        <li class="header-dropdown <?php echo $is_post_active ? 'is-active' : ''; ?>" data-header-dropdown>
                            <button class="header-dropdown__toggle" type="button" aria-expanded="false" aria-controls="post-dropdown">
                                <span>Post</span>
                                <span class="header-dropdown__chevron" aria-hidden="true"></span>
                            </button>
                            <ul id="post-dropdown" class="header-dropdown__menu" hidden>
                                <li><a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>">Post a Roommate</a></li>
                                <li><a href="<?php echo esc_url(home_url('/post-a-room/')); ?>">Post a Room</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>

                <?php if (is_user_logged_in()) : ?>
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn <?php echo $is_dashboard_active ? 'btn-primary' : 'btn-outline'; ?> header-btn">
                        Dashboard
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline header-btn">
                        Login / Signup
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
            <div class="mobile-menu__header">
                <button
                    class="mobile-menu-close"
                    type="button"
                    aria-controls="mobile-menu"
                    aria-label="<?php esc_attr_e('Close menu', 'roommate-mobile-theme'); ?>"
                >
                    <span></span>
                    <span></span>
                </button>
            </div>

            <nav class="mobile-nav" aria-label="<?php esc_attr_e('Mobile Menu', 'roommate-mobile-theme'); ?>">
                <div class="mobile-menu-actions">
                    <div class="mobile-header-dropdown" data-header-dropdown>
                        <button class="mobile-menu-btn mobile-dropdown-toggle" type="button" aria-expanded="false" aria-controls="mobile-browse-dropdown">
                            <span>Browse</span>
                            <span class="header-dropdown__chevron" aria-hidden="true"></span>
                        </button>
                        <div id="mobile-browse-dropdown" class="mobile-dropdown-menu" hidden>
                            <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">Browse Roommates</a>
                            <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>">Browse Rooms</a>
                        </div>
                    </div>

                    <div class="mobile-header-dropdown" data-header-dropdown>
                        <button class="mobile-menu-btn mobile-dropdown-toggle" type="button" aria-expanded="false" aria-controls="mobile-post-dropdown">
                            <span>Post</span>
                            <span class="header-dropdown__chevron" aria-hidden="true"></span>
                        </button>
                        <div id="mobile-post-dropdown" class="mobile-dropdown-menu" hidden>
                            <a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>">Post a Roommate</a>
                            <a href="<?php echo esc_url(home_url('/post-a-room/')); ?>">Post a Room</a>
                        </div>
                    </div>

                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn <?php echo $is_dashboard_active ? 'btn-primary' : 'btn-outline'; ?> mobile-menu-btn">
                            Dashboard
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline mobile-menu-btn">
                            Login / Signup
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>
