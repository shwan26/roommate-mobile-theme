<?php
/**
 * User feedback
 *
 * - Frontend: after a user posts, deletes or marks a listing as done, a modal asks for a
 *   1-5 star rating plus what is good / bad about the features.
 * - Storage: {prefix}rmt_feedback table.
 * - Admin only: summary widget on the WP dashboard + "Feedback" page with the details.
 */

defined('ABSPATH') || exit;

/* ------------------------------------------------------------------
 * Table
 * ---------------------------------------------------------------- */
function rmt_feedback_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'rmt_feedback';
}

function rmt_create_feedback_table() {
    global $wpdb;

    $table_name      = rmt_feedback_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        event VARCHAR(20) NOT NULL,
        listing_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        listing_type VARCHAR(20) NOT NULL DEFAULT '',
        rating TINYINT(1) UNSIGNED NOT NULL,
        liked TEXT NOT NULL,
        disliked TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY event (event),
        KEY rating (rating),
        KEY created_at (created_at)
    ) {$charset_collate};";

    dbDelta($sql);
}
add_action('after_switch_theme', 'rmt_create_feedback_table');

function rmt_maybe_create_feedback_table() {
    if ((int) get_option('rmt_feedback_table_version', 0) < 1) {
        rmt_create_feedback_table();
        update_option('rmt_feedback_table_version', 1);
    }
}
add_action('init', 'rmt_maybe_create_feedback_table');

function rmt_get_feedback_events() {
    return [
        'posted'  => __('Posted a listing', 'roommate-mobile-theme'),
        'deleted' => __('Deleted a listing', 'roommate-mobile-theme'),
        'done'    => __('Marked as done', 'roommate-mobile-theme'),
    ];
}

/* ------------------------------------------------------------------
 * Submit feedback (AJAX, logged-in users only)
 * ---------------------------------------------------------------- */
add_action('wp_ajax_rmt_submit_feedback', 'rmt_ajax_submit_feedback');

function rmt_ajax_submit_feedback() {
    global $wpdb;

    if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'rmt_feedback')) {
        wp_send_json_error('Invalid request.');
    }

    $event = sanitize_key($_POST['event'] ?? '');

    if (!isset(rmt_get_feedback_events()[$event])) {
        wp_send_json_error('Invalid request.');
    }

    $rating = absint($_POST['rating'] ?? 0);

    if ($rating < 1 || $rating > 5) {
        wp_send_json_error('Please choose a star rating.');
    }

    $listing_id   = absint($_POST['listing_id'] ?? 0);
    $listing_type = sanitize_key($_POST['listing_type'] ?? '');

    if (!in_array($listing_type, ['room', 'roommate'], true)) {
        $listing_type = '';
    }

    $liked    = mb_substr(trim(sanitize_textarea_field(wp_unslash($_POST['liked'] ?? ''))), 0, 1000);
    $disliked = mb_substr(trim(sanitize_textarea_field(wp_unslash($_POST['disliked'] ?? ''))), 0, 1000);

    $inserted = $wpdb->insert(
        rmt_feedback_table_name(),
        [
            'user_id'      => get_current_user_id(),
            'event'        => $event,
            'listing_id'   => $listing_id,
            'listing_type' => $listing_type,
            'rating'       => $rating,
            'liked'        => $liked,
            'disliked'     => $disliked,
            'created_at'   => current_time('mysql'),
        ],
        ['%d', '%s', '%d', '%s', '%d', '%s', '%s', '%s']
    );

    if (!$inserted) {
        wp_send_json_error('Could not save your feedback. Please try again.');
    }

    wp_send_json_success('Thanks for your feedback!');
}

/* ------------------------------------------------------------------
 * Frontend modal
 * ---------------------------------------------------------------- */
