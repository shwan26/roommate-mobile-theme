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

    return date_i18n('d/m/Y', $timestamp);
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
$rent_min_q    = sanitize_text_field(wp_unslash($_GET['rent_min'] ?? ($_GET['budget_min'] ?? '')));
$available_q   = sanitize_text_field(wp_unslash($_GET['available_from'] ?? ($_GET['move_in'] ?? '')));

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

$meta_query = [
    'relation' => 'AND',
];

if ($rent_min_q !== '') {
    $meta_query[] = [
        'key'     => '_budget_max',
        'value'   => absint($rent_min_q),
        'compare' => '>=',
        'type'    => 'NUMERIC',
    ];
}

if ($available_q !== '') {
    $meta_query[] = [
        'key'     => '_move_in_date',
        'value'   => $available_q,
        'compare' => '>=',
        'type'    => 'DATE',
    ];
}

$roommate_query_args = [
    'post_type'      => 'roommate',
    'post_status'    => 'publish',
    'posts_per_page' => 9,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ($q !== '') {
    $roommate_query_args['s'] = $q;
}

if (count($meta_query) > 1) {
    $roommate_query_args['meta_query'] = $meta_query;
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

                    <div class="filter-group filter-group--search">
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
                        <label for="rent_min">
                            <?php esc_html_e('Rent Price', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="number"
                            id="rent_min"
                            name="rent_min"
                            min="0"
                            value="<?php echo esc_attr($rent_min_q); ?>"
                            placeholder="<?php esc_attr_e('e.g. 6000', 'roommate-mobile-theme'); ?>"
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
                            <?php esc_html_e('Search Roommates', 'roommate-mobile-theme'); ?>
                        </button>
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
                        $move_in_date    = rmt_archive_get_meta($post_id, '_move_in_date');
                        $preferred_area  = rmt_archive_get_meta($post_id, '_preferred_area_text');

                        $location_text   = rmt_archive_terms_text($post_id, 'location_area');
                        $lifestyle_text  = rmt_archive_terms_text($post_id, 'lifestyle');

                        $display_area    = $location_text ? $location_text : $preferred_area;
                        $display_budget  = $budget_min ? number_format_i18n((int) $budget_min) . ' THB' : '';
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

                                <div class="listing-card__details">
                                    <?php if ($move_in_date) : ?>
                                        <span>
                                            <?php echo esc_html(rmt_archive_format_date($move_in_date)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_area) : ?>
                                        <span>
                                            <?php echo esc_html($display_area); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_budget) : ?>
                                        <span>
                                            <?php echo esc_html($display_budget); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($gender) : ?>
                                        <span>
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
