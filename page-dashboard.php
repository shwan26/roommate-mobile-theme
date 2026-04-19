<?php
/**
 * Template Name: User Dashboard
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user    = wp_get_current_user();
$success_message = '';
$error_message   = '';

/**
 * Handle delete action (only draft/pending posts)
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['rmt_delete_listing'])
) {
    if (
        !isset($_POST['rmt_dashboard_nonce']) ||
        !wp_verify_nonce($_POST['rmt_dashboard_nonce'], 'rmt_dashboard_action')
    ) {
        $error_message = 'Security check failed.';
    } else {
        $delete_post_id = isset($_POST['delete_post_id']) ? absint($_POST['delete_post_id']) : 0;
        $post_status    = get_post_field('post_status', $delete_post_id);

        if ($delete_post_id && get_post_field('post_author', $delete_post_id) == $current_user->ID) {
            if (in_array($post_status, ['draft', 'pending'])) {
                wp_trash_post($delete_post_id);
                $success_message = 'Listing deleted successfully.';
            } else {
                $error_message = 'Only draft or unpublished listings can be deleted. Unpublish it first.';
            }
        } else {
            $error_message = 'You are not allowed to delete this listing.';
        }
    }
}

/**
 * Handle unpublish action (move to draft)
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['rmt_unpublish_listing'])
) {
    if (
        !isset($_POST['rmt_dashboard_nonce']) ||
        !wp_verify_nonce($_POST['rmt_dashboard_nonce'], 'rmt_dashboard_action')
    ) {
        $error_message = 'Security check failed.';
    } else {
        $unpublish_post_id = isset($_POST['unpublish_post_id']) ? absint($_POST['unpublish_post_id']) : 0;

        if ($unpublish_post_id && get_post_field('post_author', $unpublish_post_id) == $current_user->ID) {
            wp_update_post([
                'ID'          => $unpublish_post_id,
                'post_status' => 'draft',
            ]);
            $success_message = 'Listing unpublished and moved to draft.';
        } else {
            $error_message = 'You are not allowed to unpublish this listing.';
        }
    }
}

$room_posts = new WP_Query(array(
    'post_type'      => 'room',
    'author'         => $current_user->ID,
    'post_status'    => array('publish', 'pending', 'draft'),
    'posts_per_page' => -1,
));

$roommate_posts = new WP_Query(array(
    'post_type'      => 'roommate',
    'author'         => $current_user->ID,
    'post_status'    => array('publish', 'pending', 'draft'),
    'posts_per_page' => -1,
));

get_header();
?>

<main id="primary" class="site-main dashboard-page">
    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Dashboard</span>
            <h1 class="archive-title">Welcome, <?php echo esc_html($current_user->display_name); ?></h1>
            <p class="archive-description">
                Manage your room and roommate listings, check their status, and keep your profile up to date.
            </p>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container">
            <?php if (!empty($success_message)) : ?>
                <div class="single-card" style="border-color:#89e219; margin-bottom: 1rem;">
                    <p style="margin:0; font-weight:700;"><?php echo esc_html($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)) : ?>
                <div class="single-card" style="border-color:#d9534f; margin-bottom: 1rem;">
                    <p style="margin:0; font-weight:700;"><?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['listing_submitted']) && $_GET['listing_submitted'] == '1') : ?>
                <div class="single-card" style="border-color:#89e219; margin-bottom:1rem;">
                    <p style="margin:0; font-weight:700;">Your listing has been submitted and is now live.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['listing_updated']) && $_GET['listing_updated'] == '1') : ?>
                <div class="single-card" style="border-color:#89e219; margin-bottom:1rem;">
                    <p style="margin:0; font-weight:700;">Your listing has been updated successfully.</p>
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
                        <a href="<?php echo esc_url(home_url('/post-listing')); ?>" class="btn btn-primary">
                            Post New Listing
                        </a>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-secondary">
                            Back to Home
                        </a>
                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn btn-secondary">
                            Log Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         MY ROOMS
    ═══════════════════════════════════════════════════════════ -->
    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">My Rooms</span>
                <h2>Your Room Listings</h2>
                <p>These are the room listings you have submitted.</p>
            </div>

            <?php if ($room_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($room_posts->have_posts()) : $room_posts->the_post(); ?>
                        <?php
                        $rent           = rmt_get_meta(get_the_ID(), '_rent');
                        $available_date = rmt_get_meta(get_the_ID(), '_available_date');
                        $address        = rmt_get_meta(get_the_ID(), '_address');
                        $post_status    = get_post_status();
                        $is_published   = ($post_status === 'publish');
                        ?>
                        <article class="listing-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <span class="listing-chip <?php echo !$is_published ? 'listing-chip--draft' : ''; ?>">
                                        <?php echo esc_html(ucfirst($post_status)); ?>
                                    </span>
                                    <?php if ($rent) : ?>
                                        <div class="listing-card__price"><?php echo esc_html(rmt_format_price($rent)); ?>/month</div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($address) : ?>
                                    <p class="listing-card__address"><?php echo esc_html($address); ?></p>
                                <?php endif; ?>

                                <?php if ($available_date) : ?>
                                    <p class="listing-card__address">Available: <?php echo esc_html($available_date); ?></p>
                                <?php endif; ?>

                                <div class="cta-actions" style="flex-wrap:wrap;gap:.5rem;">

                                    <?php if ($is_published) : ?>
                                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">View</a>
                                    <?php endif; ?>

                                    <!-- Edit: go to WP Admin editor for rooms (no custom front-end room editor yet) -->
                                    <a href="<?php echo esc_url(get_edit_post_link(get_the_ID())); ?>" class="btn btn-secondary">Edit</a>

                                    <?php if ($is_published) : ?>
                                        <!-- Unpublish (move to draft) -->
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="unpublish_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                            <button type="submit" name="rmt_unpublish_listing" value="1"
                                                class="btn btn-secondary"
                                                onclick="return confirm('Unpublish this listing? It will move to draft and won\'t be visible to others.');">
                                                Unpublish
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <!-- Delete only for non-published posts -->
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="delete_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                            <button type="submit" name="rmt_delete_listing" value="1"
                                                class="btn btn-secondary btn--danger"
                                                onclick="return confirm('Permanently delete this listing?');">
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

    <!-- ═══════════════════════════════════════════════════════════
         MY ROOMMATES
    ═══════════════════════════════════════════════════════════ -->
    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">My Roommates</span>
                <h2>Your Roommate Profiles</h2>
                <p>These are the roommate profiles you have submitted.</p>
            </div>

            <?php if ($roommate_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($roommate_posts->have_posts()) : $roommate_posts->the_post(); ?>
                        <?php
                        $budget_min     = rmt_get_meta(get_the_ID(), '_budget_min');
                        $budget_max     = rmt_get_meta(get_the_ID(), '_budget_max');
                        $move_in_date   = rmt_get_meta(get_the_ID(), '_move_in_date');
                        $preferred_area = rmt_get_meta(get_the_ID(), '_preferred_area_text');
                        $post_status    = get_post_status();
                        $is_published   = ($post_status === 'publish');

                        // Build the front-end edit URL
                        $edit_url = add_query_arg('edit_id', get_the_ID(), home_url('/edit-roommate/'));
                        ?>
                        <article class="listing-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <span class="listing-chip <?php echo !$is_published ? 'listing-chip--draft' : ''; ?>">
                                        <?php echo esc_html(ucfirst($post_status)); ?>
                                    </span>
                                    <div class="listing-card__price">
                                        <?php if ($budget_min) echo esc_html(rmt_format_price($budget_min)); ?>
                                        <?php if ($budget_min && $budget_max) echo ' – '; ?>
                                        <?php if ($budget_max) echo esc_html(rmt_format_price($budget_max)); ?>
                                    </div>
                                </div>

                                <h3 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>

                                <?php if ($preferred_area) : ?>
                                    <p class="listing-card__address">Preferred Area: <?php echo esc_html($preferred_area); ?></p>
                                <?php endif; ?>

                                <?php if ($move_in_date) : ?>
                                    <p class="listing-card__address">Move-in: <?php echo esc_html($move_in_date); ?></p>
                                <?php endif; ?>

                                <div class="cta-actions" style="flex-wrap:wrap;gap:.5rem;">

                                    <?php if ($is_published) : ?>
                                        <a href="<?php the_permalink(); ?>" class="btn btn-primary">View</a>
                                    <?php endif; ?>

                                    <!-- Edit: front-end edit page -->
                                    <a href="<?php echo esc_url($edit_url); ?>" class="btn btn-secondary">Edit</a>

                                    <?php if ($is_published) : ?>
                                        <!-- Unpublish (move to draft) -->
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="unpublish_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                            <button type="submit" name="rmt_unpublish_listing" value="1"
                                                class="btn btn-secondary"
                                                onclick="return confirm('Unpublish this profile? It will move to draft and won\'t be visible to others.');">
                                                Unpublish
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <!-- Delete only for draft/pending -->
                                        <form method="post" style="display:inline;">
                                            <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                            <input type="hidden" name="delete_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                            <button type="submit" name="rmt_delete_listing" value="1"
                                                class="btn btn-secondary btn--danger"
                                                onclick="return confirm('Permanently delete this profile?');">
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

<style>
/* Draft chip variant */
.listing-chip--draft {
    background: rgba(255,193,7,0.15);
    border-color: rgba(255,193,7,0.4);
    color: #b78a00;
}

/* Danger button */
.btn--danger {
    border-color: rgba(220,53,69,0.4) !important;
    color: #dc3545 !important;
}
.btn--danger:hover {
    background: rgba(220,53,69,0.08) !important;
}
</style>

<?php get_footer(); ?>