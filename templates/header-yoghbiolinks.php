<?php
/**
 * The Template for displaying YoghBioLinks page
 *
 * This template can be overridden by copying it to yourtheme/yoghbl/header-yoghbl.php.
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

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<main class="yoghbl-main">
		<div class="container">
