<?php
/**
 * Be Roomies — footer.php
 *
 * Called by get_footer() in every template.
 * Contains: main footer, mobile bottom nav bar, FAB button,
 *           back-to-top, scripts, wp_footer(), </body>, </html>.
 *
 * @package RoommateMobileTheme
 */

defined( 'ABSPATH' ) || exit;

// Counts used in footer stats
$rooms_count   = wp_count_posts( 'room_listing' )->publish ?? 0;
$seekers_count = wp_count_posts( 'room_seeker' )->publish  ?? 0;
?>

<!-- ============================================================
     NEWSLETTER / MINI CTA STRIP
     ============================================================ -->
<section class="footer-cta-strip" aria-label="<?php esc_attr_e( 'Newsletter', 'roommate-mobile-theme' ); ?>">
    <div class="container footer-cta-inner">
        <div class="footer-cta-text">
            <h3><?php esc_html_e( 'Get New Listings in Your Inbox', 'roommate-mobile-theme' ); ?></h3>
            <p><?php esc_html_e( 'Be first to know when a room opens up in your area.', 'roommate-mobile-theme' ); ?></p>
        </div>
        <?php if ( function_exists( 'mc4wp_form' ) ) : ?>
            <?php mc4wp_form(); ?>
        <?php else : ?>
        <form class="newsletter-form" aria-label="<?php esc_attr_e( 'Newsletter signup', 'roommate-mobile-theme' ); ?>">
            <input type="email"
                   name="email"
                   placeholder="<?php esc_attr_e( 'your@email.com', 'roommate-mobile-theme' ); ?>"
                   required
                   aria-label="<?php esc_attr_e( 'Email address', 'roommate-mobile-theme' ); ?>">
            <button type="submit" class="btn btn-primary">
                <?php esc_html_e( 'Notify Me', 'roommate-mobile-theme' ); ?> 🔔
            </button>
        </form>
        <?php endif; ?>
    </div>
</section>


<!-- ============================================================
     MAIN FOOTER
     ============================================================ -->
