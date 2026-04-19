<?php
/**
 * Template Name: Post a Roommate
 * Description: Allows users to post their roommate profile so others can connect with them.
 */

defined('ABSPATH') || exit;

// Handle form submission
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_post_roommate_nonce'])) {

    if (!wp_verify_nonce($_POST['rmt_post_roommate_nonce'], 'rmt_post_roommate')) {
        $errors[] = 'Security check failed. Please try again.';
    } elseif (!is_user_logged_in()) {
        $errors[] = 'You must be logged in to post a roommate profile.';
    } else {

        $nickname = sanitize_text_field($_POST['nickname'] ?? '');
        if (empty($nickname)) {
            $errors[] = 'Nickname / name is required.';
        }

        $gender = sanitize_text_field($_POST['gender'] ?? '');
        if (empty($gender)) {
            $errors[] = 'Gender is required.';
        }

        $bio = sanitize_textarea_field($_POST['bio'] ?? '');
        if (empty($bio)) {
            $errors[] = 'A short bio is required.';
        }

        $age = absint($_POST['age'] ?? 0);
        if ($age < 18 || $age > 80) {
            $errors[] = 'Please enter a valid age (18–80).';
        }

        if (empty($errors)) {

            $hobbies_raw = sanitize_text_field($_POST['hobbies'] ?? '');
            if (!empty($hobbies_raw)) {
                $post_title = $nickname . ', ' . $age . ' — ' . $hobbies_raw;
            } else {
                $post_title = $nickname . ', ' . $age;
            }

            $post_id = wp_insert_post([
                'post_title'   => $post_title,
                'post_content' => $bio,
                'post_status'  => 'publish',
                'post_type'    => 'roommate',
                'post_author'  => get_current_user_id(),
            ]);

            if (is_wp_error($post_id)) {
                $errors[] = 'Could not save the profile. Please try again.';
            } else {

                // Handle profile photo upload
                if (!empty($_FILES['profile_photo']['name'])) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                    require_once(ABSPATH . 'wp-admin/includes/media.php');

                    $attachment_id = media_handle_upload('profile_photo', $post_id);
                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                } else {
                    // Set default profile image as featured image
                    $default_image_path = get_template_directory() . '/images/default-profile.jpg';
                    $default_image_url  = get_template_directory_uri() . '/images/default-profile.jpg';

                    if (file_exists($default_image_path)) {
                        $filetype      = wp_check_filetype(basename($default_image_path), null);
                        $attachment    = [
                            'guid'           => $default_image_url,
                            'post_mime_type' => $filetype['type'],
                            'post_title'     => 'Default Profile',
                            'post_content'   => '',
                            'post_status'    => 'inherit',
                        ];
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_id = wp_insert_attachment($attachment, $default_image_path, $post_id);
                        if (!is_wp_error($attach_id)) {
                            $attach_data = wp_generate_attachment_metadata($attach_id, $default_image_path);
                            wp_update_attachment_metadata($attach_id, $attach_data);
                            set_post_thumbnail($post_id, $attach_id);
                        }
                    }
                }

                // Personal meta
                $personal_meta = [
                    '_nickname'   => 'nickname',
                    '_age'        => 'age',
                    '_gender'     => 'gender',
                    '_occupation' => 'occupation',
                    '_languages'  => 'languages',
                    '_hobbies'    => 'hobbies',
                ];
                foreach ($personal_meta as $meta_key => $post_key) {
                    if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
                    }
                }

                // Lifestyle meta
                $lifestyle_meta = [
                    '_cleanliness'    => 'cleanliness',
                    '_sleep_schedule' => 'sleep_schedule',
                    '_smoker'         => 'smoker',
                    '_has_pets'       => 'has_pets',
                    '_social_level'   => 'social_level',
                ];
                foreach ($lifestyle_meta as $meta_key => $post_key) {
                    if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
                    }
                }

                // Long-text meta
                if (!empty($_POST['bio'])) {
                    update_post_meta($post_id, '_bio', sanitize_textarea_field($_POST['bio']));
                }
                if (!empty($_POST['roommate_preference'])) {
                    update_post_meta($post_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference']));
                }

                // Room preference meta
                $pref_meta = [
                    '_budget'         => 'budget',
                    '_move_in_date'   => 'move_in_date',
                    '_preferred_area' => 'preferred_area',
                ];
                foreach ($pref_meta as $meta_key => $post_key) {
                    if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
                    }
                }

                // Taxonomies
                if (!empty($_POST['lifestyle']) && is_array($_POST['lifestyle'])) {
                    wp_set_post_terms($post_id, array_map('intval', $_POST['lifestyle']), 'lifestyle');
                }
                if (!empty($_POST['location_area']) && is_array($_POST['location_area'])) {
                    wp_set_post_terms($post_id, array_map('intval', $_POST['location_area']), 'location_area');
                }

                $success = true;
            }
        }
    }
}

