<?php
/**
 * Single Template: Room
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('rmt_single_terms_text')) {
    function rmt_single_terms_text($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return implode(', ', wp_list_pluck($terms, 'name'));
    }
}

if (!function_exists('rmt_single_format_date')) {
    function rmt_single_format_date($date) {
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

if (have_posts()) :
    while (have_posts()) : the_post();

        $post_id         = get_the_ID();
        $post_author_id  = (int) get_post_field('post_author', $post_id);
        $current_user_id = get_current_user_id();
        $is_author       = is_user_logged_in() && ($current_user_id === $post_author_id);
        $is_visitor      = !$is_author;

        /*
         * Room meta
         */
        $property_name     = rmt_get_meta($post_id, '_property_name');
        $property_type     = rmt_get_meta($post_id, '_property_type');
        $rent              = rmt_get_meta($post_id, '_rent');
        $deposit           = rmt_get_meta($post_id, '_deposit');
        $available_date    = rmt_get_meta($post_id, '_available_date');
        $address           = rmt_get_meta($post_id, '_address');
        $nearby_landmark   = rmt_get_meta($post_id, '_nearby_landmark');
        $map_url           = rmt_get_meta($post_id, '_map_url');
        $utilities         = rmt_get_meta($post_id, '_utilities');
        $bills_included    = rmt_get_meta($post_id, '_bills_included');
        $min_stay          = rmt_get_meta($post_id, '_min_stay');
        $gender_preference = rmt_get_meta($post_id, '_gender_preference');
        $pet_policy        = rmt_get_meta($post_id, '_pet_policy');
        $smoking_policy    = rmt_get_meta($post_id, '_smoking_policy');

        /*
         * Current roommate meta
         */
        $nickname            = rmt_get_meta($post_id, '_nickname');
        $age                 = rmt_get_meta($post_id, '_age');
        $gender              = rmt_get_meta($post_id, '_gender');
        $occupation          = rmt_get_meta($post_id, '_occupation');
        $languages           = rmt_get_meta($post_id, '_languages');
        $cleanliness         = rmt_get_meta($post_id, '_cleanliness');
        $sleep_schedule      = rmt_get_meta($post_id, '_sleep_schedule');
        $smoker              = rmt_get_meta($post_id, '_smoker');
        $has_pets            = rmt_get_meta($post_id, '_has_pets');
        $social_level        = rmt_get_meta($post_id, '_social_level');
        $hobbies             = rmt_get_meta($post_id, '_hobbies');
        $bio                 = rmt_get_meta($post_id, '_bio');
        $roommate_preference = rmt_get_meta($post_id, '_roommate_preference');

        /*
         * Taxonomies
         */
        $location_text = rmt_single_terms_text($post_id, 'location_area');
        $room_type_text = rmt_single_terms_text($post_id, 'room_type');

        $amenities  = get_the_terms($post_id, 'amenity');
        $lifestyles = get_the_terms($post_id, 'lifestyle');

        /*
         * Display values matching archive room card spans
         */
        $display_property_type = $room_type_text ? $room_type_text : $property_type;
        $display_location      = $location_text ? $location_text : $address;
        ?>

        <main id="primary" class="site-main single-page single-room">
            <div class="container">

              
                    <a
                        href="<?php echo esc_url(get_post_type_archive_link('room')); ?>"
                        class="btn btn-secondary"
                        aria-label="<?php esc_attr_e('Back to rooms', 'roommate-mobile-theme'); ?>"
                    >
                        ←
                    </a>

                <article <?php post_class('single-listing'); ?>>

                    <header class="single-listing__header">
                        <div class="single-listing__header-text">

                            <h1><?php the_title(); ?></h1>
                        </div>
                    </header>

                    <?php if (has_post_thumbnail()) : ?>
                        <div class="single-listing__media">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_author) : ?>
                        <div class="listing-owner-bar">
                            <span class="listing-owner-bar__label">
                                Your Room Details
                            </span>

                            <div class="listing-owner-bar__actions">
                                <a href="<?php echo esc_url(add_query_arg('edit_id', $post_id, home_url('/edit-room/'))); ?>" class="btn btn-outline">
                                    ✏️ Edit
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-outline btn--mark-closed js-mark-closed"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_mark_closed_' . $post_id)); ?>"
                                >
                                    ✅ Mark as closed
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline btn--unpublish js-unpublish"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_unpublish_' . $post_id)); ?>"
                                >
                                    🔒 Unpublish
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_visitor) : ?>
                        <div class="listing-visitor-actions">
                            <?php if (is_user_logged_in()) : ?>
                                <a
                                    href="<?php echo esc_url(rmt_get_chat_url($post_author_id, $post_id)); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    💬 Chat with roommate
                                </a>
                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    💬 Login to message
                                </a>
                            <?php endif; ?>

                            <button
                                type="button"
                                class="btn btn-outline btn--report js-report-spam"
                                data-post-id="<?php echo esc_attr($post_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_report_' . $post_id)); ?>"
                            >
                                🚩 Report to Admin
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="single-listing__grid">

                        <div class="single-listing__main">

                            <section class="single-card">
                                <h2><?php esc_html_e('Room Details', 'roommate-mobile-theme'); ?></h2>

                                <div class="single-listing__chips">
                                    <?php if ($property_name) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Property Name:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($property_name); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_property_type) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Property Type:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_property_type); ?>
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
                                            <?php esc_html_e('Rent:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($rent)); ?>
                                            <?php esc_html_e('/month', 'roommate-mobile-theme'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($deposit !== '') : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Deposit:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($deposit)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($bills_included) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Bills:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($bills_included); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Available From:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_single_format_date($available_date)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($gender_preference) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Gender Preference:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($gender_preference); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($pet_policy) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Pet Policy:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($pet_policy); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($smoking_policy) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Smoking Policy:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($smoking_policy); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($utilities) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Utilities:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($utilities); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($min_stay) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Minimum Stay:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($min_stay); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($nearby_landmark) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Nearby:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($nearby_landmark); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($address) : ?>
                                    <ul class="detail-list u-mt-4">
                                        <li>
                                            <strong><?php esc_html_e('Full Address:', 'roommate-mobile-theme'); ?></strong>
                                            <?php echo esc_html($address); ?>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                            </section>

                            <?php if (!empty($amenities) && !is_wp_error($amenities)) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Amenities', 'roommate-mobile-theme'); ?></h2>

                                    <div class="listing-card__chips">
                                        <?php foreach ($amenities as $amenity) : ?>
                                            <span class="listing-chip">
                                                <?php echo esc_html($amenity->name); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <section class="single-card">
                                <h2><?php esc_html_e('Description', 'roommate-mobile-theme'); ?></h2>

                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                            </section>

                            <?php if ($map_url) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Map', 'roommate-mobile-theme'); ?></h2>

                                    <p>
                                        <a
                                            class="btn btn-secondary"
                                            href="<?php echo esc_url($map_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <?php esc_html_e('Open Location Map', 'roommate-mobile-theme'); ?>
                                        </a>
                                    </p>
                                </section>
                            <?php endif; ?>

                        </div>

                        <aside class="single-listing__sidebar">

                            <section class="single-card">
                                <h2><?php esc_html_e('Current Roommate', 'roommate-mobile-theme'); ?></h2>

                                <div class="single-listing__chips">
                                    <?php if ($nickname) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Name:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($nickname); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($age) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Age:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($age); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($gender) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Gender:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($gender); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($occupation) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Occupation:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($occupation); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($languages) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Languages:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($languages); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($cleanliness) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Cleanliness:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($cleanliness); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($sleep_schedule) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Sleep Schedule:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($sleep_schedule); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($smoker) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Smoker:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($smoker); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($has_pets) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Has Pets:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($has_pets); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($social_level) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Social Level:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($social_level); ?>/10
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($hobbies) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Hobbies:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($hobbies); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <?php if (!empty($lifestyles) && !is_wp_error($lifestyles)) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Lifestyle Tags', 'roommate-mobile-theme'); ?></h2>

                                    <div class="listing-card__chips">
                                        <?php foreach ($lifestyles as $tag) : ?>
                                            <span class="listing-chip">
                                                <?php echo esc_html($tag->name); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if ($bio) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Bio', 'roommate-mobile-theme'); ?></h2>
                                    <p><?php echo nl2br(esc_html($bio)); ?></p>
                                </section>
                            <?php endif; ?>

                            <?php if ($roommate_preference) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Preferred Roommate', 'roommate-mobile-theme'); ?></h2>
                                    <p><?php echo nl2br(esc_html($roommate_preference)); ?></p>
                                </section>
                            <?php endif; ?>

                        </aside>

                    </div>

                </article>

            </div>
        </main>

        <script>
        (function () {
            const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

            async function postAction(action, postId, nonce) {
                const body = new URLSearchParams({
                    action: action,
                    post_id: postId,
                    nonce: nonce
                });

                const response = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: body
                });

                return response.json();
            }

            document.querySelectorAll('.js-mark-closed').forEach(function (button) {
                button.addEventListener('click', async function () {
                    if (!confirm('Mark this listing as closed? It will stay visible but show a "Room Taken" badge.')) {
                        return;
                    }

                    button.disabled = true;

                    const data = await postAction(
                        'rmt_mark_closed',
                        button.dataset.postId,
                        button.dataset.nonce
                    );

                    if (data.success) {
                        button.textContent = '✅ Marked as closed';

                        const header = document.querySelector('.single-listing__header');

                        if (header) {
                            const badge = document.createElement('span');
                            badge.className = 'archive-badge archive-badge--closed';
                            badge.textContent = 'Room Taken';
                            header.prepend(badge);
                        }
                    } else {
                        alert(data.data || 'Something went wrong.');
                        button.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.js-unpublish').forEach(function (button) {
                button.addEventListener('click', async function () {
                    if (!confirm('Unpublish this listing? It will be moved to drafts and hidden from visitors.')) {
                        return;
                    }

                    button.disabled = true;

                    const data = await postAction(
                        'rmt_unpublish',
                        button.dataset.postId,
                        button.dataset.nonce
                    );

                    if (data.success) {
                        alert('Listing unpublished. Redirecting…');
                        window.location.href = <?php echo wp_json_encode(home_url('/dashboard/')); ?>;
                    } else {
                        alert(data.data || 'Something went wrong.');
                        button.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.js-report-spam').forEach(function (button) {
                button.addEventListener('click', async function () {
                    if (!confirm('Report this listing as spam or inappropriate?')) {
                        return;
                    }

                    button.disabled = true;

                    const data = await postAction(
                        'rmt_report_listing',
                        button.dataset.postId,
                        button.dataset.nonce
                    );

                    if (data.success) {
                        button.textContent = '🚩 Reported — thanks!';
                    } else {
                        alert(data.data || 'Something went wrong.');
                        button.disabled = false;
                    }
                });
            });
        })();
        </script>

    <?php endwhile;
endif;

get_footer();