<footer class="site-footer" id="siteFooter" role="contentinfo">
    <div class="container">

        <!-- ── TOP ROW: Brand + Menus ── -->
        <div class="footer-top">

            <!-- Brand column -->
            <div class="footer-brand">
                <!-- Logo -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo" aria-label="Be Roomies – Home">
                    <span class="footer-logo-mark" aria-hidden="true">
                        <svg width="36" height="36" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="38" height="38" rx="9" fill="#89e219"/>
                            <path d="M7 20l7-7 7 7v8H7v-8z" fill="#ffffff" opacity="0.9"/>
                            <rect x="11" y="23" width="3" height="5" rx="1" fill="#4a1a8c"/>
                            <path d="M17 18l7-7 7 7v9H17v-9z" fill="#4a1a8c" opacity="0.95"/>
                            <rect x="21" y="22" width="3" height="5" rx="1" fill="#89e219"/>
                        </svg>
                    </span>
                    <span class="footer-wordmark">Be&nbsp;<span>Roomies</span></span>
                </a>

                <p class="footer-tagline">
                    <?php esc_html_e( 'Thailand\'s friendliest way to find a room or a roommate. Browse free, connect directly — no agent fees.', 'roommate-mobile-theme' ); ?>
                </p>

                <!-- Live stats pills -->
                <div class="footer-stats">
                    <span class="footer-stat-pill">
                        <span class="pill-dot"></span>
                        <?php printf(
                            esc_html__( '%d rooms listed', 'roommate-mobile-theme' ),
                            absint( $rooms_count )
                        ); ?>
                    </span>
                    <span class="footer-stat-pill">
                        <span class="pill-dot"></span>
                        <?php printf(
                            esc_html__( '%d active seekers', 'roommate-mobile-theme' ),
                            absint( $seekers_count )
                        ); ?>
                    </span>
                </div>

                <!-- Social icons -->
                <div class="footer-social" aria-label="<?php esc_attr_e( 'Social media', 'roommate-mobile-theme' ); ?>">
                    <a href="<?php echo esc_url( get_theme_mod( 'rmt_social_facebook', '#' ) ); ?>"
                       class="social-icon" target="_blank" rel="noopener noreferrer"
                       aria-label="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="<?php echo esc_url( get_theme_mod( 'rmt_social_instagram', '#' ) ); ?>"
                       class="social-icon" target="_blank" rel="noopener noreferrer"
                       aria-label="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="<?php echo esc_url( get_theme_mod( 'rmt_social_line', '#' ) ); ?>"
                       class="social-icon" target="_blank" rel="noopener noreferrer"
                       aria-label="LINE">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>
                    </a>
                    <a href="<?php echo esc_url( get_theme_mod( 'rmt_social_tiktok', '#' ) ); ?>"
                       class="social-icon" target="_blank" rel="noopener noreferrer"
                       aria-label="TikTok">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Link columns -->
            <div class="footer-links-grid">

                <!-- Browse -->
                <div class="footer-col">
                    <h4 class="footer-col-title"><?php esc_html_e( 'Browse', 'roommate-mobile-theme' ); ?></h4>
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer-links',
                        'container'      => false,
                        'menu_class'     => 'footer-nav-list',
                        'fallback_cb'    => false,
                    ] );

                    // Fallback if no menu assigned
                    if ( ! has_nav_menu( 'footer-links' ) ) :
                    ?>
                    <ul class="footer-nav-list">
                        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'room_listing' ) ); ?>">
                            🛏 <?php esc_html_e( 'Rooms Available', 'roommate-mobile-theme' ); ?>
                        </a></li>
                        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'room_seeker' ) ); ?>">
                            🧑‍🤝‍🧑 <?php esc_html_e( 'Find a Roommate', 'roommate-mobile-theme' ); ?>
                        </a></li>
                        <li><a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>">
                            ＋ <?php esc_html_e( 'Post a Room', 'roommate-mobile-theme' ); ?>
                        </a></li>
                        <li><a href="<?php echo esc_url( home_url( '/post-profile' ) ); ?>">
                            ＋ <?php esc_html_e( 'Post My Profile', 'roommate-mobile-theme' ); ?>
                        </a></li>
                    </ul>
                    <?php endif; ?>
                </div>

                <!-- Explore neighborhoods -->
                <div class="footer-col">
                    <h4 class="footer-col-title"><?php esc_html_e( 'Neighborhoods', 'roommate-mobile-theme' ); ?></h4>
                    <ul class="footer-nav-list">
                        <?php
                        $footer_nbs = get_terms( [
                            'taxonomy'   => 'neighborhood',
                            'hide_empty' => false,
                            'number'     => 6,
                        ] );
                        if ( ! empty( $footer_nbs ) && ! is_wp_error( $footer_nbs ) ) {
                            foreach ( $footer_nbs as $nb ) {
                                echo '<li><a href="' . esc_url( get_term_link( $nb ) ) . '">📍 ' . esc_html( $nb->name ) . '</a></li>';
                            }
                        } else {
                            // Sample fallback
                            $sample = [ 'Sukhumvit', 'Silom', 'Nimman (Chiang Mai)', 'Phuket Town', 'Ari', 'On Nut' ];
                            foreach ( $sample as $s ) {
                                echo '<li><a href="' . esc_url( home_url( '/?s=' . urlencode( $s ) ) ) . '">📍 ' . esc_html( $s ) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>

                <!-- Info -->
                <div class="footer-col">
                    <h4 class="footer-col-title"><?php esc_html_e( 'Info', 'roommate-mobile-theme' ); ?></h4>
                    <ul class="footer-nav-list">
                        <li><a href="<?php echo esc_url( home_url( '/how-it-works' ) ); ?>"><?php esc_html_e( 'How It Works', 'roommate-mobile-theme' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/safety-tips' ) ); ?>"><?php esc_html_e( 'Safety Tips', 'roommate-mobile-theme' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'roommate-mobile-theme' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/blog' ) ); ?>"><?php esc_html_e( 'Blog', 'roommate-mobile-theme' ); ?></a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'roommate-mobile-theme' ); ?></a></li>
                    </ul>
                </div>

            </div><!-- .footer-links-grid -->

        </div><!-- .footer-top -->


        <!-- ── DIVIDER ── -->
        <hr class="footer-divider">


        <!-- ── BOTTOM ROW: Legal + Credits ── -->
        <div class="footer-bottom">

            <div class="footer-legal-links">
                <a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'roommate-mobile-theme' ); ?></a>
                <span class="footer-dot" aria-hidden="true">·</span>
                <a href="<?php echo esc_url( home_url( '/terms' ) ); ?>"><?php esc_html_e( 'Terms of Use', 'roommate-mobile-theme' ); ?></a>
                <span class="footer-dot" aria-hidden="true">·</span>
                <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>"><?php esc_html_e( 'Sitemap', 'roommate-mobile-theme' ); ?></a>
            </div>

            <p class="footer-copyright">
                &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                <strong>Be Roomies</strong>.
                <?php esc_html_e( 'Made with', 'roommate-mobile-theme' ); ?>
                <span aria-label="love" style="color:var(--color-primary);">💚</span>
                <?php esc_html_e( 'in Thailand.', 'roommate-mobile-theme' ); ?>
            </p>

            <!-- Language / region selector (placeholder) -->
            <div class="footer-locale">
                <select class="locale-select" aria-label="<?php esc_attr_e( 'Language', 'roommate-mobile-theme' ); ?>">
                    <option value="en">🇬🇧 EN</option>
                    <option value="th">🇹🇭 TH</option>
                </select>
            </div>

        </div><!-- .footer-bottom -->

    </div><!-- .container -->
