<?php
/**
 * Roommate Mobile Theme — functions.php
 *
 * Sets up theme features, enqueues assets, registers menus,
 * defines custom post types (Room Listings + Roommate Profiles),
 * and provides template helper functions.
 *
 * @package RoommateMobileTheme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ============================================================
   1. CONSTANTS
   ============================================================ */
define( 'RMT_VERSION',   '1.0.0' );
define( 'RMT_DIR',       get_template_directory() );
define( 'RMT_URI',       get_template_directory_uri() );
define( 'RMT_ASSETS',    RMT_URI . '/assets' );

/* ============================================================
   2. THEME SETUP
   ============================================================ */
add_action( 'after_setup_theme', 'rmt_setup' );

function rmt_setup() {
    /*
     * Make theme translatable.
     * Translations go in /languages/
     */
    load_theme_textdomain( 'roommate-mobile-theme', RMT_DIR . '/languages' );

    // Let WordPress manage the <title> tag.
    add_theme_support( 'title-tag' );

    // Enable post thumbnail support (featured images).
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'listing-card',   600, 338, true );   // 16:9 card thumb
    add_image_size( 'listing-gallery',900, 600, true );   // Gallery hero
    add_image_size( 'avatar-medium',  200, 200, true );   // Seeker avatar

    // HTML5 markup.
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ] );

    // Gutenberg wide / full alignment.
    add_theme_support( 'align-wide' );

    // Feed links in <head>.
    add_theme_support( 'automatic-feed-links' );

    // Custom logo.
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );

    // Register navigation menus.
    register_nav_menus( [
        'primary'      => __( 'Primary Navigation',     'roommate-mobile-theme' ),
        'mobile-bottom'=> __( 'Mobile Bottom Nav',      'roommate-mobile-theme' ),
        'footer-links' => __( 'Footer Links',           'roommate-mobile-theme' ),
    ] );

    // Selective Refresh for widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Responsive embeds.
    add_theme_support( 'responsive-embeds' );

    // Editor color palette matching our CSS variables.
    add_theme_support( 'editor-color-palette', [
        [ 'name' => __( 'Lime Green (Primary)',   'roommate-mobile-theme' ), 'slug' => 'primary',     'color' => '#89e219' ],
        [ 'name' => __( 'Deep Purple (Comp)',     'roommate-mobile-theme' ), 'slug' => 'complementary','color' => '#4a1a8c' ],
        [ 'name' => __( 'Dark Purple',            'roommate-mobile-theme' ), 'slug' => 'comp-dark',   'color' => '#32106b' ],
        [ 'name' => __( 'Background',             'roommate-mobile-theme' ), 'slug' => 'background',  'color' => '#f7faf0' ],
        [ 'name' => __( 'Surface',                'roommate-mobile-theme' ), 'slug' => 'surface',     'color' => '#ffffff' ],
        [ 'name' => __( 'Text',                   'roommate-mobile-theme' ), 'slug' => 'text',        'color' => '#1a2010' ],
    ] );
}

/* ============================================================
   3. ENQUEUE STYLES & SCRIPTS
   ============================================================ */
add_action( 'wp_enqueue_scripts', 'rmt_enqueue_assets' );

function rmt_enqueue_assets() {
    // Google Fonts — Syne (headings) + DM Sans (body)
    wp_enqueue_style(
        'rmt-google-fonts',
        'https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap',
        [],
        null
    );

    // Main stylesheet (style.css — also the theme header file).
    wp_enqueue_style(
        'rmt-style',
        get_stylesheet_uri(),
        [ 'rmt-google-fonts' ],
        RMT_VERSION
    );

    // Main JS bundle (handles nav toggle, tabs, etc.).
    wp_enqueue_script(
        'rmt-main',
        RMT_ASSETS . '/js/main.js',
        [],
        RMT_VERSION,
        true   // Load in footer.
    );

    // Pass PHP data to JS.
    wp_localize_script( 'rmt-main', 'rmtData', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'rmt_nonce' ),
        'siteUrl'   => get_site_url(),
        'strings'   => [
            'loading'   => __( 'Loading…',     'roommate-mobile-theme' ),
            'noResults' => __( 'No listings found.', 'roommate-mobile-theme' ),
        ],
    ] );

    // Comments script (only on singular posts with comments open).
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

