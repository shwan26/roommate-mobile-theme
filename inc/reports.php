<?php
/**
 * Listing reports
 *
 * - Frontend: "Report to Admin" opens a form (type + reason) on room/roommate pages.
 * - Storage: {prefix}rmt_reports table, one report per user per listing.
 * - Admin only: email notification, Reports page, and enforcement actions
 *   (dismiss/review, unpublish post, delete post, ban/unban user).
 */

defined('ABSPATH') || exit;

/* ------------------------------------------------------------------
 * Table
 * ---------------------------------------------------------------- */
function rmt_reports_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'rmt_reports';
}

function rmt_create_reports_table() {
    global $wpdb;

    $table_name      = rmt_reports_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        listing_id BIGINT(20) UNSIGNED NOT NULL,
        reporter_id BIGINT(20) UNSIGNED NOT NULL,
        report_type VARCHAR(20) NOT NULL,
        details TEXT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'new',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME NULL DEFAULT NULL,
        reviewed_by BIGINT(20) UNSIGNED NULL DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY listing_reporter (listing_id, reporter_id),
        KEY status (status),
        KEY created_at (created_at)
    ) {$charset_collate};";

    dbDelta($sql);
}
add_action('after_switch_theme', 'rmt_create_reports_table');

function rmt_maybe_create_reports_table() {
    if ((int) get_option('rmt_reports_table_version', 0) < 1) {
        rmt_create_reports_table();
        update_option('rmt_reports_table_version', 1);
    }
}
add_action('init', 'rmt_maybe_create_reports_table');

function rmt_get_report_types() {
    return [
        'spam'          => __('Spam', 'roommate-mobile-theme'),
        'fake'          => __('Fake listing', 'roommate-mobile-theme'),
        'impersonation' => __('Impersonation', 'roommate-mobile-theme'),
        'inappropriate' => __('Inappropriate content', 'roommate-mobile-theme'),
        'other'         => __('Other', 'roommate-mobile-theme'),
    ];
}

/* ------------------------------------------------------------------
 * Submit report (AJAX, logged-in users only)
 * ---------------------------------------------------------------- */
add_action('wp_ajax_rmt_report_listing',        'rmt_ajax_report_listing');
add_action('wp_ajax_nopriv_rmt_report_listing', 'rmt_ajax_report_listing');

function rmt_ajax_report_listing() {
    global $wpdb;

    $post_id = absint($_POST['post_id'] ?? 0);
    $nonce   = sanitize_text_field($_POST['nonce'] ?? '');

    if (!$post_id || !wp_verify_nonce($nonce, 'rmt_report_' . $post_id)) {
        wp_send_json_error('Invalid request.');
    }

    if (!is_user_logged_in()) {
        wp_send_json_error('Please log in to report a listing.');
    }

    $post = get_post($post_id);

    if (!$post || !in_array($post->post_type, ['room', 'roommate'], true)) {
        wp_send_json_error('Listing not found.');
    }

    $reporter_id = get_current_user_id();

    if ($reporter_id === (int) $post->post_author) {
        wp_send_json_error('You cannot report your own listing.');
    }

    $type  = sanitize_key($_POST['report_type'] ?? '');
    $types = rmt_get_report_types();

    if (!isset($types[$type])) {
        wp_send_json_error('Please choose what you want to report.');
    }

    $details = trim(sanitize_textarea_field(wp_unslash($_POST['details'] ?? '')));

    if ($details === '') {
        wp_send_json_error('Please tell us the reason for your report.');
    }

    $details = mb_substr($details, 0, 1000);
    $table   = rmt_reports_table_name();

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE listing_id = %d AND reporter_id = %d",
        $post_id,
        $reporter_id
    ));

    if ($existing) {
        wp_send_json_error('You have already reported this listing.');
    }

    $inserted = $wpdb->insert(
        $table,
        [
            'listing_id'  => $post_id,
            'reporter_id' => $reporter_id,
            'report_type' => $type,
            'details'     => $details,
            'status'      => 'new',
            'created_at'  => current_time('mysql'),
        ],
        ['%d', '%d', '%s', '%s', '%s', '%s']
    );

    if (!$inserted) {
        wp_send_json_error('Could not save your report. Please try again.');
    }

    // Keep the running counter and auto-flag used by the admin summary.
    $count = (int) get_post_meta($post_id, '_report_count', true) + 1;
    update_post_meta($post_id, '_report_count', $count);

    if ($count >= 5) {
        update_post_meta($post_id, '_flagged_for_review', 1);
    }

    rmt_send_report_notification($post, wp_get_current_user(), $type, $details);

    wp_send_json_success('Reported.');
}