function rmt_render_feedback_modal($event, $listing_id = 0, $listing_type = '') {
    $titles = [
        'posted'  => __('Thanks for posting!', 'roommate-mobile-theme'),
        'deleted' => __('Sorry to see it go', 'roommate-mobile-theme'),
        'done'    => __('Glad you found a roommate!', 'roommate-mobile-theme'),
    ];

    if (!isset($titles[$event])) {
        return;
    }
    ?>
    <div class="footer-modal is-open" id="footer-modal-feedback" aria-hidden="false">
        <div class="footer-modal__overlay" data-footer-modal-close></div>

        <div class="footer-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="footer-modal-feedback-title">
            <button type="button" class="footer-modal__close" data-footer-modal-close aria-label="Close modal">&times;</button>

            <h2 id="footer-modal-feedback-title"><?php echo esc_html($titles[$event]); ?></h2>

            <div class="footer-modal__content">
                <p><?php esc_html_e('How was your experience? Your feedback helps us improve.', 'roommate-mobile-theme'); ?></p>

                <form id="rmt-feedback-form" class="rmt-feedback-form" novalidate>
                    <input type="hidden" name="action" value="rmt_submit_feedback">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('rmt_feedback')); ?>">
                    <input type="hidden" name="event" value="<?php echo esc_attr($event); ?>">
                    <input type="hidden" name="listing_id" value="<?php echo esc_attr(absint($listing_id)); ?>">
                    <input type="hidden" name="listing_type" value="<?php echo esc_attr($listing_type); ?>">

                    <fieldset class="rmt-stars">
                        <legend class="screen-reader-text"><?php esc_html_e('Rating', 'roommate-mobile-theme'); ?></legend>
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <input type="radio" name="rating" id="rmt-star-<?php echo (int) $i; ?>" value="<?php echo (int) $i; ?>">
                            <label for="rmt-star-<?php echo (int) $i; ?>" title="<?php echo esc_attr(sprintf(_n('%d star', '%d stars', $i, 'roommate-mobile-theme'), $i)); ?>">
                                <span aria-hidden="true">&#9733;</span>
                                <span class="screen-reader-text"><?php echo esc_html(sprintf(_n('%d star', '%d stars', $i, 'roommate-mobile-theme'), $i)); ?></span>
                            </label>
                        <?php endfor; ?>
                    </fieldset>

                    <label class="rmt-report-form__label" for="rmt-feedback-liked"><?php esc_html_e("What's good or useful?", 'roommate-mobile-theme'); ?></label>
                    <textarea id="rmt-feedback-liked" name="liked" rows="3" maxlength="1000" placeholder="<?php esc_attr_e('Tell us what you like about the features', 'roommate-mobile-theme'); ?>"></textarea>

                    <label class="rmt-report-form__label" for="rmt-feedback-disliked"><?php esc_html_e('What needs to improve, and how can we improve it?', 'roommate-mobile-theme'); ?></label>
                    <textarea id="rmt-feedback-disliked" name="disliked" rows="3" maxlength="1000" placeholder="<?php esc_attr_e('Tell us what needs to improve and your ideas for how we can make it better', 'roommate-mobile-theme'); ?>"></textarea>

                    <p class="rmt-report-form__message" id="rmt-feedback-message" role="alert" hidden></p>

                    <div class="rmt-report-form__actions">
                        <button type="button" class="btn btn-outline" data-footer-modal-close><?php esc_html_e('Skip', 'roommate-mobile-theme'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php esc_html_e('Send feedback', 'roommate-mobile-theme'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('rmt-feedback-form');
        if (!form) { return; }

        const modal   = document.getElementById('footer-modal-feedback');
        const message = document.getElementById('rmt-feedback-message');
        const submit  = form.querySelector('button[type="submit"]');
        const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

        document.body.classList.add('footer-modal-open');

        // Strip one-time query args so a refresh doesn't show the form again.
        try {
            const url = new URL(window.location.href);
            ['listing_submitted', 'listing_status', 'listing_id', 'listing_type', 'feedback'].forEach(function (key) { url.searchParams.delete(key); });
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}

        function showMessage(text, isError) {
            message.textContent = text;
            message.hidden = false;
            message.className = 'rmt-report-form__message' + (isError ? ' is-error' : ' is-success');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('footer-modal-open');
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            message.hidden = true;

            if (!form.querySelector('input[name="rating"]:checked')) {
                showMessage('Please choose a star rating.', true);
                return;
            }

            submit.disabled = true;

            try {
                const response = await fetch(ajaxUrl, { method: 'POST', body: new FormData(form) });
                const data = await response.json();

                if (data.success) {
                    showMessage('Thank you for your feedback!', false);
                    form.querySelectorAll('input, textarea, button').forEach(function (el) { el.disabled = true; });
                    setTimeout(closeModal, 1400);
                } else {
                    showMessage(data.data || 'Something went wrong.', true);
                    submit.disabled = false;
                }
            } catch (error) {
                showMessage('Network error. Please try again.', true);
                submit.disabled = false;
            }
        });
    })();
    </script>
    <?php
}

/* ------------------------------------------------------------------
 * Admin: summary
 * ---------------------------------------------------------------- */
