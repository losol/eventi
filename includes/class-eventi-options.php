<?php

class Eventi_Options {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'eventi_register_options_page' ) );
	}

	function eventi_register_options_page() {
		$parent_slug = 'edit.php?post_type=eventi_event';
		$page_title  = 'Eventi configuration';
		$menu_title  = 'Configuration';
		$capability  = 'manage_options';
		$menu_slug   = 'eventiconfig';
		$function    = array( $this, 'eventi_options_page' );

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	function eventi_options_page() {
		echo 'This is the page content';
	}
}
