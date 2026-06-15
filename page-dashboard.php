<?php
/**
 * Template Name: User Dashboard
 * Frontend-only listing management for room and roommate posts.
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user    = wp_get_current_user();
$success_message = '';
$error_message   = '';
$can_create_room     = bkkroomie_user_can_create_listing($current_user->ID, 'room');
$can_create_roommate = bkkroomie_user_can_create_listing($current_user->ID, 'roommate');

function rmt_dashboard_can_manage($post_id, $user_id) {
    $post = get_post($post_id);
    return $post && in_array($post->post_type, ['room', 'roommate'], true) && (int) $post->post_author === (int) $user_id;
}

function rmt_dashboard_status_label($post_id) {
    if (get_post_meta($post_id, '_rmt_done', true)) {
        return 'Done';
    }

    $status = get_post_status($post_id);
    return $status ? ucfirst($status) : '';
}

function rmt_dashboard_edit_url($post_id) {
    $type = get_post_type($post_id);

    if ($type === 'room') {
        return add_query_arg('edit_id', $post_id, home_url('/edit-room/'));
    }

    if ($type === 'roommate') {
        return add_query_arg('edit_id', $post_id, home_url('/edit-roommate/'));
    }

    return home_url('/dashboard/');
}

function rmt_dashboard_terms_text($post_id, $taxonomy) {
    $terms = get_the_terms($post_id, $taxonomy);
    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    return implode(', ', wp_list_pluck($terms, 'name'));
}

function rmt_dashboard_format_date($date) {
    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return $date;
    }

    return date_i18n('d/m/Y', $timestamp);
}

function rmt_dashboard_request_account_deletion($user_id, $password) {
    $user_id = absint($user_id);
    $password = (string) $password;

    if (!$user_id || user_can($user_id, 'manage_options') || (function_exists('is_super_admin') && is_super_admin($user_id))) {
        return new WP_Error('not_allowed', 'This account cannot be deleted from the frontend dashboard.');
    }

    $user = get_userdata($user_id);

    if (!$user || $password === '' || !wp_check_password($password, $user->user_pass, $user_id)) {
        return new WP_Error('invalid_password', 'Please enter your current password to delete your account.');
    }

    if (!function_exists('rmt_schedule_account_deletion')) {
        return new WP_Error('schedule_unavailable', 'Account deletion is unavailable right now. Please try again later.');
    }

    return rmt_schedule_account_deletion($user_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_dashboard_action_type'])) {
    if (!isset($_POST['rmt_dashboard_nonce']) || !wp_verify_nonce($_POST['rmt_dashboard_nonce'], 'rmt_dashboard_action')) {
        $error_message = 'Security check failed.';
    } else {
        $action  = sanitize_key($_POST['rmt_dashboard_action_type']);
        $post_id = absint($_POST['post_id'] ?? 0);

        if ($action === 'delete_account') {
            $scheduled_for = rmt_dashboard_request_account_deletion($current_user->ID, wp_unslash($_POST['rmt_delete_account_password'] ?? ''));

            if (is_wp_error($scheduled_for)) {
                $error_message = $scheduled_for->get_error_message();
            } else {
                wp_logout();
                wp_safe_redirect(add_query_arg('account_deletion_scheduled', '1', home_url('/')));
                exit;
            }
        } elseif (!$post_id || !rmt_dashboard_can_manage($post_id, $current_user->ID)) {
            $error_message = 'You are not allowed to manage this listing.';
        } else {
            if ($action === 'publish') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'publish',
                ]);

                delete_post_meta($post_id, '_rmt_done');

                $success_message = 'Listing published successfully.';
            }

            if ($action === 'unpublish') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                ]);

                delete_post_meta($post_id, '_rmt_done');

                $success_message = 'Listing unpublished and moved to draft.';
            }

            if ($action === 'done') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                ]);

                update_post_meta($post_id, '_rmt_done', 1);

                $success_message = 'Listing marked as done and hidden from public listings.';
            }

            if ($action === 'delete') {
                $status = get_post_status($post_id);

                if ($status === 'publish') {
                    $error_message = 'Unpublish the listing before deleting it.';
                } else {
                    wp_trash_post($post_id);
                    $success_message = 'Listing moved to trash.';
                }
            }
        }
    }
}

$room_posts = new WP_Query([
    'post_type'      => 'room',
    'author'         => $current_user->ID,
    'post_status'    => ['publish', 'pending', 'draft'],
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'order'          => 'DESC',
]);

$roommate_posts = new WP_Query([
    'post_type'      => 'roommate',
    'author'         => $current_user->ID,
    'post_status'    => ['publish', 'pending', 'draft'],
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'order'          => 'DESC',
]);

get_header();
?>

<?php
$listing_limit = isset($_GET['listing_limit']) ? sanitize_key($_GET['listing_limit']) : '';

if ($listing_limit === 'room' || $listing_limit === 'roommate') :
?>
    <div class="container">
        <div class="rmt-notice rmt-notice--error">
            <p><?php echo esc_html(bkkroomie_get_listing_limit_message($listing_limit)); ?></p>
        </div>
    </div>
<?php endif; ?>

<main id="primary" class="site-main dashboard-page">
    <section class="archive-hero">
        <div class="container">
            <h1 class="archive-title">Welcome, <?php echo esc_html($current_user->display_name); ?></h1>
            <p class="archive-description">Manage your room and roommate listings.</p>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container">
            <?php if (!empty($success_message)) : ?>
                <div class="rmt-notice rmt-notice--success">
                    <p><?php echo esc_html($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)) : ?>
                <div class="rmt-notice rmt-notice--error">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['listing_submitted'])) : ?>
                <div class="single-card" style="border-color:#89e219; margin-bottom:1rem;">
                    <p style="margin:0;font-weight:700;">
                        <?php echo (isset($_GET['listing_status']) && $_GET['listing_status'] === 'draft') ? 'Your listing was saved as draft.' : 'Your listing was published successfully.'; ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['listing_updated'])) : ?>
                <div class="rmt-notice rmt-notice--success">
                    <p>Your listing has been updated successfully.</p>
                </div>
            <?php endif; ?>

            <div class="dashboard-summary-grid">
                <div class="single-card dashboard-account-card">
                    <div class="dashboard-card-header">
                        <h2>Account Details</h2>
                        <a href="<?php echo esc_url(home_url('/edit-profile/')); ?>" class="dashboard-edit-link" aria-label="Edit account details">
                            <span>Edit</span>
                        </a>
	                    </div>
                    <ul class="detail-list">
                        <li><strong>Name:</strong> <?php echo esc_html($current_user->display_name); ?></li>
                        <li><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></li>
                        <li><strong>Room Listings:</strong> <?php echo esc_html($room_posts->found_posts); ?></li>
                        <li><strong>Roommate Profiles:</strong> <?php echo esc_html($roommate_posts->found_posts); ?></li>
                    </ul>
                </div>

	                <div class="single-card dashboard-quick-card">
	                    <h2>Quick Actions</h2>
	                    <div class="dashboard-quick-limit-messages" aria-live="polite" hidden>
	                        <p data-limit-message="room" hidden>delete existing room post to post a new listing</p>
	                        <p data-limit-message="roommate" hidden>delete existing roommate post to post a new listing</p>
	                    </div>
	                    <div class="cta-actions dashboard-quick-actions">
	                        <?php if ($can_create_room) : ?>
	                            <a href="<?php echo esc_url(home_url('/post-a-room/')); ?>" class="btn dashboard-quick-btn">
	                                <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>
	                                <span>Post a Room</span>
	                            </a>
	                        <?php else : ?>
	                            <button class="btn dashboard-quick-btn dashboard-quick-btn--limited" type="button" aria-disabled="true" data-dashboard-limit-trigger="room">
	                                <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10.5V20h14v-9.5"/><path d="M9 20v-6h6v6"/></svg>
	                                <span>Post a Room</span>
	                            </button>
	                        <?php endif; ?>

	                        <?php if ($can_create_roommate) : ?>
	                            <a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>" class="btn dashboard-quick-btn">
	                                <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
	                                <span>Post a Roommate</span>
	                            </a>
	                        <?php else : ?>
	                            <button class="btn dashboard-quick-btn dashboard-quick-btn--limited" type="button" aria-disabled="true" data-dashboard-limit-trigger="roommate">
	                                <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
	                                <span>Post a Roommate</span>
	                            </button>
	                        <?php endif; ?>
	                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn dashboard-quick-btn dashboard-quick-btn--logout">
	                            <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10 17 15 12l-5-5"/><path d="M15 12H3"/><path d="M13 4h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/></svg>
	                            <span>Log Out</span>
	                        </a>
	                        <button
	                            class="btn dashboard-quick-btn dashboard-quick-btn--danger"
	                            type="button"
	                            data-delete-account-open
	                        >
	                            <svg class="dashboard-quick-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
	                            <span>Delete Account</span>
	                        </button>
	                    </div>
	                </div>
            </div>

            <div class="dashboard-delete-modal" id="rmt-delete-account-modal" hidden>
                <div class="dashboard-delete-modal__overlay" data-delete-account-close></div>
                <form method="post" class="dashboard-delete-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rmt-delete-account-title">
                    <button class="dashboard-delete-modal__close" type="button" aria-label="Close delete account confirmation" data-delete-account-close>&times;</button>
                    <h2 id="rmt-delete-account-title">Please confirm deletion</h2>
                    <p>Type your password to schedule account deletion. Your account will be deleted in 7 days.</p>
                    <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                    <input type="hidden" name="rmt_dashboard_action_type" value="delete_account">
                    <label for="rmt-delete-account-password">Current password</label>
                    <input
                        type="password"
                        id="rmt-delete-account-password"
                        name="rmt_delete_account_password"
                        class="dashboard-delete-password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <div class="dashboard-delete-modal__actions">
                        <button class="btn btn-secondary" type="button" data-delete-account-close>
                            Cancel
                        </button>
                        <button class="btn dashboard-quick-btn dashboard-quick-btn--danger" type="submit">
                            Confirm Deletion
                        </button>
                    </div>
                </form>
            </div>

            <?php $rmt_conversations = function_exists('rmt_get_user_conversations') ? rmt_get_user_conversations($current_user->ID, 10) : array(); ?>
            <div class="single-card rmt-dashboard-messages">
                <div class="rmt-dashboard-messages__header">
                    <div>
                        <h2>Recent Chats</h2>
                        <p>Continue conversations from your room and roommate posts.</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/messages/')); ?>" class="btn btn-secondary">Open Messages</a>
                </div>

                <?php if (!empty($rmt_conversations)) : ?>
                    <div class="rmt-conversation-list">
                        <?php foreach ($rmt_conversations as $conversation) : ?>
                            <?php
                            $other_user = get_userdata((int) $conversation->other_user_id);
                            $listing_id = (int) $conversation->listing_id;
                            $chat_url   = rmt_get_chat_url((int) $conversation->other_user_id, $listing_id);
                            ?>
                            <a class="rmt-conversation-item" href="<?php echo esc_url($chat_url); ?>">
                                <div>
                                    <strong><?php echo esc_html($other_user ? $other_user->display_name : 'User'); ?></strong>
                                    <span><?php echo esc_html(get_the_title($listing_id)); ?></span>
                                    <p><?php echo esc_html(wp_trim_words($conversation->last_message, 16)); ?></p>
                                </div>

                                <div class="rmt-conversation-meta">
                                    <?php if ((int) $conversation->unread_count > 0) : ?>
                                        <span class="rmt-unread-badge"><?php echo esc_html((int) $conversation->unread_count); ?></span>
                                    <?php endif; ?>
                                    <small><?php echo esc_html(mysql2date('M j, g:i A', $conversation->last_message_at)); ?></small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="rmt-dashboard-empty">No chats yet. When someone messages you from a listing, it will appear here.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <h2>My Room Listings</h2>
                <p>Edit, publish, mark done, or delete room listings.</p>
            </div>

            <?php if ($room_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($room_posts->have_posts()) : $room_posts->the_post(); ?>
                        <?php
                        $post_id          = get_the_ID();
                        $rent             = rmt_get_meta($post_id, '_rent');
                        $available_date   = rmt_get_meta($post_id, '_available_date');
                        $property_type    = rmt_get_meta($post_id, '_property_type');
                        $nearby_landmark  = rmt_get_meta($post_id, '_nearby_landmark');
                        $room_type_text   = rmt_dashboard_terms_text($post_id, 'room_type');
                        $location_text    = rmt_dashboard_terms_text($post_id, 'location_area');
                        $display_property = $room_type_text ? $room_type_text : $property_type;
                        $post_status      = get_post_status($post_id);
                        $is_published     = $post_status === 'publish';
                        $is_done          = (bool) get_post_meta($post_id, '_rmt_done', true);
                        ?>

                        <article class="listing-card">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="listing-card__image-link">
                                <div class="listing-card__image">
                                    <?php echo rmt_get_room_photo_html($post_id, 'large'); ?>
                                </div>
                            </a>

                            <div class="listing-card__content">
                                <h3 class="listing-card__title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
                                </h3>

                                <div class="listing-card__details">
                                    <?php if ($display_property) : ?>
                                        <span><?php echo esc_html($display_property); ?></span>
                                    <?php endif; ?>

                                    <?php if ($location_text) : ?>
                                        <span><?php echo esc_html($location_text); ?></span>
                                    <?php endif; ?>

                                    <?php if ($rent) : ?>
                                        <span><?php echo esc_html(rmt_format_price($rent)); ?>/person</span>
                                    <?php endif; ?>

                                    <?php if ($available_date) : ?>
                                        <span><?php echo esc_html(sprintf(__('Starting from %s', 'roommate-mobile-theme'), rmt_dashboard_format_date($available_date))); ?></span>
                                    <?php endif; ?>

                                    <?php if ($nearby_landmark) : ?>
                                        <span><?php echo esc_html($nearby_landmark); ?></span>
                                    <?php endif; ?>
                                </div>

                                <p class="listing-card__post-id">#<?php echo esc_html($post_id); ?></p>

                                <div class="cta-actions dashboard-card-actions">
                                    <?php if ($is_published) : ?>
                                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-primary">View</a>
                                    <?php endif; ?>

                                    <a href="<?php echo esc_url(rmt_dashboard_edit_url($post_id)); ?>" class="btn btn-secondary">Edit</a>

                                    <?php if (!$is_published || $is_done) : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="publish">
                                            <button type="submit" class="btn btn-primary">Publish</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($is_published) : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="done">
                                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Mark this listing as done?');">
                                                Done
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="delete">
                                            <button type="submit" class="btn btn-secondary btn--danger" onclick="return confirm('Move this listing to trash?');">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Room Listings Yet</h3>
                    <p>You have not submitted any room listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <h2>My Roommate Profiles</h2>
                <p>Edit, publish, mark done, or delete roommate profiles.</p>
            </div>

            <?php if ($roommate_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($roommate_posts->have_posts()) : $roommate_posts->the_post(); ?>
                        <?php
                        $post_id        = get_the_ID();
                        $nickname       = rmt_get_meta($post_id, '_nickname');
                        $age            = rmt_get_meta($post_id, '_age');
                        $gender         = rmt_get_meta($post_id, '_gender');
                        $budget_min     = rmt_get_meta($post_id, '_budget_min');
                        $legacy_budget  = rmt_get_meta($post_id, '_budget');
                        $move_in_date   = rmt_get_meta($post_id, '_move_in_date');
                        $preferred_area = rmt_get_meta($post_id, '_preferred_area_text') ?: rmt_get_meta($post_id, '_preferred_area');
                        $location_text  = rmt_dashboard_terms_text($post_id, 'location_area');
                        $display_area   = $location_text ? $location_text : $preferred_area;
                        $display_budget = $budget_min ? rmt_format_price($budget_min) : ($legacy_budget ? rmt_format_price($legacy_budget) : '');
                        $display_name   = $nickname ? $nickname : get_the_title();
                        $title_parts    = array_filter([$display_name, $age]);
                        $gender_key     = strtolower(trim($gender));
                        $gender_symbol  = '';
                        $post_status    = get_post_status($post_id);
                        $is_published   = $post_status === 'publish';
                        $is_done        = (bool) get_post_meta($post_id, '_rmt_done', true);

                        if ($gender_key === 'male') {
                            $gender_symbol = '♂';
                        } elseif ($gender_key === 'female') {
                            $gender_symbol = '♀';
                        } elseif ($gender_key === 'non-binary') {
                            $gender_symbol = '⚧';
                        }
                        ?>

                        <article class="listing-card listing-card--roommate">
                            <a href="<?php echo esc_url(get_permalink()); ?>" class="listing-card__image-link">
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
                                <h3 class="listing-card__title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>">
                                        <?php echo esc_html(implode(', ', $title_parts)); ?>
                                    </a>
                                </h3>

                                <div class="listing-card__details">
                                    <?php if ($move_in_date) : ?>
                                        <span>
                                            <?php echo esc_html(sprintf(__('Starting from %s', 'roommate-mobile-theme'), rmt_dashboard_format_date($move_in_date))); ?>
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

                                <div class="cta-actions dashboard-card-actions">
                                    <?php if ($is_published) : ?>
                                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-primary">View</a>
                                    <?php endif; ?>

                                    <a href="<?php echo esc_url(rmt_dashboard_edit_url($post_id)); ?>" class="btn btn-secondary">Edit</a>

                                    <?php if (!$is_published || $is_done) : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="publish">
                                            <button type="submit" class="btn btn-primary">Publish</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($is_published) : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="done">
                                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Mark this profile as done?');">
                                                Done
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <form method="post">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="rmt_dashboard_action_type" value="delete">
                                            <button type="submit" class="btn btn-secondary btn--danger" onclick="return confirm('Move this profile to trash?');">
                                                Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Roommate Profiles Yet</h3>
                    <p>You have not submitted any roommate profiles.</p>
                </div>
            <?php endif; ?>
        </div>
	    </section>
	</main>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
	    var quickCard = document.querySelector('.dashboard-quick-card');
	    var limitMessageTimer = null;

	    if (!quickCard) {
	        return;
	    }

	    quickCard.addEventListener('click', function (event) {
	        var trigger = event.target.closest('[data-dashboard-limit-trigger]');

	        if (!trigger) {
	            return;
	        }

	        var limitType = trigger.getAttribute('data-dashboard-limit-trigger');
	        var messageWrap = quickCard.querySelector('.dashboard-quick-limit-messages');

	        if (!messageWrap) {
	            return;
	        }

	        messageWrap.hidden = false;
	        messageWrap.querySelectorAll('[data-limit-message]').forEach(function (message) {
	            message.hidden = message.getAttribute('data-limit-message') !== limitType;
	        });

	        window.clearTimeout(limitMessageTimer);
	        limitMessageTimer = window.setTimeout(function () {
	            messageWrap.hidden = true;
	            messageWrap.querySelectorAll('[data-limit-message]').forEach(function (message) {
	                message.hidden = true;
	            });
	        }, 20000);
	    });
	});
	</script>

	<?php get_footer(); ?>
