<?php
/**
 * functions.php
 * Theme Functions
 * Roommate Mobile Theme
 * @package RoommateMobileTheme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * ------------------------------------------------------------
 * 1. THEME CONSTANTS
 * ------------------------------------------------------------
 */
define( 'RMT_VERSION',   '1.0.0' );
define( 'RMT_THEME_DIR', get_template_directory());
define( 'RMT_THEME_URI', get_template_directory_uri());

/**
 * ------------------------------------------------------------
 * 2. THEME SETUP
 * ------------------------------------------------------------
 */
function rmt_theme_setup() {
    load_theme_textdomain('roommate-mobile-theme', RMT_THEME_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    add_theme_support('responsive-embeds');

    register_nav_menus(array(
        'primary'       => __('Primary Menu', 'roommate-mobile-theme'),
        'mobile_bottom' => __('Mobile Bottom Menu', 'roommate-mobile-theme'),
        'footer'        => __('Footer Menu', 'roommate-mobile-theme'),
    ));
}
add_action('after_setup_theme', 'rmt_theme_setup');

/**
 * ------------------------------------------------------------
 * 3. ENQUEUE STYLES AND SCRIPTS
 * ------------------------------------------------------------
 */
function rmt_enqueue_assets() {
    wp_enqueue_style(
        'rmt-style',
        get_stylesheet_uri(),
        array(),
        RMT_VERSION
    );

    wp_enqueue_script(
        'rmt-main',
        RMT_THEME_URI . '/assets/js/main.js',
        array('jquery'),
        RMT_VERSION,
        true
    );

    wp_localize_script('rmt-main', 'rmtData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('rmt_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'rmt_enqueue_assets');

/**
 * ------------------------------------------------------------
 * 4. CUSTOM POST TYPES
 * ------------------------------------------------------------
 */
function rmt_register_post_types() {

    // SHOW ROOMS
    register_post_type('room', array(
        'labels' => array(
            'name'               => __('Show Rooms', 'roommate-mobile-theme'),
            'singular_name'      => __('Show Room', 'roommate-mobile-theme'),
            'add_new'            => __('Add New', 'roommate-mobile-theme'),
            'add_new_item'       => __('Add New Show Room Listing', 'roommate-mobile-theme'),
            'edit_item'          => __('Edit Show Room Listing', 'roommate-mobile-theme'),
            'new_item'           => __('New Show Room Listing', 'roommate-mobile-theme'),
            'view_item'          => __('View Show Room Listing', 'roommate-mobile-theme'),
            'view_items'         => __('View Show Room Listings', 'roommate-mobile-theme'),
            'search_items'       => __('Search Show Rooms', 'roommate-mobile-theme'),
            'not_found'          => __('No show room listings found', 'roommate-mobile-theme'),
            'not_found_in_trash' => __('No show room listings found in Trash', 'roommate-mobile-theme'),
            'all_items'          => __('All Show Rooms', 'roommate-mobile-theme'),
            'archives'           => __('Show Room Archives', 'roommate-mobile-theme'),
            'menu_name'          => __('Show Rooms', 'roommate-mobile-theme'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => false,
        'show_in_rest'       => true,
        'has_archive'        => 'room',
        'rewrite'            => array(
            'slug'       => 'room',
            'with_front' => false,
        ),
        'menu_icon'          => 'dashicons-admin-home',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
        'hierarchical'       => false,
        'menu_position'      => 5,
        'exclude_from_search'=> false,
    ));

    // ROOMMATE
    register_post_type('roommate', array(
        'labels' => array(
            'name'               => __('Roommates', 'roommate-mobile-theme'),
            'singular_name'      => __('Roommate', 'roommate-mobile-theme'),
            'add_new'            => __('Add New', 'roommate-mobile-theme'),
            'add_new_item'       => __('Add New Roommate Profile', 'roommate-mobile-theme'),
            'edit_item'          => __('Edit Roommate Profile', 'roommate-mobile-theme'),
            'new_item'           => __('New Roommate Profile', 'roommate-mobile-theme'),
            'view_item'          => __('View Roommate Profile', 'roommate-mobile-theme'),
            'view_items'         => __('View Roommate Profiles', 'roommate-mobile-theme'),
            'search_items'       => __('Search Roommates', 'roommate-mobile-theme'),
            'not_found'          => __('No roommate profiles found', 'roommate-mobile-theme'),
            'not_found_in_trash' => __('No roommate profiles found in Trash', 'roommate-mobile-theme'),
            'all_items'          => __('All Roommates', 'roommate-mobile-theme'),
            'archives'           => __('Roommate Archives', 'roommate-mobile-theme'),
            'menu_name'          => __('Roommates', 'roommate-mobile-theme'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => false,
        'show_in_rest'       => true,
        'has_archive'        => 'roommate',
        'rewrite'            => array(
            'slug'       => 'roommate',
            'with_front' => false,
        ),
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
        'hierarchical'       => false,
        'menu_position'      => 6,
        'exclude_from_search'=> false,
    ));
}
add_action('init', 'rmt_register_post_types');

/**
 * ------------------------------------------------------------
 * 5. TAXONOMIES
 * ------------------------------------------------------------
 */
function rmt_register_taxonomies() {
    register_taxonomy('location_area', array('room', 'roommate'), array(
        'labels' => array(
            'name'          => __('Location Areas', 'roommate-mobile-theme'),
            'singular_name' => __('Location Area', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => array('slug' => 'location-area'),
        'show_in_rest' => true,
    ));

    register_taxonomy('amenity', array('room'), array(
        'labels' => array(
            'name'          => __('Amenities', 'roommate-mobile-theme'),
            'singular_name' => __('Amenity', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => false,
        'rewrite'      => array('slug' => 'amenity'),
        'show_in_rest' => true,
    ));

    register_taxonomy('lifestyle', array('room', 'roommate'), array(
        'labels' => array(
            'name'          => __('Lifestyle Tags', 'roommate-mobile-theme'),
            'singular_name' => __('Lifestyle Tag', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => false,
        'rewrite'      => array('slug' => 'lifestyle'),
        'show_in_rest' => true,
    ));

    register_taxonomy('room_type', array('room', 'roommate'), array(
        'labels' => array(
            'name'          => __('Room Types', 'roommate-mobile-theme'),
            'singular_name' => __('Room Type', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => array('slug' => 'room-type'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'rmt_register_taxonomies');

/**
 * ------------------------------------------------------------
 * 6. META BOXES
 * ------------------------------------------------------------
 */
function rmt_add_meta_boxes() {
    add_meta_box(
        'rmt_room_details',
        __('Show Room Details', 'roommate-mobile-theme'),
        'rmt_render_room_meta_box',
        'room',
        'normal',
        'high'
    );

    add_meta_box(
        'rmt_roommate_details',
        __('Roommate Details', 'roommate-mobile-theme'),
        'rmt_render_roommate_meta_box',
        'roommate',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'rmt_add_meta_boxes');

/**
 * ------------------------------------------------------------
 * FLUSH REWRITE RULES ON THEME SWITCH
 * ------------------------------------------------------------
 */
function rmt_flush_rewrite_rules_on_switch() {
    rmt_register_post_types();
    rmt_register_taxonomies();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'rmt_flush_rewrite_rules_on_switch');

/**
 * ------------------------------------------------------------
 * 7. SHOW ROOM META BOX HTML
 * ------------------------------------------------------------
 */
function rmt_render_room_meta_box($post) {
    wp_nonce_field('rmt_save_meta', 'rmt_meta_nonce');

    $fields = array(
        '_property_name',
        '_property_type',
        '_address',
        '_map_url',
        '_rent',
        '_bills_included',
        '_deposit',
        '_available_date',
        '_gender_preference',
        '_pet_policy',
        '_smoking_policy',
        '_utilities',
        '_nearby_landmark',
        '_min_stay',

        '_nickname',
        '_age',
        '_gender',
        '_occupation',
        '_languages',
        '_cleanliness',
        '_sleep_schedule',
        '_smoker',
        '_has_pets',
        '_social_level',
        '_hobbies',
        '_bio',
        '_roommate_preference',
    );

    foreach ($fields as $field) {
        ${trim($field, '_')} = get_post_meta($post->ID, $field, true);
    }
    ?>
    <table class="form-table">
    <tr><th colspan="2"><h2>Room Details</h2></th></tr>

    <tr>
        <th><label for="_property_name">Property Name</label></th>
        <td><input type="text" name="_property_name" id="_property_name" value="<?php echo esc_attr($property_name); ?>" class="regular-text" placeholder="Apartment / Condo Name"></td>
    </tr>

    <tr>
        <th><label for="_property_type">Property Type</label></th>
        <td>
            <select name="_property_type" id="_property_type">
                <option value="">Select property type</option>
                <option value="Apartment" <?php selected($property_type, 'Apartment'); ?>>Apartment</option>
                <option value="Condo" <?php selected($property_type, 'Condo'); ?>>Condo</option>
                <option value="House" <?php selected($property_type, 'House'); ?>>House</option>
                <option value="Studio" <?php selected($property_type, 'Studio'); ?>>Studio</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="_address">Property Location</label></th>
        <td><input type="text" name="_address" id="_address" value="<?php echo esc_attr($address); ?>" class="regular-text" placeholder="Address / area"></td>
    </tr>

    <tr>
        <th><label for="_map_url">Google Map URL</label></th>
        <td><input type="url" name="_map_url" id="_map_url" value="<?php echo esc_attr($map_url); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="_rent">Rent Fees</label></th>
        <td><input type="number" name="_rent" id="_rent" value="<?php echo esc_attr($rent); ?>" class="regular-text" placeholder="Amount roommate pays"></td>
    </tr>

    <tr>
        <th><label for="_bills_included">Bills Included</label></th>
        <td>
            <select name="_bills_included" id="_bills_included">
                <option value="">Select</option>
                <option value="Included" <?php selected($bills_included, 'Included'); ?>>Included</option>
                <option value="Not Included" <?php selected($bills_included, 'Not Included'); ?>>Not Included</option>
                <option value="Partially Included" <?php selected($bills_included, 'Partially Included'); ?>>Partially Included</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="_deposit">Deposit</label></th>
        <td><input type="number" name="_deposit" id="_deposit" value="<?php echo esc_attr($deposit); ?>" class="regular-text" placeholder="0 if none"></td>
    </tr>

    <tr>
        <th><label for="_available_date">Available From</label></th>
        <td><input type="date" name="_available_date" id="_available_date" value="<?php echo esc_attr($available_date); ?>"></td>
    </tr>

    <tr>
        <th><label for="_gender_preference">Gender Preference</label></th>
        <td>
            <select name="_gender_preference" id="_gender_preference">
                <option value="">Select</option>
                <option value="Male" <?php selected($gender_preference, 'Male'); ?>>Male</option>
                <option value="Female" <?php selected($gender_preference, 'Female'); ?>>Female</option>
                <option value="Others" <?php selected($gender_preference, 'Others'); ?>>Others</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="_pet_policy">Pet Policy</label></th>
        <td>
            <select name="_pet_policy" id="_pet_policy">
                <option value="">Select</option>
                <option value="Accept" <?php selected($pet_policy, 'Accept'); ?>>Accept</option>
                <option value="Not Accept" <?php selected($pet_policy, 'Not Accept'); ?>>Not Accept</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="_smoking_policy">Smoking Policy</label></th>
        <td>
            <select name="_smoking_policy" id="_smoking_policy">
                <option value="">Select</option>
                <option value="Accept" <?php selected($smoking_policy, 'Accept'); ?>>Accept</option>
                <option value="Not Accept" <?php selected($smoking_policy, 'Not Accept'); ?>>Not Accept</option>
            </select>
        </td>
    </tr>

    <tr>
        <th><label for="_utilities">Bills / Utilities Notes</label></th>
        <td><input type="text" name="_utilities" id="_utilities" value="<?php echo esc_attr($utilities); ?>" class="regular-text" placeholder="Water, electricity, wifi, etc."></td>
    </tr>

    <tr>
        <th><label for="_nearby_landmark">Nearby Landmark</label></th>
        <td><input type="text" name="_nearby_landmark" id="_nearby_landmark" value="<?php echo esc_attr($nearby_landmark); ?>" class="regular-text"></td>
    </tr>

    <tr>
        <th><label for="_min_stay">Minimum Stay</label></th>
        <td><input type="text" name="_min_stay" id="_min_stay" value="<?php echo esc_attr($min_stay); ?>" class="regular-text" placeholder="e.g. 6 months"></td>
    </tr>

    <tr><th colspan="2"><h2>Current Roommate Details</h2></th></tr>

    <tr><th><label for="_nickname">Nickname</label></th><td><input type="text" name="_nickname" id="_nickname" value="<?php echo esc_attr($nickname); ?>" class="regular-text"></td></tr>
    <tr><th><label for="_age">Age</label></th><td><input type="number" name="_age" id="_age" value="<?php echo esc_attr($age); ?>"></td></tr>

        <tr>
            <th><label for="_gender">Gender</label></th>
            <td>
                <select name="_gender" id="_gender">
                    <option value="">Select</option>
                    <option value="Male" <?php selected($gender, 'Male'); ?>>Male</option>
                    <option value="Female" <?php selected($gender, 'Female'); ?>>Female</option>
                    <option value="Others" <?php selected($gender, 'Others'); ?>>Others</option>
                </select>
            </td>
        </tr>

        <tr><th><label for="_occupation">Occupation</label></th><td><input type="text" name="_occupation" id="_occupation" value="<?php echo esc_attr($occupation); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_languages">Languages</label></th><td><input type="text" name="_languages" id="_languages" value="<?php echo esc_attr($languages); ?>" class="regular-text"></td></tr>

        <tr>
            <th><label for="_cleanliness">Cleanliness</label></th>
            <td>
                <select name="_cleanliness" id="_cleanliness">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected($cleanliness, 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected($cleanliness, 'No'); ?>>No</option>
                </select>
            </td>
        </tr>

        <tr><th><label for="_sleep_schedule">Sleep Schedule</label></th><td><input type="text" name="_sleep_schedule" id="_sleep_schedule" value="<?php echo esc_attr($sleep_schedule); ?>" class="regular-text" placeholder="11 PM to 7 AM"></td></tr>

        <tr>
            <th><label for="_smoker">Smoker</label></th>
            <td>
                <select name="_smoker" id="_smoker">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected($smoker, 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected($smoker, 'No'); ?>>No</option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="_has_pets">Has Pets</label></th>
            <td>
                <select name="_has_pets" id="_has_pets">
                    <option value="">Select</option>
                    <option value="Yes" <?php selected($has_pets, 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected($has_pets, 'No'); ?>>No</option>
                </select>
            </td>
        </tr>

        <tr><th><label for="_social_level">Social Level</label></th><td><input type="number" min="0" max="10" name="_social_level" id="_social_level" value="<?php echo esc_attr($social_level); ?>"></td></tr>
        <tr><th><label for="_hobbies">Hobbies</label></th><td><input type="text" name="_hobbies" id="_hobbies" value="<?php echo esc_attr($hobbies); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_bio">Bio</label></th><td><textarea name="_bio" id="_bio" rows="4" class="large-text"><?php echo esc_textarea($bio); ?></textarea></td></tr>
        <tr><th><label for="_roommate_preference">Preferred Roommate</label></th><td><textarea name="_roommate_preference" id="_roommate_preference" rows="4" class="large-text"><?php echo esc_textarea($roommate_preference); ?></textarea></td></tr>
    </table>
    <?php
}

/**
 * ------------------------------------------------------------
 * 8. ROOMMATE META BOX HTML
 * ------------------------------------------------------------
 */
function rmt_render_roommate_meta_box($post) {
    wp_nonce_field('rmt_save_meta', 'rmt_meta_nonce');

    $fields = array(
        '_budget_min',
        '_budget_max',
        '_move_in_date',
        '_preferred_property_type',
        '_preferred_room_type',
        '_lease_duration',
        '_preferred_area_text',
        '_private_or_shared',
        '_teamup_ok',
        '_gender_preference',
        '_pets_ok',
        '_smokers_ok',
        '_nickname',
        '_age',
        '_gender',
        '_occupation',
        '_languages',
        '_cleanliness',
        '_sleep_schedule',
        '_smoker',
        '_has_pets',
        '_social_level',
        '_hobbies',
        '_bio',
        '_ideal_roommate',
    );

    foreach ($fields as $field) {
        ${trim($field, '_')} = get_post_meta($post->ID, $field, true);
    }
    ?>
    <table class="form-table">
        <tr><th><label for="_budget_min">Budget Min</label></th><td><input type="text" name="_budget_min" id="_budget_min" value="<?php echo esc_attr($budget_min); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_budget_max">Budget Max</label></th><td><input type="text" name="_budget_max" id="_budget_max" value="<?php echo esc_attr($budget_max); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_move_in_date">Move-in Date</label></th><td><input type="date" name="_move_in_date" id="_move_in_date" value="<?php echo esc_attr($move_in_date); ?>"></td></tr>
        <tr><th><label for="_preferred_property_type">Preferred Property Type</label></th><td><input type="text" name="_preferred_property_type" id="_preferred_property_type" value="<?php echo esc_attr($preferred_property_type); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_preferred_room_type">Preferred Room Type</label></th><td><input type="text" name="_preferred_room_type" id="_preferred_room_type" value="<?php echo esc_attr($preferred_room_type); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_lease_duration">Lease Duration</label></th><td><input type="text" name="_lease_duration" id="_lease_duration" value="<?php echo esc_attr($lease_duration); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_preferred_area_text">Preferred Area</label></th><td><input type="text" name="_preferred_area_text" id="_preferred_area_text" value="<?php echo esc_attr($preferred_area_text); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_private_or_shared">Private or Shared</label></th><td><input type="text" name="_private_or_shared" id="_private_or_shared" value="<?php echo esc_attr($private_or_shared); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_teamup_ok">Open to Team-up</label></th><td><input type="text" name="_teamup_ok" id="_teamup_ok" value="<?php echo esc_attr($teamup_ok); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_gender_preference">Gender Preference</label></th><td><input type="text" name="_gender_preference" id="_gender_preference" value="<?php echo esc_attr($gender_preference); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_pets_ok">Pets OK</label></th><td><input type="text" name="_pets_ok" id="_pets_ok" value="<?php echo esc_attr($pets_ok); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_smokers_ok">Smokers OK</label></th><td><input type="text" name="_smokers_ok" id="_smokers_ok" value="<?php echo esc_attr($smokers_ok); ?>" class="regular-text"></td></tr>

        <tr><th><label for="_nickname">Nickname</label></th><td><input type="text" name="_nickname" id="_nickname" value="<?php echo esc_attr($nickname); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_age">Age</label></th><td><input type="number" name="_age" id="_age" value="<?php echo esc_attr($age); ?>"></td></tr>
        <tr><th><label for="_gender">Gender</label></th><td><input type="text" name="_gender" id="_gender" value="<?php echo esc_attr($gender); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_occupation">Occupation</label></th><td><input type="text" name="_occupation" id="_occupation" value="<?php echo esc_attr($occupation); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_languages">Languages</label></th><td><input type="text" name="_languages" id="_languages" value="<?php echo esc_attr($languages); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_cleanliness">Cleanliness</label></th><td><input type="text" name="_cleanliness" id="_cleanliness" value="<?php echo esc_attr($cleanliness); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_sleep_schedule">Sleep Schedule</label></th><td><input type="text" name="_sleep_schedule" id="_sleep_schedule" value="<?php echo esc_attr($sleep_schedule); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_smoker">Smoker</label></th><td><input type="text" name="_smoker" id="_smoker" value="<?php echo esc_attr($smoker); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_has_pets">Has Pets</label></th><td><input type="text" name="_has_pets" id="_has_pets" value="<?php echo esc_attr($has_pets); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_social_level">Social Level</label></th><td><input type="text" name="_social_level" id="_social_level" value="<?php echo esc_attr($social_level); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_hobbies">Hobbies</label></th><td><input type="text" name="_hobbies" id="_hobbies" value="<?php echo esc_attr($hobbies); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_bio">Bio</label></th><td><textarea name="_bio" id="_bio" rows="4" class="large-text"><?php echo esc_textarea($bio); ?></textarea></td></tr>
        <tr><th><label for="_ideal_roommate">Ideal Roommate</label></th><td><textarea name="_ideal_roommate" id="_ideal_roommate" rows="4" class="large-text"><?php echo esc_textarea($ideal_roommate); ?></textarea></td></tr>
    </table>
    <?php
}

/**
 * ------------------------------------------------------------
 * 9. SAVE META BOX DATA
 * ------------------------------------------------------------
 */
function rmt_get_chat_url( $author_id, $post_id ) {
    return add_query_arg(
        [ 'recipient' => $author_id, 'listing' => $post_id ],
        home_url( '/messages/' )
    );
}

function rmt_save_post_meta($post_id) {
    if (!isset($_POST['rmt_meta_nonce']) || !wp_verify_nonce($_POST['rmt_meta_nonce'], 'rmt_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $text_fields = array(
        '_property_type',
        '_poperty_name',
        '_bills_included',
        '_address',
        '_nearby_landmark',
        '_utilities',
        '_min_stay',
        '_gender_preference',
        '_pet_policy',
        '_smoking_policy',
        '_nickname',
        '_gender',
        '_occupation',
        '_languages',
        '_cleanliness',
        '_sleep_schedule',
        '_smoker',
        '_has_pets',
        '_social_level',
        '_hobbies',
        '_preferred_property_type',
        '_preferred_room_type',
        '_lease_duration',
        '_preferred_area_text',
        '_private_or_shared',
        '_teamup_ok',
        '_pets_ok',
        '_smokers_ok',
    );

    $textarea_fields = array(
        '_bio',
        '_roommate_preference',
        '_ideal_roommate',
    );

    $url_fields = array(
        '_map_url',
    );

    $number_fields = array(
        '_rent',
        '_deposit',
        '_bedrooms',
        '_bathrooms',
        '_age',
        '_budget_min',
        '_budget_max',
    );

    $date_fields = array(
        '_available_date',
        '_move_in_date',
    );

    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    foreach ($textarea_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
        }
    }

    foreach ($url_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, esc_url_raw($_POST[$field]));
        }
    }

    foreach ($number_fields as $field) {
        if (isset($_POST[$field])) {
            $value = is_numeric($_POST[$field]) ? $_POST[$field] : '';
            update_post_meta($post_id, $field, $value);
        }
    }

    foreach ($date_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_room', 'rmt_save_post_meta');
add_action('save_post_roommate', 'rmt_save_post_meta');

/**
 * ------------------------------------------------------------
 * 10. HELPER FUNCTIONS
 * ------------------------------------------------------------
 */
function rmt_get_meta($post_id, $key, $default = '') {
    $value = get_post_meta($post_id, $key, true);
    return !empty($value) ? $value : $default;
}

function rmt_format_price($amount) {
    if (!$amount) {
        return '';
    }

    return number_format((float) $amount) . ' THB';
}

/**
 * ------------------------------------------------------------
 * 11. SIDEBARS / WIDGET AREAS
 * ------------------------------------------------------------
 */
function rmt_register_sidebars() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'roommate-mobile-theme'),
        'id'            => 'main-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'rmt_register_sidebars');

/**
 * Get default profile photo attachment ID.
 * Source file:
 * /wp-content/themes/roommate-mobile-theme/images/default-profile.jpg
 */
function rmt_get_default_profile_photo_id() {
    $existing_id = absint(get_option('rmt_default_profile_photo_id'));

    if ($existing_id && get_post($existing_id)) {
        return $existing_id;
    }

    $file_path = get_template_directory() . '/images/default-profile.jpg';

    if (!file_exists($file_path)) {
        return 0;
    }

    $file_type = wp_check_filetype(basename($file_path), null);

    $upload_dir = wp_upload_dir();
    $new_file_path = $upload_dir['path'] . '/rmt-default-profile.jpg';

    if (!file_exists($new_file_path)) {
        copy($file_path, $new_file_path);
    }

    $attachment = [
        'guid' => $upload_dir['url'] . '/rmt-default-profile.jpg',
        'post_mime_type' => $file_type['type'],
        'post_title'     => 'Default Profile Photo',
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attachment_id = wp_insert_attachment($attachment, $new_file_path);

    if (is_wp_error($attachment_id) || !$attachment_id) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_data = wp_generate_attachment_metadata($attachment_id, $new_file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_data);

    update_option('rmt_default_profile_photo_id', $attachment_id);

    return $attachment_id;
}

/**
 * Upload profile photo if user uploaded one.
 * Otherwise return default profile photo ID.
 */
function rmt_get_uploaded_or_default_profile_photo_id($field_name, $post_id) {
    if (!empty($_FILES[$field_name]['name'])) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload($field_name, $post_id);

        if (!is_wp_error($attachment_id)) {
            return $attachment_id;
        }
    }

    return rmt_get_default_profile_photo_id();
}

/**
 * ------------------------------------------------------------
 * 12. AJAX LOAD MORE EXAMPLE
 * ------------------------------------------------------------
 */
function rmt_ajax_load_more() {
    check_ajax_referer('rmt_nonce', 'nonce');

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'room';
    $paged     = isset($_POST['paged']) ? absint($_POST['paged']) : 1;

    $query = new WP_Query(array(
        'post_type'      => $post_type,
        'posts_per_page' => 6,
        'paged'          => $paged,
        'post_status'    => 'publish',
    ));

    if ($query->have_posts()) {
        ob_start();

        while ($query->have_posts()) {
            $query->the_post();
            ?>
            <article class="listing-card">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo esc_html(get_the_excerpt()); ?></p>
            </article>
            <?php
        }

        wp_reset_postdata();

        wp_send_json_success(array(
            'html' => ob_get_clean(),
        ));
    }

    wp_send_json_error(array(
        'message' => __('No more listings found.', 'roommate-mobile-theme'),
    ));
}
add_action('wp_ajax_rmt_load_more', 'rmt_ajax_load_more');
add_action('wp_ajax_nopriv_rmt_load_more', 'rmt_ajax_load_more');

function rmt_primary_menu_fallback() {
    echo '<ul class="menu primary-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('room')) . '">Show Rooms</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('roommate')) . '">Roommates</a></li>';
    echo '</ul>';
}



/**
 * FUNCTIONS.PHP ADDITIONS
 * ============================================================
 * Add these changes to your existing functions.php:
 *
 * 1. In rmt_save_post_meta(), add the missing fields to the
 *    correct arrays (shown below).
 *
 * 2. Add rmt_allow_subscriber_uploads() to grant logged-in
 *    users permission to upload images via the front-end form.
 * ============================================================
 */

// ---------------------------------------------------------------
// PATCH 1 — Add to $text_fields array inside rmt_save_post_meta:
// ---------------------------------------------------------------
//   '_property_name',
//   '_bills_included',
//   '_age',          // currently in $number_fields — keep there
//   '_gender',
//   '_lease_duration',
//   '_private_or_shared',
//   '_pets_ok',
//   '_smokers_ok',
//   '_preferred_room_type',   // already present — confirm
//   '_preferred_property_type', // already present — confirm

// Full corrected $text_fields for copy-paste:
/*
$text_fields = array(
    '_property_name',        // ← NEW
    '_bills_included',       // ← NEW
    '_property_type',
    '_address',
    '_nearby_landmark',
    '_utilities',
    '_min_stay',
    '_gender_preference',
    '_pet_policy',
    '_smoking_policy',
    '_nickname',
    '_gender',
    '_occupation',
    '_languages',
    '_cleanliness',
    '_sleep_schedule',
    '_smoker',
    '_has_pets',
    '_social_level',
    '_hobbies',
    '_preferred_property_type',
    '_preferred_room_type',
    '_lease_duration',
    '_preferred_area_text',
    '_private_or_shared',
    '_teamup_ok',
    '_pets_ok',
    '_smokers_ok',
);
*/

// ---------------------------------------------------------------
// PATCH 2 — Allow front-end image uploads for logged-in users.
// Paste this new function anywhere in functions.php.
// ---------------------------------------------------------------
function rmt_allow_subscriber_uploads( $caps, $cap, $user_id ) {
    if ( 'upload_files' === $cap && $user_id && is_user_logged_in() ) {
        $caps = array( 'exist' ); // grant the capability
    }
    return $caps;
}
add_filter( 'user_has_cap', 'rmt_allow_subscriber_uploads', 10, 3 );

// ---------------------------------------------------------------
// PATCH 3 — Show success notice on dashboard after redirect.
// Paste this inside your page-dashboard.php, or use the snippet
// below inside any get_header() template:
// ---------------------------------------------------------------
/*
<?php if ( isset($_GET['listing_submitted']) && $_GET['listing_submitted'] == '1' ) : ?>
    <div class="single-card" style="border-color:#89e219; margin-bottom:1rem;">
        <p style="margin:0; font-weight:700;">
            Your listing has been submitted and is pending review.
        </p>
    </div>
<?php endif; ?>
*/


/* ================================================================
   1. MARK AS CLOSED
   Stores a post meta flag and re-labels the post status visually.
================================================================ */
add_action('wp_ajax_rmt_mark_closed',        'rmt_ajax_mark_closed');
add_action('wp_ajax_nopriv_rmt_mark_closed', 'rmt_ajax_mark_closed'); // remove if guests shouldn't act
 
function rmt_ajax_mark_closed() {
    $post_id = absint($_POST['post_id'] ?? 0);
    $nonce   = sanitize_text_field($_POST['nonce'] ?? '');
 
    if (!wp_verify_nonce($nonce, 'rmt_mark_closed_' . $post_id)) {
        wp_send_json_error('Invalid request.');
    }
 
    // Only the author (or admin) may close their own listing
    $author_id = (int) get_post_field('post_author', $post_id);
    if (get_current_user_id() !== $author_id && !current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }
 
    update_post_meta($post_id, '_listing_closed', 1);
    wp_send_json_success('Marked as closed.');
}
 
/* ================================================================
   2. UNPUBLISH (move to draft)
================================================================ */
add_action('wp_ajax_rmt_unpublish', 'rmt_ajax_unpublish');
 
function rmt_ajax_unpublish() {
    $post_id = absint($_POST['post_id'] ?? 0);
    $nonce   = sanitize_text_field($_POST['nonce'] ?? '');
 
    if (!wp_verify_nonce($nonce, 'rmt_unpublish_' . $post_id)) {
        wp_send_json_error('Invalid request.');
    }
 
    $author_id = (int) get_post_field('post_author', $post_id);
    if (get_current_user_id() !== $author_id && !current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }
 
    $result = wp_update_post([
        'ID'          => $post_id,
        'post_status' => 'draft',
    ]);
 
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }
 
    wp_send_json_success('Listing unpublished.');
}
 
/* ================================================================
   3. REPORT LISTING
   Increments a report counter; you can review these in WP Admin.
================================================================ */
add_action('wp_ajax_rmt_report_listing',        'rmt_ajax_report_listing');
add_action('wp_ajax_nopriv_rmt_report_listing', 'rmt_ajax_report_listing');
 
function rmt_ajax_report_listing() {
    $post_id = absint($_POST['post_id'] ?? 0);
    $nonce   = sanitize_text_field($_POST['nonce'] ?? '');
 
    if (!wp_verify_nonce($nonce, 'rmt_report_' . $post_id)) {
        wp_send_json_error('Invalid request.');
    }
 
    // Prevent the author from reporting their own listing
    $author_id = (int) get_post_field('post_author', $post_id);
    if (is_user_logged_in() && get_current_user_id() === $author_id) {
        wp_send_json_error('You cannot report your own listing.');
    }
 
    // Bump report count
    $count = (int) get_post_meta($post_id, '_report_count', true);
    update_post_meta($post_id, '_report_count', $count + 1);
 
    // Optional: log reporter user ID (deduplicate reports per user)
    if (is_user_logged_in()) {
        $reporters   = get_post_meta($post_id, '_reporters', true) ?: [];
        $reporters[] = get_current_user_id();
        update_post_meta($post_id, '_reporters', array_unique($reporters));
    }
 
    // Optional: auto-flag for admin review after N reports
    $threshold = 5;
    if (($count + 1) >= $threshold) {
        update_post_meta($post_id, '_flagged_for_review', 1);
    }
 
    wp_send_json_success('Reported.');
}
 

add_action( 'admin_menu', function () {
    add_management_page( 'Waitlist', 'Waitlist', 'manage_options', 'bkk-waitlist', function () {
        $list = get_option( 'bkkroomie_waitlist', [] );
        echo '<div class="wrap"><h1>BKKroomie Waitlist (' . count( $list ) . ')</h1>';
        echo '<table class="widefat"><thead><tr><th>Email</th><th>Date</th></tr></thead><tbody>';
        foreach ( $list as $entry ) {
            echo '<tr><td>' . esc_html( $entry['email'] ) . '</td><td>' . esc_html( $entry['date'] ) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    });
});

/**
 * ================================================================
 * FUNCTIONS.PHP ADDITIONS
 * Add these snippets to the bottom of your existing functions.php
 * ================================================================
 *
 * These additions:
 *  1. Flush rewrite rules when 'edit-roommate' page is created,
 *     so the URL /edit-roommate/?edit_id=N works immediately.
 *  2. Redirect logged-out users away from /edit-roommate/ gracefully.
 *  3. Add `_budget_min` and `_budget_max` as number fields in
 *     rmt_save_post_meta() (patch for the admin meta box saver too).
 * ================================================================
 */

// ---------------------------------------------------------------
// 1. Ensure the Edit Roommate page slug resolves correctly.
//    Create a WordPress page with:
//      Title: "Edit Roommate Profile"
//      Slug:  "edit-roommate"
//      Template: "Edit Roommate Profile"   ← page-edit-roommate.php
//    Then flush permalinks once via Settings → Permalinks → Save.
// ---------------------------------------------------------------

// ---------------------------------------------------------------
// 2. Protect /edit-roommate/ from unauthenticated access via
//    template_redirect (belt-and-suspenders in addition to the
//    check inside the template itself).
// ---------------------------------------------------------------
add_action('template_redirect', 'rmt_protect_edit_roommate_page');

function rmt_protect_edit_roommate_page() {
    if (
        is_page('edit-roommate') &&
        !is_user_logged_in()
    ) {
        wp_redirect(wp_login_url(get_permalink()));
        exit;
    }
}

// ---------------------------------------------------------------
// 3. PATCH rmt_save_post_meta() to also save _budget_min /
//    _budget_max from the admin meta box (they were missing from
//    the $number_fields array).
//    If you have already patched rmt_save_post_meta(), skip this.
//
//    This standalone save_post hook covers any admin saves that
//    still miss those two fields.
// ---------------------------------------------------------------
add_action('save_post_roommate', 'rmt_save_budget_meta', 20);

function rmt_save_budget_meta($post_id) {
    if (!isset($_POST['rmt_meta_nonce']) || !wp_verify_nonce($_POST['rmt_meta_nonce'], 'rmt_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    foreach (['_budget_min', '_budget_max'] as $field) {
        if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
            update_post_meta($post_id, $field, $_POST[$field]);
        }
    }
}

add_action('pre_get_posts', function ($query) {

    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Only for roommate archive
    if (!$query->is_post_type_archive('roommate')) {
        return;
    }

    // Default order: newest listing first
    $sort = sanitize_text_field($_GET['sort'] ?? 'newest');
    $query->set('orderby', 'date');
    $query->set('order', $sort === 'oldest' ? 'ASC' : 'DESC');

    // Search (q)
    if (!empty($_GET['q'])) {
        $query->set('s', sanitize_text_field($_GET['q']));
    }

    // Meta filters
    $meta_query = ['relation' => 'AND'];

    // Budget filtering: keep it simple (posts with _budget_min/_budget_max)
    // budget_min param means: listing budget_max >= budget_min OR budget_min >= budget_min (fallback)
    $budget_min = isset($_GET['budget_min']) ? floatval($_GET['budget_min']) : null;
    if ($budget_min !== null && $_GET['budget_min'] !== '') {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => '_budget_max',
                'value'   => $budget_min,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            ],
            [
                'key'     => '_budget_min',
                'value'   => $budget_min,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            ],
        ];
    }

    // budget_max param means: listing budget_min <= budget_max OR budget_max <= budget_max (fallback)
    $budget_max = isset($_GET['budget_max']) ? floatval($_GET['budget_max']) : null;
    if ($budget_max !== null && $_GET['budget_max'] !== '') {
        $meta_query[] = [
            'relation' => 'OR',
            [
                'key'     => '_budget_min',
                'value'   => $budget_max,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ],
            [
                'key'     => '_budget_max',
                'value'   => $budget_max,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ],
        ];
    }

    // Move-in date (on/after)
    if (!empty($_GET['move_in'])) {
        $meta_query[] = [
            'key'     => '_move_in_date',
            'value'   => sanitize_text_field($_GET['move_in']),
            'type'    => 'DATE',
            'compare' => '>=',
        ];
    }


    if (count($meta_query) > 1) {
        $query->set('meta_query', $meta_query);
    }

    // Taxonomy filters
    $tax_query = ['relation' => 'AND'];

    if (!empty($_GET['location_area'])) {
        $tax_query[] = [
            'taxonomy' => 'location_area',
            'field'    => 'term_id',
            'terms'    => [absint($_GET['location_area'])],
        ];
    }


    if (count($tax_query) > 1) {
        $query->set('tax_query', $tax_query);
    }
});

/**
 * ================================================================
 * FRONTEND-ONLY USER EXPERIENCE
 * ================================================================
 * Normal users:
 * - no WP admin bar
 * - no wp-admin access
 * - can upload images from frontend forms
 * - edit room and roommate only from frontend pages
 * 
 * Admins:
 * - can still access wp-admin normally
 * ================================================================
 */

/**
 * Hide admin bar for all non-admin users on frontend.
 */
add_filter('show_admin_bar', 'rmt_hide_admin_bar_for_normal_users');

function rmt_hide_admin_bar_for_normal_users($show) {
    if (!is_user_logged_in()) {
        return false;
    }

    if (current_user_can('manage_options')) {
        return $show;
    }

    return false;
}

/**
 * Stop normal users from accessing wp-admin.
 * Admin users can still access wp-admin.
 */
add_action('admin_init', 'rmt_block_wp_admin_for_normal_users');

function rmt_block_wp_admin_for_normal_users() {
    if (!is_user_logged_in()) {
        return;
    }

    if (current_user_can('manage_options')) {
        return;
    }

    if (wp_doing_ajax()) {
        return;
    }

    wp_redirect(home_url('/dashboard/'));
    exit;
}

/**
 * Allow logged-in frontend users to upload images.
 * Needed for room images and roommate profile photos.
 */
add_filter('user_has_cap', 'rmt_allow_frontend_user_uploads', 10, 3);

function rmt_allow_frontend_user_uploads($allcaps, $caps, $args) {
    if (isset($args[0]) && $args[0] === 'upload_files' && is_user_logged_in()) {
        $allcaps['upload_files'] = true;
    }

    return $allcaps;
}

/**
 * Protect frontend edit pages.
 * Logged-out users go to login page.
 */
add_action('template_redirect', 'rmt_protect_frontend_edit_pages');

function rmt_protect_frontend_edit_pages() {
    if (
        is_page('edit-room') ||
        is_page('edit-roommate') ||
        is_page('dashboard') ||
        is_page('post-a-room') ||
        is_page('post-a-roommate')
    ) {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(get_permalink()));
            exit;
        }
    }
}

/**
 * Redirect normal users away from backend edit links.
 * If they somehow click /wp-admin/post.php?post=123&action=edit,
 * send them to the correct frontend edit page.
 */
add_action('load-post.php', 'rmt_redirect_backend_edit_to_frontend');

function rmt_redirect_backend_edit_to_frontend() {
    if (current_user_can('manage_options')) {
        return;
    }

    $post_id = absint($_GET['post'] ?? 0);

    if (!$post_id) {
        wp_redirect(home_url('/dashboard/'));
        exit;
    }

    $post = get_post($post_id);

    if (!$post) {
        wp_redirect(home_url('/dashboard/'));
        exit;
    }

    if ((int) $post->post_author !== get_current_user_id()) {
        wp_redirect(home_url('/dashboard/'));
        exit;
    }

    if ($post->post_type === 'room') {
        wp_redirect(add_query_arg('edit_id', $post_id, home_url('/edit-room/')));
        exit;
    }

    if ($post->post_type === 'roommate') {
        wp_redirect(add_query_arg('edit_id', $post_id, home_url('/edit-roommate/')));
        exit;
    }

    wp_redirect(home_url('/dashboard/'));
    exit;
}

/**
 * Remove backend edit links from frontend for normal users.
 */
add_filter('edit_post_link', 'rmt_remove_frontend_backend_edit_link_for_users', 10, 3);

function rmt_remove_frontend_backend_edit_link_for_users($link, $post_id, $text) {
    if (current_user_can('manage_options')) {
        return $link;
    }

    return '';
}

/**
 * Admin Reports Page
 */
add_action('admin_menu', 'rmt_add_reports_admin_page');

function rmt_add_reports_admin_page() {
    add_menu_page(
        'Listing Reports',
        'Reports',
        'manage_options',
        'rmt-listing-reports',
        'rmt_render_reports_admin_page',
        'dashicons-flag',
        26
    );
}

function rmt_render_reports_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $reported_posts = new WP_Query([
        'post_type'      => ['room', 'roommate'],
        'post_status'    => ['publish', 'draft', 'pending'],
        'posts_per_page' => -1,
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_report_count',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
            [
                'key'     => '_spam_report_count',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
            [
                'key'     => '_flagged_for_review',
                'value'   => 1,
                'compare' => '=',
            ],
        ],
    ]);

    echo '<div class="wrap">';
    echo '<h1>Listing Reports</h1>';

    if (!$reported_posts->have_posts()) {
        echo '<p>No reported listings yet.</p>';
        echo '</div>';
        return;
    }

    echo '<table class="widefat striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Title</th>';
    echo '<th>Type</th>';
    echo '<th>Status</th>';
    echo '<th>Report Count</th>';
    echo '<th>Flagged</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($reported_posts->have_posts()) {
        $reported_posts->the_post();

        $post_id = get_the_ID();

        $report_count = (int) get_post_meta($post_id, '_report_count', true);
        $spam_count   = (int) get_post_meta($post_id, '_spam_report_count', true);
        $total_count  = max($report_count, $spam_count);

        $flagged = get_post_meta($post_id, '_flagged_for_review', true) ? 'Yes' : 'No';

        echo '<tr>';
        echo '<td><strong>' . esc_html(get_the_title()) . '</strong></td>';
        echo '<td>' . esc_html(get_post_type($post_id)) . '</td>';
        echo '<td>' . esc_html(get_post_status($post_id)) . '</td>';
        echo '<td>' . esc_html($total_count) . '</td>';
        echo '<td>' . esc_html($flagged) . '</td>';
        echo '<td>';
        echo '<a class="button button-small" href="' . esc_url(get_edit_post_link($post_id)) . '">Review</a> ';
        echo '<a class="button button-small" href="' . esc_url(get_permalink($post_id)) . '" target="_blank">View</a>';
        echo '</td>';
        echo '</tr>';
    }

    wp_reset_postdata();

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}