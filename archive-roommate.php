<?php
/**
 * Archive Template: Roommate
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

function rmt_archive_format_budget_range($min, $max) {
    $min = $min !== '' ? absint($min) : '';
    $max = $max !== '' ? absint($max) : '';

    if ($min && $max) {
        return number_format_i18n($min) . ' - ' . number_format_i18n($max) . ' THB/month';
    }

    if ($min) {
        return number_format_i18n($min) . '+ THB/month';
    }

    if ($max) {
        return 'Up to ' . number_format_i18n($max) . ' THB/month';
    }

    return '';
}

$q             = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$budget_min_q  = sanitize_text_field(wp_unslash($_GET['budget_min'] ?? ''));
$budget_max_q  = sanitize_text_field(wp_unslash($_GET['budget_max'] ?? ''));
$move_in_q     = sanitize_text_field(wp_unslash($_GET['move_in'] ?? ''));
$location_q    = absint($_GET['location_area'] ?? 0);
$gender_q      = sanitize_text_field(wp_unslash($_GET['gender'] ?? ''));
$sort_q        = sanitize_text_field(wp_unslash($_GET['sort'] ?? 'newest'));

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

$location_terms_all = get_terms([
    'taxonomy'   => 'location_area',
    'hide_empty' => false,
]);

$meta_query = [
    'relation' => 'AND',
];

if ($budget_min_q !== '') {
    $meta_query[] = [
        'key'     => '_budget_max',
        'value'   => absint($budget_min_q),
        'compare' => '>=',
        'type'    => 'NUMERIC',
    ];
}

if ($budget_max_q !== '') {
    $meta_query[] = [
        'key'     => '_budget_min',
        'value'   => absint($budget_max_q),
        'compare' => '<=',
        'type'    => 'NUMERIC',
    ];
}

if ($move_in_q !== '') {
    $meta_query[] = [
        'key'     => '_move_in_date',
        'value'   => $move_in_q,
        'compare' => '>=',
        'type'    => 'DATE',
    ];
}

if ($gender_q !== '') {
    $meta_query[] = [
        'key'     => '_gender',
        'value'   => $gender_q,
        'compare' => '=',
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

$order = $sort_q === 'oldest' ? 'ASC' : 'DESC';

$roommate_query_args = [
    'post_type'      => 'roommate',
    'post_status'    => 'publish',
    'posts_per_page' => 9,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => $order,
];

if ($q !== '') {
    $roommate_query_args['s'] = $q;
}

if (count($meta_query) > 1) {
    $roommate_query_args['meta_query'] = $meta_query;
}

if (count($tax_query) > 1) {
    $roommate_query_args['tax_query'] = $tax_query;
}

$roommate_query = new WP_Query($roommate_query_args);
?>

<main id="primary" class="site-main archive-page archive-roommate">

    <section class="archive-hero">
        <div class="container">
            <div class="section-heading">
                <h1>
                    <?php esc_html_e('Browse Roommates', 'roommate-mobile-theme'); ?>
                </h1>

                <p>
                    <?php esc_html_e('Browse profiles from people looking for a room, a roommate, or both.', 'roommate-mobile-theme'); ?>
                </p>
            </div>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container">
            <form method="get" class="filter-form" action="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">
                <div class="filter-grid">

                    <div class="filter-group" style="grid-column:1/-1;">
                        <label for="q">
                            <?php esc_html_e('Search', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="search"
                            id="q"
                            name="q"
                            placeholder="<?php esc_attr_e('Search name, title, bio…', 'roommate-mobile-theme'); ?>"
                            value="<?php echo esc_attr($q); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="budget_min">
                            <?php esc_html_e('Budget Min', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="number"
                            id="budget_min"
                            name="budget_min"
                            min="0"
                            value="<?php echo esc_attr($budget_min_q); ?>"
                            placeholder="<?php esc_attr_e('e.g. 6000', 'roommate-mobile-theme'); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="budget_max">
                            <?php esc_html_e('Budget Max', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="number"
                            id="budget_max"
                            name="budget_max"
                            min="0"
                            value="<?php echo esc_attr($budget_max_q); ?>"
                            placeholder="<?php esc_attr_e('e.g. 12000', 'roommate-mobile-theme'); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="move_in">
                            <?php esc_html_e('Move-in On/After', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="date"
                            id="move_in"
                            name="move_in"
                            value="<?php echo esc_attr($move_in_q); ?>"
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
                        <label for="gender">
                            <?php esc_html_e('Gender', 'roommate-mobile-theme'); ?>
                        </label>

                        <select id="gender" name="gender">
                            <option value="">
                                <?php esc_html_e('— Any —', 'roommate-mobile-theme'); ?>
                            </option>
                            <option value="Male" <?php selected($gender_q, 'Male'); ?>>
                                <?php esc_html_e('Male', 'roommate-mobile-theme'); ?>
                            </option>
                            <option value="Female" <?php selected($gender_q, 'Female'); ?>>
                                <?php esc_html_e('Female', 'roommate-mobile-theme'); ?>
                            </option>
                            <option value="Other" <?php selected($gender_q, 'Other'); ?>>
                                <?php esc_html_e('Other', 'roommate-mobile-theme'); ?>
                            </option>
                            <option value="Prefer not to say" <?php selected($gender_q, 'Prefer not to say'); ?>>
                                <?php esc_html_e('Prefer not to say', 'roommate-mobile-theme'); ?>
                            </option>
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
                            <?php esc_html_e('Search Roommates', 'roommate-mobile-theme'); ?>
                        </button>

                        <a class="btn btn-secondary" href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">
                            <?php esc_html_e('Reset', 'roommate-mobile-theme'); ?>
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <section class="archive-listings">
        <div class="container">

            <?php if ($roommate_query->have_posts()) : ?>
                <div class="listing-grid">

                    <?php while ($roommate_query->have_posts()) : $roommate_query->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();

                        $nickname        = rmt_archive_get_meta($post_id, '_nickname');
                        $gender          = rmt_archive_get_meta($post_id, '_gender');
                        $occupation      = rmt_archive_get_meta($post_id, '_occupation');
                        $budget_min      = rmt_archive_get_meta($post_id, '_budget_min');
                        $budget_max      = rmt_archive_get_meta($post_id, '_budget_max');
                        $move_in_date    = rmt_archive_get_meta($post_id, '_move_in_date');
                        $preferred_area  = rmt_archive_get_meta($post_id, '_preferred_area_text');

                        $location_text   = rmt_archive_terms_text($post_id, 'location_area');
                        $lifestyle_text  = rmt_archive_terms_text($post_id, 'lifestyle');

                        $display_area    = $location_text ? $location_text : $preferred_area;
                        $display_budget  = rmt_archive_format_budget_range($budget_min, $budget_max);
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
                                    <?php if ($move_in_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Move-in:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_archive_format_date($move_in_date)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_area) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Preferred Area:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_area); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_budget) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Budget:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_budget); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($gender) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Gender:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($gender); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>


                                <a href="<?php the_permalink(); ?>" class="btn btn-secondary">
                                    <?php esc_html_e('View Roommate', 'roommate-mobile-theme'); ?>
                                </a>

                            </div>
                        </article>
                    <?php endwhile; ?>

                </div>

                <div class="pagination-wrap">
                    <?php
                    echo paginate_links([
                        'total'     => $roommate_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => esc_html__('Previous', 'roommate-mobile-theme'),
                        'next_text' => esc_html__('Next', 'roommate-mobile-theme'),
                    ]);
                    ?>
                </div>

                <?php wp_reset_postdata(); ?>

            <?php else : ?>

                <div class="empty-state">
                    <h2><?php esc_html_e('No roommates found', 'roommate-mobile-theme'); ?></h2>
                    <p><?php esc_html_e('Try changing your filters or check back later.', 'roommate-mobile-theme'); ?></p>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>