<?php

class Eventi_Public_Api {

	function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_events_route' ) );
	}

	function register_events_route() {
		register_rest_route(
			'events',
			'upcoming',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'upcoming_events_json' ),
			)
		);
	}
	function upcoming_events_json() {

		// WP_Query arguments
		$args = array(
			'post_type' => array( 'eventi_event' ),
			// 'post_status' => array( 'publish' ),
			'nopaging'  => true,
			// 'order'       => 'ASC',
			// 'orderby'     => 'menu_order',
		);

		// The Query
		$post_list = new WP_Query( $args );

		$post_data = array();
		$i         = 0;
		// The Loop
		if ( $post_list->have_posts() ) {
			while ( $post_list->have_posts() ) {
				$post_list->the_post();
				$post_id     = get_the_id();
				$post_data[] = array(
					'id'        => $post_id,
					'title'     => get_the_title(),
					'startdate' => date_i18n( 'c', get_post_meta( $post_id, 'eventi_startdate', true ) ),
					'starttime' => get_post_meta( $post_id, 'eventi_starttime', true ),
					'enddate'   => date_i18n( 'c', get_post_meta( $post_id, 'eventi_enddate', true ) ),
					'endtime'   => get_post_meta( $post_id, 'eventi_endtime', true ),
				);
			}
		} else {
			// no posts found
		}

		wp_reset_postdata();
		return rest_ensure_response( $post_data );

	}
}