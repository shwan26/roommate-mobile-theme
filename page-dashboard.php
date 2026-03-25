<?php
/**
 * Template Name: User Dashboard
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$success_message = '';
$error_message   = '';

/**
 * Handle delete action
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

        if ($delete_post_id && get_post_field('post_author', $delete_post_id) == $current_user->ID) {
            wp_trash_post($delete_post_id);
            $success_message = 'Listing moved to trash successfully.';
        } else {
            $error_message = 'You are not allowed to delete this listing.';
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

    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">My Rooms</span>
                <h2>Your Room listings</h2>
                <p>These are the room listings you have submitted.</p>
            </div>

            <?php if ($room_posts->have_posts()) : ?>
                <div class="listing-grid">
                    <?php while ($room_posts->have_posts()) : $room_posts->the_post(); ?>
                        <?php
                        $rent           = rmt_get_meta(get_the_ID(), '_rent');
                        $available_date = rmt_get_meta(get_the_ID(), '_available_date');
                        $address        = rmt_get_meta(get_the_ID(), '_address');
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
                                    <span class="listing-chip"><?php echo esc_html(ucfirst(get_post_status())); ?></span>
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

                                <div class="cta-actions">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View</a>
                                    <a href="<?php echo esc_url(get_edit_post_link(get_the_ID())); ?>" class="btn btn-secondary">Edit</a>

                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                        <input type="hidden" name="delete_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                        <button type="submit" name="rmt_delete_listing" value="1" class="btn btn-secondary">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Room listings yet</h3>
                    <p>You have not submitted any room listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <div class="section-heading">
                <span class="section-badge">My Roommates</span>
                <h2>Your Roommate profiles</h2>
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
                                    <span class="listing-chip"><?php echo esc_html(ucfirst(get_post_status())); ?></span>
                                    <div class="listing-card__price">
                                        <?php if ($budget_min) : ?>
                                            <?php echo esc_html(rmt_format_price($budget_min)); ?>
                                        <?php endif; ?>
                                        <?php if ($budget_min && $budget_max) : ?>
                                            -
                                        <?php endif; ?>
                                        <?php if ($budget_max) : ?>
                                            <?php echo esc_html(rmt_format_price($budget_max)); ?>
                                        <?php endif; ?>
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

                                <div class="cta-actions">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">View</a>
                                    <a href="<?php echo esc_url(get_edit_post_link(get_the_ID())); ?>" class="btn btn-secondary">Edit</a>

                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('rmt_dashboard_action', 'rmt_dashboard_nonce'); ?>
                                        <input type="hidden" name="delete_post_id" value="<?php echo esc_attr(get_the_ID()); ?>">
                                        <button type="submit" name="rmt_delete_listing" value="1" class="btn btn-secondary">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="empty-state">
                    <h3>No Roommate profiles yet</h3>
                    <p>You have not submitted any roommate profiles.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>