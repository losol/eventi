<?php

add_action( 'init', 'create_event_postype' );

function create_event_postype() {

	$labels = array(
		'name'               => _x( 'Events', 'post type general name' ),
		'singular_name'      => _x( 'Event', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'events' ),
		'add_new_item'       => __( 'Add New Event' ),
		'edit_item'          => __( 'Edit Event' ),
		'new_item'           => __( 'New Event' ),
		'view_item'          => __( 'View Event' ),
		'search_items'       => __( 'Search Events' ),
		'not_found'          => __( 'No events found' ),
		'not_found_in_trash' => __( 'No events found in Trash' ),
		'parent_item_colon'  => '',
	);

	$args = array(
		'label'             => __( 'Events' ),
		'labels'            => $labels,
		'public'            => true,
		'can_export'        => true,
		'show_ui'           => true,
		'_builtin'          => false,
		'capability_type'   => 'post',
		'menu_icon'         => get_bloginfo( 'template_url' ) . '/functions/images/event_16.png',
		'hierarchical'      => false,
		'rewrite'           => array( 'slug' => 'events' ),
		'supports'          => array( 'title', 'thumbnail', 'excerpt', 'editor' ),
		'show_in_nav_menus' => true,
		'taxonomies'        => array( 'tf_eventcategory', 'post_tag' ),
	);

	register_post_type( 'tf_events', $args );

}
