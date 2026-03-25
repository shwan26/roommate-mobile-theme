<?php
/**
 * Front Page Template
 */

defined('ABSPATH') || exit;

get_header();

/**
 * Latest room listings
 */
$room_query = new WP_Query(array(
    'post_type'      => 'room',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
));

/**
 * Latest roommate profiles
 */
$roommate_query = new WP_Query(array(
    'post_type'      => 'roommate',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
));
?>

<main id="primary" class="site-main front-page">

    <section class="hero-section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <h1 class="hero-title">Find a Room. Find a Roommate.</h1>
                    <p class="hero-description">
                        Browse available rooms or connect with people who are still looking.
                        Match by budget, location, lifestyle, and personality.
                    </p>

                    <div class="hero-actions">
                        <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn btn-primary">
                            Roommates
                        </a>
                        <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn btn-secondary">
                            Rooms
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <strong><?php echo esc_html(wp_count_posts('room')->publish ?? 0); ?></strong>
                            <span>Rooms</span>
                        </div>
                        <div class="hero-stat">
                            <strong><?php echo esc_html(wp_count_posts('roommate')->publish ?? 0); ?></strong>
                            <span>Roommates</span>
                        </div>
                    </div>
                </div>

                <div class="hero-card">
                    <div class="hero-card__box">
                        <h2>Start your search</h2>
                        <p>Choose the option that fits your situation.</p>

                        <div class="hero-card__options">
                            <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="hero-option">
                                <h3>Roommates</h3>
                                <p>Show me people who are looking for a room or roommate.</p>
                            </a>

                            <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="hero-option">
                                <h3>Rooms</h3>
                                <p>Show me available rooms and people who already have a place.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="listing-section listing-section--room">
        <div class="container">
            <span class="section-badge">Rooms</span>
            <h2>Latest available rooms</h2>
            <p>Browse room listings posted by people who already have a place and are looking for a compatible roommate.</p>

            <?php if ($room_query->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($room_query->have_posts()) : $room_query->the_post(); ?>
                        <?php
                        $rent            = rmt_get_meta(get_the_ID(), '_rent');
                        $available_date  = rmt_get_meta(get_the_ID(), '_available_date');
                        $address         = rmt_get_meta(get_the_ID(), '_address');
                        $nickname        = rmt_get_meta(get_the_ID(), '_nickname');
                        $occupation      = rmt_get_meta(get_the_ID(), '_occupation');
                        $cleanliness     = rmt_get_meta(get_the_ID(), '_cleanliness');
                        $sleep_schedule  = rmt_get_meta(get_the_ID(), '_sleep_schedule');

                        $location_terms  = get_the_terms(get_the_ID(), 'location_area');
                        $room_type_terms = get_the_terms(get_the_ID(), 'room_type');
                        ?>
                        <article class="listing-card">
                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="listing-card__image listing-card__image--placeholder">
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                            </a>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <?php if ($rent) : ?>
                                        <div class="listing-card__price"><?php echo esc_html(rmt_format_price($rent)); ?>/month</div>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <div class="listing-card__available">Available: <?php echo esc_html($available_date); ?></div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($address) : ?>
                                    <p class="listing-card__address"><?php echo esc_html($address); ?></p>
                                <?php endif; ?>

                                <div class="listing-card__chips">
                                    <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($location_terms[0]->name); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($room_type_terms[0]->name); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="listing-card__person">
                                    <strong>About Current Roommate</strong>
                                    <ul>
                                        <?php if ($nickname) : ?><li>Name: <?php echo esc_html($nickname); ?></li><?php endif; ?>
                                        <?php if ($occupation) : ?><li>Occupation: <?php echo esc_html($occupation); ?></li><?php endif; ?>
                                        <?php if ($cleanliness) : ?><li>Cleanliness: <?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                        <?php if ($sleep_schedule) : ?><li>Sleep Schedule: <?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
                                    </ul>
                                </div>

                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>

                <div class="section-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn btn-secondary">
                        View All Rooms
                    </a>
                </div>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Room Listings Yet</h3>
                    <p>Be the first to post a room and find a roommate.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="listing-section listing-section--roommate">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">Roommates</span>
                <h2>Latest roommate profiles</h2>
                <p>Browse profiles from people who need a room or want to team up with a roommate.</p>
            </div>

            <?php if ($roommate_query->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($roommate_query->have_posts()) : $roommate_query->the_post(); ?>
                        <?php
                        $budget_min      = rmt_get_meta(get_the_ID(), '_budget_min');
                        $budget_max      = rmt_get_meta(get_the_ID(), '_budget_max');
                        $move_in_date    = rmt_get_meta(get_the_ID(), '_move_in_date');
                        $preferred_area  = rmt_get_meta(get_the_ID(), '_preferred_area_text');
                        $nickname        = rmt_get_meta(get_the_ID(), '_nickname');
                        $occupation      = rmt_get_meta(get_the_ID(), '_occupation');
                        $cleanliness     = rmt_get_meta(get_the_ID(), '_cleanliness');
                        $sleep_schedule  = rmt_get_meta(get_the_ID(), '_sleep_schedule');
                        $teamup_ok       = rmt_get_meta(get_the_ID(), '_teamup_ok');

                        $location_terms  = get_the_terms(get_the_ID(), 'location_area');
                        $room_type_terms = get_the_terms(get_the_ID(), 'room_type');
                        ?>
                        <article class="listing-card">
                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="listing-card__image listing-card__image--placeholder">
                                        <span>No Photo</span>
                                    </div>
                                <?php endif; ?>
                            </a>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <?php if ($budget_min || $budget_max) : ?>
                                        <div class="listing-card__price">
                                            Budget:
                                            <?php if ($budget_min) : ?>
                                                <?php echo esc_html(rmt_format_price($budget_min)); ?>
                                            <?php endif; ?>

                                            <?php if ($budget_min && $budget_max) : ?>
                                                -
                                            <?php endif; ?>

                                            <?php if ($budget_max) : ?>
                                                <?php echo esc_html(rmt_format_price($budget_max)); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($move_in_date) : ?>
                                        <div class="listing-card__available">Move-in: <?php echo esc_html($move_in_date); ?></div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($preferred_area) : ?>
                                    <p class="listing-card__address">Preferred Area: <?php echo esc_html($preferred_area); ?></p>
                                <?php endif; ?>

                                <div class="listing-card__chips">
                                    <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($location_terms[0]->name); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($room_type_terms[0]->name); ?></span>
                                    <?php endif; ?>

                                    <?php if ($teamup_ok) : ?>
                                        <span class="listing-chip"><?php echo esc_html($teamup_ok); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="listing-card__person">
                                    <strong>About This Person</strong>
                                    <ul>
                                        <?php if ($nickname) : ?><li>Name: <?php echo esc_html($nickname); ?></li><?php endif; ?>
                                        <?php if ($occupation) : ?><li>Occupation: <?php echo esc_html($occupation); ?></li><?php endif; ?>
                                        <?php if ($cleanliness) : ?><li>Cleanliness: <?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                        <?php if ($sleep_schedule) : ?><li>Sleep Schedule: <?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
                                    </ul>
                                </div>

                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Profile</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>

                <div class="section-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn btn-secondary">
                        View All Roommates
                    </a>
                </div>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Roommate Profiles Yet</h3>
                    <p>Be the first to create a profile and start matching.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="how-it-works-section">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">How It Works</span>
                <h2>Simple steps to get matched</h2>
                <p>Make it easy for users to understand the platform immediately.</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <span class="step-number">01</span>
                    <h3>Create your listing</h3>
                    <p>Choose whether you want to post a room or create a roommate profile, then fill in your details.</p>
                </div>

                <div class="step-card">
                    <span class="step-number">02</span>
                    <h3>Share your lifestyle</h3>
                    <p>Tell people about your habits, personality, budget, and preferred location.</p>
                </div>

                <div class="step-card">
                    <span class="step-number">03</span>
                    <h3>Browse and compare</h3>
                    <p>Explore rooms and people based on compatibility, not just price alone.</p>
                </div>

                <div class="step-card">
                    <span class="step-number">04</span>
                    <h3>Connect and move in</h3>
                    <p>Find the right match and take the next step toward living together.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-box">
                <div class="cta-content">
                    <h2>Ready to find your next roommate?</h2>
                    <p>
                        Whether you already have a room or are still searching,
                        start by creating your listing and sharing what matters to you.
                    </p>
                </div>

                <div class="cta-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn btn-primary">
                        Roommates
                    </a>
                    <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn btn-secondary">
                        Rooms
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
wp_reset_postdata();
get_footer();