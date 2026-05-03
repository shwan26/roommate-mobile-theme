<?php
/**
 * Template Name: Messages
 * Text-only private chat between subscribers about room / roommate listings.
 */

defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user_id = get_current_user_id();
$recipient_id    = absint($_GET['recipient'] ?? $_GET['with'] ?? 0);
$listing_id      = absint($_GET['listing'] ?? 0);
$is_single_chat  = $recipient_id && $listing_id;
$notice          = '';
$error_message   = '';

/* Delete a whole conversation from the Messages list. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_delete_conversation'])) {
    $delete_recipient_id = absint($_POST['recipient_id'] ?? 0);
    $delete_listing_id   = absint($_POST['listing_id'] ?? 0);

    if (!isset($_POST['rmt_delete_nonce']) || !wp_verify_nonce($_POST['rmt_delete_nonce'], 'rmt_delete_conversation_' . $delete_listing_id . '_' . $delete_recipient_id)) {
        $error_message = 'Security check failed. Please try again.';
    } else {
        $result = rmt_delete_conversation($current_user_id, $delete_recipient_id, $delete_listing_id);

        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
        } else {
            wp_safe_redirect(add_query_arg('chat_deleted', '1', home_url('/messages/')));
            exit;
        }
    }
}

if (isset($_GET['chat_deleted'])) {
    $notice = 'Conversation deleted permanently.';
}

if ($is_single_chat) {
    if (!rmt_user_can_chat_about_listing($current_user_id, $recipient_id, $listing_id)) {
        $error_message = 'You cannot access this conversation.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rmt_send_message'])) {
        if (!isset($_POST['rmt_message_nonce']) || !wp_verify_nonce($_POST['rmt_message_nonce'], 'rmt_send_message_' . $listing_id . '_' . $recipient_id)) {
            $error_message = 'Security check failed. Please try again.';
        } else {
            $result = rmt_insert_message(
                $current_user_id,
                $recipient_id,
                $listing_id,
                wp_unslash($_POST['message'] ?? '')
            );

            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
            } else {
                wp_safe_redirect(rmt_get_chat_url($recipient_id, $listing_id));
                exit;
            }
        }
    }
}

$recipient = $recipient_id ? get_userdata($recipient_id) : null;
$listing   = $listing_id ? get_post($listing_id) : null;

if ($is_single_chat && empty($error_message)) {
    // Get messages first so newly received messages can display as Unread once.
    $messages = rmt_get_conversation_messages($current_user_id, $recipient_id, $listing_id);

    // After loading the chat, mark incoming messages as read.
    rmt_mark_conversation_read($current_user_id, $recipient_id, $listing_id);
} else {
    $messages      = array();
    $conversations = function_exists('rmt_get_user_conversations') ? rmt_get_user_conversations($current_user_id, 200) : array();
}

get_header();
?>

<main id="primary" class="site-main rmt-messages-page">
    <section class="archive-hero">
        <div class="container">
            <span class="archive-badge">Messages</span>
            <h1 class="archive-title"><?php echo $is_single_chat ? 'Chat' : 'All Chats'; ?></h1>
            <p class="archive-description">Send simple text messages about a room or roommate listing.</p>
        </div>
    </section>

    <section class="listing-section">
        <div class="container">
            <?php if (!empty($notice)) : ?>
                <div class="rmt-notice rmt-notice--success">
                    <p><?php echo esc_html($notice); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)) : ?>
                <div class="rmt-notice rmt-notice--error">
                    <p><?php echo esc_html($error_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!$is_single_chat) : ?>
                <div class="single-card rmt-all-chats">
                    

                    <div class="rmt-chat-search-wrap">
                        <input
                            type="search"
                            id="rmt-chat-search"
                            class="par-input"
                            placeholder="Search by user, post title, or message..."
                            aria-label="Search chats"
                        >
                    </div>

                    <?php if (!empty($conversations)) : ?>
                        <div class="rmt-conversation-list rmt-conversation-list--searchable" id="rmt-conversation-list">
                            <?php foreach ($conversations as $conversation) : ?>
                                <?php
                                $other_user = get_userdata((int) $conversation->other_user_id);
                                $chat_listing_id = (int) $conversation->listing_id;
                                $chat_url = rmt_get_chat_url((int) $conversation->other_user_id, $chat_listing_id);
                                $listing_title = get_the_title($chat_listing_id);
                                $search_text = trim(($other_user ? $other_user->display_name : 'User') . ' ' . $listing_title . ' ' . $conversation->last_message);
                                ?>
                                <div class="rmt-conversation-row" data-search="<?php echo esc_attr(strtolower($search_text)); ?>">
                                    <a class="rmt-conversation-item" href="<?php echo esc_url($chat_url); ?>">
                                        <div>
                                            <strong><?php echo esc_html($other_user ? $other_user->display_name : 'User'); ?></strong>
                                            <span><?php echo esc_html($listing_title ?: 'Listing'); ?></span>
                                            <p><?php echo esc_html(wp_trim_words($conversation->last_message, 18)); ?></p>
                                        </div>

                                        <div class="rmt-conversation-meta">
                                            <?php if ((int) $conversation->unread_count > 0) : ?>
                                                <span class="rmt-unread-badge"><?php echo esc_html((int) $conversation->unread_count); ?></span>
                                            <?php endif; ?>
                                            <small><?php echo esc_html(mysql2date('M j, g:i A', $conversation->last_message_at)); ?></small>
                                        </div>
                                    </a>

                                    <form method="post" class="rmt-delete-chat-form">
                                        <?php wp_nonce_field('rmt_delete_conversation_' . $chat_listing_id . '_' . (int) $conversation->other_user_id, 'rmt_delete_nonce'); ?>
                                        <input type="hidden" name="listing_id" value="<?php echo esc_attr($chat_listing_id); ?>">
                                        <input type="hidden" name="recipient_id" value="<?php echo esc_attr((int) $conversation->other_user_id); ?>">
                                        <button
                                            type="submit"
                                            name="rmt_delete_conversation"
                                            value="1"
                                            class="btn btn-secondary btn--danger rmt-delete-chat-btn"
                                            onclick="return confirm('Delete this chat permanently? This cannot be recovered.');"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="rmt-dashboard-empty rmt-chat-no-results" id="rmt-chat-no-results" hidden>No chats match your search.</p>
                    <?php else : ?>
                        <div class="empty-state">
                            <h3>No chats yet</h3>
                            <p>When you start or receive a chat from a room or roommate listing, it will appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <?php if (empty($error_message)) : ?>
                    <div class="rmt-chat-layout">
                        <div class="rmt-chat-panel">
                            <div class="rmt-chat-header">
                                <div>
                                    <h2><?php echo esc_html($recipient ? $recipient->display_name : 'User'); ?></h2>
                                    <?php if ($listing) : ?>
                                        <p>
                                            About:
                                            <a href="<?php echo esc_url(get_permalink($listing_id)); ?>">
                                                <?php echo esc_html(get_the_title($listing_id)); ?>
                                            </a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="rmt-chat-header-actions">
                                    <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/messages/')); ?>">All Chats</a>
                                    <?php if ($listing) : ?>
                                        <a class="btn btn-secondary" href="<?php echo esc_url(get_permalink($listing_id)); ?>">Details Post</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="rmt-chat-box" id="rmt-chat-box">
                                <?php if (!empty($messages)) : ?>
                                    <?php foreach ($messages as $message) : ?>
                                        <?php $is_mine = (int) $message->sender_id === (int) $current_user_id; ?>
                                        <div class="rmt-message <?php echo $is_mine ? 'rmt-message--mine' : 'rmt-message--theirs'; ?>">
                                            <div class="rmt-message__bubble">
                                                <p><?php echo nl2br(esc_html($message->message)); ?></p>
                                                <?php $status_label = function_exists('rmt_get_message_status_label') ? rmt_get_message_status_label($message, $current_user_id) : ''; ?>
                                                <span class="rmt-message__meta">
                                                    <?php echo esc_html(mysql2date('M j, Y g:i A', $message->created_at)); ?>
                                                    <?php if ($status_label) : ?>
                                                        <em class="rmt-message-status rmt-message-status--<?php echo esc_attr(strtolower($status_label)); ?>">
                                                            <?php echo esc_html($status_label); ?>
                                                        </em>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div class="empty-state rmt-chat-empty">
                                        <h3>No messages yet</h3>
                                        <p>Start the conversation with a friendly message.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form method="post" class="rmt-chat-form">
                                <?php wp_nonce_field('rmt_send_message_' . $listing_id . '_' . $recipient_id, 'rmt_message_nonce'); ?>
                                <textarea name="message" class="par-textarea" rows="3" maxlength="1500" placeholder="Write your message..." required></textarea>
                                <button type="submit" name="rmt_send_message" value="1" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
(function () {
    const box = document.getElementById('rmt-chat-box');
    if (box) box.scrollTop = box.scrollHeight;

    const search = document.getElementById('rmt-chat-search');
    const rows = Array.from(document.querySelectorAll('.rmt-conversation-row'));
    const noResults = document.getElementById('rmt-chat-no-results');

    if (search && rows.length) {
        search.addEventListener('input', function () {
            const value = this.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const matches = row.dataset.search.indexOf(value) !== -1;
                row.hidden = !matches;
                if (matches) visibleCount++;
            });

            if (noResults) {
                noResults.hidden = visibleCount !== 0;
            }
        });
    }
})();
</script>

<?php get_footer(); ?>