/* ============================================================
   4. CUSTOM POST TYPE — ROOM LISTINGS
   ============================================================ */
add_action( 'init', 'rmt_register_room_listing_cpt' );

function rmt_register_room_listing_cpt() {
    $labels = [
        'name'                  => __( 'Room Listings',            'roommate-mobile-theme' ),
        'singular_name'         => __( 'Room Listing',             'roommate-mobile-theme' ),
        'menu_name'             => __( 'Room Listings',            'roommate-mobile-theme' ),
        'add_new'               => __( 'Add New',                  'roommate-mobile-theme' ),
        'add_new_item'          => __( 'Add New Room Listing',     'roommate-mobile-theme' ),
        'edit_item'             => __( 'Edit Room Listing',        'roommate-mobile-theme' ),
        'new_item'              => __( 'New Room Listing',         'roommate-mobile-theme' ),
        'view_item'             => __( 'View Room Listing',        'roommate-mobile-theme' ),
        'search_items'          => __( 'Search Room Listings',     'roommate-mobile-theme' ),
        'not_found'             => __( 'No room listings found.',  'roommate-mobile-theme' ),
        'not_found_in_trash'    => __( 'Nothing in Trash.',        'roommate-mobile-theme' ),
    ];

    register_post_type( 'room_listing', [
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => [ 'slug' => 'rooms' ],
        'supports'           => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ],
        'show_in_rest'       => true,   // Enables Gutenberg & REST API.
        'menu_icon'          => 'dashicons-admin-home',
        'menu_position'      => 5,
        'capability_type'    => 'post',
    ] );
}

/* ============================================================
   5. CUSTOM POST TYPE — ROOMMATE PROFILES (Seeking Room)
   ============================================================ */
add_action( 'init', 'rmt_register_seeker_cpt' );

function rmt_register_seeker_cpt() {
    $labels = [
        'name'               => __( 'Roommate Seekers',        'roommate-mobile-theme' ),
        'singular_name'      => __( 'Roommate Seeker',         'roommate-mobile-theme' ),
        'menu_name'          => __( 'Roommate Seekers',        'roommate-mobile-theme' ),
        'add_new'            => __( 'Add Profile',             'roommate-mobile-theme' ),
        'add_new_item'       => __( 'Add New Seeker Profile',  'roommate-mobile-theme' ),
        'edit_item'          => __( 'Edit Seeker Profile',     'roommate-mobile-theme' ),
        'view_item'          => __( 'View Profile',            'roommate-mobile-theme' ),
        'search_items'       => __( 'Search Profiles',         'roommate-mobile-theme' ),
        'not_found'          => __( 'No seeker profiles found.','roommate-mobile-theme' ),
    ];

    register_post_type( 'room_seeker', [
        'labels'          => $labels,
        'public'          => true,
        'has_archive'     => true,
        'rewrite'         => [ 'slug' => 'seekers' ],
        'supports'        => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ],
        'show_in_rest'    => true,
        'menu_icon'       => 'dashicons-groups',
        'menu_position'   => 6,
        'capability_type' => 'post',
    ] );
}

/* ============================================================
   6. CUSTOM TAXONOMIES
   ============================================================ */
add_action( 'init', 'rmt_register_taxonomies' );

