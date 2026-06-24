<?php
/**
 * Template Name: Edit Profile
 * Description: Frontend account profile editor.
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user    = wp_get_current_user();
$errors          = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_edit_profile_nonce'])) {
    if (!wp_verify_nonce($_POST['rmt_edit_profile_nonce'], 'rmt_edit_profile')) {
        $errors[] = 'Security check failed. Please try again.';
    } else {
        $display_name = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
        $first_name   = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $last_name    = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $nickname     = sanitize_text_field(wp_unslash($_POST['nickname'] ?? ''));
        $email        = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
        $description  = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        $user_url     = esc_url_raw(wp_unslash($_POST['user_url'] ?? ''));
        $password     = (string) wp_unslash($_POST['current_password'] ?? '');

        if ($display_name === '') {
            $errors[] = 'Display name is required.';
        }

        if ($nickname === '') {
            $errors[] = 'Nickname is required.';
        }

        if ($email === '' || !is_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $email_owner = email_exists($email);

            if ($email_owner && (int) $email_owner !== (int) $current_user->ID) {
                $errors[] = 'That email address is already used by another account.';
            }

            if (strtolower($email) !== strtolower($current_user->user_email) && !wp_check_password($password, $current_user->user_pass, $current_user->ID)) {
                $errors[] = 'Enter your current password to change your email address.';
            }
        }

        if (empty($errors)) {
            $updated = wp_update_user([
                'ID'           => $current_user->ID,
                'display_name' => $display_name,
                'nickname'     => $nickname,
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'user_email'   => $email,
                'description'  => $description,
                'user_url'     => $user_url,
            ]);

            if (is_wp_error($updated)) {
                $errors[] = $updated->get_error_message();
            } else {
                wp_safe_redirect(add_query_arg('profile_updated', '1', home_url('/edit-profile/')));
                exit;
            }
        }
    }
}

if (isset($_GET['profile_updated'])) {
    $success_message = 'Profile updated successfully.';
}

$current_user = wp_get_current_user();
$is_post      = $_SERVER['REQUEST_METHOD'] === 'POST';

$v_display_name = $is_post ? sanitize_text_field(wp_unslash($_POST['display_name'] ?? '')) : $current_user->display_name;
$v_first_name   = $is_post ? sanitize_text_field(wp_unslash($_POST['first_name'] ?? '')) : get_user_meta($current_user->ID, 'first_name', true);
$v_last_name    = $is_post ? sanitize_text_field(wp_unslash($_POST['last_name'] ?? '')) : get_user_meta($current_user->ID, 'last_name', true);
$v_nickname     = $is_post ? sanitize_text_field(wp_unslash($_POST['nickname'] ?? '')) : get_user_meta($current_user->ID, 'nickname', true);
$v_email        = $is_post ? sanitize_email(wp_unslash($_POST['user_email'] ?? '')) : $current_user->user_email;
$v_description  = $is_post ? sanitize_textarea_field(wp_unslash($_POST['description'] ?? '')) : get_user_meta($current_user->ID, 'description', true);
$v_user_url     = $is_post ? esc_url_raw(wp_unslash($_POST['user_url'] ?? '')) : $current_user->user_url;

if ($v_nickname === '') {
    $v_nickname = $current_user->user_login;
}

get_header();
?>

<main id="primary" class="site-main edit-profile-page">
    <div class="container">
        <header class="par-page-header">
            <span class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                Account Settings
            </span>

            <h1>Edit Profile</h1>
            <p>Update the account details shown across your dashboard and listings.</p>
        </header>

        <?php if (!empty($success_message)) : ?>
            <div class="rmt-notice rmt-notice--success">
                <p><?php echo esc_html($success_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)) : ?>
            <div class="par-alert par-alert--error">
                <ul>
                    <?php foreach ($errors as $error) : ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <?php wp_nonce_field('rmt_edit_profile', 'rmt_edit_profile_nonce'); ?>

            <div class="par-layout edit-profile-layout">
                <div>
                    <section class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon" aria-hidden="true">ID</div>
                            <div>
                                <h2>Profile Details</h2>
                                <p>These details help identify your account to other users.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="display_name">Display Name <span class="required">*</span></label>
                            <input class="par-input" type="text" id="display_name" name="display_name" value="<?php echo esc_attr($v_display_name); ?>" required>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="first_name">First Name</label>
                                <input class="par-input" type="text" id="first_name" name="first_name" value="<?php echo esc_attr($v_first_name); ?>">
                            </div>

                            <div class="par-field">
                                <label for="last_name">Last Name</label>
                                <input class="par-input" type="text" id="last_name" name="last_name" value="<?php echo esc_attr($v_last_name); ?>">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="nickname">Nickname <span class="required">*</span></label>
                            <input class="par-input" type="text" id="nickname" name="nickname" value="<?php echo esc_attr($v_nickname); ?>" required>
                        </div>

                        <div class="par-field">
                            <label for="description">About You</label>
                            <textarea class="par-textarea" id="description" name="description" rows="5" placeholder="A short note about yourself."><?php echo esc_textarea($v_description); ?></textarea>
                        </div>
                    </section>

                    <section class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon" aria-hidden="true">@</div>
                            <div>
                                <h2>Contact Details</h2>
                                <p>Your email is used for login and account notifications.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="user_email">Email Address <span class="required">*</span></label>
                            <input class="par-input" type="email" id="user_email" name="user_email" value="<?php echo esc_attr($v_email); ?>" autocomplete="email" required>
                            <small>Changing your email requires your current password.</small>
                        </div>

                        <div class="par-field">
                            <label for="user_url">Website or Social Link</label>
                            <input class="par-input" type="url" id="user_url" name="user_url" value="<?php echo esc_attr($v_user_url); ?>" placeholder="https://">
                        </div>

                        <div class="par-field">
                            <label for="current_password">Current Password</label>
                            <input class="par-input" type="password" id="current_password" name="current_password" autocomplete="current-password">
                            <small>Only required when changing your email address.</small>
                        </div>
                    </section>
                </div>

                <aside class="par-sidebar">
                    <div class="par-card par-sticky edit-profile-summary-card">
                        <div class="edit-profile-avatar">
                            <?php echo get_avatar($current_user->ID, 96, rmt_get_default_profile_photo_url(), $current_user->display_name, ['class' => 'edit-profile-avatar__image']); ?>
                        </div>

                        <h2><?php echo esc_html($current_user->display_name); ?></h2>
                        <ul class="detail-list">
                            <li><strong>Username:</strong> <?php echo esc_html($current_user->user_login); ?></li>
                            <li><strong>Email:</strong> <?php echo esc_html($current_user->user_email); ?></li>
                        </ul>

                        <div class="cta-actions u-mt-4">
                            <button type="submit" class="btn btn-primary">Save Profile</button>
                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn btn-secondary">Back to Dashboard</a>
                            <a href="<?php echo esc_url(wp_lostpassword_url(home_url('/edit-profile/'))); ?>" class="btn btn-outline">Change Password</a>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>

<?php get_footer(); ?>
