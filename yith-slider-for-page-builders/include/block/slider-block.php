<?php

/**
 * Gutenberg block render callback.
 *
 * @param array $atts Block attributes.
 * @return string
 */
function yith_slider_for_page_builders_block_handler( $atts ) {
	$atts = shortcode_atts(
		array(
			'slider' => '',
		),
		$atts
	);

	$slider_id = yith_slider_for_page_builders_get_valid_slider_id( $atts['slider'] );
	if ( ! $slider_id ) {
		return '';
	}

	return yith_slider_for_page_builders_render_slider( $slider_id );
}

add_action( 'init', 'yith_slider_for_page_builders_register_block' );
/**
 * Register block
 *
 * @return void
 */
function yith_slider_for_page_builders_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$index_js = 'index.js';
	wp_register_script(
		'yith-slider-for-page-builders-block-script',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-block-editor',
			'wp-server-side-render',
		),
		YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION,
		true
	);

	$localize = array(
		'slidersArray' => yith_slider_for_page_builders_get_sliders_list_array(),
	);

	wp_localize_script( 'yith-slider-for-page-builders-block-script', 'yith_slider_for_page_builders_block_localized_array', $localize );

	register_block_type(
		'yith-slider-for-page-builders/slider-block',
		array(
			'editor_script'   => 'yith-slider-for-page-builders-block-script',
			'render_callback' => 'yith_slider_for_page_builders_block_handler',
			'attributes'      => array(
				'slider' => array(
					'default' => '',
					'type'    => 'string',
				),
			),
		)
	);
}
