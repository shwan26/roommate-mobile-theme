<?php
/**
 * Template Name: Post a Room
 */

defined('ABSPATH') || exit;

// Handle form submission
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_post_room_nonce'])) {

    if (!wp_verify_nonce($_POST['rmt_post_room_nonce'], 'rmt_post_room')) {
        $errors[] = 'Security check failed. Please try again.';
    } elseif (!is_user_logged_in()) {
        $errors[] = 'You must be logged in to post a room.';
    } else {

        $title = sanitize_text_field($_POST['room_title'] ?? '');
        if (empty($title)) {
            $errors[] = 'Room title is required.';
        }

        $rent = sanitize_text_field($_POST['rent'] ?? '');
        if (empty($rent) || !is_numeric($rent)) {
            $errors[] = 'A valid monthly rent amount is required.';
        }

        if (empty($errors)) {

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_content' => wp_kses_post($_POST['description'] ?? ''),
                'post_status'  => 'publish',
                'post_type'    => 'room',
                'post_author'  => get_current_user_id(),
            ]);

            if (is_wp_error($post_id)) {
                $errors[] = 'Could not save the listing. Please try again.';
            } else {

                $room_meta_fields = [
                    '_rent'              => 'rent',
                    '_deposit'           => 'deposit',
                    '_available_date'    => 'available_date',
                    '_property_type'     => 'property_type',
                    '_address'           => 'address',
                    '_nearby_landmark'   => 'nearby_landmark',
                    '_map_url'           => 'map_url',
                    '_utilities'         => 'utilities',
                    '_min_stay'          => 'min_stay',
                    '_gender_preference' => 'gender_preference',
                    '_pet_policy'        => 'pet_policy',
                    '_smoking_policy'    => 'smoking_policy',
                ];

                foreach ($room_meta_fields as $meta_key => $post_key) {
                    if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                        $value = ($meta_key === '_map_url')
                            ? esc_url_raw($_POST[$post_key])
                            : sanitize_text_field($_POST[$post_key]);
                        update_post_meta($post_id, $meta_key, $value);
                    }
                }

                $roommate_meta_fields = [
                    '_nickname'       => 'nickname',
                    '_age'            => 'age',
                    '_gender'         => 'gender',
                    '_occupation'     => 'occupation',
                    '_languages'      => 'languages',
                    '_cleanliness'    => 'cleanliness',
                    '_sleep_schedule' => 'sleep_schedule',
                    '_smoker'         => 'smoker',
                    '_has_pets'       => 'has_pets',
                    '_social_level'   => 'social_level',
                    '_hobbies'        => 'hobbies',
                ];

                foreach ($roommate_meta_fields as $meta_key => $post_key) {
                    if (isset($_POST[$post_key]) && $_POST[$post_key] !== '') {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
                    }
                }

                if (!empty($_POST['bio'])) {
                    update_post_meta($post_id, '_bio', sanitize_textarea_field($_POST['bio']));
                }
                if (!empty($_POST['roommate_preference'])) {
                    update_post_meta($post_id, '_roommate_preference', sanitize_textarea_field($_POST['roommate_preference']));
                }

                if (!empty($_POST['location_area']) && is_array($_POST['location_area'])) {
                    wp_set_post_terms($post_id, array_map('intval', $_POST['location_area']), 'location_area');
                }
                if (!empty($_POST['amenity']) && is_array($_POST['amenity'])) {
                    wp_set_post_terms($post_id, array_map('intval', $_POST['amenity']), 'amenity');
                }
                if (!empty($_POST['lifestyle']) && is_array($_POST['lifestyle'])) {
                    wp_set_post_terms($post_id, array_map('intval', $_POST['lifestyle']), 'lifestyle');
                }
                if (!empty($_POST['room_type'])) {
                    wp_set_post_terms($post_id, [intval($_POST['room_type'])], 'room_type');
                }

                if (!empty($_FILES['room_image']['tmp_name'])) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                    $attachment_id = media_handle_upload('room_image', $post_id);
                    if (!is_wp_error($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    }
                }

                $success = true;
            }
        }
    }
}

