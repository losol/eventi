<?php
/**
 * The template for displaying all single events
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Eventi
 * @since 1.0.0
 */

get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<h1><?php the_title(); ?></h1>
					<?php
					the_content();

					// Date and times
					$startdate  = strtotime( get_post_meta( get_the_id(), 'eventi_startdate', true ) );
					$enddate    = strtotime( get_post_meta( get_the_id(), 'eventi_enddate', true ) );
					$dateformat = get_option( 'date_format' );

					echo date_i18n( $dateformat, $startdate );
					if ( $startdate !== $enddate ) {
						echo ' - ' . date_i18n( $dateformat, $enddate );
					}
						?>
				</div>
			</article>

			<?php
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
