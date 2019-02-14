<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php _e( 'No events yet!', 'eventi' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php
		if ( current_user_can( 'publish_posts' ) ) :

			printf(
				'<p>' . wp_kses(
					/* translators: 1: link to WP admin new post page. */
					__( 'Ready to publish your first event? <a href="%1$s">Get started here</a>.', 'eventi' ),
					array(
						'a' => array(
							'href' => array(),
						),
					)
				) . '</p>',
				esc_url( admin_url( 'post-new.php?post_type=eventi_event' ) )
			);
		else :
			?>

			<p><?php _e( 'It seems we can&rsquo;t find any events.', 'eventi' ); ?></p>
			<?php

		endif;
		?>
	</div><!-- .page-content -->
</section><!-- .no-results -->