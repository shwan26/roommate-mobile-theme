<?php
/**
 * Template Name: Post a Room
 * Frontend-only room posting form with Save as Draft / Publish.
 */

defined('ABSPATH') || exit;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_post_room_nonce'])) {
    if (!wp_verify_nonce($_POST['rmt_post_room_nonce'], 'rmt_post_room')) {
        $errors[] = 'Security check failed. Please try again.';
    } elseif (!is_user_logged_in()) {
        $errors[] = 'You must be logged in to post a room.';
    } else {
        $title = sanitize_text_field($_POST['room_title'] ?? '');
        $rent  = sanitize_text_field($_POST['rent'] ?? '');

        if (empty($title)) {
            $errors[] = 'Room title is required.';
        }

        if (empty($rent) || !is_numeric($rent)) {
            $errors[] = 'A valid monthly rent amount is required.';
        }

        if (empty($errors)) {
            $post_status = isset($_POST['save_draft']) ? 'draft' : 'publish';

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => wp_kses_post($_POST['description'] ?? ''),
                'post_status'  => $post_status,
                'post_type'    => 'room',
                'post_author'  => get_current_user_id(),
            ], true);

            if (is_wp_error($post_id)) {
                $errors[] = 'Could not save the listing. Please try again.';
            } else {
                delete_post_meta($post_id, '_rmt_done');

                $room_meta_fields = [
                    '_rent'              => 'rent',
                    '_deposit'           => 'deposit',
                    '_available_date'    => 'available_date',
                    '_property_type'     => 'property_type',
                    '_address'           => 'address',
                    '_nearby_landmark'   => 'nearby_landmark',
                    '_map_url'           => 'map_url',
                    '_utilities'         => 'utilities',
                    '_min_stay'          => 'min_stay',
                    '_gender_preference' => 'gender_preference',
                    '_pet_policy'        => 'pet_policy',
                    '_smoking_policy'    => 'smoking_policy',
                ];

                foreach ($room_meta_fields as $meta_key => $post_key) {
                    $value = $_POST[$post_key] ?? '';

                    if ($meta_key === '_map_url') {
                        update_post_meta($post_id, $meta_key, esc_url_raw($value));
                    } else {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($value));
                    }
                }

                $owner_meta_fields = [
                    '_nickname'       => 'nickname',
                    '_age'            => 'age',
                    '_gender'         => 'gender',
                    '_occupation'     => 'occupation',
                    '_languages'      => 'languages',
                    '_cleanliness'    => 'cleanliness',
                    '_sleep_schedule' => 'sleep_schedule',
                    '_smoker'         => 'smoker',
                    '_has_pets'       => 'has_pets',
                    '_social_level'   => 'social_level',
                    '_hobbies'        => 'hobbies',
                ];

                foreach ($owner_meta_fields as $meta_key => $post_key) {
                    update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                update_post_meta($post_id, '_bio', sanitize_textarea_field($_POST['bio'] ?? ''));
                update_post_meta($post_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference'] ?? ''));

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['location_area']) && is_array($_POST['location_area'])
                        ? array_map('intval', $_POST['location_area'])
                        : [],
                    'location_area'
                );

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['amenity']) && is_array($_POST['amenity'])
                        ? array_map('intval', $_POST['amenity'])
                        : [],
                    'amenity'
                );

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['lifestyle']) && is_array($_POST['lifestyle'])
                        ? array_map('intval', $_POST['lifestyle'])
                        : [],
                    'lifestyle'
                );

                wp_set_post_terms(
                    $post_id,
                    !empty($_POST['room_type'])
                        ? [absint($_POST['room_type'])]
                        : [],
                    'room_type'
                );

                if (!empty($_FILES['room_image']['name'])) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';

                    $attachment_id = media_handle_upload('room_image', $post_id);

                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }

                $profile_photo_id = rmt_get_uploaded_or_default_profile_photo_id('profile_photo', $post_id);

                if ($profile_photo_id) {
                    update_post_meta($post_id, '_profile_photo_id', $profile_photo_id);
                }

                wp_redirect(add_query_arg([
                    'listing_submitted' => '1',
                    'listing_status'    => $post_status,
                ], home_url('/dashboard/')));
                exit;
            }
        }
    }
}

$location_terms  = get_terms(['taxonomy' => 'location_area', 'hide_empty' => false]);
$amenity_terms   = get_terms(['taxonomy' => 'amenity', 'hide_empty' => false]);
$lifestyle_terms = get_terms(['taxonomy' => 'lifestyle', 'hide_empty' => false]);
$room_type_terms = get_terms(['taxonomy' => 'room_type', 'hide_empty' => false]);

get_header();
?>

<main id="primary" class="site-main post-a-room">
    <div class="container">
        <header class="par-page-header">
            <span class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                New Room Listing
            </span>

            <h1>Post a Room</h1>
            <p>Save your listing as draft or publish it when you are ready.</p>
        </header>

        <?php if (!is_user_logged_in()) : ?>
            <div class="par-alert par-alert--error">
                <p>You need to be logged in to post a room.</p>
                <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn btn-primary">Log In</a>
            </div>
        <?php else : ?>

            <?php if (!empty($errors)) : ?>
                <div class="par-alert par-alert--error">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <?php wp_nonce_field('rmt_post_room', 'rmt_post_room_nonce'); ?>

                <div class="par-layout">
                    <div>
                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🏠</div>
                                <div>
                                    <h2>Room Details</h2>
                                    <p>Basic information about the room.</p>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="room_title">Listing Title <span class="required">*</span></label>
                                <input class="par-input" type="text" id="room_title" name="room_title" value="<?php echo esc_attr($_POST['room_title'] ?? ''); ?>" placeholder="Bright private room in Sukhumvit" required>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="rent">Monthly Rent ฿ <span class="required">*</span></label>
                                    <input class="par-input" type="number" id="rent" name="rent" value="<?php echo esc_attr($_POST['rent'] ?? ''); ?>" min="0" required>
                                </div>

                                <div class="par-field">
                                    <label for="deposit">Deposit ฿</label>
                                    <input class="par-input" type="number" id="deposit" name="deposit" value="<?php echo esc_attr($_POST['deposit'] ?? ''); ?>" min="0">
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="property_type">Property Type</label>
                                    <select class="par-select" id="property_type" name="property_type">
                                        <option value="">Select property type</option>
                                        <?php foreach (['Condo', 'Apartment', 'House', 'Townhouse', 'Studio', 'Serviced Apartment'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['property_type'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="available_date">Available From</label>
                                    <input class="par-input" type="date" id="available_date" name="available_date" value="<?php echo esc_attr($_POST['available_date'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="address">Address / Area</label>
                                <input class="par-input" type="text" id="address" name="address" value="<?php echo esc_attr($_POST['address'] ?? ''); ?>" placeholder="Sukhumvit, Bangkok">
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="nearby_landmark">Nearby Landmark</label>
                                    <input class="par-input" type="text" id="nearby_landmark" name="nearby_landmark" value="<?php echo esc_attr($_POST['nearby_landmark'] ?? ''); ?>" placeholder="5 min walk to BTS Asok">
                                </div>

                                <div class="par-field">
                                    <label for="map_url">Google Map URL</label>
                                    <input class="par-input" type="url" id="map_url" name="map_url" value="<?php echo esc_attr($_POST['map_url'] ?? ''); ?>" placeholder="https://maps.google.com/...">
                                </div>
                            </div>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">⚙️</div>
                                <div>
                                    <h2>Rules and Preferences</h2>
                                    <p>Set your room conditions.</p>
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="utilities">Utilities</label>
                                    <select class="par-select" id="utilities" name="utilities">
                                        <option value="">Select</option>
                                        <?php foreach (['Included', 'Not included', 'Partially included'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['utilities'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="min_stay">Minimum Stay</label>
                                    <select class="par-select" id="min_stay" name="min_stay">
                                        <option value="">Select</option>
                                        <?php foreach (['1 month', '2 months', '3 months', '6 months', '1 year'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['min_stay'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="par-cols-3">
                                <div class="par-field">
                                    <label for="gender_preference">Gender Preference</label>
                                    <select class="par-select" id="gender_preference" name="gender_preference">
                                        <option value="">Any</option>
                                        <?php foreach (['Male only', 'Female only', 'No preference'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['gender_preference'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="pet_policy">Pet Policy</label>
                                    <select class="par-select" id="pet_policy" name="pet_policy">
                                        <option value="">Select</option>
                                        <?php foreach (['Pets allowed', 'No pets', 'Small pets only'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['pet_policy'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="smoking_policy">Smoking Policy</label>
                                    <select class="par-select" id="smoking_policy" name="smoking_policy">
                                        <option value="">Select</option>
                                        <?php foreach (['No smoking', 'Smoking outside only', 'Smoking allowed'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['smoking_policy'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🏷️</div>
                                <div>
                                    <h2>Categories</h2>
                                    <p>Help people discover your listing.</p>
                                </div>
                            </div>

                            <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                                <div class="par-field">
                                    <label>Room Type</label>
                                    <div class="par-check-grid">
                                        <?php foreach ($room_type_terms as $term) : ?>
                                            <label class="par-check">
                                                <input type="radio" name="room_type" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(absint($_POST['room_type'] ?? 0), $term->term_id); ?>>
                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                <div class="par-field">
                                    <label>Location Area</label>
                                    <div class="par-check-grid">
                                        <?php foreach ($location_terms as $term) : ?>
                                            <label class="par-check">
                                                <input type="checkbox" name="location_area[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, array_map('intval', (array) ($_POST['location_area'] ?? [])), true)); ?>>
                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($amenity_terms) && !is_wp_error($amenity_terms)) : ?>
                                <div class="par-field">
                                    <label>Amenities</label>
                                    <div class="par-check-grid">
                                        <?php foreach ($amenity_terms as $term) : ?>
                                            <label class="par-check">
                                                <input type="checkbox" name="amenity[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, array_map('intval', (array) ($_POST['amenity'] ?? [])), true)); ?>>
                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                                <div class="par-field">
                                    <label>Lifestyle Tags</label>
                                    <div class="par-check-grid">
                                        <?php foreach ($lifestyle_terms as $term) : ?>
                                            <label class="par-check">
                                                <input type="checkbox" name="lifestyle[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, array_map('intval', (array) ($_POST['lifestyle'] ?? [])), true)); ?>>
                                                <span><?php echo esc_html($term->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">📝</div>
                                <div>
                                    <h2>Description and Photo</h2>
                                    <p>Tell people more about the room.</p>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="description">Room Description</label>
                                <textarea class="par-textarea" id="description" name="description" rows="6" placeholder="Describe the room, building, transport, and living situation."><?php echo esc_textarea($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="par-field">
                                <label for="room_image">Room Photo</label>
                                <input class="par-file-input" type="file" id="room_image" name="room_image" accept="image/*">
                            </div>
                        </section>

                        <section class="par-card">
                            <div class="par-card__header">
                                <div class="par-card__icon">🙂</div>
                                <div>
                                    <h2>About You</h2>
                                    <p>Your profile as owner or current roommate.</p>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="profile_photo">Your Photo</label>
                                <small>This photo appears with your room listing. If empty, we use the default profile photo.</small>

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
                                            class="par-file-input-hidden"
                                            type="file"
                                            id="profile_photo"
                                            name="profile_photo"
                                            accept="image/jpeg,image/png,image/webp"
                                            onchange="if(this.files&&this.files[0]){var r=new FileReader();r.onload=function(e){document.getElementById('photo-preview').src=e.target.result};r.readAsDataURL(this.files[0])}"
                                        >

                                        <span class="par-photo-hint">Leave empty to use the default photo.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="nickname">Nickname / Name</label>
                                    <input class="par-input" type="text" id="nickname" name="nickname" value="<?php echo esc_attr($_POST['nickname'] ?? ''); ?>">
                                </div>

                                <div class="par-field">
                                    <label for="age">Age</label>
                                    <input class="par-input" type="number" id="age" name="age" value="<?php echo esc_attr($_POST['age'] ?? ''); ?>" min="18" max="99">
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="gender">Gender</label>
                                    <select class="par-select" id="gender" name="gender">
                                        <option value="">Select</option>
                                        <?php foreach (['Male', 'Female', 'Non-binary', 'Prefer not to say'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['gender'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="occupation">Occupation</label>
                                    <input class="par-input" type="text" id="occupation" name="occupation" value="<?php echo esc_attr($_POST['occupation'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="par-cols-2">
                                <div class="par-field">
                                    <label for="languages">Languages</label>
                                    <input class="par-input" type="text" id="languages" name="languages" value="<?php echo esc_attr($_POST['languages'] ?? ''); ?>">
                                </div>

                                <div class="par-field">
                                    <label for="cleanliness">Cleanliness</label>
                                    <select class="par-select" id="cleanliness" name="cleanliness">
                                        <option value="">Select</option>
                                        <?php foreach (['Very tidy', 'Tidy', 'Average', 'Relaxed'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['cleanliness'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="par-cols-3">
                                <div class="par-field">
                                    <label for="sleep_schedule">Sleep Schedule</label>
                                    <select class="par-select" id="sleep_schedule" name="sleep_schedule">
                                        <option value="">Select</option>
                                        <?php foreach (['Early bird', 'Night owl', 'Flexible'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['sleep_schedule'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="smoker">Smoker</label>
                                    <select class="par-select" id="smoker" name="smoker">
                                        <option value="">Select</option>
                                        <?php foreach (['Yes', 'No', 'Outside only'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['smoker'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="par-field">
                                    <label for="has_pets">Has Pets</label>
                                    <select class="par-select" id="has_pets" name="has_pets">
                                        <option value="">Select</option>
                                        <?php foreach (['Yes', 'No'] as $option) : ?>
                                            <option value="<?php echo esc_attr($option); ?>" <?php selected($_POST['has_pets'] ?? '', $option); ?>>
                                                <?php echo esc_html($option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="par-field">
                                <label for="social_level">Social Level: <span id="sl_val"><?php echo esc_html($_POST['social_level'] ?? 5); ?></span>/10</label>
                                <input class="par-range" type="range" id="social_level" name="social_level" min="1" max="10" value="<?php echo esc_attr($_POST['social_level'] ?? 5); ?>" oninput="document.getElementById('sl_val').textContent=this.value">
                            </div>

                            <div class="par-field">
                                <label for="hobbies">Hobbies</label>
                                <input class="par-input" type="text" id="hobbies" name="hobbies" value="<?php echo esc_attr($_POST['hobbies'] ?? ''); ?>">
                            </div>

                            <div class="par-field">
                                <label for="bio">Bio</label>
                                <textarea class="par-textarea" id="bio" name="bio" rows="4"><?php echo esc_textarea($_POST['bio'] ?? ''); ?></textarea>
                            </div>
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
                                <textarea class="par-textarea" id="roommate_preference" name="roommate_preference" rows="5"><?php echo esc_textarea($_POST['roommate_preference'] ?? ''); ?></textarea>
                            </div>
                        </section>
                    </div>

                    <aside class="par-sidebar">
                        <div class="par-card par-sticky">
                            <h2>Submit Listing</h2>
                            <p>Draft listings stay hidden. Published listings appear publicly.</p>

                            <div class="cta-actions">
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
                            <ul>
                                <li>Add a clear room photo.</li>
                                <li>Be specific about BTS/MRT location.</li>
                                <li>Mention rules clearly.</li>
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