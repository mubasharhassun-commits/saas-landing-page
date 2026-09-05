<?php
/**
 * WPBakery Page Builder element registration.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps the funnel as a native WPBakery element.
 */
class MKM_RF_WPBakery {

	/**
	 * Hooks the mapping.
	 */
	public function init() {
		add_action( 'vc_before_init', array( $this, 'map_element' ) );
		add_action( 'wp', array( $this, 'maybe_enqueue_for_builder_content' ), 20 );
	}

	/**
	 * Loads the assets when the element sits inside builder markup that
	 * has_shortcode() does not detect (nested rows, templates, grid items).
	 */
	public function maybe_enqueue_for_builder_content() {
		if ( is_admin() || ! is_singular() || ! defined( 'WPB_VC_VERSION' ) ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( false !== strpos( (string) $post->post_content, '[' . MKM_RF_Shortcode::TAG ) ) {
			mkm_rf()->shortcode()->enqueue();
		}
	}

	/**
	 * Registers the element with the builder.
	 */
	public function map_element() {
		if ( ! function_exists( 'vc_map' ) ) {
			return;
		}

		vc_map(
			array(
				'name'        => __( 'Review Funnel', 'mkm-review-funnel' ),
				'base'        => MKM_RF_Shortcode::TAG,
				'category'    => __( 'Content', 'mkm-review-funnel' ),
				'icon'        => 'icon-wpb-star',
				'description' => __( 'Review request with public review links and a private feedback form.', 'mkm-review-funnel' ),
				'params'      => array(
					array(
						'type'        => 'dropdown',
						'heading'     => __( 'Mode', 'mkm-review-funnel' ),
						'param_name'  => 'mode',
						'value'       => array(
							__( 'Use the global setting', 'mkm-review-funnel' )            => '',
							__( 'Open - everyone sees both options', 'mkm-review-funnel' ) => 'open',
							__( 'Gated - ask about the experience first', 'mkm-review-funnel' ) => 'gated',
						),
						'std'         => '',
						'description' => __( 'Gated mode shows the public review links only to visitors who report a positive experience. Google\'s review policy prohibits that practice; open mode avoids the risk.', 'mkm-review-funnel' ),
					),
					array(
						'type'        => 'textfield',
						'heading'     => __( 'Heading', 'mkm-review-funnel' ),
						'param_name'  => 'heading',
						'admin_label' => true,
						'description' => __( 'Leave empty to use the value from Review Funnel settings.', 'mkm-review-funnel' ),
					),
					array(
						'type'        => 'textarea',
						'heading'     => __( 'Intro text', 'mkm-review-funnel' ),
						'param_name'  => 'intro',
						'description' => __( 'Leave empty to use the value from Review Funnel settings.', 'mkm-review-funnel' ),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Positive button label', 'mkm-review-funnel' ),
						'param_name' => 'positive_label',
						'dependency' => array(
							'element' => 'mode',
							'value'   => array( 'gated' ),
						),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Negative button label', 'mkm-review-funnel' ),
						'param_name' => 'negative_label',
						'dependency' => array(
							'element' => 'mode',
							'value'   => array( 'gated' ),
						),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Review button label', 'mkm-review-funnel' ),
						'param_name' => 'review_label',
						'dependency' => array(
							'element' => 'mode',
							'value'   => array( '', 'open' ),
						),
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Feedback button label', 'mkm-review-funnel' ),
						'param_name' => 'feedback_label',
						'dependency' => array(
							'element' => 'mode',
							'value'   => array( '', 'open' ),
						),
					),
					array(
						'type'       => 'dropdown',
						'heading'    => __( 'Alignment', 'mkm-review-funnel' ),
						'param_name' => 'align',
						'value'      => array(
							__( 'Center', 'mkm-review-funnel' ) => 'center',
							__( 'Left', 'mkm-review-funnel' )   => 'left',
							__( 'Right', 'mkm-review-funnel' )  => 'right',
						),
						'std'        => 'center',
					),
					array(
						'type'       => 'textfield',
						'heading'    => __( 'Extra CSS class', 'mkm-review-funnel' ),
						'param_name' => 'class',
						'group'      => __( 'Extra', 'mkm-review-funnel' ),
					),
					array(
						'type'       => 'css_editor',
						'heading'    => __( 'CSS box', 'mkm-review-funnel' ),
						'param_name' => 'css',
						'group'      => __( 'Design Options', 'mkm-review-funnel' ),
					),
				),
			)
		);
	}
}
