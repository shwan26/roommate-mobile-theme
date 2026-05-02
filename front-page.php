<?php
/**
 * Template Name: BKKroomie Landing Page
 * Description: Beta waitlist landing page for BKKroomie
 */

// Image URLs
$upload_base = wp_upload_dir()['baseurl'];
$bg_url      = $upload_base . '/bkkroomie/Landing%20Page%20Graphic.png';
$logo_url    = $upload_base . '/bkkroomie/logo.png';
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
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            position: relative;

            background:
                linear-gradient(
                    to bottom,
                    rgba(10,10,10,0.70) 0%,
                    rgba(10,10,10,0.45) 50%,
                    rgba(10,10,10,0.80) 100%
                ),
                url('<?php echo esc_url( $bg_url ); ?>') center center / cover no-repeat;
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
            width: clamp(240px, 40vw, 320px);
            height: auto;
            margin: 0 auto 2rem;
        }

        .hero-section .hero-title,
        .hero-section .hero-title span {
            color: #FFFFFF;
        }

        .hero-section .hero-description {
            color: rgba(255, 255, 255, 0.85);
        }

        .bkk-wordmark { display: none; }
    </style>
</head>
<body <?php body_class(); ?>>

<section class="hero-section">
    <div class="container">

        <div class="hero-content" style="max-width:600px; margin-inline:auto; text-align:center;">

            <!-- Logo -->
            <img
                src="<?php echo esc_url( $logo_url ); ?>"
                alt="BKKroomie"
                class="bkk-logo"
            >

            <!-- Headline -->
            <h1 class="hero-title" style="max-width:none; font-size:clamp(2.2rem,6vw,4rem);">
                Here's Where You
                <span style="display:block; color:var(--color-primary);">
                    Find Your Awesome Roommate in Bangkok
                </span>
            </h1>

            <!-- Divider -->
            <hr style="border:none; border-top:1px solid var(--color-border); margin:1.8rem auto; max-width:200px;">

            <!-- Sub text -->
            <p class="hero-description" style="margin-inline:auto; text-align:center;">
                <strong style="color:var(--color-primary);">Be the first to try Bkkroomie.</strong><br>
                Join the beta waitlist.
            </p>

            <!-- Pre-register Button -->
            <div style="margin-top:1.5rem;">
                <a
                    href="https://forms.gle/onrSAg4QXAp4XZ7E7"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-primary"
                    style="min-height:50px; white-space:nowrap; display:inline-flex; align-items:center; padding:0 2rem; text-decoration:none;"
                >
                    Pre-register
                </a>
            </div>

        </div><!-- .hero-content -->

    </div><!-- .container -->
</section>

<?php wp_footer(); ?>
</body>
</html>