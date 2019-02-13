<?php

class Eventi {

	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version     = EVENTI_VERSION;
		$this->plugin_name = 'eventi';

		$this->register_cpt();
		$this->register_shortcodes();

	}

	private function register_cpt() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventi-register-cpt.php';
		new Eventi_Register_Cpt( $this->plugin_name, $this->plugin_version );

	}

	private function register_shortcodes() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eventi-public.php';
		new Eventi_Public();

	}

}
