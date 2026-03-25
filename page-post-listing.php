<?php
/**
 * Template Name: Post Listing
 */

defined('ABSPATH') || exit;

get_header();

$success_message = '';
$error_message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_front_submit'])) {
    if (
        !isset($_POST['rmt_front_submit_nonce']) ||
        !wp_verify_nonce($_POST['rmt_front_submit_nonce'], 'rmt_front_submit_action')
    ) {
        $error_message = 'Security check failed. Please try again.';
    } else {
        $listing_type = isset($_POST['listing_type']) ? sanitize_text_field($_POST['listing_type']) : '';

        if (!in_array($listing_type, array('have_room', 'need_room'), true)) {
            $error_message = 'Please choose a valid listing type.';
        } else {
            $post_title   = isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '';
            $post_content = isset($_POST['post_content']) ? sanitize_textarea_field($_POST['post_content']) : '';

            if (empty($post_title) || empty($post_content)) {
                $error_message = 'Please fill in the title and description.';
            } else {
                $post_id = wp_insert_post(array(
                    'post_type'    => $listing_type,
                    'post_title'   => $post_title,
                    'post_content' => $post_content,
                    'post_status'  => 'pending',
                ));

                if ($post_id && !is_wp_error($post_id)) {
                    if ($listing_type === 'have_room') {
                        update_post_meta($post_id, '_rent', isset($_POST['_rent']) ? sanitize_text_field($_POST['_rent']) : '');
                        update_post_meta($post_id, '_deposit', isset($_POST['_deposit']) ? sanitize_text_field($_POST['_deposit']) : '');
                        update_post_meta($post_id, '_available_date', isset($_POST['_available_date']) ? sanitize_text_field($_POST['_available_date']) : '');
                        update_post_meta($post_id, '_property_type', isset($_POST['_property_type']) ? sanitize_text_field($_POST['_property_type']) : '');
                        update_post_meta($post_id, '_address', isset($_POST['_address']) ? sanitize_text_field($_POST['_address']) : '');
                        update_post_meta($post_id, '_nearby_landmark', isset($_POST['_nearby_landmark']) ? sanitize_text_field($_POST['_nearby_landmark']) : '');
                        update_post_meta($post_id, '_nickname', isset($_POST['_nickname']) ? sanitize_text_field($_POST['_nickname']) : '');
                        update_post_meta($post_id, '_occupation', isset($_POST['_occupation']) ? sanitize_text_field($_POST['_occupation']) : '');
                        update_post_meta($post_id, '_cleanliness', isset($_POST['_cleanliness']) ? sanitize_text_field($_POST['_cleanliness']) : '');
                        update_post_meta($post_id, '_sleep_schedule', isset($_POST['_sleep_schedule']) ? sanitize_text_field($_POST['_sleep_schedule']) : '');
                        update_post_meta($post_id, '_roommate_preference', isset($_POST['_roommate_preference']) ? sanitize_textarea_field($_POST['_roommate_preference']) : '');
                    }

                    if ($listing_type === 'need_room') {
                        update_post_meta($post_id, '_budget_min', isset($_POST['_budget_min']) ? sanitize_text_field($_POST['_budget_min']) : '');
                        update_post_meta($post_id, '_budget_max', isset($_POST['_budget_max']) ? sanitize_text_field($_POST['_budget_max']) : '');
                        update_post_meta($post_id, '_move_in_date', isset($_POST['_move_in_date']) ? sanitize_text_field($_POST['_move_in_date']) : '');
                        update_post_meta($post_id, '_preferred_area_text', isset($_POST['_preferred_area_text']) ? sanitize_text_field($_POST['_preferred_area_text']) : '');
                        update_post_meta($post_id, '_preferred_property_type', isset($_POST['_preferred_property_type']) ? sanitize_text_field($_POST['_preferred_property_type']) : '');
                        update_post_meta($post_id, '_preferred_room_type', isset($_POST['_preferred_room_type']) ? sanitize_text_field($_POST['_preferred_room_type']) : '');
                        update_post_meta($post_id, '_teamup_ok', isset($_POST['_teamup_ok']) ? sanitize_text_field($_POST['_teamup_ok']) : '');
                        update_post_meta($post_id, '_nickname', isset($_POST['_nickname']) ? sanitize_text_field($_POST['_nickname']) : '');
                        update_post_meta($post_id, '_occupation', isset($_POST['_occupation']) ? sanitize_text_field($_POST['_occupation']) : '');
                        update_post_meta($post_id, '_cleanliness', isset($_POST['_cleanliness']) ? sanitize_text_field($_POST['_cleanliness']) : '');
                        update_post_meta($post_id, '_sleep_schedule', isset($_POST['_sleep_schedule']) ? sanitize_text_field($_POST['_sleep_schedule']) : '');
                        update_post_meta($post_id, '_ideal_roommate', isset($_POST['_ideal_roommate']) ? sanitize_textarea_field($_POST['_ideal_roommate']) : '');
                    }

                    $success_message = 'Your listing has been submitted and is waiting for review.';
                } else {
                    $error_message = 'Something went wrong while submitting your listing.';
                }
            }
        }
    }
}
?>

