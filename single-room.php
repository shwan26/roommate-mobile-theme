<?php
/**
 * Single Template: Room
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $post_id              = get_the_ID();
        $post_author_id       = get_post_field('post_author', $post_id);
        $current_user_id      = get_current_user_id();
        $is_author            = is_user_logged_in() && ($current_user_id === (int) $post_author_id);
        $is_visitor           = !$is_author; // includes logged-out guests

        // --- Fix undefined variable warnings ---
        $property_name        = rmt_get_meta($post_id, '_property_name');
        $bills_included       = rmt_get_meta($post_id, '_bills_included');

        // Room meta
        $rent                 = rmt_get_meta($post_id, '_rent');
        $deposit              = rmt_get_meta($post_id, '_deposit');
        $available_date       = rmt_get_meta($post_id, '_available_date');
        $property_type        = rmt_get_meta($post_id, '_property_type');
        $address              = rmt_get_meta($post_id, '_address');
        $nearby_landmark      = rmt_get_meta($post_id, '_nearby_landmark');
        $map_url              = rmt_get_meta($post_id, '_map_url');
        $utilities            = rmt_get_meta($post_id, '_utilities');
        $min_stay             = rmt_get_meta($post_id, '_min_stay');
        $gender_preference    = rmt_get_meta($post_id, '_gender_preference');
        $pet_policy           = rmt_get_meta($post_id, '_pet_policy');
        $smoking_policy       = rmt_get_meta($post_id, '_smoking_policy');

        // Roommate meta
        $nickname             = rmt_get_meta($post_id, '_nickname');
        $age                  = rmt_get_meta($post_id, '_age');
        $gender               = rmt_get_meta($post_id, '_gender');
        $occupation           = rmt_get_meta($post_id, '_occupation');
        $languages            = rmt_get_meta($post_id, '_languages');
        $cleanliness          = rmt_get_meta($post_id, '_cleanliness');
        $sleep_schedule       = rmt_get_meta($post_id, '_sleep_schedule');
        $smoker               = rmt_get_meta($post_id, '_smoker');
        $has_pets             = rmt_get_meta($post_id, '_has_pets');
        $social_level         = rmt_get_meta($post_id, '_social_level');
        $hobbies              = rmt_get_meta($post_id, '_hobbies');
        $bio                  = rmt_get_meta($post_id, '_bio');
        $roommate_preference  = rmt_get_meta($post_id, '_roommate_preference');

        $location_terms = get_the_terms($post_id, 'location_area');
        $amenities      = get_the_terms($post_id, 'amenity');
        $lifestyles     = get_the_terms($post_id, 'lifestyle');
        $room_types     = get_the_terms($post_id, 'room_type');

        $room_author_id = (int) get_post_field('post_author', get_the_ID());
        $current_user_id = get_current_user_id();
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

                    <!-- ============================================================
                         AUTHOR CONTROLS — only the post owner sees this bar
                    ============================================================ -->
                    <?php if ($is_author) : ?>
                        <div class="listing-owner-bar">
                            <span class="listing-owner-bar__label">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="2"/></svg>
                                Your listing
                            </span>
                            <div class="listing-owner-bar__actions">
                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="btn btn-sm btn-outline">
                                    ✏️ Edit listing
                                </a>
                                <button
                                    class="btn btn-sm btn-outline btn--mark-closed js-mark-closed"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_mark_closed_' . $post_id)); ?>"
                                >
                                    ✅ Mark as closed
                                </button>
                                <button
                                    class="btn btn-sm btn-outline btn--unpublish js-unpublish"
                                    data-post-id="<?php echo esc_attr($post_id); ?>"
                                    data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_unpublish_' . $post_id)); ?>"
                                >
                                    🔒 Unpublish
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ============================================================
                         VISITOR ACTIONS — shown to everyone except the post owner
                    ============================================================ -->
                    <?php if ($is_visitor) : ?>
                        <div class="listing-visitor-actions">
                            <?php if (is_user_logged_in()) : ?>
                                <a
                                    href="<?php echo esc_url(rmt_get_chat_url($post_author_id, $post_id)); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    💬 Message roommate
                                </a>
                            <?php else : ?>
                                <a
                                    href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>"
                                    class="btn btn-primary btn--chat"
                                >
                                    💬 Login to message
                                </a>
                            <?php endif; ?>

                            <button
                                class="btn btn-ghost btn--report js-report-spam"
                                data-post-id="<?php echo esc_attr($post_id); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('rmt_report_' . $post_id)); ?>"
                            >
                                🚩 Report to Admin
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="single-listing__grid">
                        <div class="single-listing__main">
                            <section class="single-card">
                                <h2>Room Details</h2>
                                <ul class="detail-list">
                                    <?php if ($property_name) : ?><li><strong>Property Name:</strong> <?php echo esc_html($property_name); ?></li><?php endif; ?>
                                    <?php if ($property_type) : ?><li><strong>Property Type:</strong> <?php echo esc_html($property_type); ?></li><?php endif; ?>
                                    <?php if ($address) : ?><li><strong>Location:</strong> <?php echo esc_html($address); ?></li><?php endif; ?>
                                    <?php if ($rent) : ?><li><strong>Rent:</strong> <?php echo esc_html(rmt_format_price($rent)); ?>/month</li><?php endif; ?>
                                    <?php if ($bills_included) : ?><li><strong>Bills:</strong> <?php echo esc_html($bills_included); ?></li><?php endif; ?>
                                    <?php if ($deposit !== '') : ?><li><strong>Deposit:</strong> <?php echo esc_html(rmt_format_price($deposit)); ?></li><?php endif; ?>
                                    <?php if ($available_date) : ?><li><strong>Available From:</strong> <?php echo esc_html($available_date); ?></li><?php endif; ?>
                                    <?php if ($gender_preference) : ?><li><strong>Gender Preference:</strong> <?php echo esc_html($gender_preference); ?></li><?php endif; ?>
                                    <?php if ($pet_policy) : ?><li><strong>Pet Policy:</strong> <?php echo esc_html($pet_policy); ?></li><?php endif; ?>
                                    <?php if ($smoking_policy) : ?><li><strong>Smoking Policy:</strong> <?php echo esc_html($smoking_policy); ?></li><?php endif; ?>
                                    <?php if ($utilities) : ?><li><strong>Utilities:</strong> <?php echo esc_html($utilities); ?></li><?php endif; ?>
                                    <?php if ($min_stay) : ?><li><strong>Minimum Stay:</strong> <?php echo esc_html($min_stay); ?></li><?php endif; ?>
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
                                <h2>Current Roommate</h2>
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
                                    <?php if ($social_level) : ?><li><strong>Social Level:</strong> <?php echo esc_html($social_level); ?>/10</li><?php endif; ?>
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

        <!-- ============================================================
             INLINE JS — handles AJAX actions for owner + visitor buttons
        ============================================================ -->
        <script>
        (function () {
            const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;

            /* ---------- helper ---------- */
            async function postAction(action, postId, nonce) {
                const body = new URLSearchParams({ action, post_id: postId, nonce });
                const res  = await fetch(ajaxUrl, { method: 'POST', body });
                return res.json();
            }

            /* ---------- Mark as closed ---------- */
            document.querySelectorAll('.js-mark-closed').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Mark this listing as closed? It will stay visible but show a "Room Taken" badge.')) return;
                    btn.disabled = true;
                    const data = await postAction('rmt_mark_closed', btn.dataset.postId, btn.dataset.nonce);
                    if (data.success) {
                        btn.textContent = '✅ Marked as closed';
                        // Optionally add a badge to the header
                        const header = document.querySelector('.single-listing__header');
                        if (header) {
                            const badge = document.createElement('span');
                            badge.className = 'archive-badge archive-badge--closed';
                            badge.textContent = 'Room Taken';
                            header.prepend(badge);
                        }
                    } else {
                        alert(data.data || 'Something went wrong.');
                        btn.disabled = false;
                    }
                });
            });

            /* ---------- Unpublish ---------- */
            document.querySelectorAll('.js-unpublish').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Unpublish this listing? It will be moved to drafts and hidden from visitors.')) return;
                    btn.disabled = true;
                    const data = await postAction('rmt_unpublish', btn.dataset.postId, btn.dataset.nonce);
                    if (data.success) {
                        alert('Listing unpublished. Redirecting…');
                        window.location.href = <?php echo wp_json_encode(home_url('/dashboard/')); ?>;
                    } else {
                        alert(data.data || 'Something went wrong.');
                        btn.disabled = false;
                    }
                });
            });

            /* ---------- Report spam ---------- */
            document.querySelectorAll('.js-report-spam').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Report this listing as spam or inappropriate?')) return;
                    btn.disabled = true;
                    const data = await postAction('rmt_report_listing', btn.dataset.postId, btn.dataset.nonce);
                    if (data.success) {
                        btn.textContent = '🚩 Reported — thanks!';
                    } else {
                        alert(data.data || 'Something went wrong.');
                        btn.disabled = false;
                    }
                });
            });
        })();
        </script>

    <?php endwhile;
endif;

get_footer();