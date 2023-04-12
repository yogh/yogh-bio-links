<?php
/**
 * The Template for displaying YoghBioLinks page
 *
 * This template can be overridden by copying it to yourtheme/yoghbiolinks/yoghbiolinks.php.
 *
 * HOWEVER, on occasion YoghBioLinks will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package YoghBioLinks\Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

yoghbl_get_template_part( 'header', 'yoghbiolinks' ); ?>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<?php
		ob_start();
		yoghbiolinks_logo();
		yoghbiolinks_title();
		yoghbiolinks_description();
		yoghbiolinks_links_html();
		echo apply_filters( 'the_content', ob_get_clean() );
		?>

		<?php the_content(); ?>

	<?php endwhile; // end of the loop. ?>

<?php
yoghbl_get_template_part( 'footer', 'yoghbiolinks' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