</footer><!-- .site-footer -->


<!-- ============================================================
     MOBILE BOTTOM NAV BAR  (app-style, hidden on desktop ≥768px)
     ============================================================ -->
<nav class="mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'roommate-mobile-theme' ); ?>">

    <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
       class="bottom-nav-item <?php echo is_front_page() ? 'active' : ''; ?>"
       aria-label="<?php esc_attr_e( 'Home', 'roommate-mobile-theme' ); ?>">
        <span class="bnav-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </span>
        <span class="bnav-label"><?php esc_html_e( 'Home', 'roommate-mobile-theme' ); ?></span>
    </a>

    <a href="<?php echo esc_url( get_post_type_archive_link( 'room_listing' ) ); ?>"
       class="bottom-nav-item <?php echo is_post_type_archive( 'room_listing' ) || ( is_singular( 'room_listing' ) ) ? 'active' : ''; ?>"
       aria-label="<?php esc_attr_e( 'Rooms', 'roommate-mobile-theme' ); ?>">
        <span class="bnav-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </span>
        <span class="bnav-label"><?php esc_html_e( 'Rooms', 'roommate-mobile-theme' ); ?></span>
        <?php
        $avail = new WP_Query( [ 'post_type' => 'room_listing', 'posts_per_page' => -1,
            'meta_query' => [ [ 'key' => 'rmt_status', 'value' => 'available' ] ] ] );
        if ( $avail->found_posts > 0 ) :
        ?>
        <span class="bnav-badge"><?php echo absint( $avail->found_posts ); ?></span>
        <?php endif; wp_reset_postdata(); ?>
    </a>

    <!-- Centre POST button -->
    <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>"
       class="bottom-nav-item bottom-nav-post"
       aria-label="<?php esc_attr_e( 'Post a listing', 'roommate-mobile-theme' ); ?>">
        <span class="bnav-post-icon" aria-hidden="true">＋</span>
        <span class="bnav-label"><?php esc_html_e( 'Post', 'roommate-mobile-theme' ); ?></span>
    </a>

    <a href="<?php echo esc_url( get_post_type_archive_link( 'room_seeker' ) ); ?>"
       class="bottom-nav-item <?php echo is_post_type_archive( 'room_seeker' ) || ( is_singular( 'room_seeker' ) ) ? 'active' : ''; ?>"
       aria-label="<?php esc_attr_e( 'Seekers', 'roommate-mobile-theme' ); ?>">
        <span class="bnav-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </span>
        <span class="bnav-label"><?php esc_html_e( 'Seekers', 'roommate-mobile-theme' ); ?></span>
    </a>

    <a href="<?php echo esc_url( is_user_logged_in() ? admin_url( 'profile.php' ) : wp_login_url() ); ?>"
       class="bottom-nav-item <?php echo is_account_page() ? 'active' : ''; ?>"
       aria-label="<?php esc_attr_e( 'My Account', 'roommate-mobile-theme' ); ?>">
        <span class="bnav-icon">
            <?php if ( is_user_logged_in() ) : ?>
                <?php echo get_avatar( get_current_user_id(), 22, '', '', [ 'class' => 'bnav-avatar' ] ); ?>
            <?php else : ?>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?php endif; ?>
        </span>
        <span class="bnav-label"><?php esc_html_e( is_user_logged_in() ? 'Me' : 'Login', 'roommate-mobile-theme' ); ?></span>
    </a>