$lifestyle_terms     = get_terms(['taxonomy' => 'lifestyle',     'hide_empty' => false]);
$location_area_terms = get_terms(['taxonomy' => 'location_area', 'hide_empty' => false]);

get_header();
?>

<style>
/* ── Page layout ─────────────────────────────────────────── */
.post-a-roommate { padding: var(--space-10) 0 var(--space-16); }

/* ── Page header ─────────────────────────────────────────── */
.par-page-header { margin-bottom: var(--space-8); }

.par-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(137,226,25,0.12);
    border: 1px solid rgba(137,226,25,0.28);
    color: var(--color-secondary);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    margin-bottom: 1rem;
}

.par-eyebrow-dot {
    width: 6px; height: 6px;
    background: var(--color-primary);
    border-radius: 50%;
    flex-shrink: 0;
}

.par-page-header h1 {
    font-size: clamp(2rem,5vw,3rem);
    letter-spacing: -0.02em;
    margin: 0 0 0.5rem;
}

.par-page-header > p {
    font-size: 1.05rem;
    color: var(--color-text-soft);
    margin: 0;
}

/* ── Two-column layout ───────────────────────────────────── */
.par-layout { display: grid; gap: 1.5rem; align-items: start; }

@media (min-width: 992px) {
    .par-layout { grid-template-columns: minmax(0,2fr) minmax(260px,0.85fr); }
}

/* ── Form cards ──────────────────────────────────────────── */
.par-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.25rem;
    transition: box-shadow var(--transition), border-color var(--transition);
}

.par-card:focus-within {
    box-shadow: var(--shadow-md);
    border-color: rgba(137,226,25,0.35);
}

.par-card__header {
    display: flex; align-items: center; gap: 0.85rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.1rem;
    border-bottom: 1px solid var(--color-border);
}

.par-card__icon {
    width: 42px; height: 42px;
    background: rgba(137,226,25,0.14);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0; line-height: 1;
}

.par-card__header h2 { font-size: 1.15rem; margin: 0 0 0.15rem; }
.par-card__header p  { font-size: 0.85rem; color: var(--color-text-muted); margin: 0; }

/* ── Field rows ──────────────────────────────────────────── */
.par-field { display: flex; flex-direction: column; gap: 0.45rem; margin-bottom: 1.15rem; }
.par-field:last-child { margin-bottom: 0; }

.par-field > label {
    font-size: 0.88rem; font-weight: 700;
    color: var(--color-text);
    display: flex; align-items: center; gap: 0.35rem;
}

.par-field .required { color: #E63946; font-size: 0.8rem; }
.par-field > small   { font-size: 0.8rem; color: var(--color-text-muted); }

.par-cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.par-cols-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

@media (max-width: 600px) {
    .par-cols-2, .par-cols-3 { grid-template-columns: 1fr; }
}

/* ── Inputs / Selects / Textareas ────────────────────────── */
.par-input,
.par-select,
.par-textarea {
    width: 100%; min-height: 48px;
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: var(--color-white);
    color: var(--color-text);
    font-size: 0.95rem;
    font-family: var(--font-body);
    transition: border-color var(--transition), box-shadow var(--transition);
    appearance: none; -webkit-appearance: none;
}

.par-textarea { min-height: 110px; resize: vertical; }
.par-input::placeholder, .par-textarea::placeholder { color: var(--color-text-muted); }

.par-input:focus, .par-select:focus, .par-textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px rgba(137,226,25,0.18);
}

