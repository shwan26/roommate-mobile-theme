<?php
/**
 * Be Roomies — header.php
 *
 * Called by get_header() in every template.
 * Contains: <html>, <head>, site header bar, hero (front page only).
 *
 * @package RoommateMobileTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#4a1a8c">
    <meta name="description" content="<?php
        if ( is_singular() ) {
            echo esc_attr( wp_strip_all_tags( get_the_excerpt() ) );
        } else {
            bloginfo( 'description' );
        }
    ?>">

    <!-- Open Graph -->
    <meta property="og:site_name"  content="Be Roomies">
    <meta property="og:type"       content="website">
    <meta property="og:title"      content="<?php wp_title( '|', true, 'right' ); ?>Be Roomies">
    <meta property="og:url"        content="<?php echo esc_url( ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ); ?>">

    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ============================================================
     ANNOUNCEMENT BAR (optional — set via Customizer or remove)
     ============================================================ -->
<?php
$announcement = get_theme_mod( 'rmt_announcement_text', '' );
if ( $announcement ) :
?>
<div class="announcement-bar" role="status">
    <div class="container announcement-inner">
        <span class="announcement-icon">📢</span>
        <span class="announcement-text"><?php echo esc_html( $announcement ); ?></span>
        <button class="announcement-close" aria-label="<?php esc_attr_e( 'Dismiss', 'roommate-mobile-theme' ); ?>">✕</button>
    </div>
</div>
<?php endif; ?>


<!-- ============================================================
     SITE HEADER  —  sticky, purple, "Be Roomies" branding
     ============================================================ -->
<header class="site-header" id="siteHeader" role="banner">
    <div class="header-inner container">

        <!-- ── LOGO ── -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="Be Roomies – Home">
            <span class="logo-mark" aria-hidden="true">
                <!-- Two overlapping house shapes in SVG -->
                <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect width="38" height="38" rx="9" fill="#89e219"/>
                    <!-- House 1 (left, white) -->
                    <path d="M7 20l7-7 7 7v8H7v-8z" fill="#ffffff" opacity="0.9"/>
                    <rect x="11" y="23" width="3" height="5" rx="1" fill="#4a1a8c"/>
                    <!-- House 2 (right, overlapping) -->
                    <path d="M17 18l7-7 7 7v9H17v-9z" fill="#4a1a8c" opacity="0.95"/>
                    <rect x="21" y="22" width="3" height="5" rx="1" fill="#89e219"/>
                </svg>
            </span>
            <span class="logo-wordmark">
                Be&nbsp;<span class="logo-accent">Roomies</span>
            </span>
        </a>


        <!-- ── DESKTOP NAV ── -->
        <nav class="primary-nav" id="primaryNav"
             aria-label="<?php esc_attr_e( 'Primary Navigation', 'roommate-mobile-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location'  => 'primary',
                'container'       => false,
                'menu_class'      => 'nav-list',
                'fallback_cb'     => 'rmt_header_fallback_nav',
                'link_before'     => '<span>',
                'link_after'      => '</span>',
            ] );
            ?>

            <!-- Desktop CTA buttons -->
            <div class="header-cta-group">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'room_listing' ) ); ?>" class="btn btn-ghost btn-sm">
                    🛏 <?php esc_html_e( 'Rooms', 'roommate-mobile-theme' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="btn btn-primary btn-sm">
                    + <?php esc_html_e( 'Post Listing', 'roommate-mobile-theme' ); ?>
                </a>
                <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( admin_url() ); ?>" class="header-avatar"
                   aria-label="<?php esc_attr_e( 'My Account', 'roommate-mobile-theme' ); ?>">
                    <?php echo get_avatar( get_current_user_id(), 32, '', '', [ 'class' => 'header-avatar-img' ] ); ?>
                </a>
                <?php else : ?>
                <a href="<?php echo esc_url( wp_login_url() ); ?>" class="btn btn-ghost btn-sm header-login">
                    👤 <?php esc_html_e( 'Login', 'roommate-mobile-theme' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </nav>


        <!-- ── MOBILE ICONS (right of logo) ── -->
        <div class="header-mobile-right">
            <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( admin_url() ); ?>" class="header-avatar"
               aria-label="<?php esc_attr_e( 'My Account', 'roommate-mobile-theme' ); ?>">
                <?php echo get_avatar( get_current_user_id(), 30, '', '', [ 'class' => 'header-avatar-img' ] ); ?>
            </a>
            <?php else : ?>
            <a href="<?php echo esc_url( wp_login_url() ); ?>" class="mobile-login-icon"
               aria-label="<?php esc_attr_e( 'Login', 'roommate-mobile-theme' ); ?>">👤</a>
            <?php endif; ?>

            <!-- Hamburger -->
            <button class="nav-toggle" id="navToggle"
                    aria-controls="primaryNav"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e( 'Open menu', 'roommate-mobile-theme' ); ?>">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>

    </div><!-- .header-inner -->

    <!-- Mobile drawer (slides down from header) -->
    <div class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
        <div class="mobile-drawer-inner">

            <!-- Drawer nav links -->
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'drawer-nav-list',
                'fallback_cb'    => 'rmt_drawer_fallback_nav',
            ] );
            ?>

            <!-- Drawer CTAs -->
            <div class="drawer-cta-group">
                <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="btn btn-primary btn-full">
                    🏠 <?php esc_html_e( 'Post a Room', 'roommate-mobile-theme' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '/post-profile' ) ); ?>" class="btn btn-secondary btn-full">
                    🧑 <?php esc_html_e( 'Post My Profile', 'roommate-mobile-theme' ); ?>
                </a>
            </div>

            <!-- Social / contact quick links -->
            <div class="drawer-social">
                <a href="#" class="social-link" aria-label="LINE">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
                    LINE
                </a>
                <a href="#" class="social-link" aria-label="Facebook">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
                <a href="#" class="social-link" aria-label="Instagram">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    Instagram
                </a>
            </div>

        </div><!-- .mobile-drawer-inner -->
    </div><!-- .mobile-drawer -->

</header><!-- .site-header -->


<!-- ============================================================
     BREADCRUMB (shows on all pages except front)
     ============================================================ -->
<?php if ( ! is_front_page() ) : ?>
<nav class="breadcrumb-bar" aria-label="<?php esc_attr_e( 'Breadcrumb', 'roommate-mobile-theme' ); ?>">
    <div class="container breadcrumb-inner">
        <ol class="breadcrumb-list">
            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">🏠 <?php esc_html_e( 'Home', 'roommate-mobile-theme' ); ?></a></li>
            <?php
            if ( is_singular( 'room_listing' ) ) {
                echo '<li><a href="' . esc_url( get_post_type_archive_link( 'room_listing' ) ) . '">' . esc_html__( 'Rooms', 'roommate-mobile-theme' ) . '</a></li>';
                echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
            } elseif ( is_singular( 'room_seeker' ) ) {
                echo '<li><a href="' . esc_url( get_post_type_archive_link( 'room_seeker' ) ) . '">' . esc_html__( 'Seekers', 'roommate-mobile-theme' ) . '</a></li>';
                echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
            } elseif ( is_post_type_archive( 'room_listing' ) ) {
                echo '<li aria-current="page">' . esc_html__( 'Rooms Available', 'roommate-mobile-theme' ) . '</li>';
            } elseif ( is_post_type_archive( 'room_seeker' ) ) {
                echo '<li aria-current="page">' . esc_html__( 'Roommate Seekers', 'roommate-mobile-theme' ) . '</li>';
            } elseif ( is_page() ) {
                echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li>';
            } elseif ( is_search() ) {
                echo '<li aria-current="page">' . sprintf( esc_html__( 'Search: "%s"', 'roommate-mobile-theme' ), esc_html( get_search_query() ) ) . '</li>';
            }
            ?>
        </ol>
    </div>
</nav>
<?php endif; ?>
<!-- /header.php -->