</nav><!-- .mobile-bottom-nav -->


<!-- Back-to-top button -->
<button class="back-to-top" id="backToTop"
        aria-label="<?php esc_attr_e( 'Back to top', 'roommate-mobile-theme' ); ?>"
        title="<?php esc_attr_e( 'Back to top', 'roommate-mobile-theme' ); ?>">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
</button>


<!-- ============================================================
     FOOTER + HEADER CSS  (appended here so it loads after style.css)
     ============================================================ -->
<style>
/* ── ANNOUNCEMENT BAR ── */
.announcement-bar {
    background: var(--color-primary);
    color: var(--color-text);
    font-size: var(--text-sm);
    font-weight: 500;
    padding: var(--space-2) 0;
    position: relative;
    z-index: 101;
}
.announcement-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2);
    text-align: center;
}
.announcement-close {
    position: absolute;
    right: var(--space-4);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    color: var(--color-text);
    opacity: .6;
    line-height: 1;
    padding: 2px 6px;
}
.announcement-close:hover { opacity: 1; }

/* ── HEADER LOGO ── */
.site-logo {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    text-decoration: none;
    flex-shrink: 0;
}
.logo-mark { flex-shrink: 0; display: flex; }
.logo-wordmark {
    font-family: var(--font-heading);
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em;
    line-height: 1;
    white-space: nowrap;
}
.logo-wordmark .logo-accent { color: var(--color-primary); }

/* ── DESKTOP NAV LIST ── */
@media (min-width: 768px) {
    .primary-nav {
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }
    .primary-nav .nav-list {
        display: flex;
        align-items: center;
        gap: 2px;
    }
    .primary-nav .nav-list a {
        display: block;
        padding: var(--space-2) var(--space-3);
        border-radius: var(--radius-md);
        color: rgba(255,255,255,.8);
        font-size: var(--text-sm);
        font-weight: 500;
        transition: background var(--transition-fast), color var(--transition-fast);
    }
    .primary-nav .nav-list a:hover,
    .primary-nav .nav-list .current-menu-item a {
        background: rgba(137,226,25,.15);
        color: var(--color-primary);
    }
    .header-cta-group {
        display: flex;
        align-items: center;
        gap: var(--space-2);
    }
    .header-mobile-right { display: none; }
}

@media (max-width: 767px) {
    .primary-nav  { display: none; }
    .header-cta-group { display: none; }
}

/* ── MOBILE HAMBURGER ── */
.nav-toggle {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 26px;
    height: 18px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}
.nav-toggle .bar {
    display: block;
    height: 2px;
    background: var(--color-primary);
    border-radius: 2px;
    transition: transform var(--transition-base), opacity var(--transition-base), width var(--transition-base);
}
.nav-toggle.is-active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.nav-toggle.is-active .bar:nth-child(2) { opacity: 0; width: 0; }
.nav-toggle.is-active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

.header-mobile-right {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}
.mobile-login-icon {
    font-size: 1.3rem;
    text-decoration: none;
    line-height: 1;
}

