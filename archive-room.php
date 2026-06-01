<?php
/**
 * Archive Template: Room
 */

defined('ABSPATH') || exit;

get_header();

function rmt_archive_get_meta($post_id, $key, $default = '') {
    $value = get_post_meta($post_id, $key, true);
    return $value !== '' ? $value : $default;
}

function rmt_archive_terms_text($post_id, $taxonomy) {
    $terms = get_the_terms($post_id, $taxonomy);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    return implode(', ', wp_list_pluck($terms, 'name'));
}

function rmt_archive_format_date($date) {
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return $date;
    }

    return date_i18n('M j, Y', $timestamp);
}

$q               = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$rent_min_q      = sanitize_text_field(wp_unslash($_GET['rent_min'] ?? ''));
$rent_max_q      = sanitize_text_field(wp_unslash($_GET['rent_max'] ?? ''));
$available_q     = sanitize_text_field(wp_unslash($_GET['available_from'] ?? ''));
$location_q      = absint($_GET['location_area'] ?? 0);
$room_type_q     = absint($_GET['room_type'] ?? 0);
$sort_q          = sanitize_text_field(wp_unslash($_GET['sort'] ?? 'newest'));

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

$location_terms_all = get_terms([
    'taxonomy'   => 'location_area',
    'hide_empty' => false,
]);

$room_type_terms_all = get_terms([
    'taxonomy'   => 'room_type',
    'hide_empty' => false,
]);

$meta_query = [
    'relation' => 'AND',
];

if ($rent_min_q !== '') {
    $meta_query[] = [
        'key'     => '_rent',
        'value'   => absint($rent_min_q),
        'compare' => '>=',
        'type'    => 'NUMERIC',
    ];
}

if ($rent_max_q !== '') {
    $meta_query[] = [
        'key'     => '_rent',
        'value'   => absint($rent_max_q),
        'compare' => '<=',
        'type'    => 'NUMERIC',
    ];
}

if ($available_q !== '') {
    $meta_query[] = [
        'key'     => '_available_date',
        'value'   => $available_q,
        'compare' => '>=',
        'type'    => 'DATE',
    ];
}

$tax_query = [
    'relation' => 'AND',
];

if ($location_q) {
    $tax_query[] = [
        'taxonomy' => 'location_area',
        'field'    => 'term_id',
        'terms'    => [$location_q],
    ];
}

if ($room_type_q) {
    $tax_query[] = [
        'taxonomy' => 'room_type',
        'field'    => 'term_id',
        'terms'    => [$room_type_q],
    ];
}

$order = $sort_q === 'oldest' ? 'ASC' : 'DESC';

$room_query_args = [
    'post_type'      => 'room',
    'post_status'    => 'publish',
    'posts_per_page' => 9,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => $order,
];

if ($q !== '') {
    $room_query_args['s'] = $q;
}

if (count($meta_query) > 1) {
    $room_query_args['meta_query'] = $meta_query;
}

if (count($tax_query) > 1) {
    $room_query_args['tax_query'] = $tax_query;
}

$room_query = new WP_Query($room_query_args);
?>

<main id="primary" class="site-main archive-page archive-room">

    <section class="archive-hero">
        <div class="container">
            <div class="section-heading">
                <h1>
                    <?php esc_html_e('Browse Rooms', 'roommate-mobile-theme'); ?>
                </h1>

                <p>
                    <?php esc_html_e('Find available rooms in Bangkok and connect with people looking for a roommate.', 'roommate-mobile-theme'); ?>
                </p>
            </div>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container">
            <form method="get" class="filter-form" action="<?php echo esc_url(get_post_type_archive_link('room')); ?>">
                <div class="filter-grid">

                    <div class="filter-group" style="grid-column:1/-1;">
                        <label for="q">
                            <?php esc_html_e('Search', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="search"
                            id="q"
                            name="q"
                            placeholder="<?php esc_attr_e('Search title, description, area…', 'roommate-mobile-theme'); ?>"
                            value="<?php echo esc_attr($q); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="rent_min">
                            <?php esc_html_e('Rent Min', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="number"
                            id="rent_min"
                            name="rent_min"
                            min="0"
                            value="<?php echo esc_attr($rent_min_q); ?>"
                            placeholder="<?php esc_attr_e('e.g. 5000', 'roommate-mobile-theme'); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="rent_max">
                            <?php esc_html_e('Rent Max', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="number"
                            id="rent_max"
                            name="rent_max"
                            min="0"
                            value="<?php echo esc_attr($rent_max_q); ?>"
                            placeholder="<?php esc_attr_e('e.g. 15000', 'roommate-mobile-theme'); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="available_from">
                            <?php esc_html_e('Available From', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="date"
                            id="available_from"
                            name="available_from"
                            value="<?php echo esc_attr($available_q); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="location_area">
                            <?php esc_html_e('Location Area', 'roommate-mobile-theme'); ?>
                        </label>

                        <select id="location_area" name="location_area">
                            <option value="">
                                <?php esc_html_e('— Any —', 'roommate-mobile-theme'); ?>
                            </option>

                            <?php if (!is_wp_error($location_terms_all)) : ?>
                                <?php foreach ($location_terms_all as $term) : ?>
                                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($location_q, $term->term_id); ?>>
                                        <?php echo esc_html($term->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="room_type">
                            <?php esc_html_e('Property Type', 'roommate-mobile-theme'); ?>
                        </label>

                        <select id="room_type" name="room_type">
                            <option value="">
                                <?php esc_html_e('— Any —', 'roommate-mobile-theme'); ?>
                            </option>

                            <?php if (!is_wp_error($room_type_terms_all)) : ?>
                                <?php foreach ($room_type_terms_all as $term) : ?>
                                    <option value="<?php echo esc_attr($term->term_id); ?>" <?php selected($room_type_q, $term->term_id); ?>>
                                        <?php echo esc_html($term->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sort">
                            <?php esc_html_e('Sort', 'roommate-mobile-theme'); ?>
                        </label>

                        <select id="sort" name="sort">
                            <option value="newest" <?php selected($sort_q, 'newest'); ?>>
                                <?php esc_html_e('Newest', 'roommate-mobile-theme'); ?>
                            </option>
                            <option value="oldest" <?php selected($sort_q, 'oldest'); ?>>
                                <?php esc_html_e('Oldest', 'roommate-mobile-theme'); ?>
                            </option>
                        </select>
                    </div>

                    <div class="filter-actions" style="grid-column:1/-1;">
                        <button type="submit" class="btn btn-primary">
                            <?php esc_html_e('Search Rooms', 'roommate-mobile-theme'); ?>
                        </button>

                        <a class="btn btn-secondary" href="<?php echo esc_url(get_post_type_archive_link('room')); ?>">
                            <?php esc_html_e('Reset', 'roommate-mobile-theme'); ?>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <section class="archive-listings">
        <div class="container">

            <?php if ($room_query->have_posts()) : ?>
                <div class="listing-grid">

                    <?php while ($room_query->have_posts()) : $room_query->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();

                        $rent             = rmt_archive_get_meta($post_id, '_rent');
                        $available_date   = rmt_archive_get_meta($post_id, '_available_date');
                        $property_type    = rmt_archive_get_meta($post_id, '_property_type');
                        $address          = rmt_archive_get_meta($post_id, '_address');
                        $nearby_landmark  = rmt_archive_get_meta($post_id, '_nearby_landmark');

                        $room_type_text   = rmt_archive_terms_text($post_id, 'room_type');
                        $location_text    = rmt_archive_terms_text($post_id, 'location_area');

                        $display_property = $room_type_text ? $room_type_text : $property_type;
                        $display_location = $location_text ? $location_text : $address;
                        ?>

                        <article <?php post_class('listing-card'); ?>>

                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <div class="listing-card__image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('large'); ?>
                                    <?php else : ?>
                                        <div class="listing-card__image listing-card__image--placeholder">
                                            <?php esc_html_e('No Image', 'roommate-mobile-theme'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>

                            <div class="listing-card__content">

                                <h2 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="listing-card__chips">
                                    <?php if ($display_property) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Property Type:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_property); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_location) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Location:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_location); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($rent) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Rent Per Person:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(number_format_i18n((int) $rent)); ?>
                                            <?php esc_html_e(' THB/month', 'roommate-mobile-theme'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Available From:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_archive_format_date($available_date)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($nearby_landmark) : ?>
                                    <p class="listing-card__address">
                                        <?php esc_html_e('Nearby: ', 'roommate-mobile-theme'); ?>
                                        <?php echo esc_html($nearby_landmark); ?>
                                    </p>
                                <?php endif; ?>

                                <a href="<?php the_permalink(); ?>" class="btn btn-secondary">
                                    <?php esc_html_e('View Room', 'roommate-mobile-theme'); ?>
                                </a>

                            </div>
                        </article>

                    <?php endwhile; ?>

                </div>

                <div class="pagination-wrap">
                    <?php
                    echo paginate_links([
                        'total'     => $room_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => esc_html__('Previous', 'roommate-mobile-theme'),
                        'next_text' => esc_html__('Next', 'roommate-mobile-theme'),
                    ]);
                    ?>
                </div>

                <?php wp_reset_postdata(); ?>

            <?php else : ?>

                <div class="empty-state">
                    <h2><?php esc_html_e('No rooms found', 'roommate-mobile-theme'); ?></h2>
                    <p><?php esc_html_e('Try changing your filters or check back later.', 'roommate-mobile-theme'); ?></p>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>
