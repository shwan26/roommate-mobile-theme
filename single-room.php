<?php
/**
 * Single Template: Room
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $rent                 = rmt_get_meta(get_the_ID(), '_rent');
        $deposit              = rmt_get_meta(get_the_ID(), '_deposit');
        $available_date       = rmt_get_meta(get_the_ID(), '_available_date');
        $property_type        = rmt_get_meta(get_the_ID(), '_property_type');
        $address              = rmt_get_meta(get_the_ID(), '_address');
        $nearby_landmark      = rmt_get_meta(get_the_ID(), '_nearby_landmark');
        $map_url              = rmt_get_meta(get_the_ID(), '_map_url');
        $utilities            = rmt_get_meta(get_the_ID(), '_utilities');
        $min_stay             = rmt_get_meta(get_the_ID(), '_min_stay');
        $gender_preference    = rmt_get_meta(get_the_ID(), '_gender_preference');
        $pet_policy           = rmt_get_meta(get_the_ID(), '_pet_policy');
        $smoking_policy       = rmt_get_meta(get_the_ID(), '_smoking_policy');

        $nickname             = rmt_get_meta(get_the_ID(), '_nickname');
        $age                  = rmt_get_meta(get_the_ID(), '_age');
        $gender               = rmt_get_meta(get_the_ID(), '_gender');
        $occupation           = rmt_get_meta(get_the_ID(), '_occupation');
        $languages            = rmt_get_meta(get_the_ID(), '_languages');
        $cleanliness          = rmt_get_meta(get_the_ID(), '_cleanliness');
        $sleep_schedule       = rmt_get_meta(get_the_ID(), '_sleep_schedule');
        $smoker               = rmt_get_meta(get_the_ID(), '_smoker');
        $has_pets             = rmt_get_meta(get_the_ID(), '_has_pets');
        $social_level         = rmt_get_meta(get_the_ID(), '_social_level');
        $hobbies              = rmt_get_meta(get_the_ID(), '_hobbies');
        $bio                  = rmt_get_meta(get_the_ID(), '_bio');
        $roommate_preference  = rmt_get_meta(get_the_ID(), '_roommate_preference');

        $location_terms = get_the_terms(get_the_ID(), 'location_area');
        $amenities      = get_the_terms(get_the_ID(), 'amenity');
        $lifestyles     = get_the_terms(get_the_ID(), 'lifestyle');
        $room_types     = get_the_terms(get_the_ID(), 'room_type');
        ?>

        <main id="primary" class="site-main single-page single-room">
            <div class="container">
                <article <?php post_class('single-listing'); ?>>

                    <header class="single-listing__header">
                        <div class="single-listing__header-text">
                            <span class="archive-badge">Room</span>
                            <h1><?php the_title(); ?></h1>

                            <div class="single-listing__chips">
                                <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                    <span class="listing-chip"><?php echo esc_html($location_terms[0]->name); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($room_types) && !is_wp_error($room_types)) : ?>
                                    <span class="listing-chip"><?php echo esc_html($room_types[0]->name); ?></span>
                                <?php endif; ?>

                                <?php if ($gender_preference) : ?>
                                    <span class="listing-chip"><?php echo esc_html($gender_preference); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($rent) : ?>
                            <div class="single-listing__price">
                                <?php echo esc_html(rmt_format_price($rent)); ?>/month
                            </div>
                        <?php endif; ?>
                    </header>

                    <div class="single-listing__media">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php endif; ?>
                    </div>

                    <div class="single-listing__grid">
                        <div class="single-listing__main">
                            <section class="single-card">
                                <h2>Room Details</h2>
                                <ul class="detail-list">
                                    <?php if ($deposit) : ?><li><strong>Deposit:</strong> <?php echo esc_html(rmt_format_price($deposit)); ?></li><?php endif; ?>
                                    <?php if ($available_date) : ?><li><strong>Available Date:</strong> <?php echo esc_html($available_date); ?></li><?php endif; ?>
                                    <?php if ($property_type) : ?><li><strong>Property Type:</strong> <?php echo esc_html($property_type); ?></li><?php endif; ?>
                                    <?php if ($address) : ?><li><strong>Address:</strong> <?php echo esc_html($address); ?></li><?php endif; ?>
                                    <?php if ($nearby_landmark) : ?><li><strong>Nearby Landmark:</strong> <?php echo esc_html($nearby_landmark); ?></li><?php endif; ?>
                                    <?php if ($utilities) : ?><li><strong>Utilities:</strong> <?php echo esc_html($utilities); ?></li><?php endif; ?>
                                    <?php if ($min_stay) : ?><li><strong>Minimum Stay:</strong> <?php echo esc_html($min_stay); ?></li><?php endif; ?>
                                    <?php if ($pet_policy) : ?><li><strong>Pet Policy:</strong> <?php echo esc_html($pet_policy); ?></li><?php endif; ?>
                                    <?php if ($smoking_policy) : ?><li><strong>Smoking Policy:</strong> <?php echo esc_html($smoking_policy); ?></li><?php endif; ?>
                                </ul>
                            </section>

                            <?php if (!empty($amenities) && !is_wp_error($amenities)) : ?>
                                <section class="single-card">
                                    <h2>Amenities</h2>
                                    <div class="listing-card__chips">
                                        <?php foreach ($amenities as $amenity) : ?>
                                            <span class="listing-chip"><?php echo esc_html($amenity->name); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <section class="single-card">
                                <h2>Description</h2>
                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                            </section>

                            <?php if ($map_url) : ?>
                                <section class="single-card">
                                    <h2>Map</h2>
                                    <p>
                                        <a class="btn btn-secondary" href="<?php echo esc_url($map_url); ?>" target="_blank" rel="noopener noreferrer">
                                            Open Location Map
                                        </a>
                                    </p>
                                </section>
                            <?php endif; ?>
                        </div>

                        <aside class="single-listing__sidebar">
                            <section class="single-card">
                                <h2>About the Current Roommate</h2>
                                <ul class="detail-list">
                                    <?php if ($nickname) : ?><li><strong>Name:</strong> <?php echo esc_html($nickname); ?></li><?php endif; ?>
                                    <?php if ($age) : ?><li><strong>Age:</strong> <?php echo esc_html($age); ?></li><?php endif; ?>
                                    <?php if ($gender) : ?><li><strong>Gender:</strong> <?php echo esc_html($gender); ?></li><?php endif; ?>
                                    <?php if ($occupation) : ?><li><strong>Occupation:</strong> <?php echo esc_html($occupation); ?></li><?php endif; ?>
                                    <?php if ($languages) : ?><li><strong>Languages:</strong> <?php echo esc_html($languages); ?></li><?php endif; ?>
                                    <?php if ($cleanliness) : ?><li><strong>Cleanliness:</strong> <?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                    <?php if ($sleep_schedule) : ?><li><strong>Sleep Schedule:</strong> <?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
                                    <?php if ($smoker) : ?><li><strong>Smoker:</strong> <?php echo esc_html($smoker); ?></li><?php endif; ?>
                                    <?php if ($has_pets) : ?><li><strong>Has Pets:</strong> <?php echo esc_html($has_pets); ?></li><?php endif; ?>
                                    <?php if ($social_level) : ?><li><strong>Social Level:</strong> <?php echo esc_html($social_level); ?></li><?php endif; ?>
                                    <?php if ($hobbies) : ?><li><strong>Hobbies:</strong> <?php echo esc_html($hobbies); ?></li><?php endif; ?>
                                </ul>
                            </section>

                            <?php if (!empty($lifestyles) && !is_wp_error($lifestyles)) : ?>
                                <section class="single-card">
                                    <h2>Lifestyle Tags</h2>
                                    <div class="listing-card__chips">
                                        <?php foreach ($lifestyles as $tag) : ?>
                                            <span class="listing-chip"><?php echo esc_html($tag->name); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if ($bio) : ?>
                                <section class="single-card">
                                    <h2>Bio</h2>
                                    <p><?php echo nl2br(esc_html($bio)); ?></p>
                                </section>
                            <?php endif; ?>

                            <?php if ($roommate_preference) : ?>
                                <section class="single-card">
                                    <h2>Preferred Roommate</h2>
                                    <p><?php echo nl2br(esc_html($roommate_preference)); ?></p>
                                </section>
                            <?php endif; ?>
                        </aside>
                    </div>
                </article>
            </div>
        </main>

    <?php endwhile;
endif;

get_footer();