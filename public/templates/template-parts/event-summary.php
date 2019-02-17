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
		$post_id = get_the_ID();
		if ( is_sticky() && is_home() && ! is_paged() ) {
			printf( '<span class="sticky-post">%s</span>', _x( 'Featured', 'post', 'eventi' ) );
		}
		echo '<a href="' . esc_url( get_permalink() ) . '" class="event-details-link">' . get_the_title() . '</a>';
		echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' . get_the_excerpt();

		// Date and times
		$startdate  = strtotime( get_post_meta( $post_id, 'eventi_startdate', true ) );
		$enddate    = strtotime( get_post_meta( $post_id, 'eventi_enddate', true ) );
		$dateformat = get_option( 'date_format' );

		echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' . date_i18n( $dateformat, $startdate );
		if ( $startdate !== $enddate ) {
			echo ' - ' . date_i18n( $dateformat, $enddate );
		}

	?>
</div><!-- .entry-content -->
</article><!-- #event-${ID} -->