function rmt_register_taxonomies() {
    // Neighborhood / Location taxonomy shared by both CPTs.
    register_taxonomy( 'neighborhood', [ 'room_listing', 'room_seeker' ], [
        'labels'        => [
            'name'          => __( 'Neighborhoods', 'roommate-mobile-theme' ),
            'singular_name' => __( 'Neighborhood',  'roommate-mobile-theme' ),
            'add_new_item'  => __( 'Add Neighborhood', 'roommate-mobile-theme' ),
        ],
        'hierarchical'  => true,
        'rewrite'       => [ 'slug' => 'neighborhood' ],
        'show_in_rest'  => true,
    ] );

    // Amenities tag (non-hierarchical) for room listings.
    register_taxonomy( 'amenity', 'room_listing', [
        'labels'        => [
            'name'          => __( 'Amenities',  'roommate-mobile-theme' ),
            'singular_name' => __( 'Amenity',    'roommate-mobile-theme' ),
        ],
        'hierarchical'  => false,
        'rewrite'       => [ 'slug' => 'amenity' ],
        'show_in_rest'  => true,
    ] );

    // Lifestyle tags for seeker profiles.
    register_taxonomy( 'lifestyle', 'room_seeker', [
        'labels'        => [
            'name'          => __( 'Lifestyle Tags', 'roommate-mobile-theme' ),
            'singular_name' => __( 'Lifestyle Tag',  'roommate-mobile-theme' ),
        ],
        'hierarchical'  => false,
        'rewrite'       => [ 'slug' => 'lifestyle' ],
        'show_in_rest'  => true,
    ] );
}

/* ============================================================
   7. CUSTOM META BOXES — ROOM LISTING DETAILS
   ============================================================ */
add_action( 'add_meta_boxes', 'rmt_add_listing_meta_boxes' );

function rmt_add_listing_meta_boxes() {
    add_meta_box(
        'rmt_room_details',
        __( 'Room Details', 'roommate-mobile-theme' ),
        'rmt_render_room_details_meta_box',
        'room_listing',
        'normal',
        'high'
    );

    add_meta_box(
        'rmt_seeker_details',
        __( 'Seeker Details', 'roommate-mobile-theme' ),
        'rmt_render_seeker_details_meta_box',
        'room_seeker',
        'normal',
        'high'
    );
}