function rmt_get_feedback_summary() {
    global $wpdb;

    $table = rmt_feedback_table_name();

    $totals = $wpdb->get_row("SELECT COUNT(*) AS total, AVG(rating) AS average FROM {$table}");

    $distribution = array_fill(1, 5, 0);
    foreach ($wpdb->get_results("SELECT rating, COUNT(*) AS c FROM {$table} GROUP BY rating") as $row) {
        $distribution[(int) $row->rating] = (int) $row->c;
    }

    $by_event = [];
    foreach ($wpdb->get_results("SELECT event, COUNT(*) AS c, AVG(rating) AS a FROM {$table} GROUP BY event") as $row) {
        $by_event[$row->event] = ['count' => (int) $row->c, 'average' => (float) $row->a];
    }

    $latest = $wpdb->get_results(
        "SELECT * FROM {$table} WHERE liked <> '' OR disliked <> '' ORDER BY created_at DESC, id DESC LIMIT 3"
    );

    return [
        'total'        => (int) ($totals->total ?? 0),
        'average'      => (float) ($totals->average ?? 0),
        'distribution' => $distribution,
        'by_event'     => $by_event,
        'latest'       => $latest,
    ];
}

function rmt_feedback_stars_html($rating) {
    $full = (int) round($rating);

    return '<span style="color:#dba617;letter-spacing:1px;" aria-label="' . esc_attr(sprintf('%.1f out of 5', $rating)) . '">'
        . str_repeat('&#9733;', $full) . '<span style="color:#ccd0d4;">' . str_repeat('&#9733;', max(0, 5 - $full)) . '</span></span>';
}

function rmt_render_feedback_summary($show_link = false) {
    $summary = rmt_get_feedback_summary();
    $events  = rmt_get_feedback_events();

    if ($summary['total'] === 0) {
        echo '<p>No feedback yet.</p>';
        return;
    }

    echo '<p style="font-size:1.6em;margin:0 0 .4em;"><strong>' . esc_html(number_format_i18n($summary['average'], 1)) . '</strong> / 5 '
        . rmt_feedback_stars_html($summary['average'])
        . ' <span style="font-size:.55em;color:#646970;">' . esc_html(sprintf(_n('%d response', '%d responses', $summary['total'], 'roommate-mobile-theme'), $summary['total'])) . '</span></p>';

    echo '<table style="width:100%;max-width:420px;border-collapse:collapse;">';
    for ($star = 5; $star >= 1; $star--) {
        $count = $summary['distribution'][$star];
        $pct   = $summary['total'] ? round($count / $summary['total'] * 100) : 0;
        echo '<tr><td style="width:44px;">' . (int) $star . ' &#9733;</td>'
            . '<td><div style="background:#e5e5e5;border-radius:4px;height:10px;"><div style="background:#58cc02;border-radius:4px;height:10px;width:' . (int) $pct . '%;"></div></div></td>'
            . '<td style="width:36px;text-align:right;">' . (int) $count . '</td></tr>';
    }
    echo '</table>';

    echo '<p style="margin:1em 0 .3em;"><strong>By event</strong></p><ul style="margin:0;">';
    foreach ($events as $key => $label) {
        if (!isset($summary['by_event'][$key])) {
            continue;
        }
        echo '<li>' . esc_html($label) . ': ' . (int) $summary['by_event'][$key]['count'] . ' &middot; avg '
            . esc_html(number_format_i18n($summary['by_event'][$key]['average'], 1)) . '</li>';
    }
    echo '</ul>';

    if ($summary['latest']) {
        echo '<p style="margin:1em 0 .3em;"><strong>Latest comments</strong></p>';
        foreach ($summary['latest'] as $row) {
            echo '<blockquote style="margin:0 0 .6em;padding:.4em .8em;border-left:3px solid #58cc02;background:#f6f7f7;">';
            echo rmt_feedback_stars_html((int) $row->rating) . '<br>';
            if ($row->liked !== '') {
                echo '<em>Good:</em> ' . esc_html(wp_trim_words($row->liked, 25)) . '<br>';
            }
            if ($row->disliked !== '') {
                echo '<em>Improve:</em> ' . esc_html(wp_trim_words($row->disliked, 25));
            }
            echo '</blockquote>';
        }
    }

    if ($show_link) {
        echo '<p><a href="' . esc_url(admin_url('admin.php?page=rmt-feedback')) . '">View all feedback &rarr;</a></p>';
    }
}

add_action('wp_dashboard_setup', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_add_dashboard_widget('rmt_feedback_summary', 'Feedback summary', function () {
        rmt_render_feedback_summary(true);
    });
});

/* ------------------------------------------------------------------
 * Admin: Feedback page (details)
 * ---------------------------------------------------------------- */
function rmt_count_unseen_feedback() {
    global $wpdb;

    return (int) $wpdb->get_var($wpdb->prepare(
        'SELECT COUNT(*) FROM ' . rmt_feedback_table_name() . ' WHERE id > %d',
        (int) get_option('rmt_feedback_last_seen_id', 0)
    ));
}

add_action('admin_menu', 'rmt_add_feedback_admin_page');