.par-input:hover, .par-select:hover, .par-textarea:hover {
    border-color: var(--color-text-muted);
}

/* Select arrow */
.par-select-wrap { position: relative; }
.par-select-wrap::after {
    content: '▾';
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    font-size: 0.85rem; color: var(--color-text-muted); pointer-events: none;
}
.par-select { padding-right: 2.5rem; cursor: pointer; }

/* ── Checkbox pill group ─────────────────────────────────── */
.par-checkbox-group { display: flex; flex-wrap: wrap; gap: 0.55rem; }

.par-checkbox-label { cursor: pointer; }
.par-checkbox-label input[type="checkbox"] { display: none; }

.par-checkbox-label span {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.48rem 1rem;
    border-radius: 999px;
    border: 1.5px solid var(--color-border);
    background: var(--color-surface);
    font-size: 0.88rem; font-weight: 700;
    color: var(--color-text-soft);
    transition: var(--transition);
}

.par-checkbox-label span::before {
    content: '';
    width: 14px; height: 14px;
    border-radius: 4px;
    border: 1.5px solid var(--color-border);
    background: var(--color-white);
    flex-shrink: 0;
    transition: var(--transition);
}

.par-checkbox-label input[type="checkbox"]:checked + span {
    background: #F8FCEB;
    border-color: var(--color-primary);
    color: var(--color-secondary-dark);
}

.par-checkbox-label input[type="checkbox"]:checked + span::before {
    background: var(--color-primary);
    border-color: var(--color-primary);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 10 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3 3 5-6' stroke='%23111' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 10px;
}

.par-checkbox-label span:hover { border-color: var(--color-primary); color: var(--color-text); }

/* ── Range slider ────────────────────────────────────────── */
.par-range-wrap { display: flex; align-items: center; gap: 1rem; }

.par-range {
    flex: 1;
    -webkit-appearance: none; appearance: none;
    height: 6px; background: var(--color-border);
    border-radius: 999px; outline: none; cursor: pointer;
}

.par-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--color-primary);
    box-shadow: 0 2px 8px rgba(137,226,25,0.4);
    cursor: pointer; transition: transform var(--transition);
}

.par-range::-webkit-slider-thumb:hover { transform: scale(1.15); }
.par-range::-moz-range-thumb {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--color-primary); border: none;
}

.par-range-val {
    min-width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    background: var(--color-primary); color: var(--color-black);
    font-weight: 800; font-size: 0.9rem;
    border-radius: 10px; flex-shrink: 0;
}

/* ── Profile photo upload ────────────────────────────────── */
.par-photo-upload {
    display: flex; align-items: center; gap: 1.25rem;
    padding: 1rem;
    border: 1.5px dashed var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface);
}

.par-photo-upload img {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--color-border);
    flex-shrink: 0;
}

.par-photo-upload__right {
    display: flex; flex-direction: column; gap: 0.5rem;
}

.par-photo-hint {
    font-size: 0.8rem;
    color: var(--color-text-muted);
}

/* ── Submit row ──────────────────────────────────────────── */
.par-submit-row {
    display: flex; align-items: center; gap: 1rem;
    flex-wrap: wrap; margin-top: 0.5rem;
}

.par-submit-row .btn { padding: 1rem 2rem; font-size: 1rem; }
.par-submit-row small { font-size: 0.82rem; color: var(--color-text-muted); }

