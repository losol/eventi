<?php

class Eventi_Options {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'create_eventi_settings_page' ) );
	}

	function create_eventi_settings_page() {
		$parent_slug = 'edit.php?post_type=eventi_event';
		$page_title  = 'Eventi configuration';
		$menu_title  = 'Configuration';
		$capability  = 'manage_options';
		$menu_slug   = 'eventiconfig';
		$function    = array( $this, 'eventi_settings_page_content' );

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	function eventi_settings_page_content() {
		echo 'This is the page content';
	}
}