function rmt_send_report_notification($post, $reporter, $type, $details) {
    $types     = rmt_get_report_types();
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);

    $subject = sprintf('[%s] New listing report: %s', $site_name, $types[$type] ?? $type);

    $message  = "A listing has been reported.\n\n";
    $message .= 'Listing: ' . wp_strip_all_tags(get_the_title($post)) . ' (#' . $post->ID . ', ' . $post->post_type . ")\n";
    $message .= 'Listing URL: ' . get_permalink($post) . "\n";
    $message .= 'Report type: ' . ($types[$type] ?? $type) . "\n";
    $message .= 'Reported by: ' . $reporter->display_name . ' (' . $reporter->user_email . ")\n";
    $message .= 'Reason: ' . $details . "\n\n";
    $message .= 'Review and take action: ' . admin_url('admin.php?page=rmt-listing-reports') . "\n";

    return wp_mail(get_option('admin_email'), $subject, $message);
}

/* ------------------------------------------------------------------
 * Frontend report form (reuses the footer modal markup, CSS and JS)
 * ---------------------------------------------------------------- */
function rmt_render_report_modal($post_id) {
    $post_id = absint($post_id);
    $nonce   = wp_create_nonce('rmt_report_' . $post_id);
    ?>
    <div class="footer-modal" id="footer-modal-report" aria-hidden="true">
        <div class="footer-modal__overlay" data-footer-modal-close></div>

        <div class="footer-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="footer-modal-report-title">
            <button type="button" class="footer-modal__close" data-footer-modal-close aria-label="Close modal">&times;</button>

            <h2 id="footer-modal-report-title"><?php esc_html_e('Report this listing', 'roommate-mobile-theme'); ?></h2>

            <div class="footer-modal__content">
                <?php if (!is_user_logged_in()) : ?>
                    <p><?php esc_html_e('Please log in to report a listing.', 'roommate-mobile-theme'); ?></p>
                    <a class="btn btn-primary" href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>">
                        <?php esc_html_e('Log in', 'roommate-mobile-theme'); ?>
                    </a>
                <?php else : ?>
                    <form id="rmt-report-form" class="rmt-report-form" novalidate>
                        <input type="hidden" name="action" value="rmt_report_listing">
                        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

                        <fieldset class="rmt-report-form__types">
                            <legend><?php esc_html_e('What do you want to report?', 'roommate-mobile-theme'); ?></legend>
                            <?php foreach (rmt_get_report_types() as $value => $label) : ?>
                                <label class="rmt-report-form__option">
                                    <input type="radio" name="report_type" value="<?php echo esc_attr($value); ?>">
                                    <span><?php echo esc_html($label); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>

                        <label class="rmt-report-form__label" for="rmt-report-details">
                            <?php esc_html_e('Reason', 'roommate-mobile-theme'); ?>
                        </label>
                        <textarea id="rmt-report-details" name="details" rows="4" maxlength="1000" placeholder="<?php esc_attr_e('Tell us what is wrong with this listing', 'roommate-mobile-theme'); ?>"></textarea>

                        <p class="rmt-report-form__message" id="rmt-report-message" role="alert" hidden></p>

                        <div class="rmt-report-form__actions">
                            <button type="button" class="btn btn-outline" data-footer-modal-close><?php esc_html_e('Cancel', 'roommate-mobile-theme'); ?></button>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Submit report', 'roommate-mobile-theme'); ?></button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('rmt-report-form');
        if (!form) { return; }

        const modal   = document.getElementById('footer-modal-report');
        const message = document.getElementById('rmt-report-message');
        const submit  = form.querySelector('button[type="submit"]');
        const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

        function showMessage(text, isError) {
            message.textContent = text;
            message.hidden = false;
            message.className = 'rmt-report-form__message' + (isError ? ' is-error' : ' is-success');
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            message.hidden = true;

            if (!form.querySelector('input[name="report_type"]:checked')) {
                showMessage('Please choose what you want to report.', true);
                return;
            }
            if (!form.details.value.trim()) {
                showMessage('Please tell us the reason for your report.', true);
                return;
            }

            submit.disabled = true;

            try {
                const response = await fetch(ajaxUrl, { method: 'POST', body: new FormData(form) });
                const data = await response.json();

                if (data.success) {
                    showMessage('Thanks, your report has been sent to the admin.', false);
                    form.querySelectorAll('input, textarea, button[type="submit"]').forEach(function (el) { el.disabled = true; });

                    document.querySelectorAll('.js-report-spam').forEach(function (button) {
                        const text = button.querySelector('.listing-action-text');
                        if (text) { text.textContent = 'Reported - thanks!'; }
                        button.disabled = true;
                    });

                    setTimeout(function () {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                        document.body.classList.remove('footer-modal-open');
                    }, 1600);
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
 * Banned users
 * ---------------------------------------------------------------- */
function rmt_is_user_banned($user_id) {
    return (bool) get_user_meta((int) $user_id, '_rmt_banned', true);
}

function rmt_block_banned_login($user, $password) {
    if ($user instanceof WP_User && rmt_is_user_banned($user->ID)) {
        return new WP_Error('rmt_banned', __('<strong>Error:</strong> Your account has been suspended.', 'roommate-mobile-theme'));
    }

    return $user;
}
add_filter('wp_authenticate_user', 'rmt_block_banned_login', 10, 2);

/* ------------------------------------------------------------------
 * Admin menu + page (administrators only)
 * ---------------------------------------------------------------- */
function rmt_count_new_reports() {
    global $wpdb;

    return (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . rmt_reports_table_name() . " WHERE status = 'new'");
}

add_action('admin_menu', 'rmt_add_reports_admin_page');

function rmt_add_reports_admin_page() {
    $new_count = rmt_count_new_reports();
    $title     = 'Reports';

    if ($new_count > 0) {
        $title .= ' <span class="awaiting-mod">' . (int) $new_count . '</span>';
    }

    add_menu_page(
        'Listing Reports',
        $title,
        'manage_options',
        'rmt-listing-reports',
        'rmt_render_reports_admin_page',
        'dashicons-flag',
        26
    );
}

function rmt_report_action_button($report_id, $action, $label, $confirm = '', $class = 'button button-small') {
    $onclick = $confirm ? ' onclick="return confirm(' . esc_attr(wp_json_encode($confirm)) . ');"' : '';

    return '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="display:inline-block;margin:0 4px 4px 0;">'
        . '<input type="hidden" name="action" value="rmt_report_action">'
        . '<input type="hidden" name="report_id" value="' . (int) $report_id . '">'
        . '<input type="hidden" name="do" value="' . esc_attr($action) . '">'
        . wp_nonce_field('rmt_report_action_' . (int) $report_id, '_wpnonce', true, false)
        . '<button type="submit" class="' . esc_attr($class) . '"' . $onclick . '>' . esc_html($label) . '</button>'
        . '</form>';
}

function rmt_render_reports_admin_page() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        return;
    }

    $table  = rmt_reports_table_name();
    $types  = rmt_get_report_types();
    $filter = (isset($_GET['status']) && in_array($_GET['status'], ['new', 'reviewed', 'dismissed'], true)) ? $_GET['status'] : 'all';

    $type_filter = (isset($_GET['listing_type']) && in_array($_GET['listing_type'], ['room', 'roommate'], true)) ? $_GET['listing_type'] : 'all';

    $conditions = [];
    if ($filter !== 'all') {
        $conditions[] = $wpdb->prepare('r.status = %s', $filter);
    }
    if ($type_filter !== 'all') {
        $conditions[] = $wpdb->prepare('p.post_type = %s', $type_filter);
    }
    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // LEFT JOIN so reports for permanently deleted listings still show under "All".
    $rows = $wpdb->get_results(
        "SELECT r.* FROM {$table} r LEFT JOIN {$wpdb->posts} p ON p.ID = r.listing_id {$where} ORDER BY r.created_at DESC LIMIT 200"
    );

    $base_url = admin_url('admin.php?page=rmt-listing-reports');

    // Rooms / Roommates tabs (keep the status filter).
    $type_counts = [];
    foreach ($wpdb->get_results("SELECT p.post_type AS t, COUNT(*) AS c FROM {$table} r INNER JOIN {$wpdb->posts} p ON p.ID = r.listing_id WHERE r.status = 'new' GROUP BY p.post_type") as $tc) {
        $type_counts[$tc->t] = (int) $tc->c;
    }

    echo '<div class="wrap"><h1>Listing Reports</h1>';

    if (!empty($_GET['rmt_notice'])) {
        $is_error = !empty($_GET['rmt_error']);
        echo '<div class="notice notice-' . ($is_error ? 'error' : 'success') . ' is-dismissible"><p>' . esc_html(wp_unslash($_GET['rmt_notice'])) . '</p></div>';
    }

    echo '<h2 class="nav-tab-wrapper" style="margin-bottom:12px;">';
    foreach (['all' => 'All posts', 'room' => 'Room posts', 'roommate' => 'Roommate posts'] as $key => $label) {
        $url = $base_url;
        if ($key !== 'all') {
            $url = add_query_arg('listing_type', $key, $url);
        }
        if ($filter !== 'all') {
            $url = add_query_arg('status', $filter, $url);
        }
        $badge = ($key !== 'all' && !empty($type_counts[$key])) ? ' <span class="awaiting-mod">' . (int) $type_counts[$key] . '</span>' : '';
        echo '<a href="' . esc_url($url) . '" class="nav-tab' . ($type_filter === $key ? ' nav-tab-active' : '') . '">' . esc_html($label) . $badge . '</a>';
    }
    echo '</h2>';

    echo '<ul class="subsubsub">';
    foreach (['all' => 'All', 'new' => 'New', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'] as $key => $label) {
        $url = $key === 'all' ? $base_url : add_query_arg('status', $key, $base_url);
        if ($type_filter !== 'all') {
            $url = add_query_arg('listing_type', $type_filter, $url);
        }
        $class = $filter === $key ? ' class="current"' : '';
        echo '<li><a href="' . esc_url($url) . '"' . $class . '>' . esc_html($label) . '</a>' . ($key !== 'dismissed' ? ' |' : '') . ' </li>';
    }
    echo '</ul><br class="clear">';

    if (!$rows) {
        echo '<p>No reports.</p></div>';
        return;
    }

    echo '<table class="widefat striped"><thead><tr>';
    foreach (['Date', 'Listing', 'Report', 'Reason', 'Reported by', 'Poster', 'Status', 'Actions'] as $heading) {
        echo '<th>' . esc_html($heading) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $listing   = get_post((int) $row->listing_id);
        $reporter  = get_userdata((int) $row->reporter_id);
        $author_id = $listing ? (int) $listing->post_author : 0;
        $author    = $author_id ? get_userdata($author_id) : null;
        $banned    = $author && rmt_is_user_banned($author_id);

        echo '<tr>';
        echo '<td>' . esc_html(mysql2date('M j, Y g:i A', $row->created_at)) . '</td>';

        echo '<td>';
        if ($listing) {
            echo '<strong>' . esc_html(get_the_title($listing)) . '</strong><br>';
            echo esc_html($listing->post_type . ' #' . $listing->ID . ' · ' . $listing->post_status) . '<br>';
            if ($listing->post_status !== 'trash') {
                echo '<a href="' . esc_url(get_permalink($listing)) . '" target="_blank" rel="noopener">View</a>';
            }
        } else {
            echo '<em>Listing deleted</em>';
        }
        echo '</td>';

        echo '<td>' . esc_html($types[$row->report_type] ?? $row->report_type) . '</td>';
        echo '<td style="max-width:260px;">' . nl2br(esc_html($row->details)) . '</td>';
        echo '<td>' . ($reporter ? esc_html($reporter->display_name) : '<em>Deleted user</em>') . '</td>';
        echo '<td>' . ($author ? esc_html($author->display_name) : '&mdash;') . ($banned ? ' <strong style="color:#b32d2e;">(Banned)</strong>' : '') . '</td>';
        echo '<td>' . esc_html(ucfirst($row->status)) . '</td>';

        echo '<td>';
        if ($row->status === 'new') {
            echo rmt_report_action_button($row->id, 'reviewed', 'Mark reviewed');
            echo rmt_report_action_button($row->id, 'dismiss', 'Dismiss');
        }
        if ($listing && $listing->post_status !== 'trash') {
            if ($listing->post_status === 'publish') {
                echo rmt_report_action_button($row->id, 'unpublish', 'Unpublish', 'Unpublish this listing (set to draft)?');
            }
            echo rmt_report_action_button($row->id, 'delete_post', 'Delete post', 'Move this listing to the trash?', 'button button-small button-link-delete');
        }
        if ($author && !user_can($author_id, 'manage_options')) {
            if ($banned) {
                echo rmt_report_action_button($row->id, 'unban_user', 'Unban user');
            } else {
                echo rmt_report_action_button($row->id, 'ban_user', 'Ban user', 'Ban this user, log them out and unpublish all their listings?', 'button button-small button-link-delete');
            }
        }
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

/* ------------------------------------------------------------------
 * Admin enforcement actions (administrators only)
 * ---------------------------------------------------------------- */
add_action('admin_post_rmt_report_action', 'rmt_handle_report_action');

function rmt_report_redirect($message, $is_error = false) {
    $args = ['page' => 'rmt-listing-reports', 'rmt_notice' => $message];

    if ($is_error) {
        $args['rmt_error'] = 1;
    }

    wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
    exit;
}

function rmt_mark_listing_reports_reviewed($listing_id) {
    global $wpdb;

    $wpdb->query($wpdb->prepare(
        'UPDATE ' . rmt_reports_table_name() . " SET status = 'reviewed', reviewed_at = %s, reviewed_by = %d WHERE listing_id = %d AND status = 'new'",
        current_time('mysql'),
        get_current_user_id(),
        $listing_id
    ));
}

function rmt_handle_report_action() {
    global $wpdb;

    if (!current_user_can('manage_options')) {
        wp_die('Not allowed.');
    }

    $report_id = absint($_POST['report_id'] ?? 0);
    check_admin_referer('rmt_report_action_' . $report_id);

    $table  = rmt_reports_table_name();
    $report = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $report_id));

    if (!$report) {
        rmt_report_redirect('Report not found.', true);
    }

    $do         = sanitize_key($_POST['do'] ?? '');
    $listing_id = (int) $report->listing_id;
    $listing    = get_post($listing_id);

    switch ($do) {
        case 'reviewed':
        case 'dismiss':
            $wpdb->update(
                $table,
                [
                    'status'      => $do === 'dismiss' ? 'dismissed' : 'reviewed',
                    'reviewed_at' => current_time('mysql'),
                    'reviewed_by' => get_current_user_id(),
                ],
                ['id' => $report_id]
            );
            rmt_report_redirect($do === 'dismiss' ? 'Report dismissed.' : 'Report marked as reviewed.');
            break;

        case 'unpublish':
            if ($listing) {
                wp_update_post(['ID' => $listing_id, 'post_status' => 'draft']);
                rmt_mark_listing_reports_reviewed($listing_id);
            }
            rmt_report_redirect('Listing unpublished.');
            break;

        case 'delete_post':
            if ($listing) {
                wp_trash_post($listing_id);
                rmt_mark_listing_reports_reviewed($listing_id);
            }
            rmt_report_redirect('Listing moved to the trash.');
            break;

        case 'ban_user':
            $author_id = $listing ? (int) $listing->post_author : 0;

            if (!$author_id || user_can($author_id, 'manage_options') || $author_id === get_current_user_id()) {
                rmt_report_redirect('This user cannot be banned.', true);
            }

            update_user_meta($author_id, '_rmt_banned', 1);
            update_user_meta($author_id, '_rmt_banned_at', current_time('mysql'));
            WP_Session_Tokens::get_instance($author_id)->destroy_all();

            $their_listings = get_posts([
                'post_type'      => ['room', 'roommate'],
                'post_status'    => ['publish', 'pending'],
                'author'         => $author_id,
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]);

            foreach ($their_listings as $their_id) {
                wp_update_post(['ID' => $their_id, 'post_status' => 'draft']);
            }

            rmt_mark_listing_reports_reviewed($listing_id);
            rmt_report_redirect('User banned and their listings unpublished.');
            break;

        case 'unban_user':
            $author_id = $listing ? (int) $listing->post_author : 0;

            if ($author_id) {
                delete_user_meta($author_id, '_rmt_banned');
                delete_user_meta($author_id, '_rmt_banned_at');
            }
            rmt_report_redirect('User unbanned. Their listings stay as drafts until they republish.');
            break;
    }

    rmt_report_redirect('Unknown action.', true);
}
