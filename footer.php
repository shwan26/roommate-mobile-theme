<?php
/**
 * Theme Footer
 */

defined('ABSPATH') || exit;
?>

<footer class="site-footer site-footer--simple">
    <div class="container">
        <div class="simple-footer simple-footer--with-links">

            <div class="simple-footer__left">
                <p class="simple-footer__text">
                    Find your roommate © <?php echo esc_html(date('Y')); ?> bkkroomie. All rights reserved.
                </p>
            </div>

            <nav class="simple-footer__links" aria-label="<?php esc_attr_e('Footer links', 'roommate-mobile-theme'); ?>">
                <button type="button" class="simple-footer__link" data-footer-modal="about">
                    About Us
                </button>

                <button type="button" class="simple-footer__link" data-footer-modal="disclaimer">
                    Disclaimer
                </button>

                <button type="button" class="simple-footer__link" data-footer-modal="privacy">
                    Privacy Policy
                </button>
            </nav>

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

<div class="footer-modal" id="footer-modal-about" aria-hidden="true">
    <div class="footer-modal__overlay" data-footer-modal-close></div>

    <div class="footer-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="footer-modal-about-title">
        <button type="button" class="footer-modal__close" data-footer-modal-close aria-label="Close modal">
            ×
        </button>

        <h2 id="footer-modal-about-title">About Bkkroomie</h2>

        <div class="footer-modal__content">
            <p>
                Bkkroomie is a simple, community-driven platform designed to help people find the right roommate in Bangkok.
            </p>
            <p>
               Whether you already have a room and need someone to share it with, or you’re looking for a room to move into, Bkkroomie connects you with the right match.
            </p>

            <h3>What We Do</h3>
            <ul>
                <li>Have a room? Find a compatible roommate to share your space.</li>
                <li>Need a room? Discover listings from people looking for someone just like you.</li>
                <li>Connect directly and make arrangements that work for both sides.</li>
            </ul>

            <h3>Why Bkkroomie</h3>
            <p>
                Finding a roommate can be time-consuming and uncertain. We make the process more straightforward by bringing both sides together in one place, just real people and real opportunities.
            </p>

            <h3>Free to Browse</h3>
            <p>
                You can explore listings and opportunities on Bkkroomie for free. Start browsing, connect with others, and find a living situation that fits your lifestyle.
            </p>

            <h3>Our Mission</h3>
            <p>
                We aim to make shared living in Bangkok easier, more accessible, and more human by helping people find not just a place to stay, but the right person to share it with.
            </p>

            <h3>Join the Community</h3>
            <p>
                Whether you’re offering a room or searching for one, Bkkroomie is here to help you find your match.
            </p>
        </div>
    </div>
</div>

<div class="footer-modal" id="footer-modal-disclaimer" aria-hidden="true">
    <div class="footer-modal__overlay" data-footer-modal-close></div>

    <div class="footer-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="footer-modal-disclaimer-title">
        <button type="button" class="footer-modal__close" data-footer-modal-close aria-label="Close modal">
            ×
        </button>

        <h2 id="footer-modal-disclaimer-title">Disclaimer</h2>

        <div class="footer-modal__content">
            <p>
                The information provided on Bkkroomie is for general informational purposes only. While we aim to create a helpful and reliable platform, Bkkroomie does not guarantee the accuracy, completeness, or reliability of any listings, profiles, or user-generated content.
            </p>

            <h3>User Responsibility</h3>
            <p>
                Bkkroomie acts solely as a platform to connect individuals. We do not own, manage, or inspect any properties listed, nor do we verify the identity, background, or intentions of users. It is your responsibility to communicate carefully, verify details, and make informed decisions before entering into any agreement.
            </p>

            <h3>No Endorsement</h3>
            <p>
                Any listings, profiles, or communications between users do not constitute an endorsement, recommendation, or guarantee by Bkkroomie. Users are encouraged to conduct their own checks, including property visits and personal verification.
            </p>

            <h3>Limitation of Liability</h3>
            <p>
                Bkkroomie is not liable for any loss, damage, disputes, or issues that may arise from interactions between users, including but not limited to roommate arrangements, rental agreements, payments, or personal conduct.
            </p>

            <h3>External Links</h3>
            <p>
                Our platform may contain links to third-party websites or services. Bkkroomie does not control or take responsibility for the content, policies, or practices of these external sites.
            </p>

            <h3>Use at Your Own Risk</h3>
            <p>
                By using Bkkroomie, you acknowledge and agree that you do so at your own risk. We recommend taking all necessary precautions to ensure your safety and security when interacting with others online and offline.
            </p>

            <h3>Contact Us</h3>
            <p>
                If you have any questions about this Disclaimer, please contact us through our website.
            </p>
        </div>
    </div>
