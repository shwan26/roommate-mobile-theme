<?php
/**
 * Front Page Template
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('rmt_front_get_meta')) {
    function rmt_front_get_meta($post_id, $key, $default = '') {
        if (function_exists('rmt_get_meta')) {
            $value = rmt_get_meta($post_id, $key, $default);
        } else {
            $value = get_post_meta($post_id, $key, true);
        }

        return $value !== '' ? $value : $default;
    }
}

if (!function_exists('rmt_front_terms_text')) {
    function rmt_front_terms_text($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return implode(', ', wp_list_pluck($terms, 'name'));
    }
}

if (!function_exists('rmt_front_format_date')) {
    function rmt_front_format_date($date) {
        if (!$date) {
            return '';
        }

        $timestamp = strtotime($date);

        if (!$timestamp) {
            return $date;
        }

        return date_i18n('M j, Y', $timestamp);
    }
}

if (!function_exists('rmt_front_format_price')) {
    function rmt_front_format_price($price) {
        if (function_exists('rmt_format_price')) {
            return rmt_format_price($price);
        }

        if ($price === '' || $price === null) {
            return '';
        }

        return number_format_i18n((int) $price) . ' THB';
    }
}

if (!function_exists('rmt_front_format_budget_range')) {
    function rmt_front_format_budget_range($min, $max) {
        $min = $min !== '' ? absint($min) : '';
        $max = $max !== '' ? absint($max) : '';

        if ($min && $max) {
            return number_format_i18n($min) . ' - ' . number_format_i18n($max) . ' THB';
        }

        if ($min) {
            return number_format_i18n($min) . '+ THB';
        }

        if ($max) {
            return 'Up to ' . number_format_i18n($max) . ' THB';
        }

        return '';
    }
}

$room_count      = wp_count_posts('room')->publish ?? 0;
$roommate_count  = wp_count_posts('roommate')->publish ?? 0;
$total_listings  = (int) $room_count + (int) $roommate_count;

$total_users  = count_users();
$member_count = $total_users['total_users'] ?? 0;

$latest_rooms = new WP_Query(array(
    'post_type'           => 'room',
    'post_status'         => 'publish',
    'posts_per_page'      => 5,
    'ignore_sticky_posts' => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
));

$latest_roommates = new WP_Query(array(
    'post_type'           => 'roommate',
    'post_status'         => 'publish',
    'posts_per_page'      => 5,
    'ignore_sticky_posts' => true,
    'orderby'             => 'date',
    'order'               => 'DESC',
));
?>

<main id="primary" class="site-main front-page">

    <section class="rmt-hero">
        <div class="container">
            <h1 class="rmt-hero__headline">
                <span class="rmt-hero__headline-line rmt-hero__headline-line--main">
                    <?php esc_html_e('Find Your', 'roommate-mobile-theme'); ?>
                    <mark class="rmt-hero__highlight rmt-hero__highlight--awesome"><?php esc_html_e('Awesome', 'roommate-mobile-theme'); ?></mark>
                
                </span>
                <span class="rmt-hero__headline-line">
                    <mark class="rmt-hero__highlight rmt-hero__highlight--roommate-mobile"><?php esc_html_e('Roommate', 'roommate-mobile-theme'); ?></mark>
                    <?php esc_html_e('in Bangkok', 'roommate-mobile-theme'); ?>
                </span>
            </h1>

            <p class="rmt-hero__sub">
                <?php esc_html_e('Totally free to discover roommate profiles, and connect with people looking for shared living in Bangkok.', 'roommate-mobile-theme'); ?>
            </p>

            <div class="rmt-hero__grid">

                <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="rmt-card rmt-card--dark">
                    <span class="rmt-card__icon rmt-card__icon--dark">🙋</span>

                    <span class="rmt-card__meta">
                        <span class="rmt-card__label rmt-card__label--dark">
                            <?php esc_html_e('Need Roommate?', 'roommate-mobile-theme'); ?>
                        </span>

                        <span class="rmt-card__title rmt-card__title--dark">
                            <?php esc_html_e('Browse Roommates', 'roommate-mobile-theme'); ?>
                        </span>
                    </span>

                    <span class="rmt-card__desc rmt-card__desc--dark">
                        <?php esc_html_e('Find people looking for a room and compare budget, move-in date, lifestyle, and area.', 'roommate-mobile-theme'); ?>
                    </span>
                </a>
                
                <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="rmt-card rmt-card--light">
                    <span class="rmt-card__icon rmt-card__icon--light">🏠</span>

                    <span class="rmt-card__meta">
                        <span class="rmt-card__label rmt-card__label--light">
                            <?php esc_html_e('Need Room?', 'roommate-mobile-theme'); ?>
                        </span>

                        <span class="rmt-card__title rmt-card__title--light">
                            <?php esc_html_e('Browse Rooms', 'roommate-mobile-theme'); ?>
                        </span>
                    </span>

                    <span class="rmt-card__desc rmt-card__desc--light">
                        <?php esc_html_e('See available rooms, rent prices, areas, amenities, and roommate preferences.', 'roommate-mobile-theme'); ?>
                    </span>
                </a>

                
            </div>
        </div>
    </section>

    <section class="quick-search-section">
        <div class="container">

            <div class="section-heading">
                <h2><?php esc_html_e('What are you looking for?', 'roommate-mobile-theme'); ?></h2>
                <p><?php esc_html_e('Choose the path that matches your situation.', 'roommate-mobile-theme'); ?></p>
            </div>

            <div class="quick-search-grid">
                <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="quick-search-card">
                    <h3><?php esc_html_e('I need a room', 'roommate-mobile-theme'); ?></h3>
                    <p><?php esc_html_e('Browse available rooms and shared apartments.', 'roommate-mobile-theme'); ?></p>
                </a>

                <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="quick-search-card">
                    <h3><?php esc_html_e('I need a roommate', 'roommate-mobile-theme'); ?></h3>
                    <p><?php esc_html_e('Find people looking for rooms and flatmates.', 'roommate-mobile-theme'); ?></p>
                </a>

                <a href="<?php echo esc_url(home_url('/post-a-room/')); ?>" class="quick-search-card">
                    <h3><?php esc_html_e('Post a room', 'roommate-mobile-theme'); ?></h3>
                    <p><?php esc_html_e('List your available room and find a matching roommate.', 'roommate-mobile-theme'); ?></p>
                </a>

                <a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>" class="quick-search-card">
                    <h3><?php esc_html_e('Post roommate profile', 'roommate-mobile-theme'); ?></h3>
                    <p><?php esc_html_e('Create your profile and tell others what you are looking for.', 'roommate-mobile-theme'); ?></p>
                </a>
            </div>

        </div>
    </section>

    <section class="listing-section">
        <div class="container">

            <div class="section-heading">
                <h2><?php esc_html_e('Recently posted rooms', 'roommate-mobile-theme'); ?></h2>
                <p><?php esc_html_e('The newest room listings using the same card design as the room archive.', 'roommate-mobile-theme'); ?></p>
            </div>

            <?php if ($latest_rooms->have_posts()) : ?>
                <div class="listing-grid">

                    <?php while ($latest_rooms->have_posts()) : $latest_rooms->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();

                        $rent             = rmt_front_get_meta($post_id, '_rent');
                        $available_date   = rmt_front_get_meta($post_id, '_available_date');
                        $property_type    = rmt_front_get_meta($post_id, '_property_type');
                        $address          = rmt_front_get_meta($post_id, '_address');
                        $nearby_landmark  = rmt_front_get_meta($post_id, '_nearby_landmark');

                        $location_text    = rmt_front_terms_text($post_id, 'location_area');
                        $room_type_text   = rmt_front_terms_text($post_id, 'room_type');

                        $display_type     = $room_type_text ? $room_type_text : $property_type;
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

                                <?php if ($display_location) : ?>
                                    <p class="listing-card__address">
                                        <?php echo esc_html($display_location); ?>
                                    </p>
                                <?php elseif ($nearby_landmark) : ?>
                                    <p class="listing-card__address">
                                        <?php echo esc_html($nearby_landmark); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="listing-card__chips">
                                    <?php if ($display_type) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Property Type:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_type); ?>
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
                                            <?php echo esc_html(rmt_front_format_price($rent)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Available From:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_front_format_date($available_date)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <a href="<?php the_permalink(); ?>" class="btn btn-secondary">
                                    <?php esc_html_e('View Room', 'roommate-mobile-theme'); ?>
                                </a>

                            </div>
                        </article>
                    <?php endwhile; ?>

                </div>

                <?php wp_reset_postdata(); ?>

                <div class="cta-actions u-mt-4">
                    <a href="<?php echo esc_url(get_post_type_archive_link('room')); ?>" class="btn btn-primary">
                        <?php esc_html_e('View more rooms', 'roommate-mobile-theme'); ?>
                    </a>
                </div>

            <?php else : ?>

                <div class="empty-state">
                    <h2><?php esc_html_e('No rooms yet', 'roommate-mobile-theme'); ?></h2>
                    <p><?php esc_html_e('Check back later or post the first room.', 'roommate-mobile-theme'); ?></p>
                </div>

            <?php endif; ?>

        </div>
    </section>

    <section class="listing-section">
        <div class="container">

            <div class="section-heading">
                <h2><?php esc_html_e('Recently posted roommate profiles', 'roommate-mobile-theme'); ?></h2>
                <p><?php esc_html_e('The newest roommate profiles using the same card design as the roommate archive.', 'roommate-mobile-theme'); ?></p>
            </div>

            <?php if ($latest_roommates->have_posts()) : ?>
                <div class="listing-grid">

                    <?php while ($latest_roommates->have_posts()) : $latest_roommates->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();

                        $gender         = rmt_front_get_meta($post_id, '_gender');
                        $budget_min     = rmt_front_get_meta($post_id, '_budget_min');
                        $budget_max     = rmt_front_get_meta($post_id, '_budget_max');
                        $move_in_date   = rmt_front_get_meta($post_id, '_move_in_date');
                        $preferred_area = rmt_front_get_meta($post_id, '_preferred_area_text');

                        $location_text  = rmt_front_terms_text($post_id, 'location_area');

                        $display_area   = $location_text ? $location_text : $preferred_area;
                        $display_budget = rmt_front_format_budget_range($budget_min, $budget_max);
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
                                            <?php echo esc_html(rmt_front_format_date($move_in_date)); ?>
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

                <?php wp_reset_postdata(); ?>

                <div class="cta-actions u-mt-4">
                    <a href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>" class="btn btn-primary">
                        <?php esc_html_e('View more roommates', 'roommate-mobile-theme'); ?>
                    </a>
                </div>

            <?php else : ?>

                <div class="empty-state">
                    <h2><?php esc_html_e('No roommates yet', 'roommate-mobile-theme'); ?></h2>
                    <p><?php esc_html_e('Check back later or post the first roommate profile.', 'roommate-mobile-theme'); ?></p>
                </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>
