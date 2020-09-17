<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Troubleshooting_Manager_Assets' ) ) {

	/**
	 * Define Troubleshooting_Manager_Assets class
	 */
	class Troubleshooting_Manager_Assets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 10 );

		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function register_scripts() {

			wp_register_script(
				'troubleshooting-manager-sticky-sidebar',
				troubleshooting_manager()->plugin_url( 'assets/js/sticky-sidebar.min.js' ),
				[],
				'1.1.3',
				true
			);
		}

		/**
		 * [enqueue_scripts description]
		 * @return [type] [description]
		 */
		public function enqueue_scripts() {

			//$screen = get_current_screen();

			wp_enqueue_style(
				'troubleshooting-manager-frontend',
				troubleshooting_manager()->plugin_url( 'assets/css/troubleshooting-manager-frontend.css' ),
				false,
				troubleshooting_manager()->get_version()
			);

			wp_enqueue_script(
				'troubleshooting-manager-frontend',
				troubleshooting_manager()->plugin_url( 'assets/js/troubleshooting-manager-frontend.js' ),
				[ 'jquery', 'troubleshooting-manager-sticky-sidebar' ],
				troubleshooting_manager()->get_version(),
				true
			);

			$this->localize_data['ajax_url'] = esc_url( admin_url( 'admin-ajax.php' ) );

			wp_localize_script(
				'troubleshooting-manager-frontend',
				'TroubleshootingManager',
				$this->localize_data
			);

		}

		/**
		 * Enqueue admin styles
		 *
		 * @return void
		 */
		public function enqueue_admin_assets() {}

		/**
		 * [suffix description]
		 * @return [type] [description]
		 */
		public function suffix() {
			return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
