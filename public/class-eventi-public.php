<?php

class Eventi_Public {

	public function __construct() {
		add_shortcode( 'eventi', array( $this, 'events_loop_shortcode' ) ); // You can now call onto this shortcode with [tf-events-full limit='20']
		add_shortcode( 'tf-events-full', array( $this, 'eventi_events_full' ) ); // You can now call onto this shortcode with [tf-events-full limit='20']

		// TODO Check if public API is enabled in options.
		$this->enable_api();

		// Load templates.
		add_filter( 'single_template', array( $this, 'load_event_template' ) );
		add_filter( 'archive_template', array( $this, 'load_event_archive_template' ) );

		// Add JSON-LD metadata
		add_action( 'wp_head', array( $this, 'insert_json_ld' ) );

	}

	function enable_api() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eventi-public-api.php';
		new Eventi_Public_Api();

	}
	function events_loop_shortcode() {
		$args = array(
			'post_type'   => 'eventi_event',
			'post_status' => 'publish',
		);

		$my_query = null;
		$my_query = new WP_query( $args );
		if ( $my_query->have_posts() ) :
			while ( $my_query->have_posts() ) :
				$my_query->the_post();
				$custom = get_post_custom( get_the_ID() );
				echo '<p>' . get_the_title() . '</p>';
				echo '<p>' . get_the_content() . '</p>';
			endwhile;
			wp_reset_postdata();
		else :
			_e( 'Sorry, no posts matched your criteria.' );
		endif;
	}

	function load_event_template( $template ) {
		global $post;

		if ( $post->post_type == 'eventi_event' && $template !== locate_template( array( 'single-eventi-event.php' ) ) ) {
			return plugin_dir_path( __FILE__ ) . 'templates/single-eventi-event.php';
		}

		return $template;
	}

	function load_event_archive_template( $template ) {
		global $post;

		if ( is_archive() && $template !== locate_template( array( 'archive-eventi-event.php' ) ) ) {
			return plugin_dir_path( __FILE__ ) . 'templates/archive-eventi-event.php';
		}

		return $template;
	}

	function insert_json_ld() {
		if ( is_singular( 'eventi_event' ) ) {
			the_post();
			$context          = 'https://schema.org';
			$type             = 'Event';
			$name             = get_the_title();
			$start_date       = '2018-10-30';
			$description      = get_the_excerpt();
			$place            = 'Kult sted';
			$location_city    = 'Kul by';
			$location_country = 'Kult land';

			$metadata_array = array(
				'@context'    => $context,
				'@type'       => $type,
				'name'        => $name,
				'description' => $description,
				'startDate'   => $start_date,
				'location'    => array(
					'@type' => 'Place',
					'name'  => $place,
					'address' => array(
						'@type' => 'PostalAddress',
						'addressLocality' => $location_city,
						'addressCountry'  => $location_country,
					),
				),
			);

			$metadata_json  = json_encode( $metadata_array, JSON_UNESCAPED_SLASHES );

			$head_script = "
				<script type=\"application/ld+json\">
					$metadata_json
				</script>";
			echo $head_script;
		}
	}
}

