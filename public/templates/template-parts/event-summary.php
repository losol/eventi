<?php
/**
 * Template part for displaying post archives and search results
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

?>

<article id="event-<?php the_ID(); ?>" <?php post_class(); ?>>
<div class="entry-content">
	<?php
		if ( is_sticky() && is_home() && ! is_paged() ) {
			printf( '<span class="sticky-post">%s</span>', _x( 'Featured', 'post', 'eventi' ) );
		}
		echo '<a href="' . esc_url( get_permalink() ) . '" class="event-details-link">' . get_the_title() . '</a>';
		echo '&nbsp; &mdash; &nbsp;&nbsp;' . get_the_excerpt();
		echo date_i18n( 'c', get_post_meta( $post_id, 'eventi_startdate', true ) );

	?>
</div><!-- .entry-content -->
</article><!-- #event-${ID} -->