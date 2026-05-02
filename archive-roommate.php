<?php
/**
 * Archive Template: Roommate
 */

defined('ABSPATH') || exit;

get_header();

// Read current filters from query string
$q             = sanitize_text_field($_GET['q'] ?? '');
$budget_min_q  = sanitize_text_field($_GET['budget_min'] ?? '');
$budget_max_q  = sanitize_text_field($_GET['budget_max'] ?? '');
$move_in_q     = sanitize_text_field($_GET['move_in'] ?? '');
$location_q    = absint($_GET['location_area'] ?? 0);
$sort_q        = sanitize_text_field($_GET['sort'] ?? 'newest');

// Terms for dropdowns
$location_terms_all  = get_terms(['taxonomy' => 'location_area', 'hide_empty' => false]);
?>

<main id="primary" class="site-main archive-page archive-roommate">
    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Roommate</span>
            <h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
            <p class="archive-description">
                Browse profiles from people looking for a room, a roommate, or both.
            </p>
        </div>
    </section>

    <!-- Filters -->
    <section class="archive-filters">
        <div class="container">
            <form method="get" class="filter-form">

                <div class="filter-grid">

                    <div class="filter-group" style="grid-column:1/-1;">
                        <label for="q">Search</label>
                        <input
                            type="search"
                            id="q"
                            name="q"
                            placeholder="Search name, title, bio…"
                            value="<?php echo esc_attr($q); ?>"
                        >
                    </div>

                    <div class="filter-group">
                        <label for="budget_min">Budget Min</label>
                        <input type="number" id="budget_min" name="budget_min" min="0"
                            value="<?php echo esc_attr($budget_min_q); ?>"
                            placeholder="e.g. 6000">
                    </div>

                    <div class="filter-group">
                        <label for="budget_max">Budget Max</label>
                        <input type="number" id="budget_max" name="budget_max" min="0"
                            value="<?php echo esc_attr($budget_max_q); ?>"
                            placeholder="e.g. 12000">
                    </div>

                    <div class="filter-group">
                        <label for="move_in">Move-in (on/after)</label>
                        <input type="date" id="move_in" name="move_in"
                            value="<?php echo esc_attr($move_in_q); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="location_area">Location Area</label>
                        <select id="location_area" name="location_area">
                            <option value="">— Any —</option>
                            <?php if (!is_wp_error($location_terms_all)) : ?>
                                <?php foreach ($location_terms_all as $t) : ?>
                                    <option value="<?php echo esc_attr($t->term_id); ?>" <?php selected($location_q, $t->term_id); ?>>
                                        <?php echo esc_html($t->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sort">Sort</label>
                        <select id="sort" name="sort">
                            <option value="newest" <?php selected($sort_q, 'newest'); ?>>Newest</option>
                            <option value="oldest" <?php selected($sort_q, 'oldest'); ?>>Oldest</option>
                        </select>
                    </div>

                    <div class="filter-actions" style="grid-column:1/-1;">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a class="btn btn-secondary" href="<?php echo esc_url(get_post_type_archive_link('roommate')); ?>">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <section class="archive-listings">
        <div class="container">
            <?php if (have_posts()) : ?>
                <div class="listing-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        $budget_min     = rmt_get_meta(get_the_ID(), '_budget_min');
                        $budget_max     = rmt_get_meta(get_the_ID(), '_budget_max');
                        $move_in_date   = rmt_get_meta(get_the_ID(), '_move_in_date');
                        $preferred_area = rmt_get_meta(get_the_ID(), '_preferred_area_text');
                        $nickname       = rmt_get_meta(get_the_ID(), '_nickname');
                        $occupation     = rmt_get_meta(get_the_ID(), '_occupation');
                        $cleanliness    = rmt_get_meta(get_the_ID(), '_cleanliness');
                        $sleep_schedule = rmt_get_meta(get_the_ID(), '_sleep_schedule');
                        $teamup_ok      = rmt_get_meta(get_the_ID(), '_teamup_ok');

                        $location_terms  = get_the_terms(get_the_ID(), 'location_area');
                        $room_type_terms = get_the_terms(get_the_ID(), 'room_type');
                        ?>
                        <article class="listing-card">
                            <a href="<?php the_permalink(); ?>" class="listing-card__image-link">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="listing-card__image">
                                        <?php the_post_thumbnail('large'); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="listing-card__image listing-card__image--placeholder">
                                        <span>No Photo</span>
                                    </div>
                                <?php endif; ?>
                            </a>

                            <div class="listing-card__content">
                                <div class="listing-card__top">
                                    <?php if ($budget_min || $budget_max) : ?>
                                        <div class="listing-card__price">
                                            Budget:
                                            <?php if ($budget_min) : ?>
                                                <?php echo esc_html(rmt_format_price($budget_min)); ?>
                                            <?php endif; ?>

                                            <?php if ($budget_min && $budget_max) : ?>
                                                -
                                            <?php endif; ?>

                                            <?php if ($budget_max) : ?>
                                                <?php echo esc_html(rmt_format_price($budget_max)); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($move_in_date) : ?>
                                        <div class="listing-card__available">
                                            Move-in: <?php echo esc_html($move_in_date); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h2 class="listing-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>

                                <?php if ($preferred_area) : ?>
                                    <p class="listing-card__address">
                                        Preferred Area: <?php echo esc_html($preferred_area); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="listing-card__chips">
                                    <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($location_terms[0]->name); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                                        <span class="listing-chip"><?php echo esc_html($room_type_terms[0]->name); ?></span>
                                    <?php endif; ?>

                                    <?php if ($teamup_ok) : ?>
                                        <span class="listing-chip"><?php echo esc_html($teamup_ok); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="listing-card__person">
                                    <strong>About This Person</strong>
                                    <ul>
                                        <?php if ($nickname) : ?><li>Name: <?php echo esc_html($nickname); ?></li><?php endif; ?>
                                        <?php if ($occupation) : ?><li>Occupation: <?php echo esc_html($occupation); ?></li><?php endif; ?>
                                        <?php if ($cleanliness) : ?><li>Cleanliness: <?php echo esc_html($cleanliness); ?></li><?php endif; ?>
                                        <?php if ($sleep_schedule) : ?><li>Sleep Schedule: <?php echo esc_html($sleep_schedule); ?></li><?php endif; ?>
                                    </ul>
                                </div>

                                <div class="listing-card__excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Profile</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <div class="pagination-wrap">
                    <?php the_posts_pagination(); ?>
                </div>

            <?php else : ?>
                <div class="empty-state">
                    <h2>No Roommate Profiles Found</h2>
                    <p>Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>