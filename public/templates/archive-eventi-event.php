<?php
/**
 * The template for displaying archive pages
 *
 * @package Eventi
 * @since 1.0.0
 */
get_header();
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php
		if ( have_posts() ) :
		?>

			<header class="page-header">
				<?php
					the_archive_title( '<h1 class="page-title">', '</h1>' );
				?>
			</header><!-- .page-header -->

			<?php
			// Start the Loop.
			$args = [
				'post_type'      => 'eventi',
				'posts_per_page' => 10,
			];
			$loop = new WP_Query( $args );
			while ( $loop->have_posts() ) {
				$loop->the_post();
				require plugin_dir_path( dirname( __FILE__ ) ) . 'templates/template-parts/event-excerpt.php';
				?>
				<?php
			}

		// If no content, include the "No posts found" template.
		else :
			require plugin_dir_path( dirname( __FILE__ ) ) . 'templates/template-parts/event-none.php';
		endif;
		?>
		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();
