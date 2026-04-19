<?php
/**
 * Template Name: Profile Page
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$display_name = $current_user->display_name;
$user_email   = $current_user->user_email;
$registered   = date_i18n(get_option('date_format'), strtotime($current_user->user_registered));
$avatar_url   = get_avatar_url($user_id, ['size' => 120]);

$room_form_url     = home_url('/post-a-room/');
$roommate_form_url = home_url('/post-a-roommate/');

$success_message = '';
$error_message   = '';

/**
 * Handle delete action (draft/pending only)
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['rmt_delete_listing'])
) {
    if (
        !isset($_POST['rmt_profile_nonce']) ||
        !wp_verify_nonce($_POST['rmt_profile_nonce'], 'rmt_profile_action')
    ) {
        $error_message = 'Security check failed.';
    } else {
        $delete_post_id = absint($_POST['delete_post_id'] ?? 0);
        $post_status    = get_post_field('post_status', $delete_post_id);

        if ($delete_post_id && (int) get_post_field('post_author', $delete_post_id) === $user_id) {
            if (in_array($post_status, ['draft', 'pending'])) {
                wp_trash_post($delete_post_id);
                $success_message = 'Listing deleted successfully.';
            } else {
                $error_message = 'Only draft or unpublished listings can be deleted. Unpublish it from the dashboard first.';
            }
        } else {
            $error_message = 'You are not allowed to delete this listing.';
        }
    }
}

/**
 * Handle unpublish action
 */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['rmt_unpublish_listing'])
) {
    if (
        !isset($_POST['rmt_profile_nonce']) ||
        !wp_verify_nonce($_POST['rmt_profile_nonce'], 'rmt_profile_action')
    ) {
        $error_message = 'Security check failed.';
    } else {
        $unpublish_post_id = absint($_POST['unpublish_post_id'] ?? 0);

        if ($unpublish_post_id && (int) get_post_field('post_author', $unpublish_post_id) === $user_id) {
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

get_header();
?>

<main id="primary" class="site-main profile-page">
    <div class="container">

        <?php if (!empty($success_message)) : ?>
            <div class="pfl-notice pfl-notice--success">
                <span>✅</span> <?php echo esc_html($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)) : ?>
            <div class="pfl-notice pfl-notice--error">
                <span>⚠️</span> <?php echo esc_html($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['listing_updated']) && $_GET['listing_updated'] == '1') : ?>
            <div class="pfl-notice pfl-notice--success">
                <span>✅</span> Your listing has been updated successfully.
            </div>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?php echo esc_url($avatar_url); ?>"
                     alt="<?php echo esc_attr($display_name); ?>"
                     width="120" height="120">
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?php echo esc_html($display_name); ?></h1>
                <p class="profile-email"><?php echo esc_html($user_email); ?></p>
                <p class="profile-since">
                    <?php printf(esc_html__('Member since %s', 'roommate-mobile-theme'), esc_html($registered)); ?>
                </p>
            </div>
            <div class="profile-actions">
                <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="btn btn-secondary">
                    <?php esc_html_e('Edit Profile', 'roommate-mobile-theme'); ?>
                </a>
                <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn btn-outline">
                    <?php esc_html_e('Log Out', 'roommate-mobile-theme'); ?>
                </a>
            </div>
        </div>

        <!-- Post a Listing -->
        <section class="profile-post-listing">
            <h2><?php esc_html_e('Post a listing', 'roommate-mobile-theme'); ?></h2>
            <p class="profile-post-listing__subtitle">
                <?php esc_html_e("What's your situation?", 'roommate-mobile-theme'); ?>
            </p>

            <div class="listing-type-grid">
                <a href="<?php echo esc_url($room_form_url); ?>" class="listing-type-card">
                    <span class="listing-type-card__icon" aria-hidden="true">🏠</span>
                    <strong class="listing-type-card__title">
                        <?php esc_html_e('I already have a room', 'roommate-mobile-theme'); ?>
                    </strong>
                    <p class="listing-type-card__desc">
                        <?php esc_html_e('Post your room and find the right roommate to move in.', 'roommate-mobile-theme'); ?>
                    </p>
                </a>

                <a href="<?php echo esc_url($roommate_form_url); ?>" class="listing-type-card">
                    <span class="listing-type-card__icon" aria-hidden="true">🔍</span>
                    <strong class="listing-type-card__title">
                        <?php esc_html_e("I'm without a room", 'roommate-mobile-theme'); ?>
                    </strong>
                    <p class="listing-type-card__desc">
                        <?php esc_html_e('Create a profile so landlords and roommates can find you.', 'roommate-mobile-theme'); ?>
                    </p>
                </a>
            </div>
        </section>

        <!-- My Listings -->
        <section class="profile-listings">
            <h2><?php esc_html_e('My Listings', 'roommate-mobile-theme'); ?></h2>

            <?php
            $my_posts = new WP_Query([
                'post_type'      => ['room', 'roommate'],
                'author'         => $user_id,
                'posts_per_page' => 20,
                'post_status'    => ['publish', 'pending', 'draft'],
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            $status_labels = [
                'publish' => ['label' => 'Live',    'color' => '#166534', 'bg' => '#DCFCE7', 'border' => '#86EFAC'],
                'draft'   => ['label' => 'Draft',   'color' => '#92400e', 'bg' => '#FEF9C3', 'border' => '#FDE047'],
                'pending' => ['label' => 'Pending', 'color' => '#1e3a8a', 'bg' => '#DBEAFE', 'border' => '#93C5FD'],
            ];

            if ($my_posts->have_posts()) : ?>

                <ul class="pfl-list">
                    <?php while ($my_posts->have_posts()) : $my_posts->the_post();
                        $status     = get_post_status();
                        $post_type  = get_post_type();
                        $post_id    = get_the_ID();
                        $s          = $status_labels[$status] ?? ['label' => ucfirst($status), 'color' => '#333', 'bg' => '#eee', 'border' => '#ccc'];
                        $is_draft   = in_array($status, ['draft', 'pending']);
                        $is_live    = ($status === 'publish');
                        $type_label = get_post_type_object($post_type)->labels->singular_name ?? $post_type;

                        // Edit: roommate → front-end edit page, room → WP admin
                        $edit_url = ($post_type === 'roommate')
                            ? add_query_arg('edit_id', $post_id, home_url('/edit-roommate/'))
                            : get_edit_post_link($post_id);
                        ?>
                        <li class="pfl-item <?php echo $is_draft ? 'pfl-item--draft' : ''; ?>">

                            <div class="pfl-row-main">
                                <!-- Title -->
                                <a href="<?php the_permalink(); ?>" class="pfl-title"><?php the_title(); ?></a>

                                <!-- Type + Status -->
                                <div class="pfl-meta">
                                    <span class="pfl-type"><?php echo esc_html($type_label); ?></span>
                                    <span class="pfl-badge"
                                          style="color:<?php echo $s['color']; ?>;background:<?php echo $s['bg']; ?>;border-color:<?php echo $s['border']; ?>">
                                        <?php echo esc_html($s['label']); ?>
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="pfl-actions">

                                    <!-- Edit -->
                                    <a href="<?php echo esc_url($edit_url); ?>" class="pfl-btn">
                                        ✏️ Edit
                                    </a>

                                    <?php if ($is_live) : ?>
                                        <!-- Unpublish (move to draft) -->
                                        <form method="post" class="pfl-form">
                                            <?php wp_nonce_field('rmt_profile_action', 'rmt_profile_nonce'); ?>
                                            <input type="hidden" name="unpublish_post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <button type="submit" name="rmt_unpublish_listing" value="1"
                                                class="pfl-btn pfl-btn--warn"
                                                onclick="return confirm('Unpublish this listing? It will become a draft and won\'t be visible to others.');">
                                                📤 Unpublish
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($is_draft) : ?>
                                        <!-- Delete (draft/pending only) -->
                                        <form method="post" class="pfl-form">
                                            <?php wp_nonce_field('rmt_profile_action', 'rmt_profile_nonce'); ?>
                                            <input type="hidden" name="delete_post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <button type="submit" name="rmt_delete_listing" value="1"
                                                class="pfl-btn pfl-btn--danger"
                                                onclick="return confirm('Permanently delete this listing?');">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <?php if ($is_draft) : ?>
                                <p class="pfl-draft-note">
                                    ⚠️ This listing is unpublished — only you can see it. Unpublish a live listing first to enable deletion.
                                </p>
                            <?php endif; ?>

                        </li>
                    <?php endwhile; ?>
                </ul>
                <?php wp_reset_postdata(); ?>

            <?php else : ?>
                <p class="profile-listings__empty">
                    <?php esc_html_e("You haven't posted any listings yet.", 'roommate-mobile-theme'); ?>
                </p>
            <?php endif; ?>

        </section>

    </div>
</main>

<style>
/* ── Notices ──────────────────────────────────────────────── */
.pfl-notice {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .9rem 1.2rem;
    border-radius: var(--radius-md);
    font-weight: 700;
    font-size: .9rem;
    margin-bottom: 1.25rem;
}
.pfl-notice--success { background: #F0FDF4; border: 1px solid #86EFAC; color: #166534; }
.pfl-notice--error   { background: #FFF1F2; border: 1px solid #FCA5A5; color: #991b1b; }

/* ── List ─────────────────────────────────────────────────── */
.pfl-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

.pfl-item {
    padding: .9rem 1.1rem;
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
}

.pfl-item--draft {
    border-color: #FDE047;
    background: #FFFEF0;
}

/* ── Main row ─────────────────────────────────────────────── */
.pfl-row-main {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
}

/* Title */
.pfl-title {
    flex: 1;
    font-weight: 700;
    font-size: .95rem;
    color: var(--color-text);
    text-decoration: none;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pfl-title:hover { text-decoration: underline; }

/* Type + badge */
.pfl-meta {
    display: flex;
    align-items: center;
    gap: .45rem;
    flex-shrink: 0;
}
.pfl-type {
    font-size: .78rem;
    color: var(--color-text-muted);
    white-space: nowrap;
}
.pfl-badge {
    display: inline-flex;
    align-items: center;
    padding: .2rem .65rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    white-space: nowrap;
    border: 1px solid;
}

/* ── Action buttons ───────────────────────────────────────── */
.pfl-actions {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-shrink: 0;
}

.pfl-form { display: inline; margin: 0; padding: 0; }

.pfl-btn {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    font-size: .8rem;
    font-weight: 700;
    color: var(--color-text-soft);
    text-decoration: none;
    padding: .3rem .7rem;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: transparent;
    cursor: pointer;
    font-family: var(--font-body);
    transition: border-color .15s, color .15s, background .15s;
    white-space: nowrap;
    line-height: 1.4;
}
.pfl-btn:hover {
    border-color: var(--color-primary);
    color: var(--color-text);
    background: rgba(137, 226, 25, .07);
}

.pfl-btn--warn {
    border-color: rgba(234, 179, 8, .45);
    color: #92400e;
}
.pfl-btn--warn:hover {
    background: rgba(234, 179, 8, .1);
    border-color: #f59e0b;
    color: #78350f;
}

.pfl-btn--danger {
    border-color: rgba(220, 53, 69, .35);
    color: #dc2626;
}
.pfl-btn--danger:hover {
    background: rgba(220, 53, 69, .08);
    border-color: #dc2626;
    color: #b91c1c;
}

/* ── Draft note ───────────────────────────────────────────── */
.pfl-draft-note {
    font-size: .78rem;
    color: #92400e;
    margin: .45rem 0 0;
    padding: 0;
}
</style>

<?php get_footer(); ?>