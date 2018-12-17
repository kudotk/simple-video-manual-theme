<?php
/**
 * The index template
 *
 * If the post belongs to svm_book post type, redirect to book page.
 *
 * @package simplevideomanual
 */

$page_type = '';
if ( is_single() && ( get_post_type() === Svm_Const::POST_TYPE_PAGE ) ) {
	$meta = get_post_meta( get_the_ID() );
	// if this post belongs to manual post, redirect to a manual post page.
	if ( isset( $meta['svm_book_id'] ) && isset( $meta['svm_book_id'][0] ) ) {
		if ( get_post_type( $meta['svm_book_id'][0] ) === Svm_Const::POST_TYPE_MANUAL ) {
			$book_post = get_post( $meta['svm_book_id'][0] );
			if ( $book_post ) {
				wp_safe_redirect( get_the_permalink( $book_post ), '301' );
				exit;
			}
		} else {
			$page_type = 'notInBook';
		}
	} else {
		$page_type = 'notInBook';
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php the_title(); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body>
</body>
<div class="mat-Contents">
	<header class="mat-Header">
		<h1 class="mat-HeaderTitle">Manual Book</h1>
	</header>
	<div class="mat-MainContents">
		<div class="er-Message">
			<p class="er-Message_Text">
				<?php if ( 'notInBook' === $page_type ) { ?>
		<?php esc_html_e( 'Not belongs in Manual book.' ); ?><br>
		<?php esc_html_e( 'Attach to Manual book.' ); ?>
				<?php } else { ?>
		<?php esc_html_e( 'This theme supports only Manual posts.' ); ?>
				<?php } ?>
			</p>
		</div>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>

<?php get_header( 'svm_book' ); ?>
	<div id="app"></div>
<?php
get_footer( 'svm_book' );
