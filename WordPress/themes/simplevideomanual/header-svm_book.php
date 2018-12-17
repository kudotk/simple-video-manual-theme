<?php
/**
 * The template of svm_book post type for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package simplevideomanual
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php is_single() ? the_title() : ''; ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body>
