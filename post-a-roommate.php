<?php
/**
 * Template Name: Post a Roommate
 * Frontend-only roommate posting form with Save as Draft / Publish.
 */

defined('ABSPATH') || exit;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_post_roommate_nonce'])) {
    if (!wp_verify_nonce($_POST['rmt_post_roommate_nonce'], 'rmt_post_roommate')) {
        $errors[] = 'Security check failed. Please try again.';
    } elseif (!is_user_logged_in()) {
        $errors[] = 'You must be logged in to post a roommate profile.';
    } else {
        $nickname = sanitize_text_field($_POST['nickname'] ?? '');
        $gender   = sanitize_text_field($_POST['gender'] ?? '');
        $bio      = sanitize_textarea_field($_POST['bio'] ?? '');
        $age      = absint($_POST['age'] ?? 0);

        if (empty($nickname)) {
            $errors[] = 'Nickname / name is required.';
        }

        if (empty($gender)) {
            $errors[] = 'Gender is required.';
        }

        if (empty($bio)) {
            $errors[] = 'A short bio is required.';
        }

        if ($age < 18 || $age > 80) {
            $errors[] = 'Please enter a valid age between 18 and 80.';
        }

        if (empty($errors)) {
            $hobbies_raw = sanitize_text_field($_POST['hobbies'] ?? '');

            $post_title = $hobbies_raw
                ? $nickname . ', ' . $age . ' — ' . $hobbies_raw
                : $nickname . ', ' . $age;

            $post_status = isset($_POST['save_draft']) ? 'draft' : 'publish';

            $post_id = wp_insert_post([
                'post_title'   => $post_title,
                'post_content' => $bio,
                'post_status'  => $post_status,
                'post_type'    => 'roommate',
                'post_author'  => get_current_user_id(),
            ], true);

            if (is_wp_error($post_id)) {
                $errors[] = 'Could not save the profile. Please try again.';
            } else {
                delete_post_meta($post_id, '_rmt_done');

                $profile_photo_id = rmt_get_uploaded_or_default_profile_photo_id('profile_photo', $post_id);

                if ($profile_photo_id) {
                    set_post_thumbnail($post_id, $profile_photo_id);
                    update_post_meta($post_id, '_profile_photo_id', $profile_photo_id);
                }

                foreach ([
                    '_nickname'   => 'nickname',
                    '_age'        => 'age',
                    '_gender'     => 'gender',
                    '_occupation' => 'occupation',
                    '_languages'  => 'languages',
                    '_hobbies'    => 'hobbies',
                ] as $meta_key => $post_key) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                foreach ([
                    '_cleanliness'    => 'cleanliness',
                    '_sleep_schedule' => 'sleep_schedule',
                    '_smoker'         => 'smoker',
                    '_has_pets'       => 'has_pets',
                    '_social_level'   => 'social_level',
                ] as $meta_key => $post_key) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                update_post_meta($post_id, '_bio', $bio);
                update_post_meta($post_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference'] ?? ''));

                $budget_min = sanitize_text_field($_POST['budget_min'] ?? '');
                $budget_max = sanitize_text_field($_POST['budget_max'] ?? '');

                update_post_meta($post_id, '_budget_min', $budget_min);
                update_post_meta($post_id, '_budget_max', $budget_max);

                if ($budget_min) {
                    update_post_meta($post_id, '_budget', $budget_min);
                }

                update_post_meta($post_id, '_move_in_date', sanitize_text_field($_POST['move_in_date'] ?? ''));

                $preferred_area = sanitize_text_field($_POST['preferred_area'] ?? '');
                update_post_meta($post_id, '_preferred_area_text', $preferred_area);
                update_post_meta($post_id, '_preferred_area', $preferred_area);

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['lifestyle']) && is_array($_POST['lifestyle'])
                        ? array_map('intval', $_POST['lifestyle'])
                        : [],
                    'lifestyle'
                );

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['location_area']) && is_array($_POST['location_area'])
                        ? array_map('intval', $_POST['location_area'])
                        : [],
                    'location_area'
                );

                wp_redirect(add_query_arg([
                    'listing_submitted' => '1',
                    'listing_status'    => $post_status,
                ], home_url('/dashboard/')));
                exit;
            }
        }
    }
}

$lifestyle_terms = get_terms([
    'taxonomy'   => 'lifestyle',
    'hide_empty' => false,
]);

$location_area_terms = get_terms([
    'taxonomy'   => 'location_area',
    'hide_empty' => false,
]);

get_header();
?>

