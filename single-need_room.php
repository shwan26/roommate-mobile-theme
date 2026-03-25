<?php
/**
 * Single Template: Need Room
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $budget_min              = rmt_get_meta(get_the_ID(), '_budget_min');
        $budget_max              = rmt_get_meta(get_the_ID(), '_budget_max');
        $move_in_date            = rmt_get_meta(get_the_ID(), '_move_in_date');
        $preferred_property_type = rmt_get_meta(get_the_ID(), '_preferred_property_type');
        $preferred_room_type     = rmt_get_meta(get_the_ID(), '_preferred_room_type');
        $lease_duration          = rmt_get_meta(get_the_ID(), '_lease_duration');
        $preferred_area_text     = rmt_get_meta(get_the_ID(), '_preferred_area_text');
        $private_or_shared       = rmt_get_meta(get_the_ID(), '_private_or_shared');
        $teamup_ok               = rmt_get_meta(get_the_ID(), '_teamup_ok');
        $gender_preference       = rmt_get_meta(get_the_ID(), '_gender_preference');
        $pets_ok                 = rmt_get_meta(get_the_ID(), '_pets_ok');
        $smokers_ok              = rmt_get_meta(get_the_ID(), '_smokers_ok');

        $nickname                = rmt_get_meta(get_the_ID(), '_nickname');
        $age                     = rmt_get_meta(get_the_ID(), '_age');
        $gender                  = rmt_get_meta(get_the_ID(), '_gender');
        $occupation              = rmt_get_meta(get_the_ID(), '_occupation');
        $languages               = rmt_get_meta(get_the_ID(), '_languages');
        $cleanliness             = rmt_get_meta(get_the_ID(), '_cleanliness');
        $sleep_schedule          = rmt_get_meta(get_the_ID(), '_sleep_schedule');
        $smoker                  = rmt_get_meta(get_the_ID(), '_smoker');
        $has_pets                = rmt_get_meta(get_the_ID(), '_has_pets');
        $social_level            = rmt_get_meta(get_the_ID(), '_social_level');
        $hobbies                 = rmt_get_meta(get_the_ID(), '_hobbies');
        $bio                     = rmt_get_meta(get_the_ID(), '_bio');
        $ideal_roommate          = rmt_get_meta(get_the_ID(), '_ideal_roommate');

        $location_terms = get_the_terms(get_the_ID(), 'location_area');
        $lifestyles     = get_the_terms(get_the_ID(), 'lifestyle');
        $room_types     = get_the_terms(get_the_ID(), 'room_type');
        ?>

        <main id="primary" class="site-main single-page single-need-room">
            <div class="container">
                <article <?php post_class('single-listing'); ?>>

                    <header class="single-listing__header">
                        <div class="single-listing__header-text">
                            <span class="archive-badge">Need Room</span>
                            <h1><?php the_title(); ?></h1>

                            <div class="single-listing__chips">
                                <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                    <span class="listing-chip"><?php echo esc_html($location_terms[0]->name); ?></span>
                                <?php endif; ?>

                                <?php if (!empty($room_types) && !is_wp_error($room_types)) : ?>
                                    <span class="listing-chip"><?php echo esc_html($room_types[0]->name); ?></span>
                                <?php endif; ?>

                                <?php if ($teamup_ok) : ?>
                                    <span class="listing-chip"><?php echo esc_html($teamup_ok); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="single-listing__price">
                            <?php if ($budget_min || $budget_max) : ?>
                                Budget:
                                <?php echo esc_html(rmt_format_price($budget_min)); ?>
                                <?php if ($budget_max) : ?>
                                    - <?php echo esc_html(rmt_format_price($budget_max)); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="single-listing__media">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php endif; ?>
                    </div>

                    <div class="single-listing__grid">
                        <div class="single-listing__main">
                            <section class="single-card">
                                <h2>Looking For</h2>
                                <ul class="detail-list">
                                    <?php if ($move_in_date) : ?><li><strong>Move-in Date:</strong> <?php echo esc_html($move_in_date); ?></li><?php endif; ?>
                                    <?php if ($preferred_property_type) : ?><li><strong>Preferred Property Type:</strong> <?php echo esc_html($preferred_property_type); ?></li><?php endif; ?>
                                    <?php if ($preferred_room_type) : ?><li><strong>Preferred Room Type:</strong> <?php echo esc_html($preferred_room_type); ?></li><?php endif; ?>
                                    <?php if ($lease_duration) : ?><li><strong>Lease Duration:</strong> <?php echo esc_html($lease_duration); ?></li><?php endif; ?>
                                    <?php if ($preferred_area_text) : ?><li><strong>Preferred Area:</strong> <?php echo esc_html($preferred_area_text); ?></li><?php endif; ?>
                                    <?php if ($private_or_shared) : ?><li><strong>Private/Shared:</strong> <?php echo esc_html($private_or_shared); ?></li><?php endif; ?>
                                    <?php if ($gender_preference) : ?><li><strong>Gender Preference:</strong> <?php echo esc_html($gender_preference); ?></li><?php endif; ?>
                                    <?php if ($pets_ok) : ?><li><strong>Pets OK:</strong> <?php echo esc_html($pets_ok); ?></li><?php endif; ?>
                                    <?php if ($smokers_ok) : ?><li><strong>Smokers OK:</strong> <?php echo esc_html($smokers_ok); ?></li><?php endif; ?>
                                </ul>
                            </section>

                            <section class="single-card">
                                <h2>Description</h2>
                                <div class="entry-content">
                                    <?php the_content(); ?>
                                </div>
                            </section>
                        </div>

                        <aside class="single-listing__sidebar">
                            <section class="single-card">
                                <h2>About this person</h2>
                                <ul class="detail-list">
                                    <?php if ($nickname) : ?><li><strong>Name:</strong> <?php echo esc_html($nickname); ?></li><?php endif; ?>
                                    <?php if ($age) : ?><li><strong>Age:</strong> <?php echo esc_html($age); ?></li><?php endif; ?>
                                    <?php if ($gender) : ?><li><strong>Gender:</strong> <?php echo esc_html($gender); ?></li><?php endif; ?>
                                    <?php if ($occupation) : ?><li><strong>Occupation:</strong> <?php echo esc_html($occupation); ?></li><?php endif; ?>
                                    <?php if ($languages) : ?><li><strong>Languages:</strong> <?php echo esc_html($languages); ?></li><?php endif; ?>
                                    <?php if ($cleanliness) : ?><li><strong>Cleanliness:</strong> <?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                    <?php if ($sleep_schedule) : ?><li><strong>Sleep:</strong> <?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
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

                            <?php if ($ideal_roommate) : ?>
                                <section class="single-card">
                                    <h2>Ideal Roommate</h2>
                                    <p><?php echo nl2br(esc_html($ideal_roommate)); ?></p>
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