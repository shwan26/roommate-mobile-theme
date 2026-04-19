<?php
/**
 * Template Name: Post Listing
 *
 * Front-end form allowing logged-in users to submit a Room or Roommate listing.
 * Handles image upload, all meta fields, taxonomy assignment, and redirects on success.
 */

defined('ABSPATH') || exit;

// ---------------------------------------------------------------
// GUARD: must be logged in
// ---------------------------------------------------------------
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user    = wp_get_current_user();
$success_message = '';
$error_message   = '';
$redirect_url    = '';

// ---------------------------------------------------------------
// PROCESS SUBMISSION
// ---------------------------------------------------------------
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['rmt_front_submit'] ) ) {

    // 1. Nonce check
    if (
        ! isset( $_POST['rmt_front_submit_nonce'] ) ||
        ! wp_verify_nonce( $_POST['rmt_front_submit_nonce'], 'rmt_front_submit_action' )
    ) {
        $error_message = 'Security check failed. Please try again.';

    } else {

        $listing_type = isset( $_POST['listing_type'] ) ? sanitize_text_field( $_POST['listing_type'] ) : '';

        // 2. Validate listing type
        if ( ! in_array( $listing_type, array( 'room', 'roommate' ), true ) ) {
            $error_message = 'Please choose a valid listing type.';

        } else {

            $post_title   = isset( $_POST['post_title'] )   ? sanitize_text_field( $_POST['post_title'] )       : '';
            $post_content = isset( $_POST['post_content'] ) ? sanitize_textarea_field( $_POST['post_content'] ) : '';

            // 3. Validate required fields
            if ( empty( $post_title ) || empty( $post_content ) ) {
                $error_message = 'Please fill in the title and description.';

            } else {

                // 4. Create the post (pending review)
                $post_id = wp_insert_post( array(
                    'post_type'    => $listing_type,
                    'post_title'   => $post_title,
                    'post_content' => $post_content,
                    'post_status'  => 'pending',
                    'post_author'  => $current_user->ID,
                ) );

                if ( $post_id && ! is_wp_error( $post_id ) ) {

                    // -----------------------------------------------
                    // 5. Save featured image (photo upload)
                    // -----------------------------------------------
                    if ( ! empty( $_FILES['listing_photo']['name'] ) ) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        require_once ABSPATH . 'wp-admin/includes/media.php';

                        $attachment_id = media_handle_upload( 'listing_photo', $post_id );
                        if ( ! is_wp_error( $attachment_id ) ) {
                            set_post_thumbnail( $post_id, $attachment_id );
                        }
                    }

                    // -----------------------------------------------
                    // 6. Helper: save a single meta field
                    // -----------------------------------------------
                    $save_text = function( $key ) use ( $post_id ) {
                        if ( isset( $_POST[ $key ] ) ) {
                            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
                        }
                    };
                    $save_num = function( $key ) use ( $post_id ) {
                        if ( isset( $_POST[ $key ] ) && is_numeric( $_POST[ $key ] ) ) {
                            update_post_meta( $post_id, $key, (float) $_POST[ $key ] );
                        }
                    };
                    $save_date = function( $key ) use ( $post_id ) {
                        if ( ! empty( $_POST[ $key ] ) ) {
                            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
                        }
                    };
                    $save_url = function( $key ) use ( $post_id ) {
                        if ( ! empty( $_POST[ $key ] ) ) {
                            update_post_meta( $post_id, $key, esc_url_raw( $_POST[ $key ] ) );
                        }
                    };
                    $save_textarea = function( $key ) use ( $post_id ) {
                        if ( isset( $_POST[ $key ] ) ) {
                            update_post_meta( $post_id, $key, sanitize_textarea_field( $_POST[ $key ] ) );
                        }
                    };

                    // -----------------------------------------------
                    // 7a. ROOM meta fields
                    // -----------------------------------------------
                    if ( $listing_type === 'room' ) {
                        $save_text( '_property_name' );
                        $save_text( '_property_type' );
                        $save_text( '_address' );
                        $save_text( '_nearby_landmark' );
                        $save_url(  '_map_url' );
                        $save_num(  '_rent' );
                        $save_text( '_bills_included' );
                        $save_num(  '_deposit' );
                        $save_date( '_available_date' );
                        $save_text( '_gender_preference' );
                        $save_text( '_pet_policy' );
                        $save_text( '_smoking_policy' );
                        $save_text( '_utilities' );
                        $save_text( '_min_stay' );

                        // Current roommate info
                        $save_text( '_nickname' );
                        $save_num(  '_age' );
                        $save_text( '_gender' );
                        $save_text( '_occupation' );
                        $save_text( '_languages' );
                        $save_text( '_cleanliness' );
                        $save_text( '_sleep_schedule' );
                        $save_text( '_smoker' );
                        $save_text( '_has_pets' );
                        $save_num(  '_social_level' );
                        $save_text( '_hobbies' );
                        $save_textarea( '_bio' );
                        $save_textarea( '_roommate_preference' );

                        // Taxonomies
                        if ( ! empty( $_POST['location_area'] ) ) {
                            wp_set_object_terms( $post_id, absint( $_POST['location_area'] ), 'location_area' );
                        }
                        if ( ! empty( $_POST['room_type'] ) ) {
                            wp_set_object_terms( $post_id, absint( $_POST['room_type'] ), 'room_type' );
                        }
                        if ( ! empty( $_POST['amenities'] ) && is_array( $_POST['amenities'] ) ) {
                            wp_set_object_terms( $post_id, array_map( 'absint', $_POST['amenities'] ), 'amenity' );
                        }
                        if ( ! empty( $_POST['lifestyles'] ) && is_array( $_POST['lifestyles'] ) ) {
                            wp_set_object_terms( $post_id, array_map( 'absint', $_POST['lifestyles'] ), 'lifestyle' );
                        }
                    }

                    // -----------------------------------------------
                    // 7b. ROOMMATE meta fields
                    // -----------------------------------------------
                    if ( $listing_type === 'roommate' ) {
                        $save_num(  '_budget_min' );
                        $save_num(  '_budget_max' );
                        $save_date( '_move_in_date' );
                        $save_text( '_preferred_property_type' );
                        $save_text( '_preferred_room_type' );
                        $save_text( '_lease_duration' );
                        $save_text( '_preferred_area_text' );
                        $save_text( '_private_or_shared' );
                        $save_text( '_teamup_ok' );
                        $save_text( '_gender_preference' );
                        $save_text( '_pets_ok' );
                        $save_text( '_smokers_ok' );

                        // About this person
                        $save_text( '_nickname' );
                        $save_num(  '_age' );
                        $save_text( '_gender' );
                        $save_text( '_occupation' );
                        $save_text( '_languages' );
                        $save_text( '_cleanliness' );
                        $save_text( '_sleep_schedule' );
                        $save_text( '_smoker' );
                        $save_text( '_has_pets' );
                        $save_num(  '_social_level' );
                        $save_text( '_hobbies' );
                        $save_textarea( '_bio' );
                        $save_textarea( '_ideal_roommate' );

                        // Taxonomies
                        if ( ! empty( $_POST['location_area'] ) ) {
                            wp_set_object_terms( $post_id, absint( $_POST['location_area'] ), 'location_area' );
                        }
                        if ( ! empty( $_POST['room_type'] ) ) {
                            wp_set_object_terms( $post_id, absint( $_POST['room_type'] ), 'room_type' );
                        }
                        if ( ! empty( $_POST['lifestyles'] ) && is_array( $_POST['lifestyles'] ) ) {
                            wp_set_object_terms( $post_id, array_map( 'absint', $_POST['lifestyles'] ), 'lifestyle' );
                        }
                    }

                    // Redirect to dashboard on success
                    wp_redirect( add_query_arg( 'listing_submitted', '1', home_url( '/dashboard' ) ) );
                    exit;

                } else {
                    $error_message = 'Something went wrong while submitting your listing. Please try again.';
                }
            }
        }
    }
}