function rmt_add_feedback_admin_page() {
    $unseen = rmt_count_unseen_feedback();
    $title  = 'Feedback';

    if ($unseen > 0) {
        $title .= ' <span class="awaiting-mod">' . (int) $unseen . '</span>';
    }

    add_menu_page('Feedback', $title, 'manage_options', 'rmt-feedback', 'rmt_render_feedback_admin_page', 'dashicons-star-filled', 27);
}

function rmt_render_feedback_admin_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    $table  = rmt_feedback_table_name();
    $events = rmt_get_feedback_events();

    $event  = isset($_GET['event']) && isset($events[$_GET['event']]) ? $_GET['event'] : '';
    $rating = isset($_GET['rating']) ? absint($_GET['rating']) : 0;

    $type_filter = (isset($_GET['listing_type']) && in_array($_GET['listing_type'], ['room', 'roommate'], true)) ? $_GET['listing_type'] : '';

    $where = [];
    if ($type_filter) {
        $where[] = $wpdb->prepare('listing_type = %s', $type_filter);
    }
    if ($event) {
        $where[] = $wpdb->prepare('event = %s', $event);
    }
    if ($rating >= 1 && $rating <= 5) {
        $where[] = $wpdb->prepare('rating = %d', $rating);
    }
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $rows = $wpdb->get_results("SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC, id DESC LIMIT 300");

    echo '<div class="wrap"><h1>Feedback</h1>';

    echo '<h2 class="nav-tab-wrapper" style="margin-bottom:12px;">';
    foreach (['' => 'All posts', 'room' => 'Room posts', 'roommate' => 'Roommate posts'] as $key => $label) {
        $url = admin_url('admin.php?page=rmt-feedback');
        if ($key !== '') {
            $url = add_query_arg('listing_type', $key, $url);
        }
        echo '<a href="' . esc_url($url) . '" class="nav-tab' . ($type_filter === $key ? ' nav-tab-active' : '') . '">' . esc_html($label) . '</a>';
    }
    echo '</h2>';

    echo '<div class="postbox" style="padding:12px 16px;max-width:560px;"><h2 style="padding:0;">Summary</h2>';
    rmt_render_feedback_summary(false);
    echo '</div>';

    echo '<form method="get" style="margin:1em 0;"><input type="hidden" name="page" value="rmt-feedback">';
    if ($type_filter) {
        echo '<input type="hidden" name="listing_type" value="' . esc_attr($type_filter) . '">';
    }
    echo '<select name="event"><option value="">All events</option>';
    foreach ($events as $key => $label) {
        echo '<option value="' . esc_attr($key) . '"' . selected($event, $key, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select> <select name="rating"><option value="0">All ratings</option>';
    for ($i = 5; $i >= 1; $i--) {
        echo '<option value="' . (int) $i . '"' . selected($rating, $i, false) . '>' . (int) $i . ' stars</option>';
    }
    echo '</select> <button class="button">Filter</button></form>';

    if (!$rows) {
        echo '<p>No feedback found.</p></div>';
    } else {
        echo '<table class="widefat striped"><thead><tr>';
        foreach (['Date', 'User', 'Event', 'Listing', 'Rating', 'Good', 'Needs improvement'] as $heading) {
            echo '<th>' . esc_html($heading) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $user    = get_userdata((int) $row->user_id);
            $listing = $row->listing_id ? get_post((int) $row->listing_id) : null;

            echo '<tr>';
            echo '<td>' . esc_html(mysql2date('M j, Y g:i A', $row->created_at)) . '</td>';
            echo '<td>' . ($user ? esc_html($user->display_name) : '<em>Deleted user</em>') . '</td>';
            echo '<td>' . esc_html($events[$row->event] ?? $row->event) . '</td>';
            echo '<td>';
            if ($listing && $listing->post_status !== 'trash') {
                echo '<a href="' . esc_url(get_permalink($listing)) . '" target="_blank" rel="noopener">' . esc_html(get_the_title($listing)) . '</a><br>' . esc_html($listing->post_type . ' #' . $listing->ID);
            } elseif ($row->listing_id) {
                echo '<em>Deleted</em> (' . esc_html($row->listing_type . ' #' . $row->listing_id) . ')';
            } else {
                echo '&mdash;';
            }
            echo '</td>';
            echo '<td>' . rmt_feedback_stars_html((int) $row->rating) . '</td>';
            echo '<td style="max-width:260px;">' . nl2br(esc_html($row->liked)) . '</td>';
            echo '<td style="max-width:260px;">' . nl2br(esc_html($row->disliked)) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }

    // Opening the page marks everything as seen (clears the menu bubble).
    $max_id = (int) $wpdb->get_var("SELECT MAX(id) FROM {$table}");
    update_option('rmt_feedback_last_seen_id', $max_id, false);
}