$location_terms  = get_terms(['taxonomy' => 'location_area', 'hide_empty' => false]);
$amenity_terms   = get_terms(['taxonomy' => 'amenity',        'hide_empty' => false]);
$lifestyle_terms = get_terms(['taxonomy' => 'lifestyle',      'hide_empty' => false]);
$room_type_terms = get_terms(['taxonomy' => 'room_type',      'hide_empty' => false]);

get_header();
?>

<style>
/* ── Page layout ─────────────────────────────────────────── */
.post-a-room { padding: var(--space-10) 0 var(--space-16); }

/* ── Page header ─────────────────────────────────────────── */
.par-page-header { margin-bottom: var(--space-8); }

.par-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: rgba(137,226,25,0.12);
    border: 1px solid rgba(137,226,25,0.28);
    color: var(--color-secondary);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    margin-bottom: 1rem;
}

.par-eyebrow-dot {
    width: 6px; height: 6px;
    background: var(--color-primary);
    border-radius: 50%;
    flex-shrink: 0;
}

.par-page-header h1 {
    font-size: clamp(2rem,5vw,3rem);
    letter-spacing: -0.02em;
    margin: 0 0 0.5rem;
}

.par-page-header > p {
    font-size: 1.05rem;
    color: var(--color-text-soft);
    margin: 0;
}

/* ── Two-column layout ───────────────────────────────────── */
.par-layout { display: grid; gap: 1.5rem; align-items: start; }

@media (min-width: 992px) {
    .par-layout { grid-template-columns: minmax(0,2fr) minmax(260px,0.85fr); }
}

/* ── Form cards ──────────────────────────────────────────── */
.par-card {
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.25rem;
    transition: box-shadow var(--transition), border-color var(--transition);
}

.par-card:focus-within {
    box-shadow: var(--shadow-md);
    border-color: rgba(137,226,25,0.35);
}

.par-card__header {
    display: flex; align-items: center; gap: 0.85rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.1rem;
    border-bottom: 1px solid var(--color-border);
}

.par-card__icon {
    width: 42px; height: 42px;
    background: rgba(137,226,25,0.14);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0; line-height: 1;
}

.par-card__header h2 { font-size: 1.15rem; margin: 0 0 0.15rem; }
.par-card__header p  { font-size: 0.85rem; color: var(--color-text-muted); margin: 0; }

/* ── Field rows ──────────────────────────────────────────── */
.par-field { display: flex; flex-direction: column; gap: 0.45rem; margin-bottom: 1.15rem; }
.par-field:last-child { margin-bottom: 0; }

.par-field > label {
    font-size: 0.88rem; font-weight: 700;
    color: var(--color-text);
    display: flex; align-items: center; gap: 0.35rem;
}

.par-field .required { color: #E63946; font-size: 0.8rem; }
.par-field > small   { font-size: 0.8rem; color: var(--color-text-muted); }

.par-cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.par-cols-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

@media (max-width: 600px) {
    .par-cols-2, .par-cols-3 { grid-template-columns: 1fr; }
}

/* ── Inputs / Selects / Textareas ────────────────────────── */
.par-input,
.par-select,
.par-textarea {
    width: 100%; min-height: 48px;
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-sm);
    background: var(--color-white);
    color: var(--color-text);
    font-size: 0.95rem;
    font-family: var(--font-body);
    transition: border-color var(--transition), box-shadow var(--transition);
    appearance: none; -webkit-appearance: none;
}

.par-textarea { min-height: 110px; resize: vertical; }
.par-input::placeholder, .par-textarea::placeholder { color: var(--color-text-muted); }

.par-input:focus, .par-select:focus, .par-textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 4px rgba(137,226,25,0.18);
}

.par-input:hover, .par-select:hover, .par-textarea:hover {
    border-color: var(--color-text-muted);
}

