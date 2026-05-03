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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_dashboard_action_type'])) {
    if (!isset($_POST['rmt_dashboard_nonce']) || !wp_verify_nonce($_POST['rmt_dashboard_nonce'], 'rmt_dashboard_action')) {
        $error_message = 'Security check failed.';
    } else {
        $action  = sanitize_key($_POST['rmt_dashboard_action_type']);
        $post_id = absint($_POST['post_id'] ?? 0);

        if (!$post_id || !rmt_dashboard_can_manage($post_id, $current_user->ID)) {
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

<main id="primary" class="site-main dashboard-page">
    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Dashboard</span>
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
                <div class="single-card">
                    <h2>Account</h2>
                    <ul class="detail-list">
                        <li><strong>Name:</strong> <?php echo esc_html($current_user->display_name); ?></li>
                        <li><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></li>
                        <li><strong>Room Listings:</strong> <?php echo esc_html($room_posts->found_posts); ?></li>
                        <li><strong>Roommate Profiles:</strong> <?php echo esc_html($roommate_posts->found_posts); ?></li>
                    </ul>
                </div>

                <div class="single-card">
                    <h2>Quick Actions</h2>
                    <div class="cta-actions">
                        <a href="<?php echo esc_url(home_url('/post-a-room/')); ?>" class="btn btn-primary">
                            Post a Room
                        </a>

                        <a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>" class="btn btn-secondary">
                            Post a Roommate
                        </a>
                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn btn-secondary">Log Out</a>
                    </div>
                </div>
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
                <span class="section-badge">My Rooms</span>
                <h2>Your Room Listings</h2>
                <p>Edit, publish, unpublish, mark done, or delete room listings.</p>
            </div>

            <?php if ($room_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($room_posts->have_posts()) : $room_posts->the_post(); ?>
                        <?php
                        $post_id        = get_the_ID();
                        $rent           = rmt_get_meta($post_id, '_rent');
                        $available_date = rmt_get_meta($post_id, '_available_date');
                        $address        = rmt_get_meta($post_id, '_address');
                        $post_status    = get_post_status($post_id);
                        $is_published   = $post_status === 'publish';
                        $is_done        = (bool) get_post_meta($post_id, '_rmt_done', true);
                        ?>

                        <article class="listing-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="listing-card__image-link">
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <span class="listing-chip <?php echo !$is_published ? 'listing-chip--draft' : ''; ?> <?php echo $is_done ? 'listing-chip--done' : ''; ?>">
                                        <?php echo esc_html(rmt_dashboard_status_label($post_id)); ?>
                                    </span>

                                    <?php if ($rent) : ?>
                                        <div class="listing-card__price">
                                            <?php echo esc_html(rmt_format_price($rent)); ?>/month
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($address) : ?>
                                    <p class="listing-card__address"><?php echo esc_html($address); ?></p>
                                <?php endif; ?>

                                <?php if ($available_date) : ?>
                                    <p class="listing-card__address">Available: <?php echo esc_html($available_date); ?></p>
                                <?php endif; ?>

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
                                            <input type="hidden" name="rmt_dashboard_action_type" value="unpublish">
                                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Unpublish this listing?');">
                                                Unpublish
                                            </button>
                                        </form>

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
                <span class="section-badge">My Roommates</span>
                <h2>Your Roommate Profiles</h2>
                <p>Edit, publish, unpublish, mark done, or delete roommate profiles.</p>
            </div>

            <?php if ($roommate_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($roommate_posts->have_posts()) : $roommate_posts->the_post(); ?>
                        <?php
                        $post_id        = get_the_ID();
                        $budget_min     = rmt_get_meta($post_id, '_budget_min');
                        $budget_max     = rmt_get_meta($post_id, '_budget_max');
                        $legacy_budget  = rmt_get_meta($post_id, '_budget');
                        $move_in_date   = rmt_get_meta($post_id, '_move_in_date');
                        $preferred_area = rmt_get_meta($post_id, '_preferred_area_text') ?: rmt_get_meta($post_id, '_preferred_area');
                        $post_status    = get_post_status($post_id);
                        $is_published   = $post_status === 'publish';
                        $is_done        = (bool) get_post_meta($post_id, '_rmt_done', true);
                        ?>

                        <article class="listing-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="listing-card__image-link">
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <span class="listing-chip <?php echo !$is_published ? 'listing-chip--draft' : ''; ?> <?php echo $is_done ? 'listing-chip--done' : ''; ?>">
                                        <?php echo esc_html(rmt_dashboard_status_label($post_id)); ?>
                                    </span>

                                    <div class="listing-card__price">
                                        <?php
                                        if ($budget_min || $budget_max) {
                                            if ($budget_min) {
                                                echo esc_html(rmt_format_price($budget_min));
                                            }

                                            if ($budget_min && $budget_max) {
                                                echo ' – ';
                                            }

                                            if ($budget_max) {
                                                echo esc_html(rmt_format_price($budget_max));
                                            }
                                        } elseif ($legacy_budget) {
                                            echo esc_html(rmt_format_price($legacy_budget));
                                        }
                                        ?>
                                    </div>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($preferred_area) : ?>
                                    <p class="listing-card__address">Preferred Area: <?php echo esc_html($preferred_area); ?></p>
                                <?php endif; ?>

                                <?php if ($move_in_date) : ?>
                                    <p class="listing-card__address">Move-in: <?php echo esc_html($move_in_date); ?></p>
                                <?php endif; ?>

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
                                            <input type="hidden" name="rmt_dashboard_action_type" value="unpublish">
                                            <button type="submit" class="btn btn-secondary" onclick="return confirm('Unpublish this profile?');">
                                                Unpublish
                                            </button>
                                        </form>

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

<?php get_footer(); ?>