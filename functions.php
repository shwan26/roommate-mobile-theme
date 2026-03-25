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
    // Translation support
    load_theme_textdomain('roommate-mobile-theme', RMT_THEME_DIR . '/languages');

    // Let WordPress handle the <title>
    add_theme_support('title-tag');

    // Featured images
    add_theme_support('post-thumbnails');

    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Custom logo
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Responsive embeds
    add_theme_support('responsive-embeds');

    // Menus
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
/**
 * ------------------------------------------------------------
 * 4. CUSTOM POST TYPES
 * ------------------------------------------------------------
 */
function rmt_register_post_types() {

    // HAVE ROOM
    register_post_type('have_room', array(
        'labels' => array(
            'name'               => __('Have Room', 'roommate-mobile-theme'),
            'singular_name'      => __('Have Room', 'roommate-mobile-theme'),
            'add_new'            => __('Add New', 'roommate-mobile-theme'),
            'add_new_item'       => __('Add New Have Room Listing', 'roommate-mobile-theme'),
            'edit_item'          => __('Edit Have Room Listing', 'roommate-mobile-theme'),
            'new_item'           => __('New Have Room Listing', 'roommate-mobile-theme'),
            'view_item'          => __('View Have Room Listing', 'roommate-mobile-theme'),
            'view_items'         => __('View Have Room Listings', 'roommate-mobile-theme'),
            'search_items'       => __('Search Have Room Listings', 'roommate-mobile-theme'),
            'not_found'          => __('No Have Room listings found', 'roommate-mobile-theme'),
            'not_found_in_trash' => __('No Have Room listings found in Trash', 'roommate-mobile-theme'),
            'all_items'          => __('All Have Room Listings', 'roommate-mobile-theme'),
            'archives'           => __('Have Room Archives', 'roommate-mobile-theme'),
            'menu_name'          => __('Have Room', 'roommate-mobile-theme'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'has_archive'        => 'have-room',
        'rewrite'            => array(
            'slug'       => 'have-room',
            'with_front' => false,
        ),
        'menu_icon'          => 'dashicons-admin-home',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
        'hierarchical'       => false,
        'menu_position'      => 5,
        'exclude_from_search'=> false,
    ));

    // NEED ROOM
    register_post_type('need_room', array(
        'labels' => array(
            'name'               => __('Need Room', 'roommate-mobile-theme'),
            'singular_name'      => __('Need Room', 'roommate-mobile-theme'),
            'add_new'            => __('Add New', 'roommate-mobile-theme'),
            'add_new_item'       => __('Add New Need Room Profile', 'roommate-mobile-theme'),
            'edit_item'          => __('Edit Need Room Profile', 'roommate-mobile-theme'),
            'new_item'           => __('New Need Room Profile', 'roommate-mobile-theme'),
            'view_item'          => __('View Need Room Profile', 'roommate-mobile-theme'),
            'view_items'         => __('View Need Room Profiles', 'roommate-mobile-theme'),
            'search_items'       => __('Search Need Room Profiles', 'roommate-mobile-theme'),
            'not_found'          => __('No Need Room profiles found', 'roommate-mobile-theme'),
            'not_found_in_trash' => __('No Need Room profiles found in Trash', 'roommate-mobile-theme'),
            'all_items'          => __('All Need Room Profiles', 'roommate-mobile-theme'),
            'archives'           => __('Need Room Archives', 'roommate-mobile-theme'),
            'menu_name'          => __('Need Room', 'roommate-mobile-theme'),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
        'show_in_admin_bar'  => true,
        'show_in_rest'       => true,
        'has_archive'        => 'need-room',
        'rewrite'            => array(
            'slug'       => 'need-room',
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
    // Location Area
    register_taxonomy('location_area', array('have_room', 'need_room'), array(
        'labels' => array(
            'name'          => __('Location Areas', 'roommate-mobile-theme'),
            'singular_name' => __('Location Area', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => array('slug' => 'location-area'),
        'show_in_rest' => true,
    ));

    // Amenities
    register_taxonomy('amenity', array('have_room'), array(
        'labels' => array(
            'name'          => __('Amenities', 'roommate-mobile-theme'),
            'singular_name' => __('Amenity', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => false,
        'rewrite'      => array('slug' => 'amenity'),
        'show_in_rest' => true,
    ));

    // Lifestyle
    register_taxonomy('lifestyle', array('have_room', 'need_room'), array(
        'labels' => array(
            'name'          => __('Lifestyle Tags', 'roommate-mobile-theme'),
            'singular_name' => __('Lifestyle Tag', 'roommate-mobile-theme'),
        ),
        'public'       => true,
        'hierarchical' => false,
        'rewrite'      => array('slug' => 'lifestyle'),
        'show_in_rest' => true,
    ));

    // Room Type
    register_taxonomy('room_type', array('have_room', 'need_room'), array(
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
        'rmt_have_room_details',
        __('Have Room Details', 'roommate-mobile-theme'),
        'rmt_render_have_room_meta_box',
        'have_room',
        'normal',
        'high'
    );

    add_meta_box(
        'rmt_need_room_details',
        __('Need Room Details', 'roommate-mobile-theme'),
        'rmt_render_need_room_meta_box',
        'need_room',
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
 * 7. HAVE ROOM META BOX HTML
 * ------------------------------------------------------------
 */
function rmt_render_have_room_meta_box($post) {
    wp_nonce_field('rmt_save_meta', 'rmt_meta_nonce');

    $fields = array(
        '_rent',
        '_deposit',
        '_available_date',
        '_property_type',
        '_bedrooms',
        '_bathrooms',
        '_address',
        '_nearby_landmark',
        '_map_url',
        '_utilities',
        '_min_stay',
        '_gender_preference',
        '_pet_policy',
        '_smoking_policy',
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
        <tr><th><label for="_rent">Monthly Rent</label></th><td><input type="text" name="_rent" id="_rent" value="<?php echo esc_attr($rent); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_deposit">Deposit</label></th><td><input type="text" name="_deposit" id="_deposit" value="<?php echo esc_attr($deposit); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_available_date">Available Date</label></th><td><input type="date" name="_available_date" id="_available_date" value="<?php echo esc_attr($available_date); ?>"></td></tr>
        <tr><th><label for="_property_type">Property Type</label></th><td><input type="text" name="_property_type" id="_property_type" value="<?php echo esc_attr($property_type); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_bedrooms">Bedrooms</label></th><td><input type="number" name="_bedrooms" id="_bedrooms" value="<?php echo esc_attr($bedrooms); ?>"></td></tr>
        <tr><th><label for="_bathrooms">Bathrooms</label></th><td><input type="number" name="_bathrooms" id="_bathrooms" value="<?php echo esc_attr($bathrooms); ?>"></td></tr>
        <tr><th><label for="_address">Address</label></th><td><input type="text" name="_address" id="_address" value="<?php echo esc_attr($address); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_nearby_landmark">Nearby Landmark / BTS / University</label></th><td><input type="text" name="_nearby_landmark" id="_nearby_landmark" value="<?php echo esc_attr($nearby_landmark); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_map_url">Map URL</label></th><td><input type="url" name="_map_url" id="_map_url" value="<?php echo esc_attr($map_url); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_utilities">Utilities Included</label></th><td><input type="text" name="_utilities" id="_utilities" value="<?php echo esc_attr($utilities); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_min_stay">Minimum Stay</label></th><td><input type="text" name="_min_stay" id="_min_stay" value="<?php echo esc_attr($min_stay); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_gender_preference">Gender Preference</label></th><td><input type="text" name="_gender_preference" id="_gender_preference" value="<?php echo esc_attr($gender_preference); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_pet_policy">Pet Policy</label></th><td><input type="text" name="_pet_policy" id="_pet_policy" value="<?php echo esc_attr($pet_policy); ?>" class="regular-text"></td></tr>
        <tr><th><label for="_smoking_policy">Smoking Policy</label></th><td><input type="text" name="_smoking_policy" id="_smoking_policy" value="<?php echo esc_attr($smoking_policy); ?>" class="regular-text"></td></tr>

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
        <tr><th><label for="_roommate_preference">Preferred Roommate</label></th><td><textarea name="_roommate_preference" id="_roommate_preference" rows="4" class="large-text"><?php echo esc_textarea($roommate_preference); ?></textarea></td></tr>
    </table>
    <?php
}

/**
 * ------------------------------------------------------------
 * 8. NEED ROOM META BOX HTML
 * ------------------------------------------------------------
 */
function rmt_render_need_room_meta_box($post) {
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
add_action('save_post_have_room', 'rmt_save_post_meta');
add_action('save_post_need_room', 'rmt_save_post_meta');

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
 * ------------------------------------------------------------
 * 12. AJAX LOAD MORE EXAMPLE
 * ------------------------------------------------------------
 */
function rmt_ajax_load_more() {
    check_ajax_referer('rmt_nonce', 'nonce');

    $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'have_room';
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
    echo '<li><a href="' . esc_url(get_post_type_archive_link('have_room')) . '">Have Room</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('need_room')) . '">Need Room</a></li>';
    echo '</ul>';
}