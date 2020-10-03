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

class Troubleshooting_Manager_Related_Articles extends Troubleshooting_Manager_Base {

	public function get_name() {
		return 'troubleshooting-manager-related-articles';
	}

	public function get_title() {
		return esc_html__( 'Troubleshooting Related Articles', 'troubleshooting-manager' );
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
			[
				'label'        => esc_html__( 'Use as Single Article Widget', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'troubleshooting-manager' ),
				'label_off'    => esc_html__( 'No', 'troubleshooting-manager' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
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

			$this->add_control(
				'term_id',
				[
					'label'   => esc_html__( 'Article Category', 'troubleshooting-manager' ),
					'type'    => Controls_Manager::SELECT,
					'options' => $avaliable_category,
					'default' => $default_category,
				]
			);
		}

		$this->add_control(
			'use_article_limit',
			array(
				'label'        => esc_html__( 'Use Article limit', 'troubleshooting-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'troubleshooting-manager' ),
				'label_off'    => esc_html__( 'No', 'troubleshooting-manager' ),
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

		$term_id = $settings['term_id'];

		if ( $is_archive_template && isset( get_queried_object()->ID ) ) {
			$article_id = get_queried_object()->ID;

			$term_list = get_the_terms( $article_id, troubleshooting_manager()->post_type->category_term_slug() );

			if ( ! empty( $term_list ) ) {
				$term_id = $term_list[0]->term_id;
			}
		}

		$this->add_render_attribute( 'container', [
			'class' => [
				'troubleshooting-manager-related-articles',
			],
		] );

		?><div <?php echo $this->get_render_attribute_string( 'container' ); ?>>
			<div class="troubleshooting-manager-related-articles__inner"><?php
				$this->generate_article_list( $term_id );
			?></div>
		</div><?php
	}

	/**
	 * [generate_article_list description]
	 * @param  string $term_id [description]
	 * @return [type]          [description]
	 */
	public function generate_article_list( $term_id = '' ) {

		if ( empty( $term_id ) ) {
			return false;
		}

		$settings = $this->get_settings();
		$is_archive_template = filter_var( $settings['is_archive_template'], FILTER_VALIDATE_BOOLEAN );
		$use_article_limit = filter_var( $settings['use_article_limit'], FILTER_VALIDATE_BOOLEAN );
		$article_list_limit = $settings['article_limit'];

		$query = new \WP_Query( [
			'post_type' => troubleshooting_manager()->post_type->article_post_slug(),
			'tax_query' => [
				[
					'taxonomy'	=> troubleshooting_manager()->post_type->category_term_slug(),
					'field'		=> 'term_id',
					'terms'		=> $term_id,
				]
			]
		] );

		if( ! empty( $query->posts ) ) {
			?><div class="troubleshooting-manager-related-articles__list">

				<ul><?php

					$count = 0;
					$current_course_article = get_the_ID();

					while ( $query->have_posts() ) : $query->the_post();
						$post_id = $query->post->ID;

						$is_course_article = troubleshooting_manager()->progress->is_course_article( $post_id );

						if ( $use_article_limit && $count > $article_list_limit - 1 ) {
							continue;
						}
			        $article_item_current_class = $post_id === $current_course_article ? 'current' : '';
						?>

						<li class="troubleshooting-manager-related-articles__item <?php echo esc_attr( $article_item_current_class ) ?>" id="croco-article-<?php the_ID(); ?>">
                            <span class="troubleshooting-manager-related-articles__icon"></span>
							<a class="troubleshooting-manager-related-articles__link" href="<?php the_permalink(); ?>"><?php
								the_title(); ?>
                            </a>
						</li><?php

						$count++;

					endwhile;

					wp_reset_postdata();?>
				</ul>
			</div><?php
		}
	}

}

