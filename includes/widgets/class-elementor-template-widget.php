<?php
/*
Widget Name: Troubleshooting Manager Elementor Template
Description: This widget is used to display a Elementor Template in your sidebar.
Settings:
 Title - Widget's text title
 Select template - Select elementor template
*/

/**
 * Troubleshooting Manager Elementor Template widget.
 *
 * @package Crocoblock
 */

if ( ! class_exists( 'Troubleshooting_Manager_Elementor_Template_Widget' ) ) {

	/**
	 * Class Storycle_Cta_Widget.
	 */
	class Troubleshooting_Manager_Elementor_Template_Widget extends WP_Widget {

		/**
		 * CSS class
		 *
		 * @var string
		 */
		public $widget_cssclass;

		/**
		 * Widget description
		 *
		 * @var string
		 */
		public $widget_description;

		/**
		 * Widget ID
		 *
		 * @var string
		 */
		public $widget_id;

		/**
		 * Widget name
		 *
		 * @var string
		 */
		public $widget_name;

		/**
		 * Settings
		 *
		 * @var array
		 */
		public $settings;

		/**
		 * Interface builder instance.
		 *
		 * @var object
		 */
		public $builder = null;

		/**
		 * Temporary arguments holder
		 *
		 * @var array
		 */
		public $args;

		/**
		 * Temporary instance holder
		 *
		 * @var array
		 */
		public $instance;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			$this->widget_name        = esc_html__( 'Elementor Template', 'troubleshooting-manager' );
			$this->widget_description = esc_html__( 'Display your Elementor Template.', 'troubleshooting-manager' );
			$this->widget_id          = 'sroco-school-elementor-template-widget';
			$this->widget_cssclass    = 'elementor-template-widget';
			$this->settings           = array(
				'title' => array(
					'type'  => 'text',
					'value' => '',
					'label' => esc_html__( 'Title', 'troubleshooting-manager' ),
				),
				'template_id' => array(
					'type'             => 'select',
					'size'             => 1,
					'value'            => '',
					'options_callback' => array( $this, 'get_template_list' ),
					'options'          => false,
					'label'            => esc_html__( 'Select template', 'troubleshooting-manager' ),
					'multiple'         => false,
					'placeholder'      => esc_html__( 'Select template', 'troubleshooting-manager' ),
				),
			);

			$widget_ops = array(
				'classname'   => $this->widget_cssclass,
				'description' => $this->widget_description,
			);

			parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );

			add_action( 'save_post', array( $this, 'flush_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_cache' ) );

			if ( $this->is_ajax() ) {
				add_action( 'admin_init', array( $this, 'admin_init' ) );
			} else {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_init' ), 1 );
			}

			add_action( 'widgets.php', array( $this, 'ajax_init' ), 1 );

			add_filter( 'widget_display_callback', array( $this, 'prepare_instance' ), 10, 2 );
		}

		/**
		 * Get default widget instance from settings
		 *
		 * @since  1.0.0
		 * @param  array     $instance The current widget instance's settings.
		 * @param  WP_Widget $widget   The current widget instance.
		 * @return array
		 */
		public function prepare_instance( $instance, $widget ) {

			if ( ! empty( $instance ) ) {
				return $instance;
			}

			$instance = array();

			if ( empty( $widget->settings ) ) {
				return $instance;
			}

			foreach ( $widget->settings as $key => $data ) {

				if ( ! isset( $data['value'] ) ) {
					$instance[ $key ] = '';
				} else {
					$instance[ $key ] = $data['value'];
				}
			}

			return $instance;
		}

		/**
		 * Check if is AJAX-request processing.
		 *
		 * @return boolean
		 */
		public function is_ajax() {
			return ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX );
		}

		/**
		 * Initalize UI elements in admin area widgets page
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		public function admin_init() {

			$current_screen  = get_current_screen();
			$is_allowed_page = ( $current_screen && 'widgets' == $current_screen->id );

			/**
			 * Filter - is current admin page are allowed to apply UI elements for
			 *
			 * @var bool
			 */
			$is_allowed_page = apply_filters( 'cherry_widget_factory_allowed_ui_page', $is_allowed_page );

			if ( ! $this->is_ajax() && ! $is_allowed_page ) {
				return false;
			}

			$this->init_ui();
		}

		/**
		 * Initalize UI elements on AJAX callbacks
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function ajax_init() {
			$this->init_ui();
		}

		/**
		 * Init UI builder.
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		public function init_ui() {

			if ( empty( $this->settings ) ) {
				return false;
			}


			$this->builder = $this->get_interface_builder();

			return true;
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
		 * Get widget cache ID
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_cache_id() {
			return apply_filters( 'cherry_cached_widget_id', $this->widget_id );
		}

		/**
		 * Get cached widget from WordPress object cache
		 *
		 * @since  1.0.0
		 * @param  array $args widget arguments array.
		 * @return bool
		 */
		public function get_cached_widget( $args ) {

			$cache = wp_cache_get( $this->get_cache_id(), 'widget' );

			if ( ! is_array( $cache ) ) {
				$cache = array();
			}

			if ( isset( $cache[ $args['widget_id'] ] ) ) {
				echo $cache[ $args['widget_id'] ];
				return true;
			}

			return false;
		}

		/**
		 * Save widget into WordPress object cache
		 *
		 * @since  1.0.0
		 * @param  array  $args    widget arguments.
		 * @param  [type] $content widget content.
		 * @return string the content that was cached
		 */
		public function cache_widget( $args, $content ) {
			wp_cache_set( $this->get_cache_id(), array(
				$args['widget_id'] => $content,
			), 'widget' );

			return $content;
		}

		/**
		 * Flush the cache
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function flush_cache() {
			wp_cache_delete( $this->get_cache_id(), 'widget' );
		}

		/**
		 * Output the html at the start of a widget
		 *
		 * @since  1.0.0
		 * @param  array $args     widget arguments.
		 * @param  array $instance widget instance.
		 * @return void
		 */
		public function widget_start( $args, $instance ) {

			echo $args['before_widget'];

			$title = apply_filters(
				'widget_title',
				empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base
			);

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
		}

		/**
		 * Output the html at the end of a widget
		 *
		 * @since  1.0.0
		 * @param  array $args widget arguments.
		 * @return void
		 */
		public function widget_end( $args ) {
			echo $args['after_widget'];
		}

		/**
		 * Update function.
		 *
		 * @since  1.0.0
		 * @see    WP_Widget->update
		 * @param  array $new_instance new widget instance, passed from widget form.
		 * @param  array $old_instance old instance, saved in database.
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;

			if ( empty( $this->settings ) ) {
				return $instance;
			}

			foreach ( $this->settings as $key => $setting ) {

				if ( isset( $new_instance[ $key ] ) ) {

					$instance[ $key ] = ! empty( $setting['sanitize_callback'] ) && is_callable( $setting['sanitize_callback'] )
					? call_user_func( $setting['sanitize_callback'],$new_instance[ $key ] )
					: $this->sanitize_instance_item( $new_instance[ $key ] );

				} elseif ( 'checkbox' === $setting['type'] ) {
					$instance[ $key ] = array();
				} elseif ( isset( $old_instance[ $key ] ) && is_array( $old_instance[ $key ] ) ) {
					$instance[ $key ] = array();
				} elseif ( isset( $old_instance[ $key ] ) ) {
					$instance[ $key ] = '';
				}

				// WPML - register strings for translation.
				if ( in_array( $setting['type'], array( 'text', 'textarea' ) ) && 'title' !== $key ) {
					do_action( 'wpml_register_single_string', 'Widgets', "{$this->widget_name} - {$key}", $instance[ $key ] );
				}
			}

			$this->flush_cache();

			/**
			 * Fires after current widget update is proceed, before returning result.
			 *
			 * @since 1.0.0
			 * @param array $instance current widget instance.
			 */
			do_action( 'cherry_widget_after_update', $instance );

			return $instance;
		}

		/**
		 * Sanitize widget instance item
		 *
		 * @since  1.0.0
		 * @param  mixed $input instance item to sanitize.
		 * @return mixed
		 */
		public function sanitize_instance_item( $input ) {
			if ( is_array( $input ) ) {
				return array_filter( $input );
			} else {
				return sanitize_text_field( $input );
			}
		}

		/**
		 * Show widget form
		 *
		 * @since  1.0.0
		 * @see    WP_Widget->form
		 * @param  array $instance current widget instance.
		 * @return void
		 */
		public function form( $instance ) {

			if ( empty( $this->settings ) ) {
				return;
			}

			$this->builder->reset_structure();

			foreach ( $this->settings as $key => $setting ) {

				if ( isset( $setting['options_callback'] ) ) {

					$callback           = $this->get_callback_data( $setting['options_callback'] );
					$setting['options'] = call_user_func_array( $callback['callback'], $callback['args'] );

					unset( $setting['options_callback'] );

				}

				$value = isset( $instance[ $key ] ) ? $instance[ $key ] : self::get_arg( $setting, 'value', '' );

				$element          = self::get_arg( $setting, 'element', 'control' );
				$setting['id']    = $this->get_field_id( $key );
				$setting['name']  = $this->get_field_name( $key );
				$setting['type']  = self::get_arg( $setting, 'type', '' );
				$setting['value'] = $value;

				if ( 'select' === $setting['type'] && ! isset( $setting['placeholder'] ) ) {
					$setting['placeholder'] = '';
				}

				$register_callback = 'register_' . $element;

				if ( method_exists( $this->builder, $register_callback ) ) {
					call_user_func( array( $this->builder, $register_callback ), array( $key => $setting ) );
				}

			}

			$this->builder->render();
		}

		/**
		 * Safely get attribute from field settings array.
		 *
		 * @since  1.0.0
		 * @param  array            $field   arguments array.
		 * @param  string|int|float $arg     argument key.
		 * @param  mixed            $default default argument value.
		 * @return mixed
		 */
		public static function get_arg( $field, $arg, $default = '' ) {

			if ( is_array( $field ) && isset( $field[ $arg ] ) ) {
				return $field[ $arg ];
			}

			return $default;
		}

		/**
		 * Parse callback data.
		 *
		 * @since  1.0.0
		 * @param  array $options_callback Callback data.
		 * @return array
		 */
		public function get_callback_data( $options_callback ) {

			if ( 2 === count( $options_callback ) ) {

				$callback = array(
					'callback' => $options_callback,
					'args'     => array(),
				);

				return $callback;
			}

			$callback = array(
				'callback' => array_slice( $options_callback, 0, 2 ),
				'args'     => $options_callback[2],
			);

			return $callback;
		}

		/**
		 * Save current widget data to property object properties
		 *
		 * @since  1.0.0
		 * @param  array $args     widget arguments.
		 * @param  array $instance current widget instance.
		 */
		public function setup_widget_data( $args, $instance ) {
			$this->args     = $args;
			$this->instance = $this->prepare_instance( $instance, $this );
		}

		/**
		 * Clear current widget data.
		 *
		 * @since  1.0.0
		 */
		public function reset_widget_data() {

			$this->args     = null;
			$this->instance = null;

			/**
			 * Fires on widgget data reseting
			 */
			do_action( 'cherry_widget_reset_data' );
		}

		/**
		 * Add widget_id-related CSS selector
		 *
		 * @since  1.2.0
		 * @param  string $selector Selector inside widget.
		 * @param  array  $args     widget arguments (optional, pass it only setup_widget_data not called before).
		 * @return string|bool
		 */
		public function add_selector( $selector = null, $args = array() ) {

			if ( null == $this->args && empty( $args ) ) {
				return false;
			}

			$args = null !== $this->args ? $this->args : $args;

			return sprintf( '#%1$s %2$s', $args['widget_id'], $selector );
		}

		/**
		 * Retrieve a string translation via WPML.
		 *
		 * @since  1.0.1
		 * @param  [type] $id Widget setting ID.
		 */
		public function use_wpml_translate( $id ) {
			return ! empty( $this->instance[ $id ] ) ? apply_filters( 'wpml_translate_single_string', $this->instance[ $id ], 'Widgets', "{$this->widget_name} - {$id}" ) : '';
		}

		/**
		 * Get elementor template list.
		 *
		 * @return array
		 */
		public function get_template_list() {
			$result_list = array(
				'' => esc_html__( '-- Select template --', 'Croco-school' ),
			);

			$templates = Elementor\Plugin::$instance->templates_manager->get_source( 'local' )->get_items();

			if ( $templates ) {
				foreach ( $templates as $template ) {
					$result_list[ $template['template_id'] ] = sprintf( '%1$s (%2$s)', $template['title'], $template['type'] );
				}
			}

			return $result_list;
		}

		/**
		 * Widget function.
		 *
		 * @see WP_Widget
		 *
		 * @since  1.0.0
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {

			$this->setup_widget_data( $args, $instance );
			$this->widget_start( $args, $instance );

			if ( ! $instance['template_id'] ) {
				return;
			}

			$content = Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $instance['template_id'] );

			echo $content;

			$this->widget_end( $args );
			$this->reset_widget_data();
		}
	}

	add_action( 'widgets_init', 'troubleshooting_manager_register_elementor_template_widget' );

	/**
	 * Register elementor template widget.
	 */
	function troubleshooting_manager_register_elementor_template_widget() {
		register_widget( 'Troubleshooting_Manager_Elementor_Template_Widget' );
	}
}
