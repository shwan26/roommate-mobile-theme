<?php
/**
 * Single Template: Roommate
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) :
    while (have_posts()) : the_post();

        $post_id        = get_the_ID();
        $post_author_id = (int) get_post_field('post_author', $post_id);
        $current_user   = wp_get_current_user();
        $is_owner       = is_user_logged_in() && ($current_user->ID === $post_author_id);
        $is_visitor     = is_user_logged_in() && !$is_owner;
        $is_admin       = current_user_can('manage_options');

        // About You
        $nickname       = rmt_get_meta($post_id, '_nickname');
        $age            = rmt_get_meta($post_id, '_age');
        $gender         = rmt_get_meta($post_id, '_gender');
        $occupation     = rmt_get_meta($post_id, '_occupation');
        $languages      = rmt_get_meta($post_id, '_languages');
        $hobbies        = rmt_get_meta($post_id, '_hobbies');
        $bio            = rmt_get_meta($post_id, '_bio');

        // Lifestyle
        $cleanliness    = rmt_get_meta($post_id, '_cleanliness');
        $sleep_schedule = rmt_get_meta($post_id, '_sleep_schedule');
        $smoker         = rmt_get_meta($post_id, '_smoker');
        $has_pets       = rmt_get_meta($post_id, '_has_pets');
        $social_level   = rmt_get_meta($post_id, '_social_level');

        // Room Preferences
        $budget_min     = rmt_get_meta($post_id, '_budget_min');
        $budget_max     = rmt_get_meta($post_id, '_budget_max');
        $move_in_date   = rmt_get_meta($post_id, '_move_in_date');
        $preferred_area = rmt_get_meta($post_id, '_preferred_area');

        // Preferred Roommate
        $roommate_preference = rmt_get_meta($post_id, '_roommate_preference');

        // Taxonomies
        $location_terms = get_the_terms($post_id, 'location_area');
        $lifestyles     = get_the_terms($post_id, 'lifestyle');

        $edit_url = add_query_arg('edit_id', $post_id, home_url('/edit-roommate/'));

        
        // Profile photo — use post thumbnail, else hard fallback to default image URL
        if (has_post_thumbnail($post_id)) {
            $photo_url = get_the_post_thumbnail_url($post_id, 'medium');
        } else {
            $photo_url = get_template_directory_uri() . '/images/default-profile.jpg';
        }

        // Handle unpublish action (owner only)
        $action_message = '';
        if (
            $is_owner &&
            isset($_POST['rmt_unpublish_nonce']) &&
            wp_verify_nonce($_POST['rmt_unpublish_nonce'], 'rmt_unpublish_' . $post_id)
        ) {
            wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
            $action_message = 'Your profile has been unpublished.';
        }

        // Handle report action (visitors only)
        $report_sent = false;
        if (
            $is_visitor &&
            isset($_POST['rmt_report_nonce']) &&
            wp_verify_nonce($_POST['rmt_report_nonce'], 'rmt_report_' . $post_id)
        ) {
            $reason      = sanitize_textarea_field($_POST['report_reason'] ?? '');
            $reporter    = $current_user->display_name . ' (ID: ' . $current_user->ID . ')';
            $report_time = current_time('mysql');

            // Save report as post meta (admin can view these)
            $reports   = get_post_meta($post_id, '_spam_reports', true) ?: [];
            $reports[] = [
                'reporter_id'   => $current_user->ID,
                'reporter_name' => $current_user->display_name,
                'reporter_email'=> $current_user->user_email,
                'reason'        => $reason,
                'time'          => $report_time,
            ];
            update_post_meta($post_id, '_spam_reports', $reports);
            update_post_meta($post_id, '_spam_report_count', count($reports));

            // Email notification to admin
            $admin_email = get_option('admin_email');
            $subject     = '[BkkRoomie] Spam Report: ' . get_the_title($post_id);
            $body        = "A post has been reported as spam.\n\n"
                         . "Post: " . get_the_title($post_id) . "\n"
                         . "URL: " . get_permalink($post_id) . "\n"
                         . "Reported by: {$reporter}\n"
                         . "Time: {$report_time}\n"
                         . "Reason: {$reason}\n\n"
                         . "Total reports on this post: " . count($reports) . "\n\n"
                         . "Review in admin: " . admin_url('post.php?post=' . $post_id . '&action=edit');
            wp_mail($admin_email, $subject, $body);

            $report_sent = true;
        }
        ?>

        <style>
        .srm-hero { padding: var(--space-10) 0 var(--space-6); }

        .srm-profile-header { display: flex; align-items: center; gap: 1.75rem; flex-wrap: wrap; }

        .srm-avatar {
            width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
            border: 3px solid var(--color-border); flex-shrink: 0; box-shadow: var(--shadow-md);
            background: #e8e8e8;
        }

        .srm-profile-header__info { flex: 1; min-width: 200px; }
        .srm-profile-header__info h1 {
            font-size: clamp(1.5rem, 4vw, 2.2rem);
            margin: 0.25rem 0 0.6rem; letter-spacing: -0.02em;
        }

        .srm-meta-row { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 0.6rem; }
        .srm-meta-item { font-size: 0.88rem; color: var(--color-text-soft); }
        .srm-meta-item::after { content: '·'; margin-left: 0.5rem; color: var(--color-border); }
        .srm-meta-item:last-of-type::after { content: ''; }

        .srm-movein {
            display: inline-flex; align-items: center; gap: 0.4rem;
            background: rgba(137,226,25,0.12); border: 1px solid rgba(137,226,25,0.3);
            color: var(--color-secondary-dark); font-size: 0.82rem; font-weight: 700;
            padding: 0.3rem 0.8rem; border-radius: 999px;
        }

        .srm-budget { font-size: 1.25rem; font-weight: 800; color: var(--color-secondary); margin-top: 0.4rem; }

        /* Action bar */
        .srm-action-bar {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;
            padding: 1rem 1.25rem;
            background: var(--color-surface); border: 1px solid var(--color-border);
            border-radius: var(--radius-lg); margin-bottom: 1.5rem;
        }

        .srm-action-bar .btn { font-size: 0.88rem; padding: 0.55rem 1.1rem; }

        .btn-danger {
            background: transparent; border: 1px solid #E24B4A; color: #E24B4A;
            border-radius: var(--radius-sm); font-weight: 700; cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .btn-danger:hover { background: #E24B4A; color: #fff; }

        .btn-chat {
            background: var(--color-primary); color: var(--color-black);
            border: none; border-radius: var(--radius-sm);
            font-weight: 700; cursor: pointer; padding: 0.55rem 1.1rem; font-size: 0.88rem;
            transition: opacity 0.15s;
        }
        .btn-chat:hover { opacity: 0.85; }

        /* Owner notice */
        .srm-owner-notice {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 0.82rem; color: var(--color-text-soft);
            margin-left: auto;
        }
        .srm-owner-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #89e219; flex-shrink: 0;
        }

        /* Report modal overlay */
        .srm-modal-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .srm-modal-backdrop.open { display: flex; }

        .srm-modal {
            background: var(--color-surface); border: 1px solid var(--color-border);
            border-radius: var(--radius-lg); padding: 1.75rem; width: 90%; max-width: 460px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .srm-modal h3 { margin: 0 0 0.4rem; font-size: 1.1rem; }
        .srm-modal p  { margin: 0 0 1.25rem; font-size: 0.88rem; color: var(--color-text-soft); }
        .srm-modal textarea {
            width: 100%; min-height: 100px; resize: vertical;
            padding: 0.75rem 1rem; border: 1.5px solid var(--color-border);
            border-radius: var(--radius-sm); font-size: 0.92rem;
            font-family: var(--font-body); background: var(--color-white);
            color: var(--color-text); box-sizing: border-box; margin-bottom: 1rem;
        }
        .srm-modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; }

        /* Notices */
        .srm-notice {
            padding: 0.9rem 1.2rem; border-radius: var(--radius-md);
            margin-bottom: 1.25rem; font-size: 0.92rem; font-weight: 600;
        }
        .srm-notice--success { background: #F0FDF4; border: 1px solid #86EFAC; color: #166534; }
        .srm-notice--warning { background: #FEFCE8; border: 1px solid #FDE047; color: #854d0e; }
        .srm-notice--error   { background: #FFF1F2; border: 1px solid #FCA5A5; color: #991b1b; }

        /* Grid */
        .srm-grid { display: grid; gap: 1.5rem; align-items: start; padding-bottom: var(--space-12); }
        @media (min-width: 900px) { .srm-grid { grid-template-columns: minmax(0, 2fr) 300px; } }

        /* Cards */
        .srm-card {
            background: var(--color-surface); border: 1px solid var(--color-border);
            border-radius: var(--radius-lg); padding: 1.5rem;
            box-shadow: var(--shadow-sm); margin-bottom: 1.25rem;
        }
        .srm-card:last-child { margin-bottom: 0; }
        .srm-card h2 {
            font-size: 0.78rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.07em; color: var(--color-text-soft);
            margin: 0 0 1rem; padding-bottom: 0.65rem; border-bottom: 1px solid var(--color-border);
        }

        .srm-detail-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.65rem; }
        .srm-detail-list li { display: flex; align-items: center; gap: 0.6rem; font-size: 0.92rem; }
        .srm-detail-list li strong {
            min-width: 120px; flex-shrink: 0; color: var(--color-text-soft); font-weight: 700;
            font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.04em;
        }

        .srm-social-bar { display: flex; align-items: center; gap: 0.65rem; flex: 1; }
        .srm-social-track { flex: 1; height: 6px; background: var(--color-border); border-radius: 999px; overflow: hidden; }
        .srm-social-fill { height: 100%; background: var(--color-primary); border-radius: 999px; }
        .srm-social-label { font-size: 0.8rem; color: var(--color-text-muted); white-space: nowrap; }

        .srm-prose { font-size: 0.95rem; line-height: 1.8; color: var(--color-text); margin: 0; white-space: pre-line; }

        .srm-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        </style>

        <main id="primary" class="site-main single-page single-roommate">
            <div class="container">

         

                <!-- Profile header -->
                <div class="srm-hero">
                    <div class="srm-profile-header">
                        <img class="srm-avatar"
                            src="<?php echo esc_url($photo_url); ?>"
                            alt="<?php echo esc_attr($nickname ?: 'Profile photo'); ?>"
                            onerror="this.src='<?php echo esc_url(get_template_directory_uri() . '/images/default-profile.jpg'); ?>'">

                        <div class="srm-profile-header__info">
                            <span class="archive-badge">Roommate</span>
                            <h1><?php the_title(); ?></h1>

                            <div class="srm-meta-row">
                                <?php if ($gender) : ?><span class="srm-meta-item"><?php echo esc_html($gender); ?></span><?php endif; ?>
                                <?php if ($occupation) : ?><span class="srm-meta-item"><?php echo esc_html($occupation); ?></span><?php endif; ?>
                                <?php if ($languages) : ?><span class="srm-meta-item">Speaks <?php echo esc_html($languages); ?></span><?php endif; ?>
                                <?php if ($move_in_date) : ?><span class="srm-movein">📅 Move-in <?php echo esc_html($move_in_date); ?></span><?php endif; ?>
                            </div>

                            <?php if ($budget_min || $budget_max) : ?>
                                <div class="srm-budget">
                                    Budget:
                                    <?php if ($budget_min) echo esc_html(rmt_format_price($budget_min)); ?>
                                    <?php if ($budget_min && $budget_max) echo ' – '; ?>
                                    <?php if ($budget_max) echo esc_html(rmt_format_price($budget_max)); ?>
                                    <span style="font-weight:400;font-size:0.9rem;color:var(--color-text-soft)">/month</span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                <div class="srm-chips" style="margin-top:0.75rem;">
                                    <?php foreach ($location_terms as $term) : ?>
                                        <span class="listing-chip"><?php echo esc_html($term->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Action bar ─────────────────────────────── -->
                <?php if ($is_owner || $is_visitor || $is_admin) : ?>
                    <div class="srm-action-bar">

                        <?php if ($is_owner || $is_admin) : ?>
                            <!-- Owner / admin actions -->
                            <a href="<?php echo esc_url($edit_url); ?>" class="btn btn-secondary">
                                ✏️ Edit Profile
                            </a>

                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('rmt_unpublish_' . $post_id, 'rmt_unpublish_nonce'); ?>
                                <button type="submit" class="btn btn-secondary"
                                    onclick="return confirm('Unpublish this profile? It will no longer be visible to others.')">
                                    🚫 Unpublish
                                </button>
                            </form>
                            <span class="srm-owner-notice">
                                <span class="srm-owner-dot"></span>
                                This is your profile
                            </span>
                        <?php endif; ?>

                        <?php if ($is_visitor) : ?>
                            <!-- Visitor actions -->
                            <?php if (is_user_logged_in()) : ?>
                                <a class="btn btn-primary btn--chat" href="<?php echo esc_url(rmt_get_chat_url($post_author_id, $post_id)); ?>">
                                    💬 Chat with roommate
                                </a>
                            <?php else : ?>
                                <a class="btn btn-primary btn--chat" href="<?php echo esc_url(wp_login_url(get_permalink($post_id))); ?>">
                                    💬 Login to chat
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-ghost btn--report js-report-spam" onclick="document.getElementById('report-modal').classList.add('open')">
                                🚩 Report to Admin
                            </button>
                        <?php endif; ?>

                        <?php if (!is_user_logged_in()) : ?>
                            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="btn btn-secondary">
                                Log in to chat or report
                            </a>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>

                <!-- Main grid -->
                <div class="srm-grid">

                    <!-- Left column -->
                    <div>

                        <?php if ($bio) : ?>
                            <div class="srm-card">
                                <h2>About Me</h2>
                                <p class="srm-prose"><?php echo nl2br(esc_html($bio)); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($cleanliness || $sleep_schedule || $smoker || $has_pets || $social_level) : ?>
                            <div class="srm-card">
                                <h2>Lifestyle</h2>
                                <ul class="srm-detail-list">
                                    <?php if ($cleanliness) : ?><li><strong>Cleanliness</strong><?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                    <?php if ($sleep_schedule) : ?><li><strong>Sleep schedule</strong><?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
                                    <?php if ($smoker) : ?><li><strong>Smoker</strong><?php echo esc_html($smoker); ?></li><?php endif; ?>
                                    <?php if ($has_pets) : ?><li><strong>Has pets</strong><?php echo esc_html($has_pets); ?></li><?php endif; ?>
                                    <?php if ($social_level) : ?>
                                        <li>
                                            <strong>Social level</strong>
                                            <span class="srm-social-bar">
                                                <span class="srm-social-track">
                                                    <span class="srm-social-fill" style="width:<?php echo esc_attr((intval($social_level) / 10) * 100); ?>%"></span>
                                                </span>
                                                <span class="srm-social-label"><?php echo esc_html($social_level); ?>/10</span>
                                            </span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($lifestyles) && !is_wp_error($lifestyles)) : ?>
                            <div class="srm-card">
                                <h2>Lifestyle Tags</h2>
                                <div class="srm-chips">
                                    <?php foreach ($lifestyles as $tag) : ?>
                                        <span class="listing-chip"><?php echo esc_html($tag->name); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($roommate_preference) : ?>
                            <div class="srm-card">
                                <h2>Ideal Roommate</h2>
                                <p class="srm-prose"><?php echo nl2br(esc_html($roommate_preference)); ?></p>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Right sidebar -->
                    <aside>

                        <?php if ($move_in_date || $preferred_area || $budget_min || $budget_max || (!empty($location_terms) && !is_wp_error($location_terms))) : ?>
                            <div class="srm-card">
                                <h2>Room Preferences</h2>
                                <ul class="srm-detail-list">
                                    <?php if ($move_in_date) : ?><li><strong>Move-in</strong><?php echo esc_html($move_in_date); ?></li><?php endif; ?>
                                    <?php if ($preferred_area) : ?><li><strong>Preferred area</strong><?php echo esc_html($preferred_area); ?></li><?php endif; ?>
                                    <?php if ($budget_min || $budget_max) : ?>
                                        <li>
                                            <strong>Budget</strong>
                                            <?php
                                            if ($budget_min) echo esc_html(rmt_format_price($budget_min));
                                            if ($budget_min && $budget_max) echo ' – ';
                                            if ($budget_max) echo esc_html(rmt_format_price($budget_max));
                                            ?>/mo
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                    <div class="srm-chips" style="margin-top:1rem;">
                                        <?php foreach ($location_terms as $term) : ?>
                                            <span class="listing-chip"><?php echo esc_html($term->name); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="srm-card">
                            <h2>Personal Info</h2>
                            <ul class="srm-detail-list">
                                <?php if ($nickname) : ?><li><strong>Name</strong><?php echo esc_html($nickname); ?></li><?php endif; ?>
                                <?php if ($age) : ?><li><strong>Age</strong><?php echo esc_html($age); ?></li><?php endif; ?>
                                <?php if ($gender) : ?><li><strong>Gender</strong><?php echo esc_html($gender); ?></li><?php endif; ?>
                                <?php if ($occupation) : ?><li><strong>Occupation</strong><?php echo esc_html($occupation); ?></li><?php endif; ?>
                                <?php if ($languages) : ?><li><strong>Languages</strong><?php echo esc_html($languages); ?></li><?php endif; ?>
                                <?php if ($hobbies) : ?><li><strong>Hobbies</strong><?php echo esc_html($hobbies); ?></li><?php endif; ?>
                            </ul>
                        </div>

                        <a href="<?php echo esc_url(home_url('/roommates/')); ?>" class="btn btn-secondary" style="width:100%;text-align:center;display:block;">
                            ← Back to Roommates
                        </a>

                    </aside>

                </div><!-- .srm-grid -->

            </div><!-- .container -->
        </main>

        <!-- ── Report Modal ───────────────────────────────── -->
        <?php if ($is_visitor) : ?>
            <div id="report-modal" class="srm-modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
                <div class="srm-modal">
                    <h3>🚩 Report this profile</h3>
                    <p>Tell us why you're reporting this profile. Our team will review it within 24 hours.</p>
                    <form method="post">
                        <?php wp_nonce_field('rmt_report_' . $post_id, 'rmt_report_nonce'); ?>
                        <textarea name="report_reason" placeholder="Describe the issue — e.g. fake profile, offensive content, spam…" required></textarea>
                        <div class="srm-modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('report-modal').classList.remove('open')">
                                Cancel
                            </button>
                            <button type="submit" class="btn-danger" style="padding:0.6rem 1.25rem;border-radius:var(--radius-sm);">
                                Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    <?php endwhile;
endif;

get_footer();