function rmt_render_room_details_meta_box( $post ) {
    wp_nonce_field( 'rmt_save_room_details', 'rmt_room_nonce' );

    $fields = [
        'rmt_price'        => [ 'label' => __( 'Monthly Rent (฿ / $)', 'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_deposit'      => [ 'label' => __( 'Deposit Amount',        'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_available'    => [ 'label' => __( 'Available From (date)', 'roommate-mobile-theme' ), 'type' => 'date'   ],
        'rmt_room_type'    => [ 'label' => __( 'Room Type',             'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'private' => 'Private Room', 'shared' => 'Shared Room', 'studio' => 'Studio', 'condo' => 'Full Condo/Apartment' ] ],
        'rmt_bedrooms'     => [ 'label' => __( 'Total Bedrooms',        'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_bathrooms'    => [ 'label' => __( 'Bathrooms',             'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_address'      => [ 'label' => __( 'Full Address',          'roommate-mobile-theme' ), 'type' => 'text'   ],
        'rmt_google_maps'  => [ 'label' => __( 'Google Maps Embed URL', 'roommate-mobile-theme' ), 'type' => 'url'    ],
        'rmt_contact_name' => [ 'label' => __( 'Contact Name',          'roommate-mobile-theme' ), 'type' => 'text'   ],
        'rmt_contact_phone'=> [ 'label' => __( 'Contact Phone / LINE',  'roommate-mobile-theme' ), 'type' => 'text'   ],
        'rmt_status'       => [ 'label' => __( 'Listing Status',        'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'available' => 'Available', 'pending' => 'Pending', 'taken' => 'Taken' ] ],
        'rmt_gender_pref'  => [ 'label' => __( 'Preferred Gender',      'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'any' => 'Any', 'female' => 'Female only', 'male' => 'Male only' ] ],
        'rmt_pets_allowed' => [ 'label' => __( 'Pets Allowed?',         'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'no' => 'No', 'yes' => 'Yes', 'negotiable' => 'Negotiable' ] ],
    ];

    rmt_render_meta_fields( $post->ID, $fields );
}

function rmt_render_seeker_details_meta_box( $post ) {
    wp_nonce_field( 'rmt_save_seeker_details', 'rmt_seeker_nonce' );

    $fields = [
        'rmt_budget_min'   => [ 'label' => __( 'Budget Min (฿/$)',    'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_budget_max'   => [ 'label' => __( 'Budget Max (฿/$)',    'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_move_in_date' => [ 'label' => __( 'Move-in Date',        'roommate-mobile-theme' ), 'type' => 'date'   ],
        'rmt_seeker_age'   => [ 'label' => __( 'Age',                 'roommate-mobile-theme' ), 'type' => 'number' ],
        'rmt_seeker_gender'=> [ 'label' => __( 'Gender',              'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'prefer_not' => 'Prefer not to say', 'male' => 'Male', 'female' => 'Female', 'non_binary' => 'Non-binary' ] ],
        'rmt_occupation'   => [ 'label' => __( 'Occupation',          'roommate-mobile-theme' ), 'type' => 'text'   ],
        'rmt_smoker'       => [ 'label' => __( 'Smoker?',             'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'no' => 'No', 'yes' => 'Yes', 'outside_only' => 'Outside only' ] ],
        'rmt_has_pets'     => [ 'label' => __( 'Has Pets?',           'roommate-mobile-theme' ), 'type' => 'select',
                                'options' => [ 'no' => 'No', 'yes' => 'Yes' ] ],
        'rmt_preferred_area'=> [ 'label' => __( 'Preferred Areas',    'roommate-mobile-theme' ), 'type' => 'text'   ],
        'rmt_contact_line' => [ 'label' => __( 'LINE / WhatsApp ID',  'roommate-mobile-theme' ), 'type' => 'text'   ],
    ];

    rmt_render_meta_fields( $post->ID, $fields );
}

/**
 * Generic helper to output meta box fields.
 */
function rmt_render_meta_fields( $post_id, array $fields ) {
    echo '<table class="form-table" style="width:100%;">';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post_id, $key, true );
        $label = esc_html( $field['label'] );
        echo '<tr><th style="width:200px;padding:10px 0;"><label for="' . esc_attr( $key ) . '">' . $label . '</label></th><td style="padding:6px 0;">';

        switch ( $field['type'] ) {
            case 'select':
                echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" style="min-width:200px;">';
                foreach ( $field['options'] as $opt_val => $opt_label ) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr( $opt_val ),
                        selected( $value, $opt_val, false ),
                        esc_html( $opt_label )
                    );
                }
                echo '</select>';
                break;

            default:
                printf(
                    '<input type="%s" id="%s" name="%s" value="%s" style="min-width:240px;" />',
                    esc_attr( $field['type'] ),
                    esc_attr( $key ),
                    esc_attr( $key ),
                    esc_attr( $value )
                );
        }

        echo '</td></tr>';
    }
    echo '</table>';
}

/* ============================================================
   8. SAVE META BOX DATA
   ============================================================ */
add_action( 'save_post_room_listing', 'rmt_save_room_listing_meta' );
add_action( 'save_post_room_seeker',  'rmt_save_seeker_meta' );

function rmt_save_room_listing_meta( $post_id ) {
    if ( ! isset( $_POST['rmt_room_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['rmt_room_nonce'], 'rmt_save_room_details' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $meta_keys = [
        'rmt_price', 'rmt_deposit', 'rmt_available', 'rmt_room_type',
        'rmt_bedrooms', 'rmt_bathrooms', 'rmt_address', 'rmt_google_maps',
        'rmt_contact_name', 'rmt_contact_phone', 'rmt_status',
        'rmt_gender_pref', 'rmt_pets_allowed',
    ];

    foreach ( $meta_keys as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
        }
    }
}

function rmt_save_seeker_meta( $post_id ) {
    if ( ! isset( $_POST['rmt_seeker_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['rmt_seeker_nonce'], 'rmt_save_seeker_details' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $meta_keys = [
        'rmt_budget_min', 'rmt_budget_max', 'rmt_move_in_date', 'rmt_seeker_age',
        'rmt_seeker_gender', 'rmt_occupation', 'rmt_smoker', 'rmt_has_pets',
        'rmt_preferred_area', 'rmt_contact_line',
    ];

    foreach ( $meta_keys as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
        }
    }
}

/* ============================================================
   9. WIDGETS / SIDEBARS
   ============================================================ */
add_action( 'widgets_init', 'rmt_register_sidebars' );

function rmt_register_sidebars() {
    register_sidebar( [
        'name'          => __( 'Listing Sidebar', 'roommate-mobile-theme' ),
        'id'            => 'listing-sidebar',
        'description'   => __( 'Widgets shown on room listing pages.', 'roommate-mobile-theme' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => __( 'Footer Widget Area', 'roommate-mobile-theme' ),
        'id'            => 'footer-widgets',
        'description'   => __( 'Widgets in the site footer.', 'roommate-mobile-theme' ),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="footer-heading">',
        'after_title'   => '</h4>',
    ] );
}

/* ============================================================
   10. TEMPLATE HELPER FUNCTIONS
   ============================================================ */

/**
 * Render the site logo (custom logo or text fallback).
 */
function rmt_site_logo() {
    if ( has_custom_logo() ) {
        the_custom_logo();
        return;
    }
    ?>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home">
        <span class="logo-icon">🏠</span>
        <span class="logo-text">Roomi<span>es</span></span>
    </a>
    <?php
}

/**
 * Return a formatted price string with currency.
 *
 * @param int    $amount   Numeric amount.
 * @param string $currency Currency symbol (default ฿).
 * @param string $period   Period label (default /mo).
 * @return string
 */
function rmt_format_price( $amount, $currency = '฿', $period = '/mo' ) {
    if ( empty( $amount ) ) return __( 'Price on request', 'roommate-mobile-theme' );
    return esc_html( $currency . number_format( (float) $amount ) . '<span>' . $period . '</span>' );
}

/**
 * Return post meta with a fallback.
 *
 * @param int    $post_id
 * @param string $key
 * @param string $fallback
 * @return string
 */
function rmt_meta( $post_id, $key, $fallback = '—' ) {
    $val = get_post_meta( $post_id, $key, true );
    return ( $val !== '' && $val !== false ) ? esc_html( $val ) : esc_html( $fallback );
}

/**
 * Render a status badge for a listing.
 *
 * @param string $status  available | pending | taken
 */
function rmt_status_badge( $status ) {
    $labels = [
        'available' => __( 'Available', 'roommate-mobile-theme' ),
        'pending'   => __( 'Pending',   'roommate-mobile-theme' ),
        'taken'     => __( 'Taken',     'roommate-mobile-theme' ),
    ];
    $label = $labels[ $status ] ?? ucfirst( $status );
    echo '<span class="card-status ' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
}

/**
 * Output the card amenity tags from taxonomy.
 *
 * @param int $post_id
 */
function rmt_amenity_tags( $post_id ) {
    $terms = get_the_terms( $post_id, 'amenity' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return;

    echo '<div class="card-tags">';
    foreach ( array_slice( $terms, 0, 5 ) as $term ) {
        echo '<span class="tag">' . esc_html( $term->name ) . '</span>';
    }
    echo '</div>';
}

/**
 * Output lifestyle tags for a seeker profile.
 *
 * @param int $post_id
 */
function rmt_lifestyle_tags( $post_id ) {
    $terms = get_the_terms( $post_id, 'lifestyle' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return;

    echo '<div class="seeker-tags">';
    foreach ( $terms as $term ) {
        echo '<span class="tag">' . esc_html( $term->name ) . '</span>';
    }
    echo '</div>';
}

/**
 * Display a WP_Query loop of listing cards.
 *
 * @param WP_Query $query
 * @param string   $type  'room_listing' | 'room_seeker'
 */
function rmt_listing_loop( $query, $type = 'room_listing' ) {
    if ( ! $query->have_posts() ) {
        echo '<p class="text-muted">' . esc_html__( 'No listings found.', 'roommate-mobile-theme' ) . '</p>';
        return;
    }

    echo '<div class="listings-grid">';
    while ( $query->have_posts() ) {
        $query->the_post();

        if ( $type === 'room_seeker' ) {
            get_template_part( 'template-parts/card', 'seeker' );
        } else {
            get_template_part( 'template-parts/card', 'room' );
        }
    }
    echo '</div>';
    wp_reset_postdata();
}

/* ============================================================
   11. AJAX — LOAD MORE LISTINGS
   ============================================================ */
add_action( 'wp_ajax_rmt_load_more',        'rmt_ajax_load_more' );
add_action( 'wp_ajax_nopriv_rmt_load_more', 'rmt_ajax_load_more' );

function rmt_ajax_load_more() {
    check_ajax_referer( 'rmt_nonce', 'nonce' );

    $paged     = absint( $_POST['paged'] ?? 2 );
    $post_type = sanitize_key( $_POST['post_type'] ?? 'room_listing' );

    $args = [
        'post_type'      => $post_type,
        'posts_per_page' => 9,
        'paged'          => $paged,
        'post_status'    => 'publish',
    ];

    // Optional neighborhood filter.
    if ( ! empty( $_POST['neighborhood'] ) ) {
        $args['tax_query'] = [ [
            'taxonomy' => 'neighborhood',
            'field'    => 'slug',
            'terms'    => sanitize_text_field( $_POST['neighborhood'] ),
        ] ];
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        ob_start();
        rmt_listing_loop( $query, $post_type );
        $html = ob_get_clean();
        wp_send_json_success( [
            'html'     => $html,
            'has_more' => ( $paged < $query->max_num_pages ),
        ] );
    } else {
        wp_send_json_success( [ 'html' => '', 'has_more' => false ] );
    }
}

/* ============================================================
   12. DOCUMENT TITLE SEPARATOR
   ============================================================ */
add_filter( 'document_title_separator', function() { return '|'; } );

/* ============================================================
   13. EXCERPT LENGTH & MORE TEXT
   ============================================================ */
add_filter( 'excerpt_length', function() { return 25; }, 20 );
add_filter( 'excerpt_more',   function() {
    return '… <a href="' . esc_url( get_permalink() ) . '" class="read-more">'
         . __( 'View Listing', 'roommate-mobile-theme' ) . '</a>';
} );

/* ============================================================
   14. BODY CLASSES — add useful context classes
   ============================================================ */
add_filter( 'body_class', 'rmt_body_classes' );

function rmt_body_classes( $classes ) {
    if ( is_singular( 'room_listing' ) ) $classes[] = 'is-room-listing';
    if ( is_singular( 'room_seeker' ) )  $classes[] = 'is-room-seeker';
    if ( is_post_type_archive( 'room_listing' ) ) $classes[] = 'archive-room-listing';
    if ( is_post_type_archive( 'room_seeker' ) )  $classes[] = 'archive-room-seeker';
    return $classes;
}

/* ============================================================
   15. SECURITY — remove version from scripts/styles
   ============================================================ */
add_filter( 'style_loader_src',  'rmt_remove_version_from_assets', 9999 );
add_filter( 'script_loader_src', 'rmt_remove_version_from_assets', 9999 );

function rmt_remove_version_from_assets( $src ) {
    if ( strpos( $src, 'ver=' ) ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}