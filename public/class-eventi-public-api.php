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
		return rest_ensure_response( 'Hello World! This is my first REST API' );

	}
}