<main id="primary" class="site-main post-listing-page">
    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Post Listing</span>
            <h1 class="archive-title">Create Your Listing</h1>
            <p class="archive-description">
                Choose whether you have a room or need a room, then fill in the details so people can find a better match.
            </p>
        </div>
    </section>

    <section class="archive-filters">
        <div class="container" style="max-width: 900px;">
            <?php if (!empty($success_message)) : ?>
                <div class="single-card" style="border-color:#89e219;">
                    <p style="margin:0; font-weight:700;"><?php echo esc_html($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)) : ?>
                <div class="single-card" style="border-color:#d9534f;">
                    <p style="margin:0; font-weight:700;"><?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" class="filter-form">
                <?php wp_nonce_field('rmt_front_submit_action', 'rmt_front_submit_nonce'); ?>

                <div class="filter-grid" style="grid-template-columns: 1fr;">
                    <div class="filter-group">
                        <label for="listing_type">Listing Type</label>
                        <select name="listing_type" id="listing_type" required>
                            <option value="">Select one</option>
                            <option value="have_room" <?php selected(isset($_POST['listing_type']) ? $_POST['listing_type'] : '', 'have_room'); ?>>
                                I Have a Room
                            </option>
                            <option value="need_room" <?php selected(isset($_POST['listing_type']) ? $_POST['listing_type'] : '', 'need_room'); ?>>
                                I Need a Room
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="post_title">Title</label>
                        <input
                            type="text"
                            name="post_title"
                            id="post_title"
                            required
                            value="<?php echo isset($_POST['post_title']) ? esc_attr($_POST['post_title']) : ''; ?>"
                            placeholder="Example: Cozy condo near BTS looking for female roommate"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="post_content">Description</label>
                        <textarea
                            name="post_content"
                            id="post_content"
                            rows="6"
                            required
                            placeholder="Describe the room or what you are looking for..."
                        ><?php echo isset($_POST['post_content']) ? esc_textarea($_POST['post_content']) : ''; ?></textarea>
                    </div>
                </div>

                <div id="have-room-fields" class="single-card" style="margin-top: 1.5rem;">
                    <h2>Have Room Details</h2>
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="_rent">Monthly Rent</label>
                            <input type="text" name="_rent" id="_rent" value="<?php echo isset($_POST['_rent']) ? esc_attr($_POST['_rent']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_deposit">Deposit</label>
                            <input type="text" name="_deposit" id="_deposit" value="<?php echo isset($_POST['_deposit']) ? esc_attr($_POST['_deposit']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_available_date">Available Date</label>
                            <input type="date" name="_available_date" id="_available_date" value="<?php echo isset($_POST['_available_date']) ? esc_attr($_POST['_available_date']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_property_type">Property Type</label>
                            <input type="text" name="_property_type" id="_property_type" value="<?php echo isset($_POST['_property_type']) ? esc_attr($_POST['_property_type']) : ''; ?>" placeholder="Condo / Apartment / House">
                        </div>

                        <div class="filter-group">
                            <label for="_address">Address</label>
                            <input type="text" name="_address" id="_address" value="<?php echo isset($_POST['_address']) ? esc_attr($_POST['_address']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_nearby_landmark">Nearby Landmark / BTS / University</label>
                            <input type="text" name="_nearby_landmark" id="_nearby_landmark" value="<?php echo isset($_POST['_nearby_landmark']) ? esc_attr($_POST['_nearby_landmark']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_nickname">Nickname</label>
                            <input type="text" name="_nickname" id="_nickname" value="<?php echo isset($_POST['_nickname']) ? esc_attr($_POST['_nickname']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_occupation">Occupation</label>
                            <input type="text" name="_occupation" id="_occupation" value="<?php echo isset($_POST['_occupation']) ? esc_attr($_POST['_occupation']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_cleanliness">Cleanliness</label>
                            <input type="text" name="_cleanliness" id="_cleanliness" value="<?php echo isset($_POST['_cleanliness']) ? esc_attr($_POST['_cleanliness']) : ''; ?>" placeholder="Very clean / Moderate / Flexible">
                        </div>

                        <div class="filter-group">
                            <label for="_sleep_schedule">Sleep Schedule</label>
                            <input type="text" name="_sleep_schedule" id="_sleep_schedule" value="<?php echo isset($_POST['_sleep_schedule']) ? esc_attr($_POST['_sleep_schedule']) : ''; ?>" placeholder="Early bird / Night owl">
                        </div>

                        <div class="filter-group" style="grid-column: 1 / -1;">
                            <label for="_roommate_preference">Preferred Roommate</label>
                            <textarea name="_roommate_preference" id="_roommate_preference" rows="4"><?php echo isset($_POST['_roommate_preference']) ? esc_textarea($_POST['_roommate_preference']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="need-room-fields" class="single-card" style="margin-top: 1.5rem;">
                    <h2>Need Room Details</h2>
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="_budget_min">Budget Min</label>
                            <input type="text" name="_budget_min" id="_budget_min" value="<?php echo isset($_POST['_budget_min']) ? esc_attr($_POST['_budget_min']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_budget_max">Budget Max</label>
                            <input type="text" name="_budget_max" id="_budget_max" value="<?php echo isset($_POST['_budget_max']) ? esc_attr($_POST['_budget_max']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_move_in_date">Move-in Date</label>
                            <input type="date" name="_move_in_date" id="_move_in_date" value="<?php echo isset($_POST['_move_in_date']) ? esc_attr($_POST['_move_in_date']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_preferred_area_text">Preferred Area</label>
                            <input type="text" name="_preferred_area_text" id="_preferred_area_text" value="<?php echo isset($_POST['_preferred_area_text']) ? esc_attr($_POST['_preferred_area_text']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_preferred_property_type">Preferred Property Type</label>
                            <input type="text" name="_preferred_property_type" id="_preferred_property_type" value="<?php echo isset($_POST['_preferred_property_type']) ? esc_attr($_POST['_preferred_property_type']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_preferred_room_type">Preferred Room Type</label>
                            <input type="text" name="_preferred_room_type" id="_preferred_room_type" value="<?php echo isset($_POST['_preferred_room_type']) ? esc_attr($_POST['_preferred_room_type']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_teamup_ok">Open to Team-up?</label>
                            <input type="text" name="_teamup_ok" id="_teamup_ok" value="<?php echo isset($_POST['_teamup_ok']) ? esc_attr($_POST['_teamup_ok']) : ''; ?>" placeholder="Yes / No">
                        </div>

                        <div class="filter-group">
                            <label for="_nickname_need">Nickname</label>
                            <input type="text" name="_nickname" id="_nickname_need" value="<?php echo isset($_POST['_nickname']) ? esc_attr($_POST['_nickname']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_occupation_need">Occupation</label>
                            <input type="text" name="_occupation" id="_occupation_need" value="<?php echo isset($_POST['_occupation']) ? esc_attr($_POST['_occupation']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_cleanliness_need">Cleanliness</label>
                            <input type="text" name="_cleanliness" id="_cleanliness_need" value="<?php echo isset($_POST['_cleanliness']) ? esc_attr($_POST['_cleanliness']) : ''; ?>">
                        </div>

                        <div class="filter-group">
                            <label for="_sleep_schedule_need">Sleep Schedule</label>
                            <input type="text" name="_sleep_schedule" id="_sleep_schedule_need" value="<?php echo isset($_POST['_sleep_schedule']) ? esc_attr($_POST['_sleep_schedule']) : ''; ?>">
                        </div>

                        <div class="filter-group" style="grid-column: 1 / -1;">
                            <label for="_ideal_roommate">Ideal Roommate</label>
                            <textarea name="_ideal_roommate" id="_ideal_roommate" rows="4"><?php echo isset($_POST['_ideal_roommate']) ? esc_textarea($_POST['_ideal_roommate']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="filter-actions" style="margin-top: 1.5rem;">
                    <button type="submit" name="rmt_front_submit" value="1" class="btn btn-primary">
                        Submit Listing
                    </button>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const listingType = document.getElementById('listing_type');
    const haveRoomFields = document.getElementById('have-room-fields');
    const needRoomFields = document.getElementById('need-room-fields');

    function toggleListingFields() {
        const value = listingType ? listingType.value : '';

        if (haveRoomFields) {
            haveRoomFields.style.display = value === 'have_room' ? 'block' : 'none';
        }

        if (needRoomFields) {
            needRoomFields.style.display = value === 'need_room' ? 'block' : 'none';
        }
    }

    if (listingType) {
        listingType.addEventListener('change', toggleListingFields);
        toggleListingFields();
    }
});
</script>

<?php get_footer(); ?>