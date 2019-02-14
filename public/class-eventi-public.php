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




	function eventi_events_full( $atts ) {
		// - define arguments -
		extract(
			shortcode_atts(
				array(
					'limit' => '10', // # of events to show
				),
				$atts
			)
		);

		// ===== OUTPUT FUNCTION =====
		ob_start();

		$today6am = strtotime( 'today 6:00' ) + ( get_option( 'gmt_offset' ) * 3600 );
		// - query -
		global $wpdb;
		$querystr = "
		SELECT *
		FROM $wpdb->posts wposts, $wpdb->postmeta metastart, $wpdb->postmeta metaend
		WHERE (wposts.ID = metastart.post_id AND wposts.ID = metaend.post_id)
		AND (metaend.meta_key = 'eventi_enddate' AND metaend.meta_value > $today6am )
		AND metastart.meta_key = 'eventi_enddate'
		AND wposts.post_type = 'eventi_event'
		AND wposts.post_status = 'publish'
		ORDER BY metastart.meta_value ASC LIMIT $limit
	 ";
		$events   = $wpdb->get_results( $querystr, OBJECT );
		// - declare fresh day -
		$daycheck = null;
		// - loop -
		if ( $events ) :
			global $post;
			foreach ( $events as $post ) :
				setup_postdata( $post );
				// - custom variables -
				$custom = get_post_custom( get_the_ID() );
				$sd     = $custom['eventi_startdate'][0];
				$ed     = $custom['eventi_enddate'][0];
				// - determine if it's a new day -
				$longdate = date( 'l, F j, Y', $sd );
				if ( $daycheck == null ) {
					echo '<h2 class="full-events">' . $longdate . '</h2>'; }
				if ( $daycheck != $longdate && $daycheck != null ) {
					echo '<h2 class="full-events">' . $longdate . '</h2>'; }
				// - local time format -
				$time_format = get_option( 'time_format' );
				$stime       = date( $time_format, $sd );
				$etime       = date( $time_format, $ed );
				// - output - ?>
	<div class="full-events">
		<div class="text">
			<div class="title">
				<div class="time"><?php echo $stime . ' - ' . $etime; ?></div>
				<div class="eventtext"><?php the_title(); ?></div>
			</div>
		</div>
		<div class="desc">
				<?php
				if ( strlen( $post->post_content ) > 150 ) {
					echo substr( $post->post_content, 0, 150 ) . '...';
				} else {
						echo $post->post_content; }
				?>
			</div>
	</div>
				<?php
				// - fill daycheck with the current day -
				$daycheck = $longdate;
		endforeach;
		else :
		endif;
		// ===== RETURN: FULL EVENTS SECTION =====
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}
