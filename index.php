<?php
/**
 * Roommate Mobile Theme — index.php
 *
 * The main template file. Acts as:
 *   - Homepage (shows hero + tabbed room/seeker listings)
 *   - Fallback for any template WordPress can't find
 *
 * Template hierarchy means WordPress will prefer single-room_listing.php,
 * archive-room_listing.php, etc. when those files exist. This file handles
 * the front page and any unmatched requests.
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
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ============================================================
     SITE HEADER
     ============================================================ -->
<header class="site-header" role="banner">
    <div class="header-inner container">

        <?php rmt_site_logo(); ?>

        <!-- Hamburger (mobile) -->
        <button class="nav-toggle" id="navToggle" aria-controls="primaryNav"
                aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'roommate-mobile-theme' ); ?>">
            <span></span><span></span><span></span>
        </button>

        <!-- Primary navigation -->
        <nav class="primary-nav" id="primaryNav" aria-label="<?php esc_attr_e( 'Primary', 'roommate-mobile-theme' ); ?>">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => 'rmt_default_nav',
            ] );
            ?>
            <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="nav-cta">
                + <?php esc_html_e( 'Post Listing', 'roommate-mobile-theme' ); ?>
            </a>
        </nav>

    </div><!-- .header-inner -->
</header><!-- .site-header -->

<?php
// ============================================================
// FRONT PAGE — show hero + listings
// ============================================================
if ( is_front_page() || is_home() ) :
?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero" aria-label="<?php esc_attr_e( 'Find your perfect roommate', 'roommate-mobile-theme' ); ?>">
    <div class="container hero-inner">

        <div class="hero-badge">
            🏠 <?php esc_html_e( 'Thailand\'s Roommate Finder', 'roommate-mobile-theme' ); ?>
        </div>

        <h1 class="hero-title">
            <?php esc_html_e( 'Find Your Perfect', 'roommate-mobile-theme' ); ?>
            <span class="accent"><?php esc_html_e( 'Roommate', 'roommate-mobile-theme' ); ?></span>
            <br><?php esc_html_e( 'or Room Today', 'roommate-mobile-theme' ); ?>
        </h1>

        <p class="hero-subtitle">
            <?php esc_html_e( 'Whether you have a room to offer or you\'re looking for one, Roomies connects you with the right people fast.', 'roommate-mobile-theme' ); ?>
        </p>

        <!-- Search bar -->
        <form class="hero-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <input type="hidden" name="post_type" value="room_listing">

            <select name="neighborhood" aria-label="<?php esc_attr_e( 'Neighborhood', 'roommate-mobile-theme' ); ?>">
                <option value=""><?php esc_html_e( 'All Areas', 'roommate-mobile-theme' ); ?></option>
                <?php
                $neighborhoods = get_terms( [ 'taxonomy' => 'neighborhood', 'hide_empty' => false ] );
                foreach ( (array) $neighborhoods as $nb ) {
                    if ( ! is_wp_error( $nb ) ) {
                        echo '<option value="' . esc_attr( $nb->slug ) . '">' . esc_html( $nb->name ) . '</option>';
                    }
                }
                ?>
            </select>

            <input type="search" name="s" placeholder="<?php esc_attr_e( 'Search by area, price…', 'roommate-mobile-theme' ); ?>"
                   value="<?php echo get_search_query(); ?>" aria-label="<?php esc_attr_e( 'Search listings', 'roommate-mobile-theme' ); ?>">

            <button type="submit" class="hero-search-btn">
                🔍 <?php esc_html_e( 'Search', 'roommate-mobile-theme' ); ?>
            </button>
        </form>

        <!-- Stats -->
        <div class="hero-stats" aria-label="<?php esc_attr_e( 'Site statistics', 'roommate-mobile-theme' ); ?>">
            <?php
            $rooms_count   = wp_count_posts( 'room_listing' )->publish ?? 0;
            $seekers_count = wp_count_posts( 'room_seeker' )->publish  ?? 0;
            ?>
            <div class="stat-item">
                <span class="stat-number"><?php echo absint( $rooms_count ); ?>+</span>
                <span class="stat-label"><?php esc_html_e( 'Rooms Listed', 'roommate-mobile-theme' ); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo absint( $seekers_count ); ?>+</span>
                <span class="stat-label"><?php esc_html_e( 'Seekers Active', 'roommate-mobile-theme' ); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number">Free</span>
                <span class="stat-label"><?php esc_html_e( 'To Browse', 'roommate-mobile-theme' ); ?></span>
            </div>
        </div>

    </div><!-- .container -->
</section><!-- .hero -->


<!-- ============================================================
     LISTINGS SECTION — TABBED: Room Available / Seeking Room
     ============================================================ -->
<section class="listing-tabs-section" id="listings" aria-label="<?php esc_attr_e( 'Listings', 'roommate-mobile-theme' ); ?>">
    <div class="container">

        <!-- Section header -->
        <div class="section-header text-center">
            <h2><?php esc_html_e( 'Browse Listings', 'roommate-mobile-theme' ); ?></h2>
            <div class="divider" style="margin-inline:auto;"></div>
            <p class="section-subtitle" style="margin-inline:auto;">
                <?php esc_html_e( 'Find available rooms or connect with people looking for a place to stay.', 'roommate-mobile-theme' ); ?>
            </p>
        </div>

        <!-- Tab selector -->
        <div class="tabs-header" role="tablist" aria-label="<?php esc_attr_e( 'Listing type', 'roommate-mobile-theme' ); ?>">
            <button class="tab-btn active" role="tab" id="tab-rooms" aria-selected="true"
                    aria-controls="panel-rooms" data-tab="rooms">
                <span class="tab-icon">🏠</span>
                <?php esc_html_e( 'Room Available', 'roommate-mobile-theme' ); ?>
                <span class="tab-count"><?php echo absint( wp_count_posts( 'room_listing' )->publish ?? 0 ); ?></span>
            </button>
            <button class="tab-btn" role="tab" id="tab-seekers" aria-selected="false"
                    aria-controls="panel-seekers" data-tab="seekers">
                <span class="tab-icon">🧑‍🤝‍🧑</span>
                <?php esc_html_e( 'Seeking a Room', 'roommate-mobile-theme' ); ?>
                <span class="tab-count"><?php echo absint( wp_count_posts( 'room_seeker' )->publish ?? 0 ); ?></span>
            </button>
        </div>


        <!-- ---- PANEL: ROOMS AVAILABLE ---- -->
        <div class="tab-panel active" id="panel-rooms" role="tabpanel" aria-labelledby="tab-rooms">

            <!-- Filter bar -->
            <div class="filter-bar">
                <span class="filter-label"><?php esc_html_e( 'Filter:', 'roommate-mobile-theme' ); ?></span>
                <select class="filter-select" id="filterNeighborhood" aria-label="<?php esc_attr_e( 'Filter by area', 'roommate-mobile-theme' ); ?>">
                    <option value=""><?php esc_html_e( 'All Areas', 'roommate-mobile-theme' ); ?></option>
                    <?php
                    $nbs = get_terms( [ 'taxonomy' => 'neighborhood', 'hide_empty' => true ] );
                    foreach ( (array) $nbs as $nb ) {
                        if ( ! is_wp_error( $nb ) ) {
                            echo '<option value="' . esc_attr( $nb->slug ) . '">' . esc_html( $nb->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <select class="filter-select" id="filterRoomType" aria-label="<?php esc_attr_e( 'Room type', 'roommate-mobile-theme' ); ?>">
                    <option value=""><?php esc_html_e( 'Any Type', 'roommate-mobile-theme' ); ?></option>
                    <option value="private"><?php esc_html_e( 'Private Room', 'roommate-mobile-theme' ); ?></option>
                    <option value="shared"><?php esc_html_e( 'Shared Room', 'roommate-mobile-theme' ); ?></option>
                    <option value="studio"><?php esc_html_e( 'Studio', 'roommate-mobile-theme' ); ?></option>
                    <option value="condo"><?php esc_html_e( 'Full Condo', 'roommate-mobile-theme' ); ?></option>
                </select>
                <select class="filter-select" id="filterStatus" aria-label="<?php esc_attr_e( 'Listing status', 'roommate-mobile-theme' ); ?>">
                    <option value=""><?php esc_html_e( 'Any Status', 'roommate-mobile-theme' ); ?></option>
                    <option value="available"><?php esc_html_e( 'Available', 'roommate-mobile-theme' ); ?></option>
                    <option value="pending"><?php esc_html_e( 'Pending', 'roommate-mobile-theme' ); ?></option>
                </select>
            </div>

            <!-- Listings grid -->
            <?php
            $rooms_query = new WP_Query( [
                'post_type'      => 'room_listing',
                'posts_per_page' => 9,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );

            if ( $rooms_query->have_posts() ) :
            ?>
            <div class="listings-grid" id="roomsGrid">
                <?php while ( $rooms_query->have_posts() ) : $rooms_query->the_post();
                    $pid     = get_the_ID();
                    $price   = get_post_meta( $pid, 'rmt_price', true );
                    $status  = get_post_meta( $pid, 'rmt_status', true ) ?: 'available';
                    $rtype   = get_post_meta( $pid, 'rmt_room_type', true );
                    $address = get_post_meta( $pid, 'rmt_address', true );
                    $rtypes  = [ 'private' => 'Private Room', 'shared' => 'Shared Room', 'studio' => 'Studio', 'condo' => 'Full Condo' ];
                ?>
                <article class="listing-card" data-status="<?php echo esc_attr( $status ); ?>"
                         data-type="<?php echo esc_attr( $rtype ); ?>">

                    <!-- Image -->
                    <a href="<?php the_permalink(); ?>" class="card-image-wrap" tabindex="-1" aria-hidden="true">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'listing-card', [ 'class' => '', 'alt' => get_the_title() ] ); ?>
                        <?php else : ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:3rem;color:var(--color-border);">🏠</div>
                        <?php endif; ?>
                        <?php rmt_status_badge( $status ); ?>
                        <span class="card-type has-room"><?php esc_html_e( 'Room Available', 'roommate-mobile-theme' ); ?></span>
                    </a>

                    <!-- Body -->
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <?php if ( $address ) : ?>
                        <p class="card-location">
                            📍 <?php echo esc_html( wp_trim_words( $address, 6 ) ); ?>
                        </p>
                        <?php endif; ?>

                        <div class="card-price">
                            <?php
                            if ( $price ) {
                                echo '฿' . number_format( (float) $price );
                                echo '<span> /mo</span>';
                            } else {
                                esc_html_e( 'Price on request', 'roommate-mobile-theme' );
                            }
                            ?>
                        </div>

                        <?php if ( $rtype && isset( $rtypes[ $rtype ] ) ) : ?>
                        <div class="card-tags">
                            <span class="tag">🛏 <?php echo esc_html( $rtypes[ $rtype ] ); ?></span>
                            <?php
                            $bedrooms = get_post_meta( $pid, 'rmt_bedrooms', true );
                            if ( $bedrooms ) echo '<span class="tag">' . absint( $bedrooms ) . ' bed</span>';
                            $bathrooms = get_post_meta( $pid, 'rmt_bathrooms', true );
                            if ( $bathrooms ) echo '<span class="tag">' . absint( $bathrooms ) . ' bath</span>';
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php rmt_amenity_tags( $pid ); ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer">
                        <span class="card-posted">
                            <?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago' ); ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                            <?php esc_html_e( 'View Room', 'roommate-mobile-theme' ); ?>
                        </a>
                    </div>

                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div><!-- #roomsGrid -->

            <?php if ( $rooms_query->max_num_pages > 1 ) : ?>
            <div style="text-align:center;margin-top:var(--space-8);">
                <button class="btn btn-secondary" id="loadMoreRooms"
                        data-page="2" data-post-type="room_listing" data-max="<?php echo absint( $rooms_query->max_num_pages ); ?>">
                    <?php esc_html_e( 'Load More Rooms', 'roommate-mobile-theme' ); ?>
                </button>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <div style="text-align:center;padding:var(--space-12) 0;">
                <p style="font-size:3rem;margin-bottom:var(--space-4);">🏠</p>
                <h3><?php esc_html_e( 'No room listings yet', 'roommate-mobile-theme' ); ?></h3>
                <p class="text-muted"><?php esc_html_e( 'Be the first to post a room!', 'roommate-mobile-theme' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="btn btn-primary" style="margin-top:var(--space-4);">
                    <?php esc_html_e( 'Post a Room', 'roommate-mobile-theme' ); ?>
                </a>
            </div>
            <?php endif; ?>

        </div><!-- #panel-rooms -->


        <!-- ---- PANEL: SEEKING A ROOM ---- -->
        <div class="tab-panel" id="panel-seekers" role="tabpanel" aria-labelledby="tab-seekers">

            <!-- Filter bar for seekers -->
            <div class="filter-bar">
                <span class="filter-label"><?php esc_html_e( 'Filter:', 'roommate-mobile-theme' ); ?></span>
                <select class="filter-select" id="filterSeekerArea" aria-label="<?php esc_attr_e( 'Preferred area', 'roommate-mobile-theme' ); ?>">
                    <option value=""><?php esc_html_e( 'All Areas', 'roommate-mobile-theme' ); ?></option>
                    <?php
                    $nbs2 = get_terms( [ 'taxonomy' => 'neighborhood', 'hide_empty' => true ] );
                    foreach ( (array) $nbs2 as $nb ) {
                        if ( ! is_wp_error( $nb ) ) {
                            echo '<option value="' . esc_attr( $nb->slug ) . '">' . esc_html( $nb->name ) . '</option>';
                        }
                    }
                    ?>
                </select>
                <select class="filter-select" id="filterBudget" aria-label="<?php esc_attr_e( 'Budget range', 'roommate-mobile-theme' ); ?>">
                    <option value=""><?php esc_html_e( 'Any Budget', 'roommate-mobile-theme' ); ?></option>
                    <option value="0-5000"><?php esc_html_e( 'Under ฿5,000', 'roommate-mobile-theme' ); ?></option>
                    <option value="5000-10000"><?php esc_html_e( '฿5,000 – ฿10,000', 'roommate-mobile-theme' ); ?></option>
                    <option value="10000-20000"><?php esc_html_e( '฿10,000 – ฿20,000', 'roommate-mobile-theme' ); ?></option>
                    <option value="20000+"><?php esc_html_e( '฿20,000+', 'roommate-mobile-theme' ); ?></option>
                </select>
            </div>

            <!-- Seekers grid -->
            <?php
            $seekers_query = new WP_Query( [
                'post_type'      => 'room_seeker',
                'posts_per_page' => 9,
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );

            if ( $seekers_query->have_posts() ) :
            ?>
            <div class="listings-grid" id="seekersGrid">
                <?php while ( $seekers_query->have_posts() ) : $seekers_query->the_post();
                    $pid        = get_the_ID();
                    $budget_min = get_post_meta( $pid, 'rmt_budget_min', true );
                    $budget_max = get_post_meta( $pid, 'rmt_budget_max', true );
                    $move_in    = get_post_meta( $pid, 'rmt_move_in_date', true );
                    $occupation = get_post_meta( $pid, 'rmt_occupation', true );
                    $pref_area  = get_post_meta( $pid, 'rmt_preferred_area', true );
                ?>
                <article class="listing-card" style="text-align:center;">

                    <!-- Avatar -->
                    <a href="<?php the_permalink(); ?>" class="card-image-wrap" tabindex="-1" aria-hidden="true">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'avatar-medium', [ 'class' => 'profile-avatar', 'alt' => get_the_title() ] ); ?>
                        <?php else : ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:4rem;background:var(--color-surface-alt);">🧑</div>
                        <?php endif; ?>
                        <span class="card-type needs-room"><?php esc_html_e( 'Needs Room', 'roommate-mobile-theme' ); ?></span>
                    </a>

                    <!-- Body -->
                    <div class="card-body">
                        <h3 class="card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <?php if ( $occupation ) : ?>
                        <p class="text-muted" style="font-size:var(--text-sm);">💼 <?php echo esc_html( $occupation ); ?></p>
                        <?php endif; ?>

                        <?php if ( $budget_min || $budget_max ) : ?>
                        <div class="card-price">
                            ฿<?php echo number_format( (float) $budget_min ); ?>
                            <?php if ( $budget_max ) echo ' – ฿' . number_format( (float) $budget_max ); ?>
                            <span> /mo</span>
                        </div>
                        <?php endif; ?>

                        <div class="card-tags" style="justify-content:center;">
                            <?php if ( $pref_area ) : ?>
                                <span class="tag">📍 <?php echo esc_html( $pref_area ); ?></span>
                            <?php endif; ?>
                            <?php if ( $move_in ) : ?>
                                <span class="tag">📅 <?php echo esc_html( date_i18n( 'M j', strtotime( $move_in ) ) ); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php rmt_lifestyle_tags( $pid ); ?>

                        <?php the_excerpt(); ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer">
                        <span class="card-posted">
                            <?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago' ); ?>
                        </span>
                        <a href="<?php the_permalink(); ?>" class="btn btn-primary btn-sm">
                            <?php esc_html_e( 'View Profile', 'roommate-mobile-theme' ); ?>
                        </a>
                    </div>

                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div><!-- #seekersGrid -->

            <?php if ( $seekers_query->max_num_pages > 1 ) : ?>
            <div style="text-align:center;margin-top:var(--space-8);">
                <button class="btn btn-secondary" id="loadMoreSeekers"
                        data-page="2" data-post-type="room_seeker" data-max="<?php echo absint( $seekers_query->max_num_pages ); ?>">
                    <?php esc_html_e( 'Load More Profiles', 'roommate-mobile-theme' ); ?>
                </button>
            </div>
            <?php endif; ?>

            <?php else : ?>
            <div style="text-align:center;padding:var(--space-12) 0;">
                <p style="font-size:3rem;margin-bottom:var(--space-4);">🧑‍🤝‍🧑</p>
                <h3><?php esc_html_e( 'No seeker profiles yet', 'roommate-mobile-theme' ); ?></h3>
                <p class="text-muted"><?php esc_html_e( 'Be the first to post your profile!', 'roommate-mobile-theme' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/post-profile' ) ); ?>" class="btn btn-primary" style="margin-top:var(--space-4);">
                    <?php esc_html_e( 'Post Your Profile', 'roommate-mobile-theme' ); ?>
                </a>
            </div>
            <?php endif; ?>

        </div><!-- #panel-seekers -->

    </div><!-- .container -->
</section><!-- .listing-tabs-section -->


<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section class="how-it-works" aria-label="<?php esc_attr_e( 'How it works', 'roommate-mobile-theme' ); ?>">
    <div class="container">
        <div class="section-header text-center">
            <h2><?php esc_html_e( 'How It Works', 'roommate-mobile-theme' ); ?></h2>
            <div class="divider" style="margin-inline:auto;"></div>
            <p class="section-subtitle" style="margin-inline:auto;">
                <?php esc_html_e( 'Find or fill a room in four easy steps.', 'roommate-mobile-theme' ); ?>
            </p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3 class="step-title"><?php esc_html_e( 'Create Your Listing', 'roommate-mobile-theme' ); ?></h3>
                <p class="step-desc"><?php esc_html_e( 'Post your room with photos, price, and details — or create a seeker profile showing who you are.', 'roommate-mobile-theme' ); ?></p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3 class="step-title"><?php esc_html_e( 'Browse Matches', 'roommate-mobile-theme' ); ?></h3>
                <p class="step-desc"><?php esc_html_e( 'Filter by area, budget, and lifestyle to find the best match quickly from your phone.', 'roommate-mobile-theme' ); ?></p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3 class="step-title"><?php esc_html_e( 'Connect Directly', 'roommate-mobile-theme' ); ?></h3>
                <p class="step-desc"><?php esc_html_e( 'Message via LINE, WhatsApp, or the built-in contact form — no middleman, no fees.', 'roommate-mobile-theme' ); ?></p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3 class="step-title"><?php esc_html_e( 'Move In!', 'roommate-mobile-theme' ); ?></h3>
                <p class="step-desc"><?php esc_html_e( 'Agree on the terms, sign your contract, and settle in to your new home with the right roommate.', 'roommate-mobile-theme' ); ?></p>
            </div>
        </div>
    </div><!-- .container -->
</section><!-- .how-it-works -->


<!-- ============================================================
     CTA BANNER
     ============================================================ -->
<section class="cta-banner" aria-label="<?php esc_attr_e( 'Call to action', 'roommate-mobile-theme' ); ?>">
    <div class="container">
        <h2><?php esc_html_e( 'Ready to Find Your Roomie?', 'roommate-mobile-theme' ); ?></h2>
        <p><?php esc_html_e( 'List your room or post your profile — it\'s free and takes less than 3 minutes.', 'roommate-mobile-theme' ); ?></p>
        <div class="cta-actions">
            <a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="btn btn-dark">
                🏠 <?php esc_html_e( 'Post a Room', 'roommate-mobile-theme' ); ?>
            </a>
            <a href="<?php echo esc_url( home_url( '/post-profile' ) ); ?>" class="btn btn-secondary" style="background:#fff;border-color:#fff;color:var(--color-complementary);">
                🧑 <?php esc_html_e( 'Post My Profile', 'roommate-mobile-theme' ); ?>
            </a>
        </div>
    </div>
</section>

<?php
// ============================================================
// FALLBACK — non-front-page with no specific template
// ============================================================
elseif ( have_posts() ) :
?>
<main class="container" style="padding-top:var(--space-8);padding-bottom:var(--space-16);">
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="section-title"><?php the_title(); ?></h1>
            <div class="divider"></div>
            <div class="entry-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php else : ?>
<main class="container" style="padding-top:var(--space-8);text-align:center;">
    <h1><?php esc_html_e( 'Nothing found', 'roommate-mobile-theme' ); ?></h1>
    <p class="text-muted"><?php esc_html_e( 'Try searching or browsing our listings.', 'roommate-mobile-theme' ); ?></p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary" style="margin-top:var(--space-4);">
        <?php esc_html_e( '← Back to Home', 'roommate-mobile-theme' ); ?>
    </a>
</main>
<?php endif; ?>


<!-- ============================================================
     SITE FOOTER
     ============================================================ -->
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <div class="footer-grid">

            <!-- Brand column -->
            <div class="footer-brand">
                <?php rmt_site_logo(); ?>
                <p class="footer-tagline">
                    <?php esc_html_e( 'Thailand\'s friendliest roommate finder. Find your space, find your people.', 'roommate-mobile-theme' ); ?>
                </p>
            </div>

            <!-- Quick links -->
            <div>
                <h4 class="footer-heading"><?php esc_html_e( 'Browse', 'roommate-mobile-theme' ); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'room_listing' ) ); ?>"><?php esc_html_e( 'Rooms Available', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'room_seeker' ) ); ?>"><?php esc_html_e( 'Find a Roommate', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>"><?php esc_html_e( 'Post a Room', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/post-profile' ) ); ?>"><?php esc_html_e( 'Post My Profile', 'roommate-mobile-theme' ); ?></a></li>
                </ul>
            </div>

            <!-- Info links -->
            <div>
                <h4 class="footer-heading"><?php esc_html_e( 'Info', 'roommate-mobile-theme' ); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/how-it-works' ) ); ?>"><?php esc_html_e( 'How It Works', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/safety-tips' ) ); ?>"><?php esc_html_e( 'Safety Tips', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'roommate-mobile-theme' ); ?></a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="footer-heading"><?php esc_html_e( 'Legal', 'roommate-mobile-theme' ); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'roommate-mobile-theme' ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/terms' ) ); ?>"><?php esc_html_e( 'Terms of Use', 'roommate-mobile-theme' ); ?></a></li>
                </ul>
            </div>

        </div><!-- .footer-grid -->

        <div class="footer-bottom">
            <span>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'roommate-mobile-theme' ); ?></span>
            <span><?php esc_html_e( 'Made with 💚 in Thailand', 'roommate-mobile-theme' ); ?></span>
        </div>

    </div><!-- .container -->
</footer>


<!-- ============================================================
     MOBILE BOTTOM NAVIGATION (app-style)
     ============================================================ -->
<nav class="mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'roommate-mobile-theme' ); ?>">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="bottom-nav-item active" aria-label="<?php esc_attr_e( 'Home', 'roommate-mobile-theme' ); ?>">
        <span class="nav-icon">🏠</span>
        <span><?php esc_html_e( 'Home', 'roommate-mobile-theme' ); ?></span>
    </a>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'room_listing' ) ); ?>" class="bottom-nav-item" aria-label="<?php esc_attr_e( 'Rooms', 'roommate-mobile-theme' ); ?>">
        <span class="nav-icon">🛏</span>
        <span><?php esc_html_e( 'Rooms', 'roommate-mobile-theme' ); ?></span>
    </a>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'room_seeker' ) ); ?>" class="bottom-nav-item" aria-label="<?php esc_attr_e( 'Seekers', 'roommate-mobile-theme' ); ?>">
        <span class="nav-icon">🧑‍🤝‍🧑</span>
        <span><?php esc_html_e( 'Seekers', 'roommate-mobile-theme' ); ?></span>
    </a>
    <a href="<?php echo esc_url( home_url( '/saved' ) ); ?>" class="bottom-nav-item" aria-label="<?php esc_attr_e( 'Saved', 'roommate-mobile-theme' ); ?>">
        <span class="nav-icon">💚</span>
        <span><?php esc_html_e( 'Saved', 'roommate-mobile-theme' ); ?></span>
    </a>
    <a href="<?php echo esc_url( wp_login_url() ); ?>" class="bottom-nav-item" aria-label="<?php esc_attr_e( 'Profile', 'roommate-mobile-theme' ); ?>">
        <span class="nav-icon">👤</span>
        <span><?php esc_html_e( 'Profile', 'roommate-mobile-theme' ); ?></span>
    </a>
</nav>

<!-- FAB: quick post button -->
<a href="<?php echo esc_url( home_url( '/post-listing' ) ); ?>" class="fab-post"
   aria-label="<?php esc_attr_e( 'Post a listing', 'roommate-mobile-theme' ); ?>" title="Post a listing">
    ＋
</a>


<!-- ============================================================
     INLINE JAVASCRIPT — tabs, nav toggle, load more
     (falls back gracefully if /assets/js/main.js is not yet present)
     ============================================================ -->
<script>
(function () {
    'use strict';

    /* ---- Mobile nav toggle ---- */
    var toggle = document.getElementById('navToggle');
    var nav    = document.getElementById('primaryNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.classList.toggle('is-active', open);
            toggle.setAttribute('aria-expanded', open);
        });
    }

    /* ---- Tab switching ---- */
    var tabBtns   = document.querySelectorAll('.tab-btn');
    var tabPanels = document.querySelectorAll('.tab-panel');

    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = this.dataset.tab;

            tabBtns.forEach(function (b) {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            tabPanels.forEach(function (p) { p.classList.remove('active'); });

            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            var panel = document.getElementById('panel-' + target);
            if (panel) panel.classList.add('active');
        });
    });

    /* ---- AJAX load more ---- */
    function initLoadMore(btnId, gridId) {
        var btn = document.getElementById(btnId);
        if (!btn) return;

        btn.addEventListener('click', function () {
            var page     = parseInt(this.dataset.page, 10);
            var postType = this.dataset.postType;
            var maxPages = parseInt(this.dataset.max, 10);
            var grid     = document.getElementById(gridId);

            if (!grid || page > maxPages) { btn.remove(); return; }

            btn.textContent = 'Loading…';
            btn.disabled = true;

            var fd = new FormData();
            fd.append('action',    'rmt_load_more');
            fd.append('nonce',     (typeof rmtData !== 'undefined' ? rmtData.nonce : ''));
            fd.append('paged',     page);
            fd.append('post_type', postType);

            fetch((typeof rmtData !== 'undefined' ? rmtData.ajaxUrl : '/wp-admin/admin-ajax.php'), {
                method: 'POST',
                body: fd,
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.data.html) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = data.data.html;
                    // append only article elements
                    tmp.querySelectorAll('.listing-card').forEach(function (card) {
                        grid.appendChild(card);
                    });
                }
                if (!data.success || !data.data.has_more || page + 1 > maxPages) {
                    btn.remove();
                } else {
                    btn.dataset.page = page + 1;
                    btn.textContent = (postType === 'room_seeker') ? 'Load More Profiles' : 'Load More Rooms';
                    btn.disabled = false;
                }
            })
            .catch(function () {
                btn.textContent = 'Error – try again';
                btn.disabled = false;
            });
        });
    }

    initLoadMore('loadMoreRooms',    'roomsGrid');
    initLoadMore('loadMoreSeekers',  'seekersGrid');

    /* ---- Highlight active bottom nav item ---- */
    var currentPath = window.location.pathname;
    document.querySelectorAll('.bottom-nav-item').forEach(function (item) {
        if (item.getAttribute('href') === currentPath) {
            document.querySelectorAll('.bottom-nav-item').forEach(function (i) { i.classList.remove('active'); });
            item.classList.add('active');
        }
    });

})();
</script>

<?php wp_footer(); ?>
</body>
</html>

<?php
/* ============================================================
   Fallback nav (renders if no menu is assigned to 'primary')
   ============================================================ */
function rmt_default_nav() {
    $links = [
        home_url( '/' )                                         => __( 'Home',          'roommate-mobile-theme' ),
        get_post_type_archive_link( 'room_listing' )            => __( 'Rooms',         'roommate-mobile-theme' ),
        get_post_type_archive_link( 'room_seeker' )             => __( 'Find Roommate', 'roommate-mobile-theme' ),
        home_url( '/how-it-works' )                             => __( 'How It Works',  'roommate-mobile-theme' ),
        home_url( '/contact' )                                  => __( 'Contact',       'roommate-mobile-theme' ),
    ];
    echo '<ul>';
    foreach ( $links as $url => $label ) {
        echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
    }
    echo '</ul>';
}