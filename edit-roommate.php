<?php
/**
 * Template Name: Edit Roommate Profile
 * Description: Front-end edit form for a roommate post, pre-filled with saved data.
 *              Access via: /edit-roommate/?edit_id=POST_ID
 */

defined('ABSPATH') || exit;

// Must be logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$errors       = [];

// ── Resolve the post being edited ────────────────────────────────────────────
$edit_id = isset($_GET['edit_id']) ? absint($_GET['edit_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_edit_id'])) {
    $edit_id = absint($_POST['rmt_edit_id']);
}

if (!$edit_id) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

$post = get_post($edit_id);

// Validate: must exist, be a roommate, and belong to the current user
if (
    !$post ||
    $post->post_type !== 'roommate' ||
    (int) $post->post_author !== $current_user->ID
) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

/**
 * Read post meta, trying multiple possible key names in order.
 * Returns the first non-empty value found, or '' if none.
 */
function rmt_edit_get($post_id, ...$keys) {
    foreach ($keys as $key) {
        $v = get_post_meta($post_id, $key, true);
        if ($v !== '' && $v !== false && $v !== null) {
            return $v;
        }
    }
    return '';
}

// ── Handle form submission ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_edit_roommate_nonce'])) {

    if (!wp_verify_nonce($_POST['rmt_edit_roommate_nonce'], 'rmt_edit_roommate_' . $edit_id)) {
        $errors[] = 'Security check failed. Please try again.';
    } else {

        $nickname = sanitize_text_field($_POST['nickname'] ?? '');
        if (empty($nickname)) $errors[] = 'Nickname / name is required.';

        $gender = sanitize_text_field($_POST['gender'] ?? '');
        if (empty($gender)) $errors[] = 'Gender is required.';

        $bio = sanitize_textarea_field($_POST['bio'] ?? '');
        if (empty($bio)) $errors[] = 'A short bio is required.';

        $age = absint($_POST['age'] ?? 0);
        if ($age < 18 || $age > 80) $errors[] = 'Please enter a valid age (18–80).';

        if (empty($errors)) {

            $hobbies_raw = sanitize_text_field($_POST['hobbies'] ?? '');
            $post_title  = $hobbies_raw
                ? $nickname . ', ' . $age . ' — ' . $hobbies_raw
                : $nickname . ', ' . $age;

            // Keep existing status, or re-publish if it was a draft
            $new_status = in_array($post->post_status, ['draft', 'pending']) ? 'publish' : $post->post_status;

            $result = wp_update_post([
                'ID'           => $edit_id,
                'post_title'   => $post_title,
                'post_content' => $bio,
                'post_status'  => $new_status,
            ], true);

            if (is_wp_error($result)) {
                $errors[] = 'Could not update the profile. Please try again.';
            } else {

                // Profile photo
                if (!empty($_FILES['profile_photo']['name'])) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                    $attachment_id = media_handle_upload('profile_photo', $edit_id);
                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($edit_id, $attachment_id);
                    }
                }

                // Personal meta
                foreach ([
                    '_nickname'   => 'nickname',
                    '_age'        => 'age',
                    '_gender'     => 'gender',
                    '_occupation' => 'occupation',
                    '_languages'  => 'languages',
                    '_hobbies'    => 'hobbies',
                ] as $meta_key => $post_key) {
                    update_post_meta($edit_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                // Lifestyle meta
                foreach ([
                    '_cleanliness'    => 'cleanliness',
                    '_sleep_schedule' => 'sleep_schedule',
                    '_smoker'         => 'smoker',
                    '_has_pets'       => 'has_pets',
                    '_social_level'   => 'social_level',
                ] as $meta_key => $post_key) {
                    update_post_meta($edit_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                // Bio (also stored in post_content above)
                update_post_meta($edit_id, '_bio', $bio);
                update_post_meta($edit_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference'] ?? ''));

                // Budget — write to both split keys AND legacy single key
                $budget_min = sanitize_text_field($_POST['budget_min'] ?? '');
                $budget_max = sanitize_text_field($_POST['budget_max'] ?? '');
                update_post_meta($edit_id, '_budget_min', $budget_min);
                update_post_meta($edit_id, '_budget_max', $budget_max);
                if ($budget_min) {
                    update_post_meta($edit_id, '_budget', $budget_min); // legacy key
                }

                // Move-in date
                update_post_meta($edit_id, '_move_in_date', sanitize_text_field($_POST['move_in_date'] ?? ''));

                // Preferred area — write to both key variants
                $pref_area = sanitize_text_field($_POST['preferred_area'] ?? '');
                update_post_meta($edit_id, '_preferred_area_text', $pref_area);
                update_post_meta($edit_id, '_preferred_area',      $pref_area); // legacy key

                // Taxonomies
                wp_set_post_terms(
                    $edit_id,
                    (!empty($_POST['lifestyle']) && is_array($_POST['lifestyle']))
                        ? array_map('intval', $_POST['lifestyle'])
                        : [],
                    'lifestyle'
                );
                wp_set_post_terms(
                    $edit_id,
                    (!empty($_POST['location_area']) && is_array($_POST['location_area']))
                        ? array_map('intval', $_POST['location_area'])
                        : [],
                    'location_area'
                );

                wp_redirect(add_query_arg('listing_updated', '1', home_url('/dashboard/')));
                exit;
            }
        }
    }
}

// ── Pre-fill values ───────────────────────────────────────────────────────────
// On a failed POST, keep submitted values so the user doesn't re-type everything.
// On a fresh GET, read from DB — trying multiple meta key variants so both old
// posts (created by post-a-roommate.php) and already-edited posts are covered.

$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');

$v_nickname       = $is_post ? sanitize_text_field($_POST['nickname']       ?? '') : rmt_edit_get($edit_id, '_nickname');
$v_age            = $is_post ? absint($_POST['age']                          ?? 0) : rmt_edit_get($edit_id, '_age');
$v_gender         = $is_post ? sanitize_text_field($_POST['gender']         ?? '') : rmt_edit_get($edit_id, '_gender');
$v_occupation     = $is_post ? sanitize_text_field($_POST['occupation']     ?? '') : rmt_edit_get($edit_id, '_occupation');
$v_languages      = $is_post ? sanitize_text_field($_POST['languages']      ?? '') : rmt_edit_get($edit_id, '_languages');
$v_hobbies        = $is_post ? sanitize_text_field($_POST['hobbies']        ?? '') : rmt_edit_get($edit_id, '_hobbies');

// Bio: try _bio meta first, then fall back to the post_content field
$v_bio = $is_post ? sanitize_textarea_field($_POST['bio'] ?? '') : rmt_edit_get($edit_id, '_bio');
if (!$v_bio && !$is_post) {
    $v_bio = $post->post_content; // post-a-roommate.php stored bio as post content
}

$v_cleanliness    = $is_post ? sanitize_text_field($_POST['cleanliness']    ?? '') : rmt_edit_get($edit_id, '_cleanliness');
$v_sleep_schedule = $is_post ? sanitize_text_field($_POST['sleep_schedule'] ?? '') : rmt_edit_get($edit_id, '_sleep_schedule');
$v_smoker         = $is_post ? sanitize_text_field($_POST['smoker']         ?? '') : rmt_edit_get($edit_id, '_smoker');
$v_has_pets       = $is_post ? sanitize_text_field($_POST['has_pets']       ?? '') : rmt_edit_get($edit_id, '_has_pets');
$v_social_level   = $is_post ? absint($_POST['social_level'] ?? 5)                : (rmt_edit_get($edit_id, '_social_level') ?: 5);

// Budget: split keys first, then fall back to the legacy _budget single key for min
$v_budget_min = $is_post ? sanitize_text_field($_POST['budget_min'] ?? '') : rmt_edit_get($edit_id, '_budget_min', '_budget');
$v_budget_max = $is_post ? sanitize_text_field($_POST['budget_max'] ?? '') : rmt_edit_get($edit_id, '_budget_max');

// Move-in date
$v_move_in_date = $is_post ? sanitize_text_field($_POST['move_in_date'] ?? '') : rmt_edit_get($edit_id, '_move_in_date');

// Preferred area: try both key variants
$v_preferred_area = $is_post
    ? sanitize_text_field($_POST['preferred_area'] ?? '')
    : rmt_edit_get($edit_id, '_preferred_area_text', '_preferred_area');

$v_rm_pref = $is_post
    ? sanitize_textarea_field($_POST['roommate_preference'] ?? '')
    : rmt_edit_get($edit_id, '_roommate_preference');

// Taxonomy selections
if ($is_post) {
    $selected_lifestyles = array_map('intval', (array)($_POST['lifestyle']     ?? []));
    $selected_locs       = array_map('intval', (array)($_POST['location_area'] ?? []));
} else {
    $ex_life = get_the_terms($edit_id, 'lifestyle');
    $ex_loc  = get_the_terms($edit_id, 'location_area');
    $selected_lifestyles = (!$ex_life || is_wp_error($ex_life)) ? [] : wp_list_pluck($ex_life, 'term_id');
    $selected_locs       = (!$ex_loc  || is_wp_error($ex_loc))  ? [] : wp_list_pluck($ex_loc,  'term_id');
}

$lifestyle_terms     = get_terms(['taxonomy' => 'lifestyle',     'hide_empty' => false]);
$location_area_terms = get_terms(['taxonomy' => 'location_area', 'hide_empty' => false]);

get_header();
?>

<style>
/* ── Reuse post-a-roommate styles ─────────────────────────────────────────── */
.post-a-roommate { padding: var(--space-10) 0 var(--space-16); }
.par-page-header { margin-bottom: var(--space-8); }
.par-eyebrow {
    display: inline-flex; align-items: center; gap: .45rem;
    background: rgba(137,226,25,.12); border: 1px solid rgba(137,226,25,.28);
    color: var(--color-secondary); font-size: .75rem; font-weight: 800;
    letter-spacing: .08em; text-transform: uppercase;
    padding: .35rem .85rem; border-radius: 999px; margin-bottom: 1rem;
}
.par-eyebrow-dot { width:6px;height:6px;background:var(--color-primary);border-radius:50%;flex-shrink:0; }
.par-page-header h1 { font-size: clamp(2rem,5vw,3rem); letter-spacing: -.02em; margin: 0 0 .5rem; }
.par-page-header > p { font-size: 1.05rem; color: var(--color-text-soft); margin: 0; }
.par-layout { display: grid; gap: 1.5rem; align-items: start; }
@media (min-width:992px) { .par-layout { grid-template-columns: minmax(0,2fr) minmax(260px,.85fr); } }
.par-card {
    background: var(--color-surface); border: 1px solid var(--color-border);
    border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: var(--shadow-sm);
    margin-bottom: 1.25rem; transition: box-shadow var(--transition), border-color var(--transition);
}
.par-card:focus-within { box-shadow: var(--shadow-md); border-color: rgba(137,226,25,.35); }
.par-card__header {
    display: flex; align-items: center; gap: .85rem;
    margin-bottom: 1.5rem; padding-bottom: 1.1rem;
    border-bottom: 1px solid var(--color-border);
}
.par-card__icon {
    width:42px;height:42px; background:rgba(137,226,25,.14); border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;flex-shrink:0;line-height:1;
}
.par-card__header h2 { font-size:1.15rem;margin:0 0 .15rem; }
.par-card__header p  { font-size:.85rem;color:var(--color-text-muted);margin:0; }
.par-field { display:flex;flex-direction:column;gap:.45rem;margin-bottom:1.15rem; }
.par-field:last-child { margin-bottom:0; }
.par-field > label { font-size:.88rem;font-weight:700;color:var(--color-text);display:flex;align-items:center;gap:.35rem; }
.par-field .required { color:#E63946;font-size:.8rem; }
.par-field > small   { font-size:.8rem;color:var(--color-text-muted); }
.par-cols-2 { display:grid;grid-template-columns:1fr 1fr;gap:1rem; }
@media (max-width:600px) { .par-cols-2 { grid-template-columns:1fr; } }
.par-input,.par-select,.par-textarea {
    width:100%;min-height:48px;padding:.75rem 1rem;
    border:1.5px solid var(--color-border);border-radius:var(--radius-sm);
    background:var(--color-white);color:var(--color-text);
    font-size:.95rem;font-family:var(--font-body);
    transition:border-color var(--transition),box-shadow var(--transition);
    appearance:none;-webkit-appearance:none;
}
.par-textarea { min-height:110px;resize:vertical; }
.par-input::placeholder,.par-textarea::placeholder { color:var(--color-text-muted); }
.par-input:focus,.par-select:focus,.par-textarea:focus {
    outline:none;border-color:var(--color-primary);
    box-shadow:0 0 0 4px rgba(137,226,25,.18);
}
.par-input:hover,.par-select:hover,.par-textarea:hover { border-color:var(--color-text-muted); }
.par-select-wrap { position:relative; }
.par-select-wrap::after {
    content:'▾';position:absolute;right:1rem;top:50%;transform:translateY(-50%);
    font-size:.85rem;color:var(--color-text-muted);pointer-events:none;
}
.par-select { padding-right:2.5rem;cursor:pointer; }
.par-checkbox-group { display:flex;flex-wrap:wrap;gap:.55rem; }
.par-checkbox-label { cursor:pointer; }
.par-checkbox-label input[type="checkbox"] { display:none; }
.par-checkbox-label span {
    display:inline-flex;align-items:center;gap:.4rem;
    padding:.48rem 1rem;border-radius:999px;border:1.5px solid var(--color-border);
    background:var(--color-surface);font-size:.88rem;font-weight:700;
    color:var(--color-text-soft);transition:var(--transition);
}
.par-checkbox-label span::before {
    content:'';width:14px;height:14px;border-radius:4px;
    border:1.5px solid var(--color-border);background:var(--color-white);
    flex-shrink:0;transition:var(--transition);
}
.par-checkbox-label input[type="checkbox"]:checked + span {
    background:#F8FCEB;border-color:var(--color-primary);color:var(--color-secondary-dark);
}
.par-checkbox-label input[type="checkbox"]:checked + span::before {
    background:var(--color-primary);border-color:var(--color-primary);
    background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 10 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3 3 5-6' stroke='%23111' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat:no-repeat;background-position:center;background-size:10px;
}
.par-checkbox-label span:hover { border-color:var(--color-primary);color:var(--color-text); }
.par-range-wrap { display:flex;align-items:center;gap:1rem; }
.par-range {
    flex:1;-webkit-appearance:none;appearance:none;
    height:6px;background:var(--color-border);border-radius:999px;outline:none;cursor:pointer;
}
.par-range::-webkit-slider-thumb {
    -webkit-appearance:none;width:22px;height:22px;border-radius:50%;
    background:var(--color-primary);box-shadow:0 2px 8px rgba(137,226,25,.4);
    cursor:pointer;transition:transform var(--transition);
}
.par-range::-webkit-slider-thumb:hover { transform:scale(1.15); }
.par-range::-moz-range-thumb { width:22px;height:22px;border-radius:50%;background:var(--color-primary);border:none; }
.par-range-val {
    min-width:38px;height:38px;display:flex;align-items:center;justify-content:center;
    background:var(--color-primary);color:var(--color-black);
    font-weight:800;font-size:.9rem;border-radius:10px;flex-shrink:0;
}
.par-photo-upload {
    display:flex;align-items:center;gap:1.25rem;padding:1rem;
    border:1.5px dashed var(--color-border);border-radius:var(--radius-md);
    background:var(--color-surface);
}
.par-photo-upload img { width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--color-border);flex-shrink:0; }
.par-photo-upload__right { display:flex;flex-direction:column;gap:.5rem; }
.par-photo-hint { font-size:.8rem;color:var(--color-text-muted); }
.par-submit-row { display:flex;align-items:center;gap:1rem;flex-wrap:wrap;margin-top:.5rem; }
.par-submit-row .btn { padding:1rem 2rem;font-size:1rem; }
.par-submit-row small { font-size:.82rem;color:var(--color-text-muted); }
.par-notice {
    display:flex;gap:1rem;align-items:flex-start;
    padding:1.2rem 1.4rem;border-radius:var(--radius-md);margin-bottom:1.5rem;font-size:.95rem;
}
.par-notice__icon { font-size:1.3rem;flex-shrink:0;line-height:1;margin-top:1px; }
.par-notice__body p { margin:0 0 .75rem; }
.par-notice__body p:last-child { margin:0; }
.par-notice--error { background:#FFF1F2;border:1px solid #FCA5A5; }
.par-notice--error ul { margin:0;padding-left:1.25rem;color:var(--color-text-soft); }
.par-sidebar { position:sticky;top:90px;display:flex;flex-direction:column;gap:1.25rem; }
.par-tip-card {
    background:linear-gradient(135deg,#4B4B4B 0%,#363636 100%);
    border-radius:var(--radius-lg);padding:1.4rem;box-shadow:var(--shadow-md);
}
.par-tip-card h3 { color:#fff;font-size:1rem;margin:0 0 1rem; }
.par-tip-list { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.75rem; }
.par-tip-list li {
    display:flex;gap:.7rem;font-size:.85rem;color:rgba(255,255,255,.78);
    align-items:flex-start;line-height:1.5;
}
.par-tip-list li::before {
    content:'✓';width:20px;height:20px;background:var(--color-primary);border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:.65rem;color:var(--color-black);font-weight:900;flex-shrink:0;margin-top:1px;
}
</style>

<main id="primary" class="site-main single-page post-a-roommate">
    <div class="container">

        <div class="par-page-header">
            <div class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                Edit Profile
            </div>
            <h1>Edit Roommate Profile</h1>
            <p>Update your information below and save when you're done.</p>
        </div>

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

            <!-- ── FORM ──────────────────────────────────────────────────── -->
            <form id="roommate-edit-form" method="post" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('rmt_edit_roommate_' . $edit_id, 'rmt_edit_roommate_nonce'); ?>
                <input type="hidden" name="rmt_edit_id" value="<?php echo esc_attr($edit_id); ?>">

                <!-- ① About You -->
                <div class="par-card">
                    <div class="par-card__header">
                        <div class="par-card__icon">👤</div>
                        <div><h2>About You</h2><p>Update your personal info.</p></div>
                    </div>

                    <div class="par-field">
                        <label for="profile_photo">Profile Photo</label>
                        <small>Upload a new photo to replace the current one. Max 5 MB. JPG or PNG.</small>
                        <div class="par-photo-upload">
                            <?php
                            $thumb_id  = get_post_thumbnail_id($edit_id);
                            $thumb_url = $thumb_id
                                ? wp_get_attachment_image_url($thumb_id, 'thumbnail')
                                : get_template_directory_uri() . '/images/default-profile.jpg';
                            ?>
                            <img id="photo-preview" src="<?php echo esc_url($thumb_url); ?>" alt="Profile photo preview">
                            <div class="par-photo-upload__right">
                                <label for="profile_photo" class="btn btn-secondary" style="cursor:pointer;display:inline-block;">Change Photo</label>
                                <input type="file" id="profile_photo" name="profile_photo"
                                    accept="image/jpeg,image/png,image/webp" style="display:none;"
                                    onchange="if(this.files&&this.files[0]){var r=new FileReader();r.onload=function(e){document.getElementById('photo-preview').src=e.target.result};r.readAsDataURL(this.files[0])}">
                                <span class="par-photo-hint">Leave empty to keep your current photo.</span>
                            </div>
                        </div>
                    </div>

                    <div class="par-cols-2">
                        <div class="par-field">
                            <label for="nickname">Name / Nickname <span class="required">*</span></label>
                            <input class="par-input" type="text" id="nickname" name="nickname"
                                value="<?php echo esc_attr($v_nickname); ?>"
                                placeholder="e.g. Nook, Lek, Jamie" required>
                        </div>
                        <div class="par-field">
                            <label for="age">Age <span class="required">*</span></label>
                            <input class="par-input" type="number" id="age" name="age"
                                value="<?php echo esc_attr($v_age); ?>"
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
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($v_gender, $opt); ?>><?php echo esc_html($opt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="par-field">
                            <label for="occupation">Occupation</label>
                            <input class="par-input" type="text" id="occupation" name="occupation"
                                value="<?php echo esc_attr($v_occupation); ?>"
                                placeholder="e.g. Graphic Designer, Student">
                        </div>
                    </div>

                    <div class="par-cols-2">
                        <div class="par-field">
                            <label for="languages">Languages Spoken</label>
                            <input class="par-input" type="text" id="languages" name="languages"
                                value="<?php echo esc_attr($v_languages); ?>"
                                placeholder="e.g. Thai, English">
                        </div>
                        <div class="par-field">
                            <label for="hobbies">Hobbies / Interests</label>
                            <input class="par-input" type="text" id="hobbies" name="hobbies"
                                value="<?php echo esc_attr($v_hobbies); ?>"
                                placeholder="e.g. Reading, Cooking, Yoga">
                        </div>
                    </div>

                    <div class="par-field">
                        <label for="bio">About Me <span class="required">*</span></label>
                        <textarea class="par-textarea" id="bio" name="bio" rows="5"
                            placeholder="Share a bit about yourself…"
                            required><?php echo esc_textarea($v_bio); ?></textarea>
                    </div>
                </div>

                <!-- ② Lifestyle -->
                <div class="par-card">
                    <div class="par-card__header">
                        <div class="par-card__icon">🌿</div>
                        <div><h2>Lifestyle</h2><p>Help potential roommates gauge compatibility.</p></div>
                    </div>

                    <div class="par-cols-2">
                        <div class="par-field">
                            <label for="cleanliness">Cleanliness Level</label>
                            <div class="par-select-wrap">
                                <select class="par-select" id="cleanliness" name="cleanliness">
                                    <option value="">— Select —</option>
                                    <?php foreach (['Very tidy','Tidy','Average','Relaxed'] as $opt) : ?>
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($v_cleanliness, $opt); ?>><?php echo esc_html($opt); ?></option>
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
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($v_sleep_schedule, $opt); ?>><?php echo esc_html($opt); ?></option>
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
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($v_smoker, $opt); ?>><?php echo esc_html($opt); ?></option>
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
                                        <option value="<?php echo esc_attr($opt); ?>" <?php selected($v_has_pets, $opt); ?>><?php echo esc_html($opt); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="par-field">
                        <label for="social_level">Social Level</label>
                        <small>1 = very private &nbsp;·&nbsp; 10 = very social</small>
                        <div class="par-range-wrap" style="margin-top:.5rem">
                            <input class="par-range" type="range" id="social_level" name="social_level"
                                min="1" max="10"
                                value="<?php echo esc_attr($v_social_level); ?>"
                                oninput="document.getElementById('sl_val').textContent=this.value">
                            <span class="par-range-val" id="sl_val"><?php echo esc_html($v_social_level); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                        <div class="par-field">
                            <label>Lifestyle Tags</label>
                            <div class="par-checkbox-group">
                                <?php foreach ($lifestyle_terms as $term) : ?>
                                    <label class="par-checkbox-label">
                                        <input type="checkbox" name="lifestyle[]"
                                            value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php checked(in_array($term->term_id, $selected_lifestyles)); ?>>
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
                        <div><h2>Room Preferences</h2><p>What are you looking for in a room?</p></div>
                    </div>

                    <div class="par-cols-2">
                        <div class="par-field">
                            <label for="budget_min">Budget Min (฿)</label>
                            <input class="par-input" type="number" id="budget_min" name="budget_min" min="0"
                                value="<?php echo esc_attr($v_budget_min); ?>"
                                placeholder="e.g. 6000">
                        </div>
                        <div class="par-field">
                            <label for="budget_max">Budget Max (฿)</label>
                            <input class="par-input" type="number" id="budget_max" name="budget_max" min="0"
                                value="<?php echo esc_attr($v_budget_max); ?>"
                                placeholder="e.g. 10000">
                        </div>
                    </div>

                    <div class="par-cols-2">
                        <div class="par-field">
                            <label for="move_in_date">Preferred Move-in Date</label>
                            <input class="par-input" type="date" id="move_in_date" name="move_in_date"
                                value="<?php echo esc_attr($v_move_in_date); ?>">
                        </div>
                        <div class="par-field">
                            <label for="preferred_area">Preferred Area / Neighbourhood</label>
                            <input class="par-input" type="text" id="preferred_area" name="preferred_area"
                                value="<?php echo esc_attr($v_preferred_area); ?>"
                                placeholder="e.g. Silom, On Nut, Ari">
                        </div>
                    </div>

                    <?php if (!empty($location_area_terms) && !is_wp_error($location_area_terms)) : ?>
                        <div class="par-field">
                            <label>Location Areas</label>
                            <div class="par-checkbox-group">
                                <?php foreach ($location_area_terms as $term) : ?>
                                    <label class="par-checkbox-label">
                                        <input type="checkbox" name="location_area[]"
                                            value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php checked(in_array($term->term_id, $selected_locs)); ?>>
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
                        <div><h2>Preferred Roommate</h2><p>Describe who you'd love to live with.</p></div>
                    </div>
                    <div class="par-field">
                        <label for="roommate_preference">Who are you looking for?</label>
                        <textarea class="par-textarea" id="roommate_preference" name="roommate_preference" rows="5"
                            placeholder="Describe your ideal roommate — lifestyle, habits, deal-breakers…"><?php echo esc_textarea($v_rm_pref); ?></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="par-submit-row">
                    <button type="submit" class="btn btn-primary">Save Changes →</button>
                    <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn btn-secondary">Cancel</a>
                    <?php if (in_array($post->post_status, ['draft', 'pending'])) : ?>
                        <small>Saving will re-publish your profile.</small>
                    <?php else : ?>
                        <small>Changes will be visible immediately.</small>
                    <?php endif; ?>
                </div>

            </form>

            <!-- ── SIDEBAR ────────────────────────────────────────────────── -->
            <aside class="par-sidebar">

                <div class="par-tip-card">
                    <h3>💡 Editing Tips</h3>
                    <ul class="par-tip-list">
                        <li>Update your move-in date if your plans have changed.</li>
                        <li>A fresh photo gets 2× more responses on average.</li>
                        <li>Be specific about deal-breakers to attract the right match.</li>
                        <li>Unpublished profiles won't appear in search — save to go live.</li>
                    </ul>
                </div>

                <div style="background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:1.4rem;box-shadow:var(--shadow-sm);">
                    <h3 style="font-size:1rem;margin:0 0 .75rem;">Current Status</h3>
                    <p style="margin:0 0 .75rem;font-size:.9rem;">
                        This profile is currently <strong><?php echo esc_html(ucfirst($post->post_status)); ?></strong>.
                    </p>
                    <?php if ($post->post_status === 'publish') : ?>
                        <a href="<?php echo esc_url(get_permalink($edit_id)); ?>" class="btn btn-secondary" style="width:100%;display:block;text-align:center;">
                            View Live Profile
                        </a>
                    <?php endif; ?>
                </div>

            </aside>

        </div><!-- .par-layout -->
    </div>
</main>

<?php get_footer(); ?>