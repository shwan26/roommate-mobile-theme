<?php
/**
 * Single Template: Roommate
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('rmt_single_roommate_terms_text')) {
    function rmt_single_roommate_terms_text($post_id, $taxonomy) {
        $terms = get_the_terms($post_id, $taxonomy);

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        return implode(', ', wp_list_pluck($terms, 'name'));
    }
}

if (!function_exists('rmt_single_roommate_format_date')) {
    function rmt_single_roommate_format_date($date) {
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

if (!function_exists('rmt_single_roommate_format_budget_range')) {
    function rmt_single_roommate_format_budget_range($min, $max) {
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
}

if (have_posts()) :
    while (have_posts()) : the_post();

        $post_id         = get_the_ID();
        $post_author_id  = (int) get_post_field('post_author', $post_id);
        $current_user_id = get_current_user_id();
        $is_author       = is_user_logged_in() && ($current_user_id === $post_author_id);
        $is_visitor      = !$is_author;

        /*
         * Roommate meta
         */
        $nickname             = rmt_get_meta($post_id, '_nickname');
        $age                  = rmt_get_meta($post_id, '_age');
        $gender               = rmt_get_meta($post_id, '_gender');
        $occupation           = rmt_get_meta($post_id, '_occupation');
        $languages            = rmt_get_meta($post_id, '_languages');
        $hobbies              = rmt_get_meta($post_id, '_hobbies');
        $bio                  = rmt_get_meta($post_id, '_bio');

        /*
         * Lifestyle meta
         */
        $cleanliness          = rmt_get_meta($post_id, '_cleanliness');
        $sleep_schedule       = rmt_get_meta($post_id, '_sleep_schedule');
        $smoker               = rmt_get_meta($post_id, '_smoker');
        $has_pets             = rmt_get_meta($post_id, '_has_pets');
        $social_level         = rmt_get_meta($post_id, '_social_level');

        /*
         * Room preference meta
         */
        $budget_min           = rmt_get_meta($post_id, '_budget_min');
        $budget_max           = rmt_get_meta($post_id, '_budget_max');
        $move_in_date         = rmt_get_meta($post_id, '_move_in_date');
        $preferred_area_text  = rmt_get_meta($post_id, '_preferred_area_text');
        $preferred_area       = rmt_get_meta($post_id, '_preferred_area');
        $roommate_preference  = rmt_get_meta($post_id, '_roommate_preference');

        /*
         * Taxonomies
         */
        $location_text = rmt_single_roommate_terms_text($post_id, 'location_area');
        $lifestyle_text = rmt_single_roommate_terms_text($post_id, 'lifestyle');

        $lifestyles = get_the_terms($post_id, 'lifestyle');

        /*
         * Display values matching archive roommate cards
         */
        $display_area   = $location_text ? $location_text : ($preferred_area_text ? $preferred_area_text : $preferred_area);
        $display_budget = rmt_single_roommate_format_budget_range($budget_min, $budget_max);
        ?>

        <main id="primary" class="site-main single-page single-roommate">
            <div class="container">

                <p class="u-mb-4">
                    <a
                        href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>"
                        class="btn btn-secondary"
                        aria-label="<?php esc_attr_e('Back to roommates', 'roommate-mobile-theme'); ?>"
                    >
                        ←
                    </a>
                </p>

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
                                <?php esc_html_e('Your profile', 'roommate-mobile-theme'); ?>
                            </span>

                            <div class="listing-owner-bar__actions">
                                <a href="<?php echo esc_url(add_query_arg('edit_id', $post_id, home_url('/edit-roommate/'))); ?>" class="btn btn-outline">
                                    ✏️ Edit
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-outline btn--mark-closed js-mark-closed"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_mark_closed_' . $post_id)); ?>"
                                >
                                    ✅ <?php esc_html_e('Mark as closed', 'roommate-mobile-theme'); ?>
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline btn--unpublish js-unpublish"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_unpublish_' . $post_id)); ?>"
                                >
                                    🔒 <?php esc_html_e('Unpublish', 'roommate-mobile-theme'); ?>
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
                                    💬 <?php esc_html_e('Chat with roommate', 'roommate-mobile-theme'); ?>
                                </a>
                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    💬 <?php esc_html_e('Login to message', 'roommate-mobile-theme'); ?>
                                </a>
                            <?php endif; ?>

                            <button
                                type="button"
                                class="btn btn-outline btn--report js-report-spam"
                                data-post-id="<?php echo esc_attr($post_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_report_' . $post_id)); ?>"
                            >
                                🚩 <?php esc_html_e('Report to Admin', 'roommate-mobile-theme'); ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="single-listing__grid">

                        <div class="single-listing__main">

                            <section class="single-card">
                                <h2><?php esc_html_e('Roommate Details', 'roommate-mobile-theme'); ?></h2>

                                <div class="single-listing__chips">
                                    <?php if ($move_in_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Move-in:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_single_roommate_format_date($move_in_date)); ?>
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

                                    <?php if ($hobbies) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Hobbies:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($hobbies); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <section class="single-card">
                                <h2><?php esc_html_e('Lifestyle', 'roommate-mobile-theme'); ?></h2>

                                <div class="single-listing__chips">
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

                            <section class="single-card">
                                <h2><?php esc_html_e('Bio', 'roommate-mobile-theme'); ?></h2>

                                <div class="entry-content">
                                    <?php
                                    if ($bio) {
                                        echo wpautop(esc_html($bio));
                                    } else {
                                        the_content();
                                    }
                                    ?>
                                </div>
                            </section>

                            <?php if ($roommate_preference) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('Preferred Roommate', 'roommate-mobile-theme'); ?></h2>
                                    <p><?php echo nl2br(esc_html($roommate_preference)); ?></p>
                                </section>
                            <?php endif; ?>

                        </div>

                        <aside class="single-listing__sidebar">

                            <section class="single-card">
                                <h2><?php esc_html_e('Quick Summary', 'roommate-mobile-theme'); ?></h2>

                                <div class="single-listing__chips">
                                    <?php if ($display_budget) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Budget:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_budget); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($display_area) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Area:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($display_area); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($move_in_date) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Move-in:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html(rmt_single_roommate_format_date($move_in_date)); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($gender) : ?>
                                        <span class="listing-chip">
                                            <?php esc_html_e('Gender:', 'roommate-mobile-theme'); ?>
                                            <?php echo esc_html($gender); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <?php if ($nickname || $age || $occupation) : ?>
                                <section class="single-card">
                                    <h2><?php esc_html_e('About', 'roommate-mobile-theme'); ?></h2>

                                    <ul class="detail-list">
                                        <?php if ($nickname) : ?>
                                            <li>
                                                <strong><?php esc_html_e('Name:', 'roommate-mobile-theme'); ?></strong>
                                                <?php echo esc_html($nickname); ?>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($age) : ?>
                                            <li>
                                                <strong><?php esc_html_e('Age:', 'roommate-mobile-theme'); ?></strong>
                                                <?php echo esc_html($age); ?>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($occupation) : ?>
                                            <li>
                                                <strong><?php esc_html_e('Occupation:', 'roommate-mobile-theme'); ?></strong>
                                                <?php echo esc_html($occupation); ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
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
                    if (!confirm('Mark this roommate profile as closed?')) {
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
                            badge.textContent = 'Closed';
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
                    if (!confirm('Unpublish this roommate profile? It will be moved to drafts and hidden from visitors.')) {
                        return;
                    }

                    button.disabled = true;

                    const data = await postAction(
                        'rmt_unpublish',
                        button.dataset.postId,
                        button.dataset.nonce
                    );

                    if (data.success) {
                        alert('Profile unpublished. Redirecting…');
                        window.location.href = <?php echo wp_json_encode(home_url('/dashboard/')); ?>;
                    } else {
                        alert(data.data || 'Something went wrong.');
                        button.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.js-report-spam').forEach(function (button) {
                button.addEventListener('click', async function () {
                    if (!confirm('Report this roommate profile as spam or inappropriate?')) {
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