/* ── MOBILE DRAWER ── */
.mobile-drawer {
    display: none;
    background: var(--color-comp-dark);
    border-top: 1px solid rgba(137,226,25,.15);
    overflow: hidden;
}
.mobile-drawer.is-open { display: block; }
.mobile-drawer-inner {
    padding: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
}
.drawer-nav-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
}
.drawer-nav-list a {
    display: block;
    padding: var(--space-3) var(--space-4);
    color: rgba(255,255,255,.8);
    font-weight: 500;
    border-radius: var(--radius-md);
    font-size: var(--text-base);
    transition: background var(--transition-fast), color var(--transition-fast);
}
.drawer-nav-list a:hover,
.drawer-nav-list .current-menu-item a {
    background: rgba(137,226,25,.12);
    color: var(--color-primary);
}
.drawer-cta-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}
.drawer-social {
    display: flex;
    gap: var(--space-4);
    padding-top: var(--space-3);
    border-top: 1px solid rgba(255,255,255,.08);
}
.social-link {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    color: rgba(255,255,255,.5);
    font-size: var(--text-xs);
    transition: color var(--transition-fast);
    text-decoration: none;
}
.social-link:hover { color: var(--color-primary); }

/* ── HEADER AVATAR ── */
.header-avatar { display: flex; align-items: center; }
.header-avatar-img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid var(--color-primary);
    object-fit: cover;
}

/* ── BREADCRUMB ── */
.breadcrumb-bar {
    background: var(--color-surface-alt);
    border-bottom: 1px solid var(--color-border);
    padding: var(--space-2) 0;
}
.breadcrumb-inner { overflow-x: auto; }
.breadcrumb-list {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
    white-space: nowrap;
    color: var(--color-text-muted);
}
.breadcrumb-list li + li::before {
    content: '›';
    color: var(--color-border);
    margin-right: var(--space-2);
}
.breadcrumb-list a {
    color: var(--color-complementary);
    font-weight: 500;
}
.breadcrumb-list a:hover { color: var(--color-comp-light); }
.breadcrumb-list [aria-current="page"] {
    color: var(--color-text);
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    display: inline-block;
}

/* ── FOOTER CTA STRIP ── */
.footer-cta-strip {
    background: linear-gradient(120deg, var(--color-comp-dark) 0%, var(--color-complementary) 100%);
    padding: var(--space-10) 0;
}
.footer-cta-inner {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
    align-items: flex-start;
}
@media (min-width: 640px) {
    .footer-cta-inner {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}
.footer-cta-text h3 {
    color: #fff;
    font-size: var(--text-xl);
    margin-bottom: var(--space-1);
}
.footer-cta-text p {
    color: rgba(255,255,255,.6);
    font-size: var(--text-sm);
    margin: 0;
}
.newsletter-form {
    display: flex;
    gap: var(--space-2);
    flex-wrap: wrap;
}
.newsletter-form input[type="email"] {
    flex: 1 1 200px;
    padding: var(--space-3) var(--space-4);
    border: 1.5px solid rgba(255,255,255,.2);
    border-radius: var(--radius-md);
    background: rgba(255,255,255,.1);
    color: #fff;
    font-family: var(--font-body);
    font-size: var(--text-sm);
    transition: border-color var(--transition-fast);
}
.newsletter-form input[type="email"]::placeholder { color: rgba(255,255,255,.4); }
.newsletter-form input[type="email"]:focus {
    outline: none;
    border-color: var(--color-primary);
    background: rgba(255,255,255,.15);
}

/* ── FOOTER MAIN ── */
.site-footer {
    background: #1a0a38;  /* deeper than comp-dark for strong contrast */
    color: rgba(255,255,255,.75);
    padding: var(--space-12) 0 var(--space-6);
}
.footer-top {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}
@media (min-width: 768px) {
    .footer-top { grid-template-columns: 280px 1fr; }
}

/* Footer logo */
.footer-logo {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    text-decoration: none;
    margin-bottom: var(--space-4);
}
.footer-logo-mark { flex-shrink: 0; display: flex; }
.footer-wordmark {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.02em;
}
.footer-wordmark span { color: var(--color-primary); }

.footer-tagline {
    font-size: var(--text-sm);
    color: rgba(255,255,255,.5);
    line-height: 1.7;
    margin-bottom: var(--space-5);
}

/* Stats pills */
.footer-stats {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
    margin-bottom: var(--space-5);
}
.footer-stat-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    background: rgba(137,226,25,.1);
    border: 1px solid rgba(137,226,25,.2);
    color: var(--color-primary);
    padding: 3px 12px;
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
}
.pill-dot {
    width: 7px;
    height: 7px;
    background: var(--color-primary);
    border-radius: 50%;
    animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .5; transform: scale(.7); }
}