/* Select arrow */
.par-select-wrap { position: relative; }
.par-select-wrap::after {
    content: '▾';
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    font-size: 0.85rem; color: var(--color-text-muted); pointer-events: none;
}
.par-select { padding-right: 2.5rem; cursor: pointer; }

/* ── Radio pill group ────────────────────────────────────── */
.par-radio-group, .par-checkbox-group {
    display: flex; flex-wrap: wrap; gap: 0.55rem;
}

.par-radio-label, .par-checkbox-label { cursor: pointer; }
.par-radio-label  input[type="radio"],
.par-checkbox-label input[type="checkbox"] { display: none; }

.par-radio-label span,
.par-checkbox-label span {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.48rem 1rem;
    border-radius: 999px;
    border: 1.5px solid var(--color-border);
    background: var(--color-surface);
    font-size: 0.88rem; font-weight: 700;
    color: var(--color-text-soft);
    transition: var(--transition);
}

.par-checkbox-label span::before {
    content: '';
    width: 14px; height: 14px;
    border-radius: 4px;
    border: 1.5px solid var(--color-border);
    background: var(--color-white);
    flex-shrink: 0;
    transition: var(--transition);
}

.par-radio-label input[type="radio"]:checked + span {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-black);
    box-shadow: 0 2px 8px rgba(137,226,25,0.30);
}

.par-checkbox-label input[type="checkbox"]:checked + span {
    background: #F8FCEB;
    border-color: var(--color-primary);
    color: var(--color-secondary-dark);
}

.par-checkbox-label input[type="checkbox"]:checked + span::before {
    background: var(--color-primary);
    border-color: var(--color-primary);
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 10 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3 3 5-6' stroke='%23111' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 10px;
}

.par-radio-label span:hover,
.par-checkbox-label span:hover { border-color: var(--color-primary); color: var(--color-text); }

/* ── Range slider ────────────────────────────────────────── */
.par-range-wrap { display: flex; align-items: center; gap: 1rem; }

.par-range {
    flex: 1;
    -webkit-appearance: none; appearance: none;
    height: 6px; background: var(--color-border);
    border-radius: 999px; outline: none; cursor: pointer;
}

.par-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--color-primary);
    box-shadow: 0 2px 8px rgba(137,226,25,0.4);
    cursor: pointer; transition: transform var(--transition);
}

.par-range::-webkit-slider-thumb:hover { transform: scale(1.15); }
.par-range::-moz-range-thumb {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--color-primary); border: none;
}

.par-range-val {
    min-width: 38px; height: 38px;
    display: flex; align-items: center; justify-content: center;
    background: var(--color-primary); color: var(--color-black);
    font-weight: 800; font-size: 0.9rem;
    border-radius: 10px; flex-shrink: 0;
}

/* ── File upload ─────────────────────────────────────────── */
.par-file-label {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.7rem; padding: 2rem;
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    background: var(--color-surface-alt);
    cursor: pointer; transition: var(--transition); text-align: center;
}

.par-file-label:hover {
    border-color: var(--color-primary);
    background: rgba(137,226,25,0.05);
}

.par-file-icon {
    width: 48px; height: 48px;
    background: rgba(137,226,25,0.15);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
}

.par-file-text  { font-weight: 700; color: var(--color-text); font-size: 0.95rem; }
.par-file-sub   { font-size: 0.8rem; color: var(--color-text-muted); }
input.par-file-input { display: none; }

/* ── Submit row ──────────────────────────────────────────── */
.par-submit {
    display: flex; align-items: center; gap: 1rem;
    flex-wrap: wrap; margin-top: 0.5rem;
}

.par-submit .btn { padding: 1rem 2rem; font-size: 1rem; }
.par-submit small { font-size: 0.82rem; color: var(--color-text-muted); }

