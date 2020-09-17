<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Troubleshooting_Manager_Widgets' ) ) {

	/**
	 * Define Troubleshooting_Manager_Widgets class
	 */
	class Troubleshooting_Manager_Widgets {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public function __construct() {
			add_action( 'widgets_init', [ $this, 'register_sidebar' ] );
		}

		/**
		 * [register_sidebar description]
		 * @return [type] [description]
		 */
		public function register_sidebar() {

			register_sidebar( [
				'id'             => 'troubleshooting-manager-article-sidebar',
				'name'           => esc_html__( 'Troubleshooting Manager Article Sidebar', 'troubleshooting-manager' ),
				'description'    => '',
				'before_widget'  => '<aside id="%1$s" class="widget %2$s">',
				'after_widget'   => '</aside>',
				'before_title'   => '<h3 class="widget-title">',
				'after_title'    => '</h3>',
				'before_wrapper' => '<div id="%1$s" %2$s role="complementary">',
				'after_wrapper'  => '</div>',
			] );
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