/* Social icons */
.footer-social {
    display: flex;
    gap: var(--space-2);
}
.social-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,.55);
    transition: background var(--transition-fast), color var(--transition-fast), border-color var(--transition-fast);
    text-decoration: none;
}
.social-icon:hover {
    background: rgba(137,226,25,.15);
    border-color: rgba(137,226,25,.3);
    color: var(--color-primary);
}

/* Link columns */
.footer-links-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
}
@media (min-width: 640px) {
    .footer-links-grid { grid-template-columns: repeat(3, 1fr); }
}
.footer-col-title {
    font-family: var(--font-heading);
    font-size: var(--text-xs);
    font-weight: 700;
    color: var(--color-primary);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: var(--space-4);
}
.footer-nav-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}
.footer-nav-list a {
    color: rgba(255,255,255,.55);
    font-size: var(--text-sm);
    text-decoration: none;
    transition: color var(--transition-fast), padding-left var(--transition-fast);
    display: inline-block;
}
.footer-nav-list a:hover {
    color: var(--color-primary);
    padding-left: 4px;
}

/* Divider */
.footer-divider {
    border: none;
    border-top: 1px solid rgba(255,255,255,.08);
    margin: var(--space-8) 0 var(--space-6);
}

/* Bottom row */
.footer-bottom {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    align-items: center;
    text-align: center;
}
@media (min-width: 768px) {
    .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
    }
}
.footer-legal-links {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
}
.footer-legal-links a {
    color: rgba(255,255,255,.4);
    transition: color var(--transition-fast);
}
.footer-legal-links a:hover { color: var(--color-primary); }
.footer-dot { color: rgba(255,255,255,.2); }
.footer-copyright {
    font-size: var(--text-xs);
    color: rgba(255,255,255,.35);
    margin: 0;
}
.locale-select {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.5);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-md);
    font-size: var(--text-xs);
    cursor: pointer;
    -webkit-appearance: none;
}

/* ── MOBILE BOTTOM NAV ── */
.mobile-bottom-nav {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--color-surface);
    border-top: 1px solid var(--color-border);
    z-index: 200;
    padding-bottom: env(safe-area-inset-bottom);
    box-shadow: 0 -4px 24px rgba(74,26,140,.14);
}
body { padding-bottom: calc(60px + env(safe-area-inset-bottom)); }

.bottom-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 8px 4px 6px;
    text-decoration: none;
    color: var(--color-text-muted);
    font-size: 10px;
    font-weight: 500;
    position: relative;
    transition: color var(--transition-fast);
    -webkit-tap-highlight-color: transparent;
}
.bottom-nav-item.active { color: var(--color-complementary); }
.bottom-nav-item.active .bnav-icon svg { stroke: var(--color-complementary); }

/* Active indicator dot */
.bottom-nav-item.active::after {
    content: '';
    position: absolute;
    top: 4px;
    width: 4px;
    height: 4px;
    background: var(--color-primary);
    border-radius: 50%;
}

.bnav-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bnav-icon svg { stroke: var(--color-text-muted); transition: stroke var(--transition-fast); }
.bnav-label { line-height: 1; }

/* Badge on rooms tab */
.bnav-badge {
    position: absolute;
    top: 5px;
    right: calc(50% - 18px);
    background: var(--color-primary);
    color: var(--color-text);
    font-size: 9px;
    font-weight: 700;
    min-width: 16px;
    height: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    line-height: 1;
}