/* ── Notices ─────────────────────────────────────────────── */
.par-notice {
    display: flex; gap: 1rem; align-items: flex-start;
    padding: 1.2rem 1.4rem;
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.par-notice__icon { font-size: 1.3rem; flex-shrink: 0; line-height: 1; margin-top: 1px; }
.par-notice__body p { margin: 0 0 0.75rem; }
.par-notice__body p:last-child { margin: 0; }
.par-notice--success { background: #F0FDF4; border: 1px solid #86EFAC; }
.par-notice--error   { background: #FFF1F2; border: 1px solid #FCA5A5; }
.par-notice--error ul { margin: 0; padding-left: 1.25rem; color: var(--color-text-soft); }
.par-notice--warning { background: #FEFCE8; border: 1px solid #FDE047; }

/* ── Sticky sidebar ──────────────────────────────────────── */
.par-sidebar {
    position: sticky; top: 90px;
    display: flex; flex-direction: column; gap: 1.25rem;
}

.par-tip-card {
    background: linear-gradient(135deg,#4B4B4B 0%,#363636 100%);
    border-radius: var(--radius-lg);
    padding: 1.4rem;
    box-shadow: var(--shadow-md);
}

.par-tip-card h3 { color: #fff; font-size: 1rem; margin: 0 0 1rem; }

.par-tip-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
.par-tip-list li {
    display: flex; gap: 0.7rem;
    font-size: 0.85rem; color: rgba(255,255,255,0.78);
    align-items: flex-start; line-height: 1.5;
}

.par-tip-list li::before {
    content: '✓';
    width: 20px; height: 20px;
    background: var(--color-primary); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; color: var(--color-black); font-weight: 900;
    flex-shrink: 0; margin-top: 1px;
}

/* ── Success page ────────────────────────────────────────── */
.par-success {
    text-align: center;
    padding: var(--space-12) var(--space-8);
    background: var(--color-surface);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.par-success__icon {
    width: 72px; height: 72px;
    background: var(--color-primary); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; margin: 0 auto 1.5rem;
    box-shadow: 0 8px 24px rgba(137,226,25,0.4);
}

.par-success h2 { font-size: 1.8rem; margin-bottom: 0.75rem; }
.par-success > p { max-width: 46ch; margin: 0 auto 2rem; }
</style>

<main id="primary" class="site-main single-page post-a-room">
    <div class="container">

        <!-- Page Header -->
        <div class="par-page-header">
            <div class="par-eyebrow">
                <span class="par-eyebrow-dot"></span>
                New Listing
            </div>
            <h1>Post a Room</h1>
            <p>Fill in the details below and we'll match you with the right roommate.</p>
        </div>

        <?php if ($success) : ?>

            <div class="par-success">
                <div class="par-success__icon">🎉</div>
                <h2>Listing Published!</h2>
                <p>Your room listing is now live and visible to others.</p>
                <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
                    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/')); ?>">Back to Home</a>
                    <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/post-a-room/')); ?>">Post Another Room</a>
                </div>
            </div>

        <?php elseif (!is_user_logged_in()) : ?>

            <div class="par-notice par-notice--warning">
                <span class="par-notice__icon">🔒</span>
                <div class="par-notice__body">
                    <p>You need to be logged in to post a room listing.</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">Log In</a>
                </div>
            </div>

        <?php else : ?>

            <?php if (!empty($errors)) : ?>
                <div class="par-notice par-notice--error">
                    <span class="par-notice__icon">⚠️</span>
                    <div class="par-notice__body">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?php echo esc_html($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="par-layout">

                <!-- ──────────── MAIN FORM ──────────── -->
                <form method="post" enctype="multipart/form-data" novalidate>
                    <?php wp_nonce_field('rmt_post_room', 'rmt_post_room_nonce'); ?>

                    <!-- ① Room Details -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🏠</div>
                            <div>
                                <h2>Room Details</h2>
                                <p>Basic info about the room and property.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="room_title">Listing Title <span class="required">*</span></label>
                            <input class="par-input" type="text" id="room_title" name="room_title"
                                value="<?php echo esc_attr($_POST['room_title'] ?? ''); ?>"
                                placeholder="e.g. Bright private room in Sukhumvit" required>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="rent">Monthly Rent (฿) <span class="required">*</span></label>
                                <input class="par-input" type="number" id="rent" name="rent"
                                    value="<?php echo esc_attr($_POST['rent'] ?? ''); ?>"
                                    min="0" placeholder="e.g. 8000" required>
                            </div>
                            <div class="par-field">
                                <label for="deposit">Deposit (฿)</label>
                                <input class="par-input" type="number" id="deposit" name="deposit"
                                    value="<?php echo esc_attr($_POST['deposit'] ?? ''); ?>"
                                    min="0" placeholder="e.g. 16000">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="property_type">Property Type</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="property_type" name="property_type">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Condo','Apartment','House','Townhouse','Studio','Serviced Apartment'] as $pt) : ?>
                                            <option value="<?php echo esc_attr($pt); ?>" <?php selected($_POST['property_type'] ?? '', $pt); ?>><?php echo esc_html($pt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="available_date">Available From</label>
                                <input class="par-input" type="date" id="available_date" name="available_date"
                                    value="<?php echo esc_attr($_POST['available_date'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="address">Address / Location</label>
                            <input class="par-input" type="text" id="address" name="address"
                                value="<?php echo esc_attr($_POST['address'] ?? ''); ?>"
                                placeholder="e.g. Soi 11, Sukhumvit, Bangkok">
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="nearby_landmark">Nearby Landmark</label>
                                <input class="par-input" type="text" id="nearby_landmark" name="nearby_landmark"
                                    value="<?php echo esc_attr($_POST['nearby_landmark'] ?? ''); ?>"
                                    placeholder="e.g. 5 min walk to BTS Asok">
                            </div>
                            <div class="par-field">
                                <label for="map_url">Google Maps URL</label>
                                <input class="par-input" type="url" id="map_url" name="map_url"
                                    value="<?php echo esc_attr($_POST['map_url'] ?? ''); ?>"
                                    placeholder="https://maps.google.com/...">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="utilities">Utilities</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="utilities" name="utilities">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Included','Not included','Partially included'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['utilities'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="min_stay">Minimum Stay</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="min_stay" name="min_stay">
                                        <option value="">— Select —</option>
                                        <?php foreach (['1 month','2 months','3 months','6 months','1 year'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['min_stay'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="par-cols-3">
                            <div class="par-field">
                                <label for="gender_preference">Gender Preference</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="gender_preference" name="gender_preference">
                                        <option value="">— Any —</option>
                                        <?php foreach (['Male only','Female only','No preference'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['gender_preference'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="pet_policy">Pet Policy</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="pet_policy" name="pet_policy">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Pets allowed','No pets','Small pets only'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['pet_policy'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="smoking_policy">Smoking Policy</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="smoking_policy" name="smoking_policy">
                                        <option value="">— Select —</option>
                                        <?php foreach (['No smoking','Smoking outside only','Smoking allowed'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['smoking_policy'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ② Categories & Tags -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🏷️</div>
                            <div>
                                <h2>Categories & Tags</h2>
                                <p>Help people discover your listing faster.</p>
                            </div>
                        </div>

                        <?php if (!empty($room_type_terms) && !is_wp_error($room_type_terms)) : ?>
                            <div class="par-field">
                                <label>Room Type</label>
                                <div class="par-radio-group">
                                    <?php
                                    $selected_rt = intval($_POST['room_type'] ?? 0);
                                    foreach ($room_type_terms as $term) :
                                    ?>
                                        <label class="par-radio-label">
                                            <input type="radio" name="room_type"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php checked($selected_rt, $term->term_id); ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($location_terms) && !is_wp_error($location_terms)) : ?>
                            <div class="par-field">
                                <label>Location Area</label>
                                <div class="par-checkbox-group">
                                    <?php
                                    $selected_locs = array_map('intval', (array)($_POST['location_area'] ?? []));
                                    foreach ($location_terms as $term) :
                                    ?>
                                        <label class="par-checkbox-label">
                                            <input type="checkbox" name="location_area[]"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php echo in_array($term->term_id, $selected_locs) ? 'checked' : ''; ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($amenity_terms) && !is_wp_error($amenity_terms)) : ?>
                            <div class="par-field">
                                <label>Amenities</label>
                                <div class="par-checkbox-group">
                                    <?php
                                    $selected_amenities = array_map('intval', (array)($_POST['amenity'] ?? []));
                                    foreach ($amenity_terms as $term) :
                                    ?>
                                        <label class="par-checkbox-label">
                                            <input type="checkbox" name="amenity[]"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php echo in_array($term->term_id, $selected_amenities) ? 'checked' : ''; ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($lifestyle_terms) && !is_wp_error($lifestyle_terms)) : ?>
                            <div class="par-field">
                                <label>Lifestyle Tags</label>
                                <div class="par-checkbox-group">
                                    <?php
                                    $selected_lifestyles = array_map('intval', (array)($_POST['lifestyle'] ?? []));
                                    foreach ($lifestyle_terms as $term) :
                                    ?>
                                        <label class="par-checkbox-label">
                                            <input type="checkbox" name="lifestyle[]"
                                                value="<?php echo esc_attr($term->term_id); ?>"
                                                <?php echo in_array($term->term_id, $selected_lifestyles) ? 'checked' : ''; ?>>
                                            <span><?php echo esc_html($term->name); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ③ Description & Photo -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">📝</div>
                            <div>
                                <h2>Description & Photo</h2>
                                <p>Show off the space with words and a great photo.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="description">Room Description</label>
                            <textarea class="par-textarea" id="description" name="description" rows="6"
                                placeholder="Describe the room, the apartment, the neighbourhood, the vibe…"><?php echo esc_textarea($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="par-field">
                            <label>Room Photo</label>
                            <label class="par-file-label" for="room_image">
                                <div class="par-file-icon">📷</div>
                                <span class="par-file-text">Click to upload a photo</span>
                                <span class="par-file-sub">JPEG or PNG · Max 5 MB recommended</span>
                            </label>
                            <input class="par-file-input" type="file" id="room_image" name="room_image" accept="image/*"
                                onchange="this.previousElementSibling.querySelector('.par-file-text').textContent = this.files[0]?.name || 'Click to upload a photo'">
                        </div>
                    </div>

                    <!-- ④ About You -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">👤</div>
                            <div>
                                <h2>About You</h2>
                                <p>Tell potential roommates who they'll be living with.</p>
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="nickname">Name / Nickname</label>
                                <input class="par-input" type="text" id="nickname" name="nickname"
                                    value="<?php echo esc_attr($_POST['nickname'] ?? ''); ?>" placeholder="e.g. Alex">
                            </div>
                            <div class="par-field">
                                <label for="age">Age</label>
                                <input class="par-input" type="number" id="age" name="age"
                                    value="<?php echo esc_attr($_POST['age'] ?? ''); ?>"
                                    min="18" max="99" placeholder="e.g. 28">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="gender">Gender</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="gender" name="gender">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Male','Female','Non-binary','Prefer not to say'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['gender'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="occupation">Occupation</label>
                                <input class="par-input" type="text" id="occupation" name="occupation"
                                    value="<?php echo esc_attr($_POST['occupation'] ?? ''); ?>"
                                    placeholder="e.g. Software Developer">
                            </div>
                        </div>

                        <div class="par-cols-2">
                            <div class="par-field">
                                <label for="languages">Languages Spoken</label>
                                <input class="par-input" type="text" id="languages" name="languages"
                                    value="<?php echo esc_attr($_POST['languages'] ?? ''); ?>"
                                    placeholder="e.g. English, Thai">
                            </div>
                            <div class="par-field">
                                <label for="cleanliness">Cleanliness Level</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="cleanliness" name="cleanliness">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Very tidy','Tidy','Average','Relaxed'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['cleanliness'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="par-cols-3">
                            <div class="par-field">
                                <label for="sleep_schedule">Sleep Schedule</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="sleep_schedule" name="sleep_schedule">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Early bird','Night owl','Flexible'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['sleep_schedule'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="smoker">Do You Smoke?</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="smoker" name="smoker">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Yes','No','Outside only'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['smoker'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="par-field">
                                <label for="has_pets">Do You Have Pets?</label>
                                <div class="par-select-wrap">
                                    <select class="par-select" id="has_pets" name="has_pets">
                                        <option value="">— Select —</option>
                                        <?php foreach (['Yes','No'] as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($_POST['has_pets'] ?? '', $opt); ?>><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="social_level">Social Level</label>
                            <small>1 = very private &nbsp;·&nbsp; 10 = very social</small>
                            <div class="par-range-wrap" style="margin-top:0.5rem">
                                <input class="par-range" type="range" id="social_level" name="social_level"
                                    min="1" max="10"
                                    value="<?php echo esc_attr($_POST['social_level'] ?? 5); ?>"
                                    oninput="document.getElementById('sl_val').textContent = this.value">
                                <span class="par-range-val" id="sl_val"><?php echo esc_html($_POST['social_level'] ?? 5); ?></span>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="hobbies">Hobbies / Interests</label>
                            <input class="par-input" type="text" id="hobbies" name="hobbies"
                                value="<?php echo esc_attr($_POST['hobbies'] ?? ''); ?>"
                                placeholder="e.g. Hiking, reading, cooking">
                        </div>

                        <div class="par-field">
                            <label for="bio">About Me (Bio)</label>
                            <textarea class="par-textarea" id="bio" name="bio" rows="4"
                                placeholder="Tell potential roommates a bit about yourself…"><?php echo esc_textarea($_POST['bio'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- ⑤ Preferred Roommate -->
                    <div class="par-card">
                        <div class="par-card__header">
                            <div class="par-card__icon">🔍</div>
                            <div>
                                <h2>Preferred Roommate</h2>
                                <p>Describe who you're looking for.</p>
                            </div>
                        </div>

                        <div class="par-field">
                            <label for="roommate_preference">Who are you looking for?</label>
                            <textarea class="par-textarea" id="roommate_preference" name="roommate_preference" rows="5"
                                placeholder="Describe your ideal roommate — lifestyle, habits, deal-breakers…"><?php echo esc_textarea($_POST['roommate_preference'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="par-submit">
                        <button type="submit" class="btn btn-primary">Submit Listing →</button>
                        <small>Your listing will be published immediately.</small>
                    </div>

                </form>

                <!-- ──────────── SIDEBAR ──────────── -->
                <aside class="par-sidebar">

                    <div class="par-tip-card">
                        <h3>💡 Tips for a great listing</h3>
                        <ul class="par-tip-list">
                            <li>Add a clear, well-lit photo — listings with photos get 3× more views.</li>
                            <li>Be specific about location (BTS/MRT stop helps a lot).</li>
                            <li>Write a genuine bio — people want to know who they'll live with.</li>
                            <li>Mention your daily schedule and lifestyle honestly.</li>
                            <li>Set realistic expectations in your Preferred Roommate section.</li>
                        </ul>
                    </div>

                    <div class="par-tip-card" style="background:var(--color-surface);border:1px solid var(--color-border);box-shadow:var(--shadow-sm);text-align:center;">
                        <p style="font-size:0.82rem;color:var(--color-text-muted);margin-bottom:1rem;">
                            Looking for a room instead?
                        </p>
                        <a href="<?php echo esc_url(home_url('/post-a-roommate/')); ?>" class="btn btn-secondary" style="width:100%;">
                            Post a Roommate Profile
                        </a>
                    </div>

                </aside>

            </div><!-- .par-layout -->

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>