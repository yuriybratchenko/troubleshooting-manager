<?php

namespace Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Troubleshooting_Manager_Articles extends Troubleshooting_Manager_Base {

	public function get_name() {
		return 'troubleshooting-manager-articles';
	}

	public function get_title() {
		return esc_html__( 'Troubleshooting Articles', 'troubleshooting-manager' );
	}

	public function get_icon() {
		return 'eicon-wordpress';
	}

	public function get_categories() {
		return array( 'troubleshooting-manager' );
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'Settings', 'troubleshooting-manager' ),
			)
		);

		$this->add_control(
			'is_archive_template',
			array(
				'label'        => esc_html__( 'Use as Archive Template', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-blog' ),
				'label_off'    => esc_html__( 'No', 'jet-blog' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'show_title',
			array(
				'label'        => esc_html__( 'Show Title', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-blog' ),
				'label_off'    => esc_html__( 'No', 'jet-blog' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_child_terms',
			array(
				'label'        => esc_html__( 'Show Child Terms', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-blog' ),
				'label_off'    => esc_html__( 'No', 'jet-blog' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'show_parent_articles',
			array(
				'label'        => esc_html__( 'Show Parent Articles', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-blog' ),
				'label_off'    => esc_html__( 'No', 'jet-blog' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$avaliable_category = \Troubleshooting_Manager_Utils::avaliable_article_category();

		if ( ! $avaliable_category ) {

			$this->add_control(
				'no_category',
				array(
					'label' => false,
					'type'  => Controls_Manager::RAW_HTML,
					'raw'   => '<p>Article categories not founded</p>',
				)
			);

		} else {
			$default_category = array_keys( $avaliable_category )[0];

			$avaliable_category['all'] = esc_html__( 'All Articles', 'troubleshooting-manager' );

			$this->add_control(
				'term_id',
				[
					'label'   => esc_html__( 'Article Category', 'troubleshooting-manager' ),
					'type'    => Controls_Manager::SELECT,
					'options' => $avaliable_category,
					'default' => $default_category,
					'condition' => [
						//'is_archive_template' => 'no',
					],
				]
			);
		}

		$this->add_control(
			'use_article_limit',
			array(
				'label'        => esc_html__( 'Use article limit', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-blog' ),
				'label_off'    => esc_html__( 'No', 'jet-blog' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'article_limit',
			array(
				'label'   => esc_html__( 'Article List Limit', 'troubleshooting-manager' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 0,
				'max'     => 20,
				'step'    => 1,
				'condition' => [
					'use_article_limit' => 'yes',
				],
			)
		);

		$this->end_controls_section();
	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	protected function render() {

		$this->__context = 'render';

		$settings = $this->get_settings();

		$is_archive_template = filter_var( $settings['is_archive_template'], FILTER_VALIDATE_BOOLEAN );
		$show_child_terms = filter_var( $settings['show_child_terms'], FILTER_VALIDATE_BOOLEAN );
		$show_parent_articles = filter_var( $settings['show_parent_articles'], FILTER_VALIDATE_BOOLEAN );

		if ( $is_archive_template && isset( get_queried_object()->term_id ) ) {
			$term_id = get_queried_object()->term_id;
		} else {
			$term_id = $settings['term_id'];
		}

		if ( empty( $term_id ) ) {
			echo '<h2>Articles not found</h2>';
			return false;
		}

		$id_int = substr( $this->get_id_int(), 0, 3 );

		$this->add_render_attribute( 'container', [
			'class' => [
				'troubleshooting-manager-articles',
			],
		] );

		?><div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
			<div class="troubleshooting-manager-articles__inner"><?php

				$term_data = get_term( $term_id );

				if ( 'all' !== $term_id && ! empty( $term_data->name ) && filter_var( $settings['show_title'], FILTER_VALIDATE_BOOLEAN ) ) {
					echo sprintf( '<div class="troubleshooting-manager-articles__name-container"><h2 class="troubleshooting-manager-articles__name">%s</h2></div>', $term_data->name );
				}

				if ( $show_parent_articles ) {
					$this->generate_article_list( $term_id );
				}

				if ( $show_child_terms ) {
					$term_childs = get_term_children( $term_data->term_id, troubleshooting_manager()->post_type->category_term_slug() );?>

					<div class="troubleshooting-manager-articles__terms-list"><?php
						foreach ( $term_childs as $child ) {
							$child_term = get_term_by( 'id', $child, troubleshooting_manager()->post_type->category_term_slug() );?>
							<div class="troubleshooting-manager-articles__terms-item"><?php
								$this->generate_article_list( $child_term->term_id, true );?>
							</div><?php
						}?>
					</div><?php
				}?>
			</div>
		</div><?php
	}

	/**
	 * [generate_article_list description]
	 * @param  string $term_id [description]
	 * @return [type]          [description]
	 */
	public function generate_article_list( $term_id = '', $term_name_visible = false ) {

		$settings = $this->get_settings();
		$is_archive_template = filter_var( $settings['is_archive_template'], FILTER_VALIDATE_BOOLEAN );
		$use_article_limit = filter_var( $settings['use_article_limit'], FILTER_VALIDATE_BOOLEAN );
		$article_list_limit = $settings['article_limit'];

		$query_param = [
			'post_type'      => troubleshooting_manager()->post_type->article_post_slug(),
			'posts_per_page' => -1,
		];

		if ( empty( $term_id ) ) {
			return false;
		}

		$term_data = get_term( $term_id );

		if ( 'all' !== $term_id ) {
			$query_param['tax_query'] = [
				[
					'taxonomy'	=> troubleshooting_manager()->post_type->category_term_slug(),
					'field'		=> 'term_id',
					'terms'		=> $term_id,
				]
			];

			$atricle_thumbnail_id = get_term_meta( $term_data->term_id, 'category_thumbnail', true );

			if ( ! empty( $atricle_thumbnail_id ) ) {
				echo sprintf( '<div class="troubleshooting-manager-articles__thumbnail">%s</div>', wp_get_attachment_image( $atricle_thumbnail_id, 'full' ) );
			}
		}

		$query = new \WP_Query( $query_param );

		if( empty( $query->posts ) ) {
			return false;
		}

		?><div class="troubleshooting-manager-articles__article-list"><?php
			if ( $term_name_visible ) {?>
				<h3 class="troubleshooting-manager-articles__article-list-title"><?php echo $term_data->name; ?></h3><?php
			}?>

			<ul><?php

				$count = 0;

				while ( $query->have_posts() ) : $query->the_post();
					$post_id = $query->post->ID;

					if ( $use_article_limit && $count > $article_list_limit - 1 ) {
						continue;
					}?>

					<li id="troubleshooting-manager-article-<?php echo $post_id; ?>"><?php
						?><a class="troubleshooting-manager-articles__article-link" href="<?php the_permalink(); ?>"><?php
							the_title(); ?>
                            <span class="read-now-text">Read now</span>
                            <span class="read-now-icon"></span>
                        </a>
					</li><?php

					$count++;

				endwhile;

				wp_reset_postdata();?>
			</ul>
		</div><?php

		if ( $use_article_limit ) {
			$term_link_text = esc_html__( 'See all articles', 'troubleshooting-manager' );
			$term_link = get_term_link( (int)$term_id, troubleshooting_manager()->post_type->category_term_slug() );

			$more_link_icon_html = '<i class="nc-icon-glyph arrows-1_tail-right"></i>';

			echo sprintf( '<div class="troubleshooting-manager-articles__more-articles"><a href="%s"><span>%s</span>%s</a></div>', $term_link, $term_link_text, $more_link_icon_html );
		}
	}

}