/* ── Notices ─────────────────────────────────────────────── */
.par-notice {
    display: flex; gap: 1rem; align-items: flex-start;
    padding: 1.2rem 1.4rem;
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.par-notice__icon { font-size: 1.3rem; flex-shrink: 0; line-height: 1; margin-top: 1px; }
.par-notice__body p { margin: 0 0 0.75rem; }
.par-notice__body p:last-child { margin: 0; }
.par-notice--success { background: #F0FDF4; border: 1px solid #86EFAC; }
.par-notice--error   { background: #FFF1F2; border: 1px solid #FCA5A5; }
.par-notice--error ul { margin: 0; padding-left: 1.25rem; color: var(--color-text-soft); }
.par-notice--warning { background: #FEFCE8; border: 1px solid #FDE047; }

/* ── Sticky sidebar ──────────────────────────────────────── */
.par-sidebar {
    position: sticky; top: 90px;
    display: flex; flex-direction: column; gap: 1.25rem;
}

/* Tips card */
.par-tip-card {
    background: linear-gradient(135deg,#4B4B4B 0%,#363636 100%);
    border-radius: var(--radius-lg);
    padding: 1.4rem;
    box-shadow: var(--shadow-md);
}

.par-tip-card h3 { color: #fff; font-size: 1rem; margin: 0 0 1rem; }

.par-tip-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.par-tip-list li {
    display: flex; gap: 0.7rem;
    font-size: 0.85rem; color: rgba(255,255,255,0.78);
    align-items: flex-start; line-height: 1.5;
}

.par-tip-list li::before {
    content: '✓';
    width: 20px; height: 20px;
    background: var(--color-primary); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; color: var(--color-black); font-weight: 900;
    flex-shrink: 0; margin-top: 1px;
}

/* ── Success page ────────────────────────────────────────── */
.par-success {
    text-align: center;
    padding: var(--space-12) var(--space-8);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.par-success__icon {
    width: 72px; height: 72px;
    background: var(--color-primary); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; margin: 0 auto 1.5rem;
    box-shadow: 0 8px 24px rgba(137,226,25,0.4);
}

.par-success h2 { font-size: 1.8rem; margin-bottom: 0.75rem; }
.par-success > p { max-width: 46ch; margin: 0 auto 2rem; }
</style>

<main id="primary" class="site-main single-page post-a-roommate">
    <div class="container">

        <!-- Page Header -->
        <div class="par-page-header">
            <div class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                Need a Room
            </div>
            <h1>Post a Roommate Profile</h1>
            <p>Tell people who you are, your lifestyle, and what kind of room you're looking for.</p>
        </div>

        <?php if ($success) : ?>

            <div class="par-success">
                <div class="par-success__icon">🎉</div>
                <h2>Profile Published!</h2>
                <p>Your roommate profile is now live and visible to others.</p>
                <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>">Back to Home</a>
                    <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>">Submit Another</a>
                </div>
            </div>

        <?php elseif (!is_user_logged_in()) : ?>

            <div class="par-notice par-notice--warning">
                <span class="par-notice__icon">🔒</span>
                <div class="par-notice__body">
                    <p>You need to be logged in to post a roommate profile.</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">Log In</a>
                </div>
            </div>

        <?php else : ?>

            <?php if (!empty($errors)) : ?>
                <div class="par-notice par-notice--error">
                    <span class="par-notice__icon">⚠️</span>
                    <div class="par-notice__body">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="par-layout">

                <!-- ──────────── MAIN FORM ──────────── -->
                <form id="roommate-form" method="post" enctype="multipart/form-data" novalidate>
                    <?php wp_nonce_field('rmt_post_roommate', 'rmt_post_roommate_nonce'); ?>

                    <!-- ① About You -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">👤</div>
                            <div>
                                <h2>About You</h2>
                                <p>Basic personal info so potential roommates know who you are.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="profile_photo">Profile Photo</label>
                            <small>Upload a clear photo of yourself. Max 5MB. JPG or PNG.</small>
                            <div class="par-photo-upload">
                                <img id="photo-preview" src="<?php echo esc_url(get_template_directory_uri() . '/images/default-profile.jpg'); ?>" alt="Profile photo preview">
                                <div class="par-photo-upload__right">
                                    <label for="profile_photo" class="btn btn-secondary" style="cursor:pointer;display:inline-block;">
                                        Choose Photo
                                    </label>
                                    <input type="file" id="profile_photo" name="profile_photo"
                                        accept="image/jpeg,image/png,image/webp"
                                        style="display:none;"
                                        onchange="
                                            if (this.files && this.files[0]) {
                                                var reader = new FileReader();
                                                reader.onload = function(e) {
                                                    document.getElementById('photo-preview').src = e.target.result;
                                                };
                                                reader.readAsDataURL(this.files[0]);
                                            }
                                        ">
                                    <span class="par-photo-hint">If left empty, a default avatar will be used.</span>
                                </div>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="nickname">Name / Nickname <span class="required">*</span></label>
                                <input class="par-input" type="text" id="nickname" name="nickname"
                                    value="<?php echo esc_attr($_POST['nickname'] ?? ''); ?>"
                                    placeholder="e.g. Nook, Lek, Jamie" required>
                            </div>
                            <div class="par-field">
                                <label for="age">Age <span class="required">*</span></label>
                                <input class="par-input" type="number" id="age" name="age"
                                    value="<?php echo esc_attr($_POST['age'] ?? ''); ?>"
                                    min="18" max="80" placeholder="e.g. 25" required>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="gender">Gender <span class="required">*</span></label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="gender" name="gender" required>
                                        <option value="">— Select —</option>
                                        <?php foreach (['Male','Female','Non-binary','Prefer not to say'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['gender'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="occupation">Occupation</label>
                                <input class="par-input" type="text" id="occupation" name="occupation"
                                    value="<?php echo esc_attr($_POST['occupation'] ?? ''); ?>"
                                    placeholder="e.g. Graphic Designer, Student">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="languages">Languages Spoken</label>
                                <input class="par-input" type="text" id="languages" name="languages"
                                    value="<?php echo esc_attr($_POST['languages'] ?? ''); ?>"
                                    placeholder="e.g. Thai, English">
                            </div>
                            <div class="par-field">
                                <label for="hobbies">Hobbies / Interests</label>
                                <input class="par-input" type="text" id="hobbies" name="hobbies"
                                    value="<?php echo esc_attr($_POST['hobbies'] ?? ''); ?>"
                                    placeholder="e.g. Reading, Cooking, Yoga">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="bio">About Me <span class="required">*</span></label>
                            <textarea class="par-textarea" id="bio" name="bio" rows="5"
                                placeholder="Share a bit about yourself — your personality, daily routine, and what makes you a great roommate…"
                                required><?php echo esc_textarea($_POST['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- ② Lifestyle -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🌿</div>
                            <div>
                                <h2>Lifestyle</h2>
                                <p>Help potential roommates gauge compatibility.</p>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="cleanliness">Cleanliness Level</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="cleanliness" name="cleanliness">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Very tidy','Tidy','Average','Relaxed'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['cleanliness'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="sleep_schedule">Sleep Schedule</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="sleep_schedule" name="sleep_schedule">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Early bird','Night owl','Flexible'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['sleep_schedule'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="smoker">Do You Smoke?</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="smoker" name="smoker">
                                        <option value="">— Select —</option>
                                        <?php foreach (['No','Outside only','Yes'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['smoker'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="has_pets">Do You Have Pets?</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="has_pets" name="has_pets">
                                        <option value="">— Select —</option>
                                        <?php foreach (['No','Yes — cat','Yes — dog','Yes — other'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['has_pets'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="social_level">Social Level</label>
                            <small>1 = very private &nbsp;·&nbsp; 10 = very social</small>
                            <div class="par-range-wrap" style="margin-top:0.5rem">
                                <input class="par-range" type="range" id="social_level" name="social_level"
                                    min="1" max="10"
                                    value="<?php echo esc_attr($_POST['social_level'] ?? 5); ?>"
                                    oninput="document.getElementById('sl_val').textContent = this.value">
                                <span class="par-range-val" id="sl_val"><?php echo esc_html($_POST['social_level'] ?? 5); ?></span>
                            </div>
                        </div>

                        <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                            <div class="par-field">
                                <label>Lifestyle Tags</label>
                                <div class="par-checkbox-group">
                                    <?php
                                    $selected_lifestyles = array_map('intval', (array)($_POST['lifestyle'] ?? []));
                                    foreach ($lifestyle_terms as $term) :
                                    ?>
                                        <label class="par-checkbox-label">
                                            <input type="checkbox" name="lifestyle[]"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php echo in_array($term->term_id, $selected_lifestyles) ? 'checked' : ''; ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ③ Room Preferences -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🏠</div>
                            <div>
                                <h2>Room Preferences</h2>
                                <p>What are you looking for in a room?</p>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="budget">Monthly Budget (฿)</label>
                                <input class="par-input" type="number" id="budget" name="budget" min="0"
                                    value="<?php echo esc_attr($_POST['budget'] ?? ''); ?>"
                                    placeholder="e.g. 8000">
                            </div>
                            <div class="par-field">
                                <label for="move_in_date">Preferred Move-in Date</label>
                                <input class="par-input" type="date" id="move_in_date" name="move_in_date"
                                    value="<?php echo esc_attr($_POST['move_in_date'] ?? ''); ?>"
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="preferred_area">Preferred Area / Neighbourhood</label>
                            <input class="par-input" type="text" id="preferred_area" name="preferred_area"
                                value="<?php echo esc_attr($_POST['preferred_area'] ?? ''); ?>"
                                placeholder="e.g. Silom, On Nut, Ari">
                        </div>

                        <?php if (!empty($location_area_terms) && !is_wp_error($location_area_terms)) : ?>
                            <div class="par-field">
                                <label>Location Areas</label>
                                <div class="par-checkbox-group">
                                    <?php
                                    $selected_locs = array_map('intval', (array)($_POST['location_area'] ?? []));
                                    foreach ($location_area_terms as $term) :
                                    ?>
                                        <label class="par-checkbox-label">
                                            <input type="checkbox" name="location_area[]"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php echo in_array($term->term_id, $selected_locs) ? 'checked' : ''; ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ④ Preferred Roommate -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🔍</div>
                            <div>
                                <h2>Preferred Roommate</h2>
                                <p>Describe who you'd love to live with.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="roommate_preference">Who are you looking for?</label>
                            <textarea class="par-textarea" id="roommate_preference" name="roommate_preference" rows="5"
                                placeholder="Describe your ideal roommate — lifestyle, habits, deal-breakers…"><?php echo esc_textarea($_POST['roommate_preference'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="par-submit-row">
                        <button type="submit" class="btn btn-primary">Submit Profile →</button>
                        <small>Your profile will be published immediately.</small>
                    </div>

                </form>

                <!-- ──────────── SIDEBAR ──────────── -->
                <aside class="par-sidebar">

                    <div class="par-tip-card">
                        <h3>💡 Tips for a great profile</h3>
                        <ul class="par-tip-list">
                            <li>Be honest about your lifestyle — compatibility matters more than impressions.</li>
                            <li>Mention your work/study schedule so roommates know when you're home.</li>
                            <li>Describe your ideal home vibe: quiet study space, social, etc.</li>
                            <li>Note any deal-breakers upfront to save everyone time.</li>
                        </ul>
                    </div>

                    <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1.4rem;box-shadow:var(--shadow-sm);text-align:center;">
                        <p style="font-size:0.82rem;color:var(--color-text-muted);margin-bottom:1rem;">
                            Have a room to offer instead?
                        </p>
                        <a href="<?php echo esc_url(home_url('/post-a-room/')); ?>" class="btn btn-secondary" style="width:100%;">
                            Post a Room Listing
                        </a>
                    </div>

                </aside>

            </div><!-- .par-layout -->

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>