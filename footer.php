<?php
/**
 * Theme Footer
 */

defined('ABSPATH') || exit;
?>

<footer class="site-footer site-footer--simple">
    <div class="container">
        <div class="simple-footer">
            <p class="simple-footer__text">
                Find your roommate © <?php echo esc_html(date('Y')); ?> bkkroomie. All rights reserved.
            </p>

            <a
                class="simple-footer__social"
                href="https://www.facebook.com/bkkroomie"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path fill="currentColor" d="M22 12.06C22 6.48 17.52 2 11.94 2S2 6.48 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.88 3.77-3.88 1.09 0 2.23.19 2.23.19v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56v1.89h2.78l-.44 2.91h-2.34V22c4.78-.76 8.45-4.92 8.45-9.94Z"/>
                </svg>
            </a>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>