<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Troubleshooting_Manager_Term_Meta' ) ) {

	/**
	 * Define Troubleshooting_Manager_Term_Meta class
	 */
	class Troubleshooting_Manager_Term_Meta {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 *
		 */
		public function __construct() {

			add_action( 'init', [ $this, 'register_meta_boxes' ] );
		}

		/**
		 * [register_meta_boxes description]
		 * @return [type] [description]
		 */
		public function register_meta_boxes() {

			new Cherry_X_Term_Meta( [
				'tax'        => 'сourse',
				'builder_cb' => array( $this, 'get_interface_builder' ),
				'fields'     => [
					'container' => array(
						'type'        => 'section',
						'title'       => __( 'Course Settings', 'troubleshooting-manager' ),
					),
					'course_settings' => array(
						'type'   => 'settings',
						'parent' => 'container',
					),
					'course_thumbnail' => array(
						'type'               => 'media',
						'parent'             => 'course_settings',
						'title'              => esc_html__( 'Thumbnail', 'troubleshooting-manager' ),
						'multi_upload'       => false,
						'upload_button_text' => esc_html__( 'Set Thumbnail', 'troubleshooting-manager' ),
					),
				],
			] );

			new Cherry_X_Term_Meta( [
				'tax'        => 'article-category',
				'builder_cb' => array( $this, 'get_interface_builder' ),
				'fields'     => [
					'container' => array(
						'type'        => 'section',
						'title'       => __( 'Category Settings', 'troubleshooting-manager' ),
					),
					'category_settings' => array(
						'type'   => 'settings',
						'parent' => 'container',
					),
					'category_thumbnail' => array(
						'type'               => 'media',
						'parent'             => 'category_settings',
						'title'              => esc_html__( 'Thumbnail', 'troubleshooting-manager' ),
						'multi_upload'       => false,
						'upload_button_text' => esc_html__( 'Set Thumbnail', 'troubleshooting-manager' ),
					),
				],
			] );
		}

		/**
		 * [kava_extra_get_interface_builder description]
		 *
		 * @return [type] [description]
		 */
		public function get_interface_builder() {

			$builder_data = troubleshooting_manager()->framework->get_included_module_data( 'cherry-x-interface-builder.php' );

			return new CX_Interface_Builder(
				array(
					'path' => $builder_data['path'],
					'url'  => $builder_data['url'],
				)
			);
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
