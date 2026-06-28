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

        return date_i18n('d/m/Y', $timestamp);
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
        $total_rent        = rmt_get_meta($post_id, '_total_rent');
        $rent              = rmt_get_meta($post_id, '_rent');
        $total_deposit     = rmt_get_meta($post_id, '_total_deposit');
        $deposit           = rmt_get_meta($post_id, '_deposit');
        $available_date    = rmt_get_meta($post_id, '_available_date');
        $address           = rmt_get_meta($post_id, '_address');
        $nearby_landmark   = rmt_get_meta($post_id, '_nearby_landmark');
        $map_url           = rmt_get_meta($post_id, '_map_url');
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
        $nationality         = rmt_get_meta($post_id, '_nationality');
        $languages           = rmt_get_meta($post_id, '_languages');
        $zodiac_sign         = rmt_get_meta($post_id, '_zodiac_sign');
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
        $display_location      = $location_text;
        $share_url             = get_permalink($post_id);
        $share_title           = get_the_title($post_id);
        $share_text            = sprintf(__('Check out this room on Bkkroomie: %s', 'roommate-mobile-theme'), $share_title);
        $share_message         = $share_text . ' ' . $share_url;
        ?>

        <main id="primary" class="site-main single-page single-room">
            <div class="container">

              
                    <a
                        href="<?php echo esc_url(get_post_type_archive_link('room')); ?>"
                        class="btn btn-secondary single-listing__back"
                        aria-label="<?php esc_attr_e('Back to rooms', 'roommate-mobile-theme'); ?>"
                    >
                        ← Back to Room Listing
                    </a>

                <article <?php post_class('single-listing'); ?>>

                    <header class="single-listing__header">
                        <div class="single-listing__header-text">

                            <h1><?php the_title(); ?></h1>
                        </div>
                    </header>

                    <div class="single-listing__media">
                        <?php echo rmt_get_room_photo_html(get_the_ID(), 'large'); ?>
                    </div>

                    <?php if ($is_author) : ?>
                        <div class="listing-owner-bar">
                            <span class="listing-owner-bar__label">
                                Your Room Details
                            </span>

                            <div class="listing-owner-bar__actions">
                                <button
                                    type="button"
                                    class="btn btn-outline btn--share js-listing-share"
                                    data-share-title="<?php echo esc_attr($share_title); ?>"
                                    data-share-text="<?php echo esc_attr($share_text); ?>"
                                    data-share-url="<?php echo esc_url($share_url); ?>"
                                    data-share-modal="listing-share-modal-<?php echo esc_attr($post_id); ?>"
                                >
                                    <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
                                    <span class="listing-action-text"><?php esc_html_e('Share', 'roommate-mobile-theme'); ?></span>
                                </button>

                                <a href="<?php echo esc_url(add_query_arg('edit_id', $post_id, home_url('/edit-room/'))); ?>" class="btn btn-outline">
                                    <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    <span class="listing-action-text"><?php esc_html_e('Edit', 'roommate-mobile-theme'); ?></span>
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-outline btn--mark-closed js-mark-closed"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_mark_closed_' . $post_id)); ?>"
                                >
                                    <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>
                                    <span class="listing-action-text"><?php esc_html_e('Done', 'roommate-mobile-theme'); ?></span>
                                </button>

                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($is_visitor) : ?>
                        <div class="listing-visitor-actions">
                            <button
                                type="button"
                                class="btn btn-outline btn--share js-listing-share"
                                data-share-title="<?php echo esc_attr($share_title); ?>"
                                data-share-text="<?php echo esc_attr($share_text); ?>"
                                data-share-url="<?php echo esc_url($share_url); ?>"
                                data-share-modal="listing-share-modal-<?php echo esc_attr($post_id); ?>"
                            >
                                <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
                                <span class="listing-action-text"><?php esc_html_e('Share', 'roommate-mobile-theme'); ?></span>
                            </button>

                            <?php if (is_user_logged_in()) : ?>
                                <a
                                    href="<?php echo esc_url(rmt_get_chat_url($post_author_id, $post_id)); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                    <span class="listing-action-text"><?php esc_html_e('Chat with roommate', 'roommate-mobile-theme'); ?></span>
                                </a>
                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>
                                    <span class="listing-action-text"><?php esc_html_e('Login to message', 'roommate-mobile-theme'); ?></span>
                                </a>
                            <?php endif; ?>

                            <button
                                type="button"
                                class="btn btn-outline btn--report js-report-spam"
                                data-post-id="<?php echo esc_attr($post_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_report_' . $post_id)); ?>"
                            >
                                <svg class="listing-action-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22V15"/></svg>
                                <span class="listing-action-text"><?php esc_html_e('Report to Admin', 'roommate-mobile-theme'); ?></span>
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

                                    <?php if ($total_rent) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Total Rent:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($total_rent)); ?>
                                            <?php esc_html_e('/month', 'roommate-mobile-theme'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($rent) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Rent Per Person:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($rent)); ?>
                                            <?php esc_html_e('/month', 'roommate-mobile-theme'); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($total_deposit !== '') : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Total Deposit:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($total_deposit)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($deposit !== '') : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Deposit Per Person:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_price($deposit)); ?>
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
                                            <?php echo esc_html(rmt_format_choice_label($gender_preference)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($pet_policy) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Pet Policy:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_choice_label($pet_policy)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($smoking_policy) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Smoking Policy:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_choice_label($smoking_policy)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($min_stay) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Minimum Stay:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_format_choice_label($min_stay)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($nearby_landmark) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Nearby:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($nearby_landmark); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($address) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Full Address:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($address); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($map_url) : ?>
                                    <p class="u-mt-4">
                                        <a
                                            class="btn btn-secondary"
                                            href="<?php echo esc_url($map_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <?php esc_html_e('Open Location Map', 'roommate-mobile-theme'); ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <div class="entry-content u-mt-4">
                                    <?php the_content(); ?>
                                </div>
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
                                            <?php echo esc_html(rmt_format_choice_label($gender)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($occupation) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Occupation:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($occupation); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($nationality) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Nationality:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($nationality); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($languages) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Languages:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($languages); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($zodiac_sign) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Zodiac Sign:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($zodiac_sign); ?>
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
                                            <?php echo esc_html($social_level); ?>
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
                                    <h2><?php esc_html_e('My Ideal Roommate', 'roommate-mobile-theme'); ?></h2>
                                    <p><?php echo nl2br(esc_html($roommate_preference)); ?></p>
                                </section>
                            <?php endif; ?>

                        </aside>

                    </div>

                </article>

            </div>
        </main>

        <div
            class="listing-share-modal"
            id="listing-share-modal-<?php echo esc_attr($post_id); ?>"
            hidden
            aria-hidden="true"
        >
            <div class="listing-share-modal__overlay" data-share-close></div>
            <div
                class="listing-share-modal__dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="listing-share-title-<?php echo esc_attr($post_id); ?>"
            >
                <button
                    type="button"
                    class="listing-share-modal__close"
                    data-share-close
                    aria-label="<?php esc_attr_e('Close share dialog', 'roommate-mobile-theme'); ?>"
                >
                    ×
                </button>

                <h2 id="listing-share-title-<?php echo esc_attr($post_id); ?>">
                    <?php esc_html_e('Share this room', 'roommate-mobile-theme'); ?>
                </h2>

                <p class="listing-share-modal__listing-title"><?php echo esc_html($share_title); ?></p>

                <label class="listing-share-modal__label" for="listing-share-url-<?php echo esc_attr($post_id); ?>">
                    <?php esc_html_e('Link to share', 'roommate-mobile-theme'); ?>
                </label>

                <div class="listing-share-modal__copy-row">
                    <input
                        id="listing-share-url-<?php echo esc_attr($post_id); ?>"
                        type="text"
                        value="<?php echo esc_url($share_url); ?>"
                        readonly
                    >
                    <button type="button" class="btn btn-primary js-copy-share-link" data-share-url="<?php echo esc_url($share_url); ?>">
                        <?php esc_html_e('Copy Link', 'roommate-mobile-theme'); ?>
                    </button>
                </div>

                <div class="listing-share-modal__socials" aria-label="<?php esc_attr_e('Share options', 'roommate-mobile-theme'); ?>">
                    <a class="listing-share-modal__social listing-share-modal__social--whatsapp" href="<?php echo esc_url('https://wa.me/?text=' . rawurlencode($share_message)); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="listing-share-modal__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.54 2 2.08 6.43 2.08 11.9c0 1.89.54 3.66 1.47 5.16L2 22l5.08-1.5a10.03 10.03 0 0 0 4.96 1.3c5.5 0 9.96-4.43 9.96-9.9S17.54 2 12.04 2Zm0 18.08a8.24 8.24 0 0 1-4.2-1.15l-.3-.18-3 .89.9-2.9-.2-.31a8.08 8.08 0 1 1 6.8 3.65Zm4.53-6.04c-.25-.12-1.46-.72-1.68-.8-.23-.08-.39-.12-.56.12-.16.25-.64.8-.78.96-.14.17-.29.19-.54.07-.25-.13-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.38-1.72-.15-.25-.02-.39.11-.52.12-.11.25-.29.38-.43.12-.15.16-.25.25-.42.08-.17.04-.31-.02-.44-.06-.12-.56-1.34-.76-1.83-.2-.48-.4-.41-.56-.42h-.48c-.17 0-.44.06-.66.31-.23.25-.87.85-.87 2.07 0 1.22.9 2.4 1.02 2.57.12.17 1.76 2.67 4.27 3.75.6.26 1.06.41 1.42.52.6.19 1.14.16 1.57.1.48-.07 1.46-.6 1.67-1.17.2-.58.2-1.07.14-1.17-.06-.11-.22-.17-.47-.29Z"/></svg>
                        <span>WhatsApp</span>
                    </a>
                    <a class="listing-share-modal__social listing-share-modal__social--x" href="<?php echo esc_url('https://twitter.com/intent/tweet?text=' . rawurlencode($share_text) . '&url=' . rawurlencode($share_url)); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="listing-share-modal__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M13.9 10.47 21.35 2h-1.76l-6.37 7.24L8.13 2H2.17l7.82 11.14L2.17 22h1.76l6.76-7.68L16.09 22h5.96l-8.15-11.53Zm-2.39 2.71-.78-1.1L4.43 3.3h2.88l5.01 6.99.78 1.1 6.62 9.24h-2.88l-5.33-7.45Z"/></svg>
                        <span>X</span>
                    </a>
                    <a class="listing-share-modal__social listing-share-modal__social--facebook" href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($share_url)); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="listing-share-modal__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8.6V6.7c0-.8.18-1.2 1.42-1.2H17V2.2c-.77-.08-1.55-.13-2.32-.13-2.98 0-5.03 1.82-5.03 5.16V8.6H6.5v3.7h3.15V22H14v-9.7h3.16l.48-3.7H14Z"/></svg>
                        <span>Facebook</span>
                    </a>
                    <a class="listing-share-modal__social listing-share-modal__social--line" href="<?php echo esc_url('https://social-plugins.line.me/lineit/share?url=' . rawurlencode($share_url)); ?>" target="_blank" rel="noopener noreferrer">
                        <svg class="listing-share-modal__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3C6.49 3 2 6.67 2 11.18c0 4.04 3.58 7.43 8.42 8.08.33.07.78.22.9.5.1.26.07.66.03.92l-.14.87c-.04.26-.2 1.02.87.56 1.07-.45 5.76-3.39 7.86-5.8A7.34 7.34 0 0 0 22 11.18C22 6.67 17.51 3 12 3Zm-5.4 11.1H5.2V8.25h1.4v4.55h2.33v1.3H6.6Zm4.06 0H9.26V8.25h1.4v5.85Zm5.05 0h-1.25l-2.3-3.13v3.13h-1.39V8.25h1.25l2.3 3.15V8.25h1.39v5.85Zm3.92-3.86h-2.35v.98h2.1v1.23h-2.1v.8h2.35v1.25h-3.75V8.25h3.75v1.24Z"/></svg>
                        <span>LINE</span>
                    </a>
                    <a class="listing-share-modal__social listing-share-modal__social--email" href="<?php echo esc_url('mailto:?subject=' . rawurlencode($share_title) . '&body=' . rawurlencode($share_message)); ?>">
                        <svg class="listing-share-modal__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 3.24V17h16V8.24l-7.36 5.14a1.12 1.12 0 0 1-1.28 0L4 8.24Zm.83-1.24L12 12l7.17-5H4.83Z"/></svg>
                        <span>Email</span>
                    </a>
                </div>
            </div>
        </div>

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
                    if (!confirm('Mark this listing as done?')) {
                        return;
                    }

                    button.disabled = true;

                    const data = await postAction(
                        'rmt_mark_closed',
                        button.dataset.postId,
                        button.dataset.nonce
                    );

                    if (data.success) {
                        window.location.href = '<?php echo esc_js(home_url('/dashboard/')); ?>';
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
                        const buttonText = button.querySelector('.listing-action-text');
                        if (buttonText) {
                            buttonText.textContent = 'Reported - thanks!';
                        }
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
