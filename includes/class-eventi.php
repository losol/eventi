<?php

class Eventi {

	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version     = EVENTI_VERSION;
		$this->plugin_name = 'eventi';

		$this->register_cpt();

	}

	private function register_cpt() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eventi-register-cpt.php';
		new Eventi_Register_Cpt( $this->plugin_name, $this->plugin_version );

	}

}