<main id="primary" class="site-main single-page post-a-roommate">
    <div class="container">
        <div class="par-page-header">
            <div class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                New Roommate Profile
            </div>

            <h1>Post a Roommate Profile</h1>
            <p>Save your profile as draft or publish it when you are ready.</p>
        </div>

        <?php if (!is_user_logged_in()) : ?>
            <div class="par-notice par-notice--error">
                <span class="par-notice__icon">⚠️</span>

                <div class="par-notice__body">
                    <p>You need to be logged in to post a roommate profile.</p>
                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn btn-primary">Log In</a>
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

            <form id="roommate-post-form" method="post" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('rmt_post_roommate', 'rmt_post_roommate_nonce'); ?>

                <div class="par-layout">
                    <div>
                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">👤</div>

                                <div>
                                    <h2>About You</h2>
                                    <p>Tell people who you are.</p>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="profile_photo">Profile Photo</label>
                                <small>Add a clear photo to build trust.</small>

                                <div class="par-photo-upload">
                                    <img
                                        id="photo-preview"
                                        src="<?php echo esc_url(get_template_directory_uri() . '/images/default-profile.jpg'); ?>"
                                        alt="Profile photo preview"
                                    >

                                    <div class="par-photo-upload__right">
                                        <label for="profile_photo" class="btn btn-secondary par-file-btn">
                                            Choose Photo
                                        </label>

                                        <input
                                            type="file"
                                            id="profile_photo"
                                            name="profile_photo"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="par-file-input-hidden"
                                            onchange="if(this.files&&this.files[0]){var r=new FileReader();r.onload=function(e){document.getElementById('photo-preview').src=e.target.result};r.readAsDataURL(this.files[0])}"
                                        >

                                        <span class="par-photo-hint">JPG, PNG, or WEBP recommended.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="nickname">Name / Nickname <span class="required">*</span></label>
                                    <input
                                        class="par-input"
                                        type="text"
                                        id="nickname"
                                        name="nickname"
                                        value="<?php echo esc_attr($_POST['nickname'] ?? ''); ?>"
                                        required
                                    >
                                </div>

                                <div class="par-field">
                                    <label for="age">Age <span class="required">*</span></label>
                                    <input
                                        class="par-input"
                                        type="number"
                                        id="age"
                                        name="age"
                                        value="<?php echo esc_attr($_POST['age'] ?? ''); ?>"
                                        min="18"
                                        max="80"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="gender">Gender <span class="required">*</span></label>

                                    <div class="par-select-wrap">
                                        <select class="par-select" id="gender" name="gender" required>
                                            <option value="">— Select —</option>

                                            <?php foreach (['Male', 'Female', 'Non-binary', 'Prefer not to say'] as $option) : ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['gender'] ?? '', $option); ?>>
                                                    <?php echo esc_html($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="par-field">
                                    <label for="occupation">Occupation</label>
                                    <input
                                        class="par-input"
                                        type="text"
                                        id="occupation"
                                        name="occupation"
                                        value="<?php echo esc_attr($_POST['occupation'] ?? ''); ?>"
                                        placeholder="Student, intern, developer..."
                                    >
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="languages">Languages Spoken</label>
                                    <input
                                        class="par-input"
                                        type="text"
                                        id="languages"
                                        name="languages"
                                        value="<?php echo esc_attr($_POST['languages'] ?? ''); ?>"
                                        placeholder="English, Thai, Burmese..."
                                    >
                                </div>

                                <div class="par-field">
                                    <label for="hobbies">Hobbies / Interests</label>
                                    <input
                                        class="par-input"
                                        type="text"
                                        id="hobbies"
                                        name="hobbies"
                                        value="<?php echo esc_attr($_POST['hobbies'] ?? ''); ?>"
                                        placeholder="Gym, cooking, gaming..."
                                    >
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="bio">About Me <span class="required">*</span></label>
                                <textarea
                                    class="par-textarea"
                                    id="bio"
                                    name="bio"
                                    rows="5"
                                    required
                                    placeholder="Write a short introduction about yourself."
                                ><?php echo esc_textarea($_POST['bio'] ?? ''); ?></textarea>
                            </div>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🌿</div>

                                <div>
                                    <h2>Lifestyle</h2>
                                    <p>Help potential roommates understand your living style.</p>
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="cleanliness">Cleanliness Level</label>

                                    <div class="par-select-wrap">
                                        <select class="par-select" id="cleanliness" name="cleanliness">
                                            <option value="">— Select —</option>

                                            <?php foreach (['Very tidy', 'Tidy', 'Average', 'Relaxed'] as $option) : ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['cleanliness'] ?? '', $option); ?>>
                                                    <?php echo esc_html($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="par-field">
                                    <label for="sleep_schedule">Sleep Schedule</label>

                                    <div class="par-select-wrap">
                                        <select class="par-select" id="sleep_schedule" name="sleep_schedule">
                                            <option value="">— Select —</option>

                                            <?php foreach (['Early bird', 'Night owl', 'Flexible'] as $option) : ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['sleep_schedule'] ?? '', $option); ?>>
                                                    <?php echo esc_html($option); ?>
                                                </option>
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

                                            <?php foreach (['No', 'Outside only', 'Yes'] as $option) : ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['smoker'] ?? '', $option); ?>>
                                                    <?php echo esc_html($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="par-field">
                                    <label for="has_pets">Do You Have Pets?</label>

                                    <div class="par-select-wrap">
                                        <select class="par-select" id="has_pets" name="has_pets">
                                            <option value="">— Select —</option>

                                            <?php foreach (['No', 'Yes — cat', 'Yes — dog', 'Yes — other'] as $option) : ?>
                                                <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['has_pets'] ?? '', $option); ?>>
                                                    <?php echo esc_html($option); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="social_level">Social Level</label>
                                <small>1 = very private, 10 = very social</small>

                                <div class="par-range-wrap par-range-wrap--spaced">
                                    <input
                                        class="par-range"
                                        type="range"
                                        id="social_level"
                                        name="social_level"
                                        min="1"
                                        max="10"
                                        value="<?php echo esc_attr($_POST['social_level'] ?? 5); ?>"
                                        oninput="document.getElementById('sl_val').textContent=this.value"
                                    >

                                    <span class="par-range-val" id="sl_val">
                                        <?php echo esc_html($_POST['social_level'] ?? 5); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                                <div class="par-field">
                                    <label>Lifestyle Tags</label>

                                    <div class="par-checkbox-group">
                                        <?php foreach ($lifestyle_terms as $term) : ?>
                                            <label class="par-checkbox-label">
                                                <input
                                                    type="checkbox"
                                                    name="lifestyle[]"
                                                    value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked(in_array($term->term_id, array_map('intval', (array) ($_POST['lifestyle'] ?? [])), true)); ?>
                                                >

                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🏠</div>

                                <div>
                                    <h2>Room Preferences</h2>
                                    <p>What kind of room are you looking for?</p>
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="budget_min">Budget Min ฿</label>
                                    <input
                                        class="par-input"
                                        type="number"
                                        id="budget_min"
                                        name="budget_min"
                                        min="0"
                                        value="<?php echo esc_attr($_POST['budget_min'] ?? ''); ?>"
                                    >
                                </div>

                                <div class="par-field">
                                    <label for="budget_max">Budget Max ฿</label>
                                    <input
                                        class="par-input"
                                        type="number"
                                        id="budget_max"
                                        name="budget_max"
                                        min="0"
                                        value="<?php echo esc_attr($_POST['budget_max'] ?? ''); ?>"
                                    >
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="move_in_date">Preferred Move-in Date</label>
                                    <input
                                        class="par-input"
                                        type="date"
                                        id="move_in_date"
                                        name="move_in_date"
                                        value="<?php echo esc_attr($_POST['move_in_date'] ?? ''); ?>"
                                    >
                                </div>

                                <div class="par-field">
                                    <label for="preferred_area">Preferred Area</label>
                                    <input
                                        class="par-input"
                                        type="text"
                                        id="preferred_area"
                                        name="preferred_area"
                                        value="<?php echo esc_attr($_POST['preferred_area'] ?? ''); ?>"
                                        placeholder="Sukhumvit, On Nut, Ari..."
                                    >
                                </div>
                            </div>

                            <?php if (!empty($location_area_terms) && !is_wp_error($location_area_terms)) : ?>
                                <div class="par-field">
                                    <label>Location Areas</label>

                                    <div class="par-checkbox-group">
                                        <?php foreach ($location_area_terms as $term) : ?>
                                            <label class="par-checkbox-label">
                                                <input
                                                    type="checkbox"
                                                    name="location_area[]"
                                                    value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked(in_array($term->term_id, array_map('intval', (array) ($_POST['location_area'] ?? [])), true)); ?>
                                                >

                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🔍</div>

                                <div>
                                    <h2>Preferred Roommate</h2>
                                    <p>Describe who you want to live with.</p>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="roommate_preference">Who are you looking for?</label>
                                <textarea
                                    class="par-textarea"
                                    id="roommate_preference"
                                    name="roommate_preference"
                                    rows="5"
                                    placeholder="Example: quiet, clean, respectful, okay with guests sometimes..."
                                ><?php echo esc_textarea($_POST['roommate_preference'] ?? ''); ?></textarea>
                            </div>
                        </section>
                    </div>

                    <aside class="par-sidebar">
                        <div class="par-card par-sticky">
                            <h2>Submit Profile</h2>
                            <p>Draft profiles stay hidden. Published profiles appear publicly.</p>

                            <div class="cta-actions u-mt-4">
                                <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                                    Save as Draft
                                </button>

                                <button type="submit" name="publish_listing" value="1" class="btn btn-primary">
                                    Publish
                                </button>

                                <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>

                        <div class="par-tip-card">
                            <h3>💡 Tips</h3>

                            <ul class="par-tip-list">
                                <li>Use a clear profile photo.</li>
                                <li>Be honest about lifestyle and schedule.</li>
                                <li>Write your budget and preferred area.</li>
                                <li>Save as draft if you are not ready.</li>
                            </ul>
                        </div>
                    </aside>
                </div>
            </form>

        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>