</div>

<div class="footer-modal" id="footer-modal-privacy" aria-hidden="true">
    <div class="footer-modal__overlay" data-footer-modal-close></div>

    <div class="footer-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="footer-modal-privacy-title">
        <button type="button" class="footer-modal__close" data-footer-modal-close aria-label="Close modal">
            ×
        </button>

        <h2 id="footer-modal-privacy-title">Privacy Policy</h2>

        <div class="footer-modal__content">
            <p>
                At BKKRoomie, your privacy matters. This Privacy Policy explains how we collect, use, and protect your information when you use our platform.
            </p>

            <h3>1. Information We Collect</h3>
            <p>
                We may collect the following types of information:
            </p>
            <ul>
                <li><b> Personal information: </b> such as your name, email address, phone number, and profile details when you sign up or create a listing.</li>
                <li><b> Usage data: </b> including how you interact with the platform (e.g., pages visited, features used).</li>
                <li><b> Communication data: </b> messages or information you share with other users through the platform.</li>
            </ul>

            <h3>2. How We Use Your Information</h3>
            <p>
                We use your information to:
            </p>
            <ul>
                <li>Provide and improve our services.</li>
                <li>Connect you with potential roommates or listings.</li>
                <li>Facilitate communication between users.</li>
                <li>Maintain platform safety and prevent misuse.</li>
                <li>Send important updates or service-related notifications.</li>
            </ul>

            <h3>3. Sharing of Information</h3>
            <ul>
                <li>Your profile information and listings may be visible to other users of the platform.</li>
                <li>We do not sell your personal data to third parties.</li>
                <li>We may share information if required by law or to protect the safety and integrity of our platform.</li>
            </ul>

            <h3>4. Data Security</h3>
            <p>
               We take reasonable measures to protect your information from unauthorized access, loss, or misuse. However, no online platform can guarantee complete security.
            </p>

            <h3>5. Your Responsibility</h3>
            <p>
                Please be mindful of the information you share publicly. Avoid posting sensitive personal details that could put your privacy or safety at risk.
            </p>

            <h3>6. Cookies and Tracking</h3>
            <p>
                BKKRoomie may use cookies or similar technologies to enhance user experience, analyze usage, and improve our services.
            </p>

            <h3>7. Your Rights</h3>
            <p>
                You may request to update, correct, or delete your personal information by contacting us. We will do our best to respond in a timely manner.
            </p>

            <h3>8. Third-Party Services</h3>
            <p>
                Our platform may include links or integrations with third-party services. We are not responsible for their privacy practices.
            </p>

            <h3>9. Changes to This Policy</h3>
            <p>
                We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date.
            </p>

            <h3>10. Contact Us</h3>
            <p>
                If you have any questions or concerns about this Privacy Policy, please contact us through our website.
            </p>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openButtons = document.querySelectorAll('[data-footer-modal]');
    const closeButtons = document.querySelectorAll('[data-footer-modal-close]');
    const body = document.body;

    function openModal(type) {
        const modal = document.getElementById('footer-modal-' + type);

        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        body.classList.add('footer-modal-open');

        const closeButton = modal.querySelector('.footer-modal__close');
        if (closeButton) {
            closeButton.focus();
        }
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        body.classList.remove('footer-modal-open');
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(button.getAttribute('data-footer-modal'));
        });
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal(button.closest('.footer-modal'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            const openModalEl = document.querySelector('.footer-modal.is-open');
            closeModal(openModalEl);
        }
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>