/* Centre POST button */
.bottom-nav-post {
    position: relative;
    top: -8px;
    flex: 0 0 60px;
}
.bnav-post-icon {
    width: 46px;
    height: 46px;
    background: var(--color-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 300;
    color: var(--color-text);
    box-shadow: 0 4px 16px rgba(137,226,25,.4);
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
    line-height: 1;
}
.bottom-nav-post:hover .bnav-post-icon,
.bottom-nav-post:active .bnav-post-icon {
    transform: scale(1.1);
    box-shadow: 0 6px 24px rgba(137,226,25,.55);
}
.bottom-nav-post .bnav-label { color: var(--color-complementary); font-weight: 700; }

/* Avatar in bottom nav */
.bnav-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid var(--color-primary);
}

@media (min-width: 768px) {
    .mobile-bottom-nav { display: none; }
    body { padding-bottom: 0; }
}

/* ── BACK TO TOP ── */
.back-to-top {
    position: fixed;
    bottom: calc(72px + env(safe-area-inset-bottom) + var(--space-3));
    right: var(--space-4);
    width: 42px;
    height: 42px;
    background: var(--color-complementary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    pointer-events: none;
    transform: translateY(8px);
    transition: opacity var(--transition-base), transform var(--transition-base);
    z-index: 198;
    box-shadow: var(--shadow-md);
}
.back-to-top.visible {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}
.back-to-top:hover {
    background: var(--color-comp-light);
}
@media (min-width: 768px) {
    .back-to-top { bottom: var(--space-6); }
}
</style>


<!-- ============================================================
     FOOTER / HEADER JAVASCRIPT
     ============================================================ -->
<script>
(function () {
    'use strict';

    /* ── Announcement bar dismiss ── */
    var closeBtn = document.querySelector('.announcement-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            var bar = document.querySelector('.announcement-bar');
            if (bar) bar.style.display = 'none';
        });
    }

    /* ── Mobile nav / drawer toggle ── */
    var toggle  = document.getElementById('navToggle');
    var drawer  = document.getElementById('mobileDrawer');
    var header  = document.getElementById('siteHeader');

    if (toggle && drawer) {
        toggle.addEventListener('click', function () {
            var open = drawer.classList.toggle('is-open');
            toggle.classList.toggle('is-active', open);
            toggle.setAttribute('aria-expanded', String(open));
            drawer.setAttribute('aria-hidden',   String(!open));
        });
        // Close drawer on outside click
        document.addEventListener('click', function (e) {
            if (drawer.classList.contains('is-open') &&
                !header.contains(e.target)) {
                drawer.classList.remove('is-open');
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                drawer.setAttribute('aria-hidden', 'true');
            }
        });
    }

    /* ── Sticky header shadow ── */
    if (header) {
        var lastScroll = 0;
        window.addEventListener('scroll', function () {
            var y = window.scrollY;
            header.style.boxShadow = y > 10
                ? '0 2px 20px rgba(74,26,140,.5)'
                : '0 2px 12px rgba(74,26,140,.4)';
            lastScroll = y;
        }, { passive: true });
    }

    /* ── Back to top ── */
    var btt = document.getElementById('backToTop');
    if (btt) {
        window.addEventListener('scroll', function () {
            btt.classList.toggle('visible', window.scrollY > 300);
        }, { passive: true });
        btt.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Highlight active bottom nav item based on current URL ── */
    var currentPath = window.location.pathname;
    document.querySelectorAll('.bottom-nav-item').forEach(function (item) {
        var href = item.getAttribute('href');
        if (href && href !== '/' && currentPath.startsWith(href)) {
            document.querySelectorAll('.bottom-nav-item').forEach(function (i) {
                i.classList.remove('active');
                i.querySelector('.bnav-icon svg') &&
                    (i.querySelector('.bnav-icon svg').style.stroke = '');
            });
            item.classList.add('active');
        }
    });

    /* ── Locale selector (placeholder — integrate with WPML / Polylang) ── */
    var localeSelect = document.querySelector('.locale-select');
    if (localeSelect) {
        localeSelect.addEventListener('change', function () {
            // Hook into WPML or Polylang URL switching here
            console.log('Locale changed to:', this.value);
        });
    }

})();
</script>

<?php wp_footer(); ?>
</body>
</html>
<!-- /footer.php -->