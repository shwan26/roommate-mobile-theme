<?php
/**
 * Template Name: BKKroomie Landing Page
 * Description: Beta waitlist landing page for BKKroomie
 */

// Image URLs
$logo_url = get_template_directory_uri() . '/assets/images/bbkroomie-full.png';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BKKroomie – Find Your Awesome Roommate in Bangkok</title>
    <?php wp_head(); ?>

    <style>
        body,
        .site,
        .site-content,
        #page,
        #content {
            background: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        body {
            min-height: 100svh;
            display: flex;
            flex-direction: column;
        }

        .hero-section {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 4vh, 3rem) 0;
            position: relative;
            background: #ffffff;
            color: #000000;
        }

        .hero-content {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .bkk-logo {
            display: block;
            width: clamp(280px, 42vw, 430px);
            height: auto;
            margin: 0 auto clamp(1.1rem, 2.5vh, 2rem);
        }

        .hero-section .hero-title,
        .hero-section .hero-description,
        .hero-section .hero-description strong {
            color: #000000 !important;
        }

        .hero-section .hero-title {
            margin-bottom: 0;
            font-size: clamp(2.3rem, 6vw, 4.25rem) !important;
            font-weight: 400;
        }

        .hero-title__intro {
            display: block;
            color: #000000;
            font-weight: 400;
        }

        .hero-title__accent {
            display: block;
            color: var(--color-primary);
            font-weight: 700;
        }

        .hero-section .hero-description {
            margin-bottom: 0;
            font-size: clamp(1.1rem, 2.2vw, 1.35rem);
            line-height: 1.45;
        }

        .hero-section .btn-primary {
            color: #ffffff !important;
        }

        .hero-section .btn-primary:hover,
        .hero-section .btn-primary:focus-visible {
            background: #6bea12;
            border-color: #6bea12;
            color: #ffffff !important;
            box-shadow: 0 10px 32px rgba(88, 204, 2, 0.48), 0 0 0 4px rgba(88, 204, 2, 0.14);
            filter: brightness(1.05);
        }

        .site-footer--simple {
            flex: 0 0 auto;
            padding: 0.65rem 0 !important;
        }

        .simple-footer--with-links {
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center;
            gap: 0.4rem 0.85rem;
            text-align: center !important;
        }

        .simple-footer__left {
            min-width: 0 !important;
        }

        .simple-footer__text,
        .simple-footer__link {
            font-size: 0.72rem !important;
            line-height: 1.3;
        }

        .simple-footer__social {
            width: 26px !important;
            height: 26px !important;
        }

        .simple-footer__social svg {
            width: 14px;
            height: 14px;
        }

        .bkk-wordmark { display: none; }

        @media (max-height: 700px) {
            .bkk-logo {
                width: clamp(230px, 36vw, 330px);
                margin-bottom: 1rem;
            }

            .hero-section .hero-title {
                font-size: clamp(2rem, 5.3vw, 3.4rem) !important;
            }

            .site-footer--simple {
                padding: 0.5rem 0 !important;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>

<section class="hero-section">
    <div class="container">

        <div class="hero-content" style="max-width:760px; margin-inline:auto; text-align:center;">

            <!-- Logo -->
            <img
                src="<?php echo esc_url( $logo_url ); ?>"
                alt="BKKroomie"
                class="bkk-logo"
            >

            <!-- Headline -->
            <h1 class="hero-title" style="max-width:none;">
                <span class="hero-title__intro">Here's Where You</span>
                <span class="hero-title__accent">
                    Find Your Awesome Roommate in Bangkok
                </span>
            </h1>

            <!-- Divider -->
            <hr style="border:none; border-top:1px solid var(--color-border); margin:clamp(1.2rem,3vh,2rem) auto; max-width:360px;">

            <!-- Sub text -->
            <p class="hero-description" style="margin-inline:auto; text-align:center;">
                <strong style="color:#000000;">Be the first to try Bkkroomie.</strong><br>
                Join the beta waitlist.
            </p>

            <!-- Pre-register Button -->
            <div style="margin-top:clamp(1.25rem,3vh,2rem);">
                <a
                    href="https://forms.gle/onrSAg4QXAp4XZ7E7"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-primary"
                    style="min-height:54px; white-space:nowrap; display:inline-flex; align-items:center; padding:0 2.4rem; text-decoration:none; font-size:1.05rem;"
                >
                    Pre-register
                </a>
            </div>

        </div><!-- .hero-content -->

    </div><!-- .container -->
</section>

<?php get_footer(); ?>
</body>
</html>
