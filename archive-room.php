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

    return date_i18n('d/m/Y', $timestamp);
}

$q               = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$rent_min_q      = sanitize_text_field(wp_unslash($_GET['rent_min'] ?? ''));
$available_q     = sanitize_text_field(wp_unslash($_GET['available_from'] ?? ''));

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

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

if ($available_q !== '') {
    $meta_query[] = [
        'key'     => '_available_date',
        'value'   => $available_q,
        'compare' => '>=',
        'type'    => 'DATE',
    ];
}

$room_query_args = [
    'post_type'      => 'room',
    'post_status'    => 'publish',
    'posts_per_page' => 9,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ($q !== '') {
    $room_query_args['s'] = $q;
}

if (count($meta_query) > 1) {
    $room_query_args['meta_query'] = $meta_query;
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

                    <div class="filter-group filter-group--search">
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
                            <?php esc_html_e('Rent Price', 'roommate-mobile-theme'); ?>
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

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php esc_html_e('Search Rooms', 'roommate-mobile-theme'); ?>
                        </button>
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
                        $nearby_landmark  = rmt_archive_get_meta($post_id, '_nearby_landmark');

                        $room_type_text   = rmt_archive_terms_text($post_id, 'room_type');
                        $location_text    = rmt_archive_terms_text($post_id, 'location_area');

                        $display_property = $room_type_text ? $room_type_text : $property_type;
                        ?>

                        <article <?php post_class('listing-card'); ?>>

                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <div class="listing-card__image">
                                    <?php echo rmt_get_room_photo_html(get_the_ID(), 'large'); ?>
                                </div>
                            </a>

                            <div class="listing-card__content">

                                <h2 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="listing-card__details">
                                    <?php if ($display_property) : ?>
                                        <span>
                                            <?php echo esc_html($display_property); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($location_text) : ?>
                                        <span>
                                            <?php echo esc_html($location_text); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($rent) : ?>
                                        <span>
                                            <?php echo esc_html(number_format_i18n((int) $rent)); ?>
                                            <?php esc_html_e(' THB/person', 'roommate-mobile-theme'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <span>
                                            <?php echo esc_html(rmt_archive_format_date($available_date)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($nearby_landmark) : ?>
                                        <span>
                                            <?php echo esc_html($nearby_landmark); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="listing-card__post-id">#<?php echo esc_html($post_id); ?></p>

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
