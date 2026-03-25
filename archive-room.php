<?php
/**
 * Archive Template: Show Room
 */

defined('ABSPATH') || exit;

get_header();
?>

<main id="primary" class="site-main archive-page archive-show-room">
    <section class="archive-hero">
        <div class="container">
            <div class="archive-hero__content">
                <span class="archive-badge">Room</span>
                <h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
                <p class="archive-description">
                    Browse listings from people who already have a room and are looking for a compatible roommate.
                </p>
            </div>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container">
            <form method="get" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="location_area">Location</label>
                        <?php
                        $locations = get_terms(array(
                            'taxonomy'   => 'location_area',
                            'hide_empty' => true,
                        ));
                        ?>
                        <select name="location_area" id="location_area">
                            <option value="">All Locations</option>
                            <?php if (!is_wp_error($locations) && !empty($locations)) : ?>
                                <?php foreach ($locations as $location) : ?>
                                    <option value="<?php echo esc_attr($location->slug); ?>" <?php selected(isset($_GET['location_area']) ? $_GET['location_area'] : '', $location->slug); ?>>
                                        <?php echo esc_html($location->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="room_type">Room Type</label>
                        <?php
                        $room_types = get_terms(array(
                            'taxonomy'   => 'room_type',
                            'hide_empty' => true,
                        ));
                        ?>
                        <select name="room_type" id="room_type">
                            <option value="">All Room Types</option>
                            <?php if (!is_wp_error($room_types) && !empty($room_types)) : ?>
                                <?php foreach ($room_types as $room_type) : ?>
                                    <option value="<?php echo esc_attr($room_type->slug); ?>" <?php selected(isset($_GET['room_type']) ? $_GET['room_type'] : '', $room_type->slug); ?>>
                                        <?php echo esc_html($room_type->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="max_rent">Max Rent</label>
                        <input
                            type="number"
                            name="max_rent"
                            id="max_rent"
                            placeholder="e.g. 12000"
                            value="<?php echo isset($_GET['max_rent']) ? esc_attr($_GET['max_rent']) : ''; ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="gender_preference">Gender Preference</label>
                        <input
                            type="text"
                            name="gender_preference"
                            id="gender_preference"
                            placeholder="e.g. Female only"
                            value="<?php echo isset($_GET['gender_preference']) ? esc_attr($_GET['gender_preference']) : ''; ?>"
                        >
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="archive-listings">
        <div class="container">
            <?php
            $paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

            $tax_query = array();
            $meta_query = array();

            if (!empty($_GET['location_area'])) {
                $tax_query[] = array(
                    'taxonomy' => 'location_area',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['location_area']),
                );
            }

            if (!empty($_GET['room_type'])) {
                $tax_query[] = array(
                    'taxonomy' => 'room_type',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($_GET['room_type']),
                );
            }

            if (!empty($_GET['max_rent'])) {
                $meta_query[] = array(
                    'key'     => '_rent',
                    'value'   => (float) $_GET['max_rent'],
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                );
            }

            if (!empty($_GET['gender_preference'])) {
                $meta_query[] = array(
                    'key'     => '_gender_preference',
                    'value'   => sanitize_text_field($_GET['gender_preference']),
                    'compare' => 'LIKE',
                );
            }

            $args = array(
                'post_type'      => 'room',
                'post_status'    => 'publish',
                'posts_per_page' => 9,
                'paged'          => $paged,
            );

            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }

            if (!empty($meta_query)) {
                $args['meta_query'] = $meta_query;
            }

            $show_room_query = new WP_Query($args);
            ?>

            <?php if ($show_room_query->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($show_room_query->have_posts()) : $show_room_query->the_post(); ?>
                        <?php
                        $rent               = rmt_get_meta(get_the_ID(), '_rent');
                        $available_date     = rmt_get_meta(get_the_ID(), '_available_date');
                        $address            = rmt_get_meta(get_the_ID(), '_address');
                        $nickname           = rmt_get_meta(get_the_ID(), '_nickname');
                        $occupation         = rmt_get_meta(get_the_ID(), '_occupation');
                        $cleanliness        = rmt_get_meta(get_the_ID(), '_cleanliness');
                        $sleep_schedule     = rmt_get_meta(get_the_ID(), '_sleep_schedule');
                        $pet_policy         = rmt_get_meta(get_the_ID(), '_pet_policy');
                        $gender_preference  = rmt_get_meta(get_the_ID(), '_gender_preference');

                        $location_terms = get_the_terms(get_the_ID(), 'location_area');
                        $room_type_terms = get_the_terms(get_the_ID(), 'room_type');
                        ?>
                        <article class="listing-card show-room-card">
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
                                    <div class="listing-card__price">
                                        <?php echo esc_html(rmt_format_price($rent)); ?>/month
                                    </div>

                                    <?php if (!empty($available_date)) : ?>
                                        <div class="listing-card__available">
                                            Available: <?php echo esc_html($available_date); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h2 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <?php if (!empty($address)) : ?>
                                    <p class="listing-card__address"><?php echo esc_html($address); ?></p>
                                <?php endif; ?>

                                <div class="listing-card__meta">
                                    <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                        <span class="listing-chip">
                                            <?php echo esc_html($location_terms[0]->name); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                                        <span class="listing-chip">
                                            <?php echo esc_html($room_type_terms[0]->name); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($gender_preference)) : ?>
                                        <span class="listing-chip">
                                            <?php echo esc_html($gender_preference); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="listing-card__person">
                                    <h3 class="listing-card__person-title">About the roommate</h3>

                                    <ul class="listing-card__person-info">
                                        <?php if (!empty($nickname)) : ?>
                                            <li><strong>Name:</strong> <?php echo esc_html($nickname); ?></li>
                                        <?php endif; ?>

                                        <?php if (!empty($occupation)) : ?>
                                            <li><strong>Occupation:</strong> <?php echo esc_html($occupation); ?></li>
                                        <?php endif; ?>

                                        <?php if (!empty($cleanliness)) : ?>
                                            <li><strong>Cleanliness:</strong> <?php echo esc_html($cleanliness); ?></li>
                                        <?php endif; ?>

                                        <?php if (!empty($sleep_schedule)) : ?>
                                            <li><strong>Sleep:</strong> <?php echo esc_html($sleep_schedule); ?></li>
                                        <?php endif; ?>

                                        <?php if (!empty($pet_policy)) : ?>
                                            <li><strong>Pets:</strong> <?php echo esc_html($pet_policy); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <div class="listing-card__excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <div class="listing-card__footer">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="pagination-wrap">
                    <?php
                    echo paginate_links(array(
                        'total'   => $show_room_query->max_num_pages,
                        'current' => $paged,
                    ));
                    ?>
                </div>

                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h2>No Show Room listings found</h2>
                    <p>Try changing your filters or check back later for new listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>