<?php
/**
 * Troubleshooting Manager post type template
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Troubleshooting_Manager_Article_Data' ) ) {

	/**
	 * Define Troubleshooting_Manager_Article_Data class
	 */
	class Troubleshooting_Manager_Article_Data {

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
		public function __construct( $args = [] ) {
			add_filter( 'cx_breadcrumbs/trail_taxonomies', [ $this, 'modify_breadcrumbs_trail_taxonomies'] );
		}

		/**
		 * [modify_breadcrumbs_trail_taxonomies description]
		 * @param  [type] $trail_taxonomies [description]
		 * @return [type]                   [description]
		 */
		public function modify_breadcrumbs_trail_taxonomies ( $trail_taxonomies ) {

			$trail_taxonomies['article'] = 'article-category';

			return $trail_taxonomies;
		}


		/**
		 * [get_single_article description]
		 * @return [type] [description]
		 */
		public function get_single_article() {

			$post_id = get_the_ID();

			if ( ! has_term( '', troubleshooting_manager()->post_type->course_term_slug() ) ) {

				$this->get_single_guide_article();
			} else {
				$this->get_single_course_article();
			}
		}

        public function get_top_level_category() {
            $post_id  = get_the_ID();
            $taxonomy = troubleshooting_manager()->post_type->category_term_slug();

            $terms = wp_get_post_terms( $post_id, $taxonomy, array( 'orderby' => 'parent' ) );

            if ( ! $terms || is_wp_error( $terms ) ) {
                return false;
            }

            $term = array_pop( $terms );

            $parent_id = $term->parent;

            while ( $parent_id ) {
                $_term     = get_term_by( 'id', $parent_id, $taxonomy );
                $parent_id = $_term->parent;

                if ( $parent_id ) {
                    $term = $_term;
                }
            }

            return $term;
        }

		/**
		 * [get_single_guide_article description]
		 * @return [type] [description]
		 */
		public function get_single_guide_article() {

			$is_active_sidebar = is_active_sidebar( 'troubleshooting-manager-article-sidebar' );
			$is_active_sidebar = true;
			$is_sidebar_class = $is_active_sidebar ? 'has-sidebar' : 'no-sidebar';

            ?><div class="troubleshooting-manager__single-article container guide-article <?php echo $is_sidebar_class; ?>"><?php
					do_action( 'cx_breadcrumbs/render' );

				?><div class="troubleshooting-manager__single-article-inner"><?php

					while ( have_posts() ) : the_post();

					?><article id="primary" class="troubleshooting-manager__single-article-container">

                        <div class="troubleshooting-manager__single-article-breadcrumbs">
                            <a class="ts-manager-front" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Troubleshooting', 'troubleshooting-manager' ); ?></a>
                            <span>/</span>
                            <div class="ts-manager-tax"> <?php
                                $btn_format = '<a href="%1$s">%2$s</a>';

                                $top_level_term = $this->get_top_level_category();
                                $post_id  = get_the_ID();
                                $taxonomy = troubleshooting_manager()->post_type->category_term_slug();
                                $terms = wp_get_post_terms( $post_id, $taxonomy, array( 'orderby' => 'parent' ) );

                                if ( ! $terms || is_wp_error( $terms ) ) {
                                    return false;
                                }
                                $term = array_pop( $terms );
                                $btn_text = $term->name;

                                printf(
                                    $btn_format,
                                    get_term_link( $top_level_term->term_id, $top_level_term->taxonomy ),
                                    $btn_text
                                );

                                ?></div><span>/</span>
                            <div class="ts-manager-title"><?php echo the_title(); ?></div>
                        </div>

						<div class="troubleshooting-manager__single-article-container-inner"><?php

							$post_id = get_the_ID();

							$format = $this->get_post_format( $post_id );?>

							<h1 class="troubleshooting-manager__single-article-title"><?php echo the_title(); ?></h1>
                            <div class="troubleshooting-manager__single-article-meta"><?php echo 'Modified on: ' ?>
                                <span><?php echo get_the_modified_date('D, j M, Y \a\t g:i A') ?></span>
                            </div>
							<?php $this->get_article_media(); ?>
							<div class="troubleshooting-manager__single-article-content"><?php

								add_filter( 'embed_oembed_html', array( $this, 'add_oembed_wrapper' ) );

								ob_start();
								the_content( '' );
								$content = ob_get_contents();
								ob_end_clean();

								echo $content;

								remove_filter( 'embed_oembed_html', array( $this, 'add_oembed_wrapper' ) );
							?></div>
						</div>
					</article><?php
					endwhile;
					if ( $is_active_sidebar ) : ?>
                    <div class="troubleshooting-manager__single-article-sidebar-wrap">
						<aside id="secondary" class="troubleshooting-manager__single-article-sidebar">
							<div class="troubleshooting-manager__single-article-sidebar-inner">
								<?php dynamic_sidebar( 'troubleshooting-manager-article-sidebar' ); ?>
							</div>
						</aside><!-- #secondary -->
                    </div>
					<?php endif;?>
				</div>
			</div>
            <?php
		}

		/**
		 * [get_article_media description]
		 * @return [type] [description]
		 */
		public function get_article_media() {
			$post_id = get_the_ID();

			$article_type = get_post_meta( $post_id, 'article_format', true );

			if ( 'standard' === $article_type ) {
				return false;
			}

			?><div class="troubleshooting-manager__single-article-media">
				<div class="troubleshooting-manager__single-article-media-inner"><?php

			switch ( $article_type ) {
				case 'image':

					$article_image = get_post_meta( $post_id, 'article_image', true );
					$article_image_size = get_post_meta( $post_id, 'article_image_size', true );

					echo wp_get_attachment_image( $article_image, $article_image_size, false, [ 'class' => 'troubleshooting-manager-article-image' ] );

					# code...
					break;

				case 'video':
					$video_url = get_post_meta( $post_id, 'video_url', true );
					$video_poster = get_post_meta( $post_id, 'video_poster', true );
					$video_aspect_ratio = get_post_meta( $post_id, 'video_aspect_ratio', true );
					$video_aspect_ratio = isset( $video_aspect_ratio ) ? $video_aspect_ratio : '169';

					if ( empty( $video_url ) ) {
						return false;
					}

					?><div class="troubleshooting-manager__single-media-frame aspect-ratio-<?php echo $video_aspect_ratio; ?>"><?php

						$video_properties = $this->get_video_properties( $video_url );

						echo $this->get_embed_html( $video_url, [
							'autoplay'       => '0',
							'controls'       => '1',
							'rel'            => '0',
							'loop'           => '0',
							'wmode'          => 'opaque',
							'playlist'       => $video_properties['video_id'],
							'modestbranding' => '0',
						] );

						if ( ! empty( $video_poster ) ) {
							$poster_data = wp_get_attachment_image_src( $video_poster, 'full' );
							$poster_src = esc_url( $poster_data[0] );

							?><div class="video-embed-image-overlay" style="background-image: url( <?php echo $poster_src; ?> );">
								<div class="video-play-icon">
									<i class="fa fa-play"></i>
								</div>
							</div><?php
						}

					?></div><?php

					break;
			}

				?></div>
			</div><?php
		}

		/**
		 * [$post_id description]
		 * @var [type]
		 */
		public function get_post_format( $post_id = null ) {

			$format = get_post_format( $post_id );

			return ( ! empty( $format ) ) ? $format : 'standard';
		}

		/**
		 * Prints current page title.
		 *
		 * @return void
		 */
		public function page_title( $format = '%s' ) {

			$object = get_queried_object();

			if ( isset( $object->post_title ) ) {
				printf( $format, $object->post_title );
			} elseif ( isset( $object->name ) ) {
				printf( $format, $object->name );
			}

		}

		/**
		 * [get_embed_html description]
		 * @param  [type] $video_url        [description]
		 * @param  array  $embed_url_params [description]
		 * @param  array  $options          [description]
		 * @param  array  $frame_attributes [description]
		 * @return [type]                   [description]
		 */
		public function get_embed_html( $video_url, array $embed_url_params = [], array $options = [], array $frame_attributes = [] ) {
			$default_frame_attributes = [
				'class'           => 'troubleshooting-manager-video-iframe',
				'allowfullscreen',
			];

			$video_embed_url = $this->get_embed_url( $video_url, $embed_url_params, $options );

			if ( ! $video_embed_url ) {
				return null;
			}

			$default_frame_attributes['src'] = $video_embed_url;

			$frame_attributes = array_merge( $default_frame_attributes, $frame_attributes );

			$attributes_for_print = [];

			foreach ( $frame_attributes as $attribute_key => $attribute_value ) {
				$attribute_value = esc_attr( $attribute_value );

				if ( is_numeric( $attribute_key ) ) {
					$attributes_for_print[] = $attribute_value;
				} else {
					$attributes_for_print[] = sprintf( '%1$s="%2$s"', $attribute_key, $attribute_value );
				}
			}

			$attributes_for_print = implode( ' ', $attributes_for_print );

			$iframe_html = "<iframe $attributes_for_print></iframe>";

			/** This filter is documented in wp-includes/class-oembed.php */
			return apply_filters( 'oembed_result', $iframe_html, $video_url, $frame_attributes );
		}

		/**
		 * [get_embed_url description]
		 * @param  [type] $video_url        [description]
		 * @param  array  $embed_url_params [description]
		 * @param  array  $options          [description]
		 * @return [type]                   [description]
		 */
		public function get_embed_url( $video_url, array $embed_url_params = [], array $options = [] ) {
			$video_properties = $this->get_video_properties( $video_url );

			if ( ! $video_properties ) {
				return null;
			}

			$embed_patterns = [
				'youtube' => 'https://www.youtube{NO_COOKIE}.com/embed/{VIDEO_ID}?feature=oembed',
				'vimeo'   => 'https://player.vimeo.com/video/{VIDEO_ID}#t={TIME}',
			];

			$embed_pattern = $embed_patterns[ $video_properties['provider'] ];

			$replacements = [
				'{VIDEO_ID}' => $video_properties['video_id'],
			];

			if ( 'youtube' === $video_properties['provider'] ) {
				$replacements['{NO_COOKIE}'] = ! empty( $options['privacy'] ) ? '-nocookie' : '';
			} elseif ( 'vimeo' === $video_properties['provider'] ) {
				$time_text = '';

				if ( ! empty( $options['start'] ) ) {
					$time_text = date( 'H\hi\ms\s', $options['start'] );
				}

				$replacements['{TIME}'] = $time_text;
			}

			$embed_pattern = str_replace( array_keys( $replacements ), $replacements, $embed_pattern );

			return add_query_arg( $embed_url_params, $embed_pattern );
		}

		/**
		 * [get_video_properties description]
		 * @param  [type] $video_url [description]
		 * @return [type]            [description]
		 */
		public function get_video_properties( $video_url ) {

			$provider_match_masks = [
				'youtube' => '/^.*(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:(?:watch)?\?(?:.*&)?vi?=|(?:embed|v|vi|user)\/))([^\?&\"\'>]+)/',
				'vimeo'   => '/^.*vimeo\.com\/(?:[a-z]*\/)*([‌​0-9]{6,11})[?]?.*/',
			];

			foreach ( $provider_match_masks as $provider => $match_mask ) {
				preg_match( $match_mask, $video_url, $matches );

				if ( $matches ) {
					return [
						'provider' => $provider,
						'video_id' => $matches[1],
					];
				}
			}

			return null;
		}

		public function add_oembed_wrapper( $html ) {
			return '<div class="troubleshooting-manager-embed-responsive troubleshooting-manager-embed-responsive-16by9">' . $html . '</div>';
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