// ---------------------------------------------------------------
// Pre-load taxonomy terms for dropdowns
// ---------------------------------------------------------------
$locations  = get_terms( array( 'taxonomy' => 'location_area', 'hide_empty' => false ) );
$room_types = get_terms( array( 'taxonomy' => 'room_type',     'hide_empty' => false ) );
$amenities  = get_terms( array( 'taxonomy' => 'amenity',       'hide_empty' => false ) );
$lifestyles = get_terms( array( 'taxonomy' => 'lifestyle',     'hide_empty' => false ) );

// Helper: get posted value or empty string
function rmt_posted( $key ) {
    return isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
}

get_header();
?>

<main id="primary" class="site-main post-listing-page">

    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Post Listing</span>
            <h1 class="archive-title">Create Your Listing</h1>
            <p class="archive-description">
                Choose whether you have a room or need one, then fill in the details so people can find a great match.
            </p>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container" style="max-width: 900px;">

            <?php if ( ! empty( $error_message ) ) : ?>
                <div class="single-card" style="border-color:#d9534f; margin-bottom:1.5rem;">
                    <p style="margin:0; font-weight:700; color:#d9534f;"><?php echo esc_html( $error_message ); ?></p>
                </div>
            <?php endif; ?>

            <?php /* enctype required for file upload */ ?>
            <form method="post" enctype="multipart/form-data" class="filter-form" id="rmt-post-listing-form" novalidate>
                <?php wp_nonce_field( 'rmt_front_submit_action', 'rmt_front_submit_nonce' ); ?>

                <?php /* ======================================================
                    STEP 1 — LISTING TYPE + COMMON FIELDS
                ====================================================== */ ?>
                <div class="single-card" style="margin-bottom:1.5rem;">
                    <h2>1. Basic Information</h2>
                    <div class="filter-grid">

                        <div class="filter-group" style="grid-column:1/-1;">
                            <label for="listing_type">What are you posting? <span class="required">*</span></label>
                            <select name="listing_type" id="listing_type" required>
                                <option value="">— Select one —</option>
                                <option value="room"     <?php selected( rmt_posted('listing_type'), 'room' ); ?>>
                                    I Have a Room (looking for a roommate)
                                </option>
                                <option value="roommate" <?php selected( rmt_posted('listing_type'), 'roommate' ); ?>>
                                    I Need a Room (looking for a place)
                                </option>
                            </select>
                        </div>

                        <div class="filter-group" style="grid-column:1/-1;">
                            <label for="post_title">Listing Title <span class="required">*</span></label>
                            <input
                                type="text"
                                name="post_title"
                                id="post_title"
                                required
                                placeholder="e.g. Cozy condo near BTS looking for female roommate"
                                value="<?php echo esc_attr( rmt_posted('post_title') ); ?>"
                            >
                        </div>

                        <div class="filter-group" style="grid-column:1/-1;">
                            <label for="post_content">Description <span class="required">*</span></label>
                            <textarea
                                name="post_content"
                                id="post_content"
                                rows="5"
                                required
                                placeholder="Describe the room / what you are looking for..."
                            ><?php echo esc_textarea( rmt_posted('post_content') ); ?></textarea>
                        </div>

                        <div class="filter-group" style="grid-column:1/-1;">
                            <label for="listing_photo">Photo <span class="field-hint">(optional — JPG or PNG, max 5 MB)</span></label>
                            <input type="file" name="listing_photo" id="listing_photo" accept="image/jpeg,image/png,image/webp">
                        </div>

                    </div>
                </div>


                <?php /* ======================================================
                    STEP 2a — ROOM FIELDS
                ====================================================== */ ?>
                <div id="room-fields" class="rmt-conditional-fields" style="display:none;">

                    <div class="single-card" style="margin-bottom:1.5rem;">
                        <h2>2. Room Details</h2>
                        <div class="filter-grid">

                            <div class="filter-group">
                                <label for="_property_name">Property Name</label>
                                <input type="text" name="_property_name" id="_property_name"
                                    placeholder="e.g. Lumpini Suite"
                                    value="<?php echo esc_attr( rmt_posted('_property_name') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_property_type">Property Type</label>
                                <select name="_property_type" id="_property_type">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Apartment','Condo','House','Studio','Townhouse') as $pt ) : ?>
                                        <option value="<?php echo esc_attr($pt); ?>" <?php selected( rmt_posted('_property_type'), $pt ); ?>>
                                            <?php echo esc_html($pt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_rent">Monthly Rent (THB) <span class="required">*</span></label>
                                <input type="number" name="_rent" id="_rent" min="0" placeholder="e.g. 8000"
                                    value="<?php echo esc_attr( rmt_posted('_rent') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_deposit">Deposit (THB)</label>
                                <input type="number" name="_deposit" id="_deposit" min="0" placeholder="0 if none"
                                    value="<?php echo esc_attr( rmt_posted('_deposit') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_bills_included">Bills Included?</label>
                                <select name="_bills_included" id="_bills_included">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Included','Not Included','Partially Included') as $bi ) : ?>
                                        <option value="<?php echo esc_attr($bi); ?>" <?php selected( rmt_posted('_bills_included'), $bi ); ?>>
                                            <?php echo esc_html($bi); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_utilities">Utilities Notes</label>
                                <input type="text" name="_utilities" id="_utilities"
                                    placeholder="e.g. Water &amp; wifi included, pay own electric"
                                    value="<?php echo esc_attr( rmt_posted('_utilities') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_available_date">Available From</label>
                                <input type="date" name="_available_date" id="_available_date"
                                    value="<?php echo esc_attr( rmt_posted('_available_date') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_min_stay">Minimum Stay</label>
                                <input type="text" name="_min_stay" id="_min_stay"
                                    placeholder="e.g. 3 months"
                                    value="<?php echo esc_attr( rmt_posted('_min_stay') ); ?>">
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_address">Address / Area</label>
                                <input type="text" name="_address" id="_address"
                                    placeholder="e.g. Sukhumvit Soi 11, Bangkok"
                                    value="<?php echo esc_attr( rmt_posted('_address') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_nearby_landmark">Nearby Landmark / BTS / MRT</label>
                                <input type="text" name="_nearby_landmark" id="_nearby_landmark"
                                    placeholder="e.g. 5 min walk to BTS Asok"
                                    value="<?php echo esc_attr( rmt_posted('_nearby_landmark') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_map_url">Google Maps Link</label>
                                <input type="url" name="_map_url" id="_map_url"
                                    placeholder="https://maps.google.com/..."
                                    value="<?php echo esc_attr( rmt_posted('_map_url') ); ?>">
                            </div>

                        </div>
                    </div>

                    <div class="single-card" style="margin-bottom:1.5rem;">
                        <h2>3. Room Preferences</h2>
                        <div class="filter-grid">

                            <div class="filter-group">
                                <label for="location_area_room">Location Area</label>
                                <select name="location_area" id="location_area_room">
                                    <option value="">— Select —</option>
                                    <?php if ( ! is_wp_error($locations) ) : foreach ( $locations as $term ) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php selected( rmt_posted('location_area'), $term->term_id ); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="room_type_room">Room Type</label>
                                <select name="room_type" id="room_type_room">
                                    <option value="">— Select —</option>
                                    <?php if ( ! is_wp_error($room_types) ) : foreach ( $room_types as $term ) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php selected( rmt_posted('room_type'), $term->term_id ); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_gender_preference_room">Gender Preference</label>
                                <select name="_gender_preference" id="_gender_preference_room">
                                    <option value="">— No preference —</option>
                                    <?php foreach ( array('Male','Female','Others') as $g ) : ?>
                                        <option value="<?php echo esc_attr($g); ?>" <?php selected( rmt_posted('_gender_preference'), $g ); ?>>
                                            <?php echo esc_html($g); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_pet_policy">Pet Policy</label>
                                <select name="_pet_policy" id="_pet_policy">
                                    <option value="">— Select —</option>
                                    <option value="Accept"     <?php selected( rmt_posted('_pet_policy'), 'Accept' ); ?>>Accept</option>
                                    <option value="Not Accept" <?php selected( rmt_posted('_pet_policy'), 'Not Accept' ); ?>>Not Accept</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_smoking_policy">Smoking Policy</label>
                                <select name="_smoking_policy" id="_smoking_policy">
                                    <option value="">— Select —</option>
                                    <option value="Accept"     <?php selected( rmt_posted('_smoking_policy'), 'Accept' ); ?>>Accept</option>
                                    <option value="Not Accept" <?php selected( rmt_posted('_smoking_policy'), 'Not Accept' ); ?>>Not Accept</option>
                                </select>
                            </div>

                            <?php if ( ! is_wp_error($amenities) && ! empty($amenities) ) : ?>
                                <div class="filter-group" style="grid-column:1/-1;">
                                    <label>Amenities</label>
                                    <div class="checkbox-grid">
                                        <?php foreach ( $amenities as $term ) : ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="amenities[]"
                                                    value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked( in_array($term->term_id, (array) rmt_posted('amenities') ) ); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! is_wp_error($lifestyles) && ! empty($lifestyles) ) : ?>
                                <div class="filter-group" style="grid-column:1/-1;">
                                    <label>Lifestyle Tags</label>
                                    <div class="checkbox-grid">
                                        <?php foreach ( $lifestyles as $term ) : ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="lifestyles[]"
                                                    value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked( in_array($term->term_id, (array) rmt_posted('lifestyles') ) ); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="single-card" style="margin-bottom:1.5rem;">
                        <h2>4. About You (Current Roommate)</h2>
                        <div class="filter-grid">

                            <div class="filter-group">
                                <label for="_nickname_room">Nickname</label>
                                <input type="text" name="_nickname" id="_nickname_room"
                                    value="<?php echo esc_attr( rmt_posted('_nickname') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_age_room">Age</label>
                                <input type="number" name="_age" id="_age_room" min="18" max="99"
                                    value="<?php echo esc_attr( rmt_posted('_age') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_gender_room">Gender</label>
                                <select name="_gender" id="_gender_room">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Male','Female','Others') as $g ) : ?>
                                        <option value="<?php echo esc_attr($g); ?>" <?php selected( rmt_posted('_gender'), $g ); ?>>
                                            <?php echo esc_html($g); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_occupation_room">Occupation</label>
                                <input type="text" name="_occupation" id="_occupation_room"
                                    value="<?php echo esc_attr( rmt_posted('_occupation') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_languages_room">Languages</label>
                                <input type="text" name="_languages" id="_languages_room"
                                    placeholder="e.g. Thai, English"
                                    value="<?php echo esc_attr( rmt_posted('_languages') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_cleanliness_room">Cleanliness Level</label>
                                <select name="_cleanliness" id="_cleanliness_room">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Very Clean','Moderate','Flexible') as $cl ) : ?>
                                        <option value="<?php echo esc_attr($cl); ?>" <?php selected( rmt_posted('_cleanliness'), $cl ); ?>>
                                            <?php echo esc_html($cl); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_sleep_schedule_room">Sleep Schedule</label>
                                <input type="text" name="_sleep_schedule" id="_sleep_schedule_room"
                                    placeholder="e.g. 11 PM – 7 AM"
                                    value="<?php echo esc_attr( rmt_posted('_sleep_schedule') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_smoker_room">Do you smoke?</label>
                                <select name="_smoker" id="_smoker_room">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_smoker'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_smoker'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_has_pets_room">Do you have pets?</label>
                                <select name="_has_pets" id="_has_pets_room">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_has_pets'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_has_pets'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_social_level_room">Social Level (1–10)</label>
                                <input type="number" name="_social_level" id="_social_level_room" min="1" max="10"
                                    placeholder="1 = introvert, 10 = very social"
                                    value="<?php echo esc_attr( rmt_posted('_social_level') ); ?>">
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_hobbies_room">Hobbies / Interests</label>
                                <input type="text" name="_hobbies" id="_hobbies_room"
                                    placeholder="e.g. cooking, gaming, yoga"
                                    value="<?php echo esc_attr( rmt_posted('_hobbies') ); ?>">
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_bio_room">Short Bio</label>
                                <textarea name="_bio" id="_bio_room" rows="4"
                                    placeholder="Tell potential roommates a little about yourself..."
                                ><?php echo esc_textarea( rmt_posted('_bio') ); ?></textarea>
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_roommate_preference">Preferred Roommate</label>
                                <textarea name="_roommate_preference" id="_roommate_preference" rows="4"
                                    placeholder="Describe your ideal roommate..."
                                ><?php echo esc_textarea( rmt_posted('_roommate_preference') ); ?></textarea>
                            </div>

                        </div>
                    </div>

                </div><!-- /#room-fields -->


                <?php /* ======================================================
                    STEP 2b — ROOMMATE (NEED A ROOM) FIELDS
                ====================================================== */ ?>
                <div id="roommate-fields" class="rmt-conditional-fields" style="display:none;">

                    <div class="single-card" style="margin-bottom:1.5rem;">
                        <h2>2. What You Are Looking For</h2>
                        <div class="filter-grid">

                            <div class="filter-group">
                                <label for="_budget_min">Budget Min (THB)</label>
                                <input type="number" name="_budget_min" id="_budget_min" min="0"
                                    value="<?php echo esc_attr( rmt_posted('_budget_min') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_budget_max">Budget Max (THB)</label>
                                <input type="number" name="_budget_max" id="_budget_max" min="0"
                                    value="<?php echo esc_attr( rmt_posted('_budget_max') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_move_in_date">Move-in Date</label>
                                <input type="date" name="_move_in_date" id="_move_in_date"
                                    value="<?php echo esc_attr( rmt_posted('_move_in_date') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_lease_duration">Preferred Lease Duration</label>
                                <input type="text" name="_lease_duration" id="_lease_duration"
                                    placeholder="e.g. 6 months, 1 year"
                                    value="<?php echo esc_attr( rmt_posted('_lease_duration') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_preferred_area_text">Preferred Area</label>
                                <input type="text" name="_preferred_area_text" id="_preferred_area_text"
                                    placeholder="e.g. Sukhumvit, Silom, Ratchada"
                                    value="<?php echo esc_attr( rmt_posted('_preferred_area_text') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_preferred_property_type">Preferred Property Type</label>
                                <select name="_preferred_property_type" id="_preferred_property_type">
                                    <option value="">— No preference —</option>
                                    <?php foreach ( array('Apartment','Condo','House','Studio','Townhouse') as $pt ) : ?>
                                        <option value="<?php echo esc_attr($pt); ?>" <?php selected( rmt_posted('_preferred_property_type'), $pt ); ?>>
                                            <?php echo esc_html($pt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_private_or_shared">Private or Shared Room?</label>
                                <select name="_private_or_shared" id="_private_or_shared">
                                    <option value="">— No preference —</option>
                                    <option value="Private" <?php selected( rmt_posted('_private_or_shared'), 'Private' ); ?>>Private</option>
                                    <option value="Shared"  <?php selected( rmt_posted('_private_or_shared'), 'Shared' ); ?>>Shared</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_teamup_ok">Open to Team-up / Co-renting?</label>
                                <select name="_teamup_ok" id="_teamup_ok">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_teamup_ok'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_teamup_ok'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="location_area_rmte">Preferred Location Area</label>
                                <select name="location_area" id="location_area_rmte">
                                    <option value="">— Select —</option>
                                    <?php if ( ! is_wp_error($locations) ) : foreach ( $locations as $term ) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php selected( rmt_posted('location_area'), $term->term_id ); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="room_type_rmte">Preferred Room Type</label>
                                <select name="room_type" id="room_type_rmte">
                                    <option value="">— Select —</option>
                                    <?php if ( ! is_wp_error($room_types) ) : foreach ( $room_types as $term ) : ?>
                                        <option value="<?php echo esc_attr($term->term_id); ?>"
                                            <?php selected( rmt_posted('room_type'), $term->term_id ); ?>>
                                            <?php echo esc_html($term->name); ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_gender_preference_rmte">Roommate Gender Preference</label>
                                <select name="_gender_preference" id="_gender_preference_rmte">
                                    <option value="">— No preference —</option>
                                    <?php foreach ( array('Male','Female','Others') as $g ) : ?>
                                        <option value="<?php echo esc_attr($g); ?>" <?php selected( rmt_posted('_gender_preference'), $g ); ?>>
                                            <?php echo esc_html($g); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_pets_ok">Pets OK in the home?</label>
                                <select name="_pets_ok" id="_pets_ok">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_pets_ok'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_pets_ok'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_smokers_ok">Smokers OK?</label>
                                <select name="_smokers_ok" id="_smokers_ok">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_smokers_ok'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_smokers_ok'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <?php if ( ! is_wp_error($lifestyles) && ! empty($lifestyles) ) : ?>
                                <div class="filter-group" style="grid-column:1/-1;">
                                    <label>Lifestyle Tags</label>
                                    <div class="checkbox-grid">
                                        <?php foreach ( $lifestyles as $term ) : ?>
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="lifestyles[]"
                                                    value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked( in_array($term->term_id, (array) rmt_posted('lifestyles') ) ); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="single-card" style="margin-bottom:1.5rem;">
                        <h2>3. About You</h2>
                        <div class="filter-grid">

                            <div class="filter-group">
                                <label for="_nickname_rmte">Nickname</label>
                                <input type="text" name="_nickname" id="_nickname_rmte"
                                    value="<?php echo esc_attr( rmt_posted('_nickname') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_age_rmte">Age</label>
                                <input type="number" name="_age" id="_age_rmte" min="18" max="99"
                                    value="<?php echo esc_attr( rmt_posted('_age') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_gender_rmte">Gender</label>
                                <select name="_gender" id="_gender_rmte">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Male','Female','Others') as $g ) : ?>
                                        <option value="<?php echo esc_attr($g); ?>" <?php selected( rmt_posted('_gender'), $g ); ?>>
                                            <?php echo esc_html($g); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_occupation_rmte">Occupation</label>
                                <input type="text" name="_occupation" id="_occupation_rmte"
                                    value="<?php echo esc_attr( rmt_posted('_occupation') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_languages_rmte">Languages</label>
                                <input type="text" name="_languages" id="_languages_rmte"
                                    placeholder="e.g. Thai, English"
                                    value="<?php echo esc_attr( rmt_posted('_languages') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_cleanliness_rmte">Cleanliness Level</label>
                                <select name="_cleanliness" id="_cleanliness_rmte">
                                    <option value="">— Select —</option>
                                    <?php foreach ( array('Very Clean','Moderate','Flexible') as $cl ) : ?>
                                        <option value="<?php echo esc_attr($cl); ?>" <?php selected( rmt_posted('_cleanliness'), $cl ); ?>>
                                            <?php echo esc_html($cl); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_sleep_schedule_rmte">Sleep Schedule</label>
                                <input type="text" name="_sleep_schedule" id="_sleep_schedule_rmte"
                                    placeholder="e.g. 11 PM – 7 AM"
                                    value="<?php echo esc_attr( rmt_posted('_sleep_schedule') ); ?>">
                            </div>

                            <div class="filter-group">
                                <label for="_smoker_rmte">Do you smoke?</label>
                                <select name="_smoker" id="_smoker_rmte">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_smoker'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_smoker'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_has_pets_rmte">Do you have pets?</label>
                                <select name="_has_pets" id="_has_pets_rmte">
                                    <option value="">— Select —</option>
                                    <option value="Yes" <?php selected( rmt_posted('_has_pets'), 'Yes' ); ?>>Yes</option>
                                    <option value="No"  <?php selected( rmt_posted('_has_pets'), 'No' ); ?>>No</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="_social_level_rmte">Social Level (1–10)</label>
                                <input type="number" name="_social_level" id="_social_level_rmte" min="1" max="10"
                                    placeholder="1 = introvert, 10 = very social"
                                    value="<?php echo esc_attr( rmt_posted('_social_level') ); ?>">
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_hobbies_rmte">Hobbies / Interests</label>
                                <input type="text" name="_hobbies" id="_hobbies_rmte"
                                    placeholder="e.g. cooking, gaming, yoga"
                                    value="<?php echo esc_attr( rmt_posted('_hobbies') ); ?>">
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_bio_rmte">Short Bio</label>
                                <textarea name="_bio" id="_bio_rmte" rows="4"
                                    placeholder="Tell potential roommates a little about yourself..."
                                ><?php echo esc_textarea( rmt_posted('_bio') ); ?></textarea>
                            </div>

                            <div class="filter-group" style="grid-column:1/-1;">
                                <label for="_ideal_roommate">Describe Your Ideal Roommate</label>
                                <textarea name="_ideal_roommate" id="_ideal_roommate" rows="4"
                                    placeholder="What kind of person are you hoping to live with?"
                                ><?php echo esc_textarea( rmt_posted('_ideal_roommate') ); ?></textarea>
                            </div>

                        </div>
                    </div>

                </div><!-- /#roommate-fields -->


                <?php /* Submit */ ?>
                <div class="filter-actions" style="margin-top:1.5rem;" id="rmt-submit-wrap" style="display:none;">
                    <button type="submit" name="rmt_front_submit" value="1" class="btn btn-primary">
                        Submit Listing for Review
                    </button>
                    <a href="<?php echo esc_url( home_url('/dashboard') ); ?>" class="btn btn-secondary">Cancel</a>
                </div>

            </form>
        </div>
    </section>
</main>

<style>
.required { color: #d9534f; }
.field-hint { font-size: .85em; font-weight: 400; color: #888; }
.checkbox-grid {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem 1.25rem;
    margin-top: .35rem;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-weight: 400;
    cursor: pointer;
}
.checkbox-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select      = document.getElementById('listing_type');
    var roomFields  = document.getElementById('room-fields');
    var rmteFields  = document.getElementById('roommate-fields');
    var submitWrap  = document.getElementById('rmt-submit-wrap');

    function toggle() {
        var val = select ? select.value : '';
        if (roomFields)  roomFields.style.display  = val === 'room'     ? 'block' : 'none';
        if (rmteFields)  rmteFields.style.display  = val === 'roommate' ? 'block' : 'none';
        if (submitWrap)  submitWrap.style.display  = val ? 'flex' : 'none';
    }

    if (select) {
        select.addEventListener('change', toggle);
        toggle(); // run once on load (handles back-button restores)
    }
});
</script>

<?php get_footer(); ?>