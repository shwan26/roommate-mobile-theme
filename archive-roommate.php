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

$q               = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$gender_q        = sanitize_text_field(wp_unslash($_GET['gender'] ?? ''));
$rent_min_q      = sanitize_text_field(wp_unslash($_GET['rent_min'] ?? ($_GET['budget_min'] ?? '')));
$move_in_month_q = sanitize_text_field(wp_unslash($_GET['move_in_month'] ?? ''));
$available_q     = sanitize_text_field(wp_unslash($_GET['available_from'] ?? ($_GET['move_in'] ?? '')));

if ($move_in_month_q === '' && preg_match('/^\d{4}-\d{2}/', $available_q)) {
    $move_in_month_q = substr($available_q, 0, 7);
}

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));

$meta_query = [
    'relation' => 'AND',
];

if ($rent_min_q !== '') {
    $meta_query[] = [
        'relation' => 'OR',
        [
            'key'     => '_budget_max',
            'value'   => absint($rent_min_q),
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ],
        [
            'key'     => '_budget_min',
            'value'   => absint($rent_min_q),
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ],
    ];
}

if ($gender_q !== '') {
    $meta_query[] = [
        'key'     => '_gender',
        'value'   => $gender_q,
        'compare' => '=',
    ];
}

if ($move_in_month_q !== '' && preg_match('/^\d{4}-\d{2}$/', $move_in_month_q)) {
    $month_start = $move_in_month_q . '-01';

    $meta_query[] = [
        'key'     => '_move_in_date',
        'value'   => $month_start,
        'compare' => '>=',
        'type'    => 'DATE',
    ];
} elseif ($available_q !== '') {
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
                        <label for="gender">
                            <?php esc_html_e('Gender', 'roommate-mobile-theme'); ?>
                        </label>

                        <select id="gender" name="gender">
                            <option value=""><?php esc_html_e('Any gender', 'roommate-mobile-theme'); ?></option>
                            <?php foreach (['Male', 'Female', 'Non-binary', 'Prefer not to say'] as $gender_option) : ?>
                                <option value="<?php echo esc_attr($gender_option); ?>" <?php selected($gender_q, $gender_option); ?>>
                                    <?php echo esc_html($gender_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="rent_min">
                            <?php esc_html_e('Min Budget (THB)', 'roommate-mobile-theme'); ?>
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
                        <label for="move_in_month">
                            <?php esc_html_e('Move-in Month', 'roommate-mobile-theme'); ?>
                        </label>

                        <input
                            type="month"
                            id="move_in_month"
                            name="move_in_month"
                            value="<?php echo esc_attr($move_in_month_q); ?>"
                        >
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php esc_html_e('Search', 'roommate-mobile-theme'); ?>
                        </button>
                        <a class="btn btn-outline" href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">
                            <?php esc_html_e('Clear filters', 'roommate-mobile-theme'); ?>
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
                        $age             = rmt_archive_get_meta($post_id, '_age');
                        $gender          = rmt_archive_get_meta($post_id, '_gender');
                        $budget_min      = rmt_archive_get_meta($post_id, '_budget_min');
                        $move_in_date    = rmt_archive_get_meta($post_id, '_move_in_date');
                        $preferred_area  = rmt_archive_get_meta($post_id, '_preferred_area_text');

                        $location_text   = rmt_archive_terms_text($post_id, 'location_area');

                        $display_area    = $location_text ? $location_text : $preferred_area;
                        $display_budget  = $budget_min ? number_format_i18n((int) $budget_min) . ' THB' : '';
                        $display_name    = $nickname ? $nickname : get_the_title();
                        $title_parts     = array_filter([$display_name, $age]);
                        $gender_key      = strtolower(trim($gender));
                        $gender_symbol   = '';

                        if ($gender_key === 'male') {
                            $gender_symbol = '♂';
                        } elseif ($gender_key === 'female') {
                            $gender_symbol = '♀';
                        } elseif ($gender_key === 'non-binary') {
                            $gender_symbol = '⚧';
                        }
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
                                        <?php echo esc_html(implode(', ', $title_parts)); ?>
                                    </a>
                                </h2>

                                <div class="listing-card__details">
                                    <?php if ($move_in_date) : ?>
                                        <span>
                                            <?php echo esc_html(sprintf(__('Starting from %s', 'roommate-mobile-theme'), rmt_archive_format_date($move_in_date))); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_budget) : ?>
                                        <span>
                                            <?php echo esc_html(sprintf(__('Min budget: %s', 'roommate-mobile-theme'), $display_budget)); ?>
                                        </span>
                                    <?php endif; ?>

                                </div>

                                <div class="listing-card__mini-meta">
                                    <?php if ($display_area || $gender_symbol) : ?>
                                        <span class="listing-card__area-gender">
                                            <?php echo esc_html(implode(' ', array_filter([$display_area, $gender_symbol]))); ?>
                                        </span>
                                    <?php endif; ?>

                                    <span class="listing-card__post-id">
                                        <?php echo esc_html('#' . $post_id); ?>
                                    </span>
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
