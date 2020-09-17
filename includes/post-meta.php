<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Troubleshooting_Manager_Post_Meta' ) ) {

	/**
	 * Define Troubleshooting_Manager_Post_Meta class
	 */
	class Troubleshooting_Manager_Post_Meta {

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

			new Cherry_X_Post_Meta( [
				'id'            => 'article-settings',
				'title'         => esc_html__( 'Article Settings', 'troubleshooting-manager' ),
				'page'          => [ troubleshooting_manager()->post_type->article_post_slug() ],
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'builder_cb'    => [ $this, 'get_interface_builder' ],
				'fields'        => [

					'main_settings' => [
						'type'   => 'settings',
					],

					'article_format'  => [
						'type'        => 'radio',
						'parent'      => 'main_settings',
						'title'       => esc_html__( 'Article Format', 'troubleshooting-manager' ),
						'description' => esc_html__( 'Select Article Format', 'troubleshooting-manager' ),
						'value'       => 'standard',
						'options'     => [
							'standard' => [
								'label' => esc_html__( 'Standard', 'troubleshooting-manager' ),
							],
							'image' => [
								'label' => esc_html__( 'Image', 'troubleshooting-manager' ),
							],
							'video' => [
								'label' => esc_html__( 'Video', 'troubleshooting-manager' ),
							],
						],
					],

					'vertical_tabs' => [
						'type'   => 'component-tab-vertical',
						'parent' => 'main_settings',
					],

					'image_tab' => [
						'type'   => 'settings',
						'parent'      => 'vertical_tabs',
						'title'       => esc_html__( 'Image Settings', 'troubleshooting-manager' ),
					],

					'video_tab' => [
						'type'   => 'settings',
						'parent'      => 'vertical_tabs',
						'title'       => esc_html__( 'Video Settings', 'troubleshooting-manager' ),
					],

					'article_image' => [
						'type'               => 'media',
						'parent'             => 'image_tab',
						'title'              => esc_html__( 'Article Image', 'troubleshooting-manager' ),
						'multi_upload'       => false,
						'upload_button_text' => esc_html__( 'Set Image', 'troubleshooting-manager' ),
					],

					'article_image_size' => [
						'type'        => 'select',
						'parent'             => 'image_tab',
						'value'       => 'full',
						'options'     => Troubleshooting_Manager_Utils::get_image_sizes(),
						'title'       => esc_html__( 'Image Size', 'troubleshooting-manager' ),
					],

					'video_url' => array(
						'type'         => 'text',
						'parent'       => 'video_tab',
						'value'        => '',
						'title'        => esc_html__( 'Video Url', 'jet-elements' ),
					),

					'video_aspect_ratio'  => [
						'type'        => 'radio',
						'parent'      => 'video_tab',
						'title'       => esc_html__( 'Aspect Ratio', 'troubleshooting-manager' ),
						'value'       => '169',
						'options'     => [
							'169' => [
								'label' => esc_html__( '16:9', 'troubleshooting-manager' ),
							],
							'219' => [
								'label' => esc_html__( '21:9', 'troubleshooting-manager' ),
							],
							'43' => [
								'label' => esc_html__( '4:3', 'troubleshooting-manager' ),
							],
							'32' => [
								'label' => esc_html__( '3:2', 'troubleshooting-manager' ),
							],
						],
					],

					'video_poster' => [
						'type'               => 'media',
						'parent'             => 'video_tab',
						'title'              => esc_html__( 'Video Poster', 'troubleshooting-manager' ),
						'multi_upload'       => false,
						'upload_button_text' => esc_html__( 'Set Image', 'troubleshooting-manager' ),
					],

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
