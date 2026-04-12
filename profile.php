<?php
/**
 * Template Name: Profile Page
 *
 * Displays the logged-in user's profile with basic info and a log out option.
 */

defined('ABSPATH') || exit;

// Redirect guests to login
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

$current_user = wp_get_current_user();
$user_id      = $current_user->ID;
$display_name = $current_user->display_name;
$user_email   = $current_user->user_email;
$registered   = date_i18n( get_option('date_format'), strtotime( $current_user->user_registered ) );
$avatar_url   = get_avatar_url( $user_id, array( 'size' => 120 ) );

get_header();
?>

<main id="primary" class="site-main profile-page">
    <div class="container">

        <div class="profile-card">

            <!-- Avatar -->
            <div class="profile-avatar">
                <img src="<?php echo esc_url( $avatar_url ); ?>"
                     alt="<?php echo esc_attr( $display_name ); ?>"
                     width="120" height="120">
            </div>

            <!-- Basic Info -->
            <div class="profile-info">
                <h1 class="profile-name"><?php echo esc_html( $display_name ); ?></h1>
                <p class="profile-email"><?php echo esc_html( $user_email ); ?></p>
                <p class="profile-since">
                    <?php printf( esc_html__( 'Member since %s', 'roommate-mobile-theme' ), esc_html( $registered ) ); ?>
                </p>
            </div>

            <!-- Actions -->
            <div class="profile-actions">
                <a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>" class="btn btn-secondary">
                    <?php esc_html_e( 'Edit Profile', 'roommate-mobile-theme' ); ?>
                </a>
                <a href="<?php echo esc_url( wp_logout_url( home_url('/') ) ); ?>" class="btn btn-outline">
                    <?php esc_html_e( 'Log Out', 'roommate-mobile-theme' ); ?>
                </a>
            </div>

        </div><!-- .profile-card -->

        <!-- My Listings -->
        <section class="profile-listings">
            <h2><?php esc_html_e( 'My Listings', 'roommate-mobile-theme' ); ?></h2>

            <?php
            $my_posts = new WP_Query( array(
                'post_type'      => array( 'room', 'roommate' ),
                'author'         => $user_id,
                'posts_per_page' => 10,
                'post_status'    => 'publish',
            ) );

            if ( $my_posts->have_posts() ) : ?>
                <ul class="profile-listings__list">
                    <?php while ( $my_posts->have_posts() ) : $my_posts->the_post(); ?>
                        <li class="profile-listings__item">
                            <a href="<?php the_permalink(); ?>" class="profile-listings__link">
                                <?php the_title(); ?>
                            </a>
                            <span class="profile-listings__type">
                                <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?>
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="profile-listings__empty">
                    <?php esc_html_e( "You haven't posted any listings yet.", 'roommate-mobile-theme' ); ?>
                </p>
            <?php endif; ?>

        </section><!-- .profile-listings -->

    </div><!-- .container -->
</main>

<?php get_footer(); ?>