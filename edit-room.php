<?php
/**
 * Template Name: Edit Room Listing
 * Description: Frontend edit form for room posts.
 * Access via: /edit-room/?edit_id=POST_ID
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$errors       = [];

$edit_id = isset($_GET['edit_id']) ? absint($_GET['edit_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_edit_id'])) {
    $edit_id = absint($_POST['rmt_edit_id']);
}

if (!$edit_id) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

$post = get_post($edit_id);

if (
    !$post ||
    $post->post_type !== 'room' ||
    (int) $post->post_author !== (int) $current_user->ID
) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}

function rmt_edit_room_get($post_id, $key) {
    return get_post_meta($post_id, $key, true);
}

function rmt_edit_room_selected_terms($post_id, $taxonomy) {
    $terms = get_the_terms($post_id, $taxonomy);

    if (!$terms || is_wp_error($terms)) {
        return [];
    }

    return wp_list_pluck($terms, 'term_id');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_edit_room_nonce'])) {
    if (!wp_verify_nonce($_POST['rmt_edit_room_nonce'], 'rmt_edit_room')) {
        $errors[] = 'Security check failed. Please try again.';
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
            if (isset($_POST['save_draft'])) {
                $new_status = 'draft';
            } elseif (isset($_POST['publish_listing'])) {
                $new_status = 'publish';
            } else {
                $new_status = $post->post_status;
            }

            $result = wp_update_post([
                'ID'           => $edit_id,
                'post_title'   => $title,
                'post_content' => wp_kses_post($_POST['description'] ?? ''),
                'post_status'  => $new_status,
            ], true);

            if (is_wp_error($result)) {
                $errors[] = 'Could not update the room listing. Please try again.';
            } else {
                delete_post_meta($edit_id, '_rmt_done');

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
                        update_post_meta($edit_id, $meta_key, esc_url_raw($value));
                    } else {
                        update_post_meta($edit_id, $meta_key, sanitize_text_field($value));
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
                    update_post_meta($edit_id, $meta_key, sanitize_text_field($_POST[$post_key] ?? ''));
                }

                update_post_meta($edit_id, '_bio', sanitize_textarea_field($_POST['bio'] ?? ''));
                update_post_meta($edit_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference'] ?? ''));

                wp_set_post_terms(
                    $edit_id,
                    !empty($_POST['location_area']) && is_array($_POST['location_area'])
                        ? array_map('intval', $_POST['location_area'])
                        : [],
                    'location_area'
                );

                wp_set_post_terms(
                    $edit_id,
                    !empty($_POST['amenity']) && is_array($_POST['amenity'])
                        ? array_map('intval', $_POST['amenity'])
                        : [],
                    'amenity'
                );

                wp_set_post_terms(
                    $edit_id,
                    !empty($_POST['lifestyle']) && is_array($_POST['lifestyle'])
                        ? array_map('intval', $_POST['lifestyle'])
                        : [],
                    'lifestyle'
                );

                wp_set_post_terms(
                    $edit_id,
                    !empty($_POST['room_type'])
                        ? [absint($_POST['room_type'])]
                        : [],
                    'room_type'
                );

                if (!empty($_FILES['room_image']['name'])) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';

                    $attachment_id = media_handle_upload('room_image', $edit_id);

                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($edit_id, $attachment_id);
                    }
                }

                wp_redirect(add_query_arg('listing_updated', '1', home_url('/dashboard/')));
                exit;
            }
        }
    }
}

$is_post = $_SERVER['REQUEST_METHOD'] === 'POST';

$v_room_title = $is_post ? sanitize_text_field($_POST['room_title'] ?? '') : $post->post_title;
$v_description = $is_post ? wp_kses_post($_POST['description'] ?? '') : $post->post_content;

$v_rent              = $is_post ? sanitize_text_field($_POST['rent'] ?? '') : rmt_edit_room_get($edit_id, '_rent');
$v_deposit           = $is_post ? sanitize_text_field($_POST['deposit'] ?? '') : rmt_edit_room_get($edit_id, '_deposit');
$v_available_date    = $is_post ? sanitize_text_field($_POST['available_date'] ?? '') : rmt_edit_room_get($edit_id, '_available_date');
$v_property_type     = $is_post ? sanitize_text_field($_POST['property_type'] ?? '') : rmt_edit_room_get($edit_id, '_property_type');
$v_address           = $is_post ? sanitize_text_field($_POST['address'] ?? '') : rmt_edit_room_get($edit_id, '_address');
$v_nearby_landmark   = $is_post ? sanitize_text_field($_POST['nearby_landmark'] ?? '') : rmt_edit_room_get($edit_id, '_nearby_landmark');
$v_map_url           = $is_post ? esc_url_raw($_POST['map_url'] ?? '') : rmt_edit_room_get($edit_id, '_map_url');
$v_utilities         = $is_post ? sanitize_text_field($_POST['utilities'] ?? '') : rmt_edit_room_get($edit_id, '_utilities');
$v_min_stay          = $is_post ? sanitize_text_field($_POST['min_stay'] ?? '') : rmt_edit_room_get($edit_id, '_min_stay');
$v_gender_preference = $is_post ? sanitize_text_field($_POST['gender_preference'] ?? '') : rmt_edit_room_get($edit_id, '_gender_preference');
$v_pet_policy        = $is_post ? sanitize_text_field($_POST['pet_policy'] ?? '') : rmt_edit_room_get($edit_id, '_pet_policy');
$v_smoking_policy    = $is_post ? sanitize_text_field($_POST['smoking_policy'] ?? '') : rmt_edit_room_get($edit_id, '_smoking_policy');

$v_nickname       = $is_post ? sanitize_text_field($_POST['nickname'] ?? '') : rmt_edit_room_get($edit_id, '_nickname');
$v_age            = $is_post ? sanitize_text_field($_POST['age'] ?? '') : rmt_edit_room_get($edit_id, '_age');
$v_gender         = $is_post ? sanitize_text_field($_POST['gender'] ?? '') : rmt_edit_room_get($edit_id, '_gender');
$v_occupation     = $is_post ? sanitize_text_field($_POST['occupation'] ?? '') : rmt_edit_room_get($edit_id, '_occupation');
$v_languages      = $is_post ? sanitize_text_field($_POST['languages'] ?? '') : rmt_edit_room_get($edit_id, '_languages');
$v_cleanliness    = $is_post ? sanitize_text_field($_POST['cleanliness'] ?? '') : rmt_edit_room_get($edit_id, '_cleanliness');
$v_sleep_schedule = $is_post ? sanitize_text_field($_POST['sleep_schedule'] ?? '') : rmt_edit_room_get($edit_id, '_sleep_schedule');
$v_smoker         = $is_post ? sanitize_text_field($_POST['smoker'] ?? '') : rmt_edit_room_get($edit_id, '_smoker');
$v_has_pets       = $is_post ? sanitize_text_field($_POST['has_pets'] ?? '') : rmt_edit_room_get($edit_id, '_has_pets');
$v_social_level   = $is_post ? sanitize_text_field($_POST['social_level'] ?? '5') : (rmt_edit_room_get($edit_id, '_social_level') ?: '5');
$v_hobbies        = $is_post ? sanitize_text_field($_POST['hobbies'] ?? '') : rmt_edit_room_get($edit_id, '_hobbies');
$v_bio            = $is_post ? sanitize_textarea_field($_POST['bio'] ?? '') : rmt_edit_room_get($edit_id, '_bio');
$v_roommate_pref  = $is_post ? sanitize_textarea_field($_POST['roommate_preference'] ?? '') : rmt_edit_room_get($edit_id, '_roommate_preference');

$selected_location_area = $is_post ? array_map('intval', (array) ($_POST['location_area'] ?? [])) : rmt_edit_room_selected_terms($edit_id, 'location_area');
$selected_amenity       = $is_post ? array_map('intval', (array) ($_POST['amenity'] ?? [])) : rmt_edit_room_selected_terms($edit_id, 'amenity');
$selected_lifestyle     = $is_post ? array_map('intval', (array) ($_POST['lifestyle'] ?? [])) : rmt_edit_room_selected_terms($edit_id, 'lifestyle');
$selected_room_type     = $is_post ? absint($_POST['room_type'] ?? 0) : 0;

if (!$is_post) {
    $room_type_terms_current = rmt_edit_room_selected_terms($edit_id, 'room_type');
    $selected_room_type = !empty($room_type_terms_current) ? (int) $room_type_terms_current[0] : 0;
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
                Edit Room Listing
            </span>
            <h1>Edit your room listing</h1>
            <p>Update your listing from the frontend. No WordPress admin page needed.</p>
        </header>

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
            <?php wp_nonce_field('rmt_edit_room', 'rmt_edit_room_nonce'); ?>
            <input type="hidden" name="rmt_edit_id" value="<?php echo esc_attr($edit_id); ?>">

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
                            <label for="room_title">Room Title <span class="required">*</span></label>
                            <input class="par-input" type="text" id="room_title" name="room_title" value="<?php echo esc_attr($v_room_title); ?>" required>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="rent">Monthly Rent <span class="required">*</span></label>
                                <input class="par-input" type="number" id="rent" name="rent" value="<?php echo esc_attr($v_rent); ?>" required>
                            </div>

                            <div class="par-field">
                                <label for="deposit">Deposit</label>
                                <input class="par-input" type="number" id="deposit" name="deposit" value="<?php echo esc_attr($v_deposit); ?>">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="property_type">Property Type</label>
                                <select class="par-select" id="property_type" name="property_type">
                                    <option value="">Select property type</option>
                                    <option value="condo" <?php selected($v_property_type, 'condo'); ?>>Condo</option>
                                    <option value="apartment" <?php selected($v_property_type, 'apartment'); ?>>Apartment</option>
                                    <option value="house" <?php selected($v_property_type, 'house'); ?>>House</option>
                                    <option value="townhouse" <?php selected($v_property_type, 'townhouse'); ?>>Townhouse</option>
                                    <option value="studio" <?php selected($v_property_type, 'studio'); ?>>Studio</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="available_date">Available Date</label>
                                <input class="par-input" type="date" id="available_date" name="available_date" value="<?php echo esc_attr($v_available_date); ?>">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="address">Address / Area</label>
                            <input class="par-input" type="text" id="address" name="address" value="<?php echo esc_attr($v_address); ?>">
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="nearby_landmark">Nearby Landmark</label>
                                <input class="par-input" type="text" id="nearby_landmark" name="nearby_landmark" value="<?php echo esc_attr($v_nearby_landmark); ?>">
                            </div>

                            <div class="par-field">
                                <label for="map_url">Google Map URL</label>
                                <input class="par-input" type="url" id="map_url" name="map_url" value="<?php echo esc_attr($v_map_url); ?>">
                            </div>
                        </div>
                    </section>

                    <section class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">⚙️</div>
                            <div>
                                <h2>Room Rules</h2>
                                <p>Set your room conditions and preferences.</p>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="utilities">Utilities</label>
                                <select class="par-select" id="utilities" name="utilities">
                                    <option value="">Select</option>
                                    <option value="included" <?php selected($v_utilities, 'included'); ?>>Included</option>
                                    <option value="not_included" <?php selected($v_utilities, 'not_included'); ?>>Not Included</option>
                                    <option value="shared" <?php selected($v_utilities, 'shared'); ?>>Shared</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="min_stay">Minimum Stay</label>
                                <select class="par-select" id="min_stay" name="min_stay">
                                    <option value="">Select</option>
                                    <option value="1_month" <?php selected($v_min_stay, '1_month'); ?>>1 Month</option>
                                    <option value="3_months" <?php selected($v_min_stay, '3_months'); ?>>3 Months</option>
                                    <option value="6_months" <?php selected($v_min_stay, '6_months'); ?>>6 Months</option>
                                    <option value="1_year" <?php selected($v_min_stay, '1_year'); ?>>1 Year</option>
                                </select>
                            </div>
                        </div>

                        <div class="par-cols-3">
                            <div class="par-field">
                                <label for="gender_preference">Gender Preference</label>
                                <select class="par-select" id="gender_preference" name="gender_preference">
                                    <option value="">No preference</option>
                                    <option value="male" <?php selected($v_gender_preference, 'male'); ?>>Male</option>
                                    <option value="female" <?php selected($v_gender_preference, 'female'); ?>>Female</option>
                                    <option value="any" <?php selected($v_gender_preference, 'any'); ?>>Any</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="pet_policy">Pet Policy</label>
                                <select class="par-select" id="pet_policy" name="pet_policy">
                                    <option value="">Select</option>
                                    <option value="allowed" <?php selected($v_pet_policy, 'allowed'); ?>>Allowed</option>
                                    <option value="not_allowed" <?php selected($v_pet_policy, 'not_allowed'); ?>>Not Allowed</option>
                                    <option value="ask_first" <?php selected($v_pet_policy, 'ask_first'); ?>>Ask First</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="smoking_policy">Smoking Policy</label>
                                <select class="par-select" id="smoking_policy" name="smoking_policy">
                                    <option value="">Select</option>
                                    <option value="allowed" <?php selected($v_smoking_policy, 'allowed'); ?>>Allowed</option>
                                    <option value="not_allowed" <?php selected($v_smoking_policy, 'not_allowed'); ?>>Not Allowed</option>
                                    <option value="outside_only" <?php selected($v_smoking_policy, 'outside_only'); ?>>Outside Only</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🗂️</div>
                            <div>
                                <h2>Categories</h2>
                                <p>Choose room type, location, amenities, and lifestyle tags.</p>
                            </div>
                        </div>

                        <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                            <div class="par-field">
                                <label>Room Type</label>
                                <div class="par-check-grid">
                                    <?php foreach ($room_type_terms as $term) : ?>
                                        <label class="par-check">
                                            <input type="radio" name="room_type" value="<?php echo esc_attr($term->term_id); ?>" <?php checked($selected_room_type, $term->term_id); ?>>
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
                                            <input type="checkbox" name="location_area[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, $selected_location_area, true)); ?>>
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
                                            <input type="checkbox" name="amenity[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, $selected_amenity, true)); ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                            <div class="par-field">
                                <label>Lifestyle</label>
                                <div class="par-check-grid">
                                    <?php foreach ($lifestyle_terms as $term) : ?>
                                        <label class="par-check">
                                            <input type="checkbox" name="lifestyle[]" value="<?php echo esc_attr($term->term_id); ?>" <?php checked(in_array($term->term_id, $selected_lifestyle, true)); ?>>
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
                                <h2>Description</h2>
                                <p>Tell people about the room and living situation.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="description">Room Description</label>
                            <textarea class="par-textarea" id="description" name="description" rows="6"><?php echo esc_textarea($v_description); ?></textarea>
                        </div>

                        <div class="par-field">
                            <label for="room_image">Change Room Image</label>
                            <input class="par-file-input" type="file" id="room_image" name="room_image" accept="image/*">

                            <?php if (has_post_thumbnail($edit_id)) : ?>
                                <div class="par-current-image">
                                    <?php echo get_the_post_thumbnail($edit_id, 'medium'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🙂</div>
                            <div>
                                <h2>About You</h2>
                                <p>Your profile as the room owner or current roommate.</p>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="nickname">Nickname / Name</label>
                                <input class="par-input" type="text" id="nickname" name="nickname" value="<?php echo esc_attr($v_nickname); ?>">
                            </div>

                            <div class="par-field">
                                <label for="age">Age</label>
                                <input class="par-input" type="number" id="age" name="age" value="<?php echo esc_attr($v_age); ?>">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="gender">Gender</label>
                                <select class="par-select" id="gender" name="gender">
                                    <option value="">Select</option>
                                    <option value="male" <?php selected($v_gender, 'male'); ?>>Male</option>
                                    <option value="female" <?php selected($v_gender, 'female'); ?>>Female</option>
                                    <option value="other" <?php selected($v_gender, 'other'); ?>>Other</option>
                                    <option value="prefer_not_to_say" <?php selected($v_gender, 'prefer_not_to_say'); ?>>Prefer not to say</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="occupation">Occupation</label>
                                <input class="par-input" type="text" id="occupation" name="occupation" value="<?php echo esc_attr($v_occupation); ?>">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="languages">Languages</label>
                            <input class="par-input" type="text" id="languages" name="languages" value="<?php echo esc_attr($v_languages); ?>">
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="cleanliness">Cleanliness</label>
                                <select class="par-select" id="cleanliness" name="cleanliness">
                                    <option value="">Select</option>
                                    <option value="very_clean" <?php selected($v_cleanliness, 'very_clean'); ?>>Very Clean</option>
                                    <option value="clean" <?php selected($v_cleanliness, 'clean'); ?>>Clean</option>
                                    <option value="relaxed" <?php selected($v_cleanliness, 'relaxed'); ?>>Relaxed</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="sleep_schedule">Sleep Schedule</label>
                                <select class="par-select" id="sleep_schedule" name="sleep_schedule">
                                    <option value="">Select</option>
                                    <option value="early_bird" <?php selected($v_sleep_schedule, 'early_bird'); ?>>Early Bird</option>
                                    <option value="night_owl" <?php selected($v_sleep_schedule, 'night_owl'); ?>>Night Owl</option>
                                    <option value="flexible" <?php selected($v_sleep_schedule, 'flexible'); ?>>Flexible</option>
                                </select>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="smoker">Smoker</label>
                                <select class="par-select" id="smoker" name="smoker">
                                    <option value="">Select</option>
                                    <option value="yes" <?php selected($v_smoker, 'yes'); ?>>Yes</option>
                                    <option value="no" <?php selected($v_smoker, 'no'); ?>>No</option>
                                </select>
                            </div>

                            <div class="par-field">
                                <label for="has_pets">Has Pets</label>
                                <select class="par-select" id="has_pets" name="has_pets">
                                    <option value="">Select</option>
                                    <option value="yes" <?php selected($v_has_pets, 'yes'); ?>>Yes</option>
                                    <option value="no" <?php selected($v_has_pets, 'no'); ?>>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="social_level">Social Level: <?php echo esc_html($v_social_level); ?>/10</label>
                            <input class="par-range" type="range" id="social_level" name="social_level" min="1" max="10" value="<?php echo esc_attr($v_social_level); ?>">
                        </div>

                        <div class="par-field">
                            <label for="hobbies">Hobbies</label>
                            <input class="par-input" type="text" id="hobbies" name="hobbies" value="<?php echo esc_attr($v_hobbies); ?>">
                        </div>

                        <div class="par-field">
                            <label for="bio">Bio</label>
                            <textarea class="par-textarea" id="bio" name="bio" rows="4"><?php echo esc_textarea($v_bio); ?></textarea>
                        </div>

                        <div class="par-field">
                            <label for="roommate_preference">Roommate Preference</label>
                            <textarea class="par-textarea" id="roommate_preference" name="roommate_preference" rows="5"><?php echo esc_textarea($v_roommate_pref); ?></textarea>
                        </div>
                    </section>
                </div>

                <aside class="par-sidebar">
                    <div class="par-card par-sticky">
                        <h2>Listing Status</h2>

                        <ul class="detail-list">
                            <li><strong>Current status:</strong> <?php echo esc_html(ucfirst(get_post_status($edit_id))); ?></li>

                            <?php if (get_post_meta($edit_id, '_rmt_done', true)) : ?>
                                <li><strong>Done:</strong> Yes</li>
                            <?php endif; ?>
                        </ul>

                        <div class="cta-actions u-mt-4">
                            <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                                Save as Draft
                            </button>

                            <button type="submit" name="publish_listing" value="1" class="btn btn-primary">
                                Publish / Update
                            </button>

                            <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</main>

<?php get_footer(); ?>