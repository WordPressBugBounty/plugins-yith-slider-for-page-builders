<?php
/**
 * Slider shortcode template
 *
 * @package YITH Slider for page builders
 */

/**
 * Render a slider on the front-end.
 *
 * @param int $slider_id Slider post ID.
 * @return string
 */
function yith_slider_for_page_builders_render_slider( $slider_id ) {
	$slider_id = absint( $slider_id );
	if ( ! $slider_id ) {
		return '';
	}

	$slider_args = array(
		'post_parent' => $slider_id,
		'post_type'   => 'yith_slide',
		'numberposts' => -1,
		'post_status' => 'publish',
		'orderby'     => 'meta_value_num',
		'order'       => 'ASC',
		'meta_key'    => 'slide_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	);
	$slides      = get_children( $slider_args );

	if ( empty( $slides ) ) {
		return '';
	}

	yith_slider_for_page_builders_enqueue_frontend_assets();

	$slider_style = '';

	$slide_container_height = get_post_meta( $slider_id, 'yith_slider_control_heigth', true );
	if ( '' !== $slide_container_height ) {
		$slider_style .= 'height: ' . absint( $slide_container_height ) . 'px; ';
	}

	if ( has_post_thumbnail( $slider_id ) ) {
		$slide_bg      = get_the_post_thumbnail_url( $slider_id );
		$slider_style .= 'background-image: url(\'' . esc_url( $slide_bg ) . '\'); ';

		$slide_bg_position = get_post_meta( $slider_id, 'single_slide_background_position', true );
		$slider_style     .= 'background-position: ' . ( $slide_bg_position ? esc_attr( $slide_bg_position ) : 'center' ) . '; ';

		$slide_bg_repeat = get_post_meta( $slider_id, 'single_slide_background_repeat', true );
		$slider_style   .= 'background-repeat: ' . ( $slide_bg_repeat ? esc_attr( $slide_bg_repeat ) : 'no-repeat' ) . '; ';

		$slide_bg_size = get_post_meta( $slider_id, 'single_slide_background_size', true );
		$slider_style .= 'background-size: ' . ( $slide_bg_size ? esc_attr( $slide_bg_size ) : 'cover' ) . '; ';
	}

	$slide_bg_color = get_post_meta( $slider_id, 'single_slide_background_color', true );
	if ( $slide_bg_color ) {
		$slider_style .= 'background-color: ' . esc_attr( $slide_bg_color ) . '; ';
	}

	$transition_type  = get_post_meta( $slider_id, 'yith_slider_control_animation_type', true ) === 'fade';
	$autoplay         = get_post_meta( $slider_id, 'yith_slider_control_autoplay', true ) === 'autoplay';
	$autoplay_timing  = absint( get_post_meta( $slider_id, 'yith_slider_control_autoplay_timing', true ) );
	$infinite_sliding = get_post_meta( $slider_id, 'yith_slider_control_infinite_sliding', true ) === 'infinite-sliding';
	$slider_layout    = get_post_meta( $slider_id, 'yith_slider_control_slider_layout', true );
	$center_mode      = false;

	if ( '' === $slider_layout ) {
		$slider_layout = 'alignfull';
	}

	if ( ! $autoplay_timing ) {
		$autoplay_timing = 3000;
	}

	$arrow_nav       = get_post_meta( $slider_id, 'yith_slider_control_navigation_style', true );
	$arrow_nav_style = '';
	if ( '' === $arrow_nav || 'none' === $arrow_nav ) {
		$arrow_nav = false;
	} elseif ( 'prev_next_slides' === $arrow_nav ) {
		$arrow_nav   = false;
		$center_mode = true;
	} else {
		$arrow_nav_style = $arrow_nav;
		$arrow_nav       = true;
	}

	$dots_nav       = get_post_meta( $slider_id, 'yith_slider_control_dots_navigation_style', true );
	$dots_nav_style = '';
	if ( '' === $dots_nav || 'none' === $dots_nav ) {
		$dots_nav = false;
	} else {
		$dots_nav_style = $dots_nav;
		$dots_nav       = true;
	}

	$data_slick_options = array(
		'autoplay'      => $autoplay,
		'fade'          => $transition_type,
		'infinite'      => $infinite_sliding,
		'arrows'        => $arrow_nav,
		'prevArrow'     => '<button type="button" class="yith-slider-nav slide-prev ' . esc_attr( $arrow_nav_style ) . '">Previous</button>',
		'nextArrow'     => '<button type="button" class="yith-slider-nav slide-next ' . esc_attr( $arrow_nav_style ) . '">Next</button>',
		'centerMode'    => $center_mode,
		'dots'          => $dots_nav,
		'dotsClass'     => 'yith-slider-dots ' . esc_attr( $dots_nav_style ),
		'autoplaySpeed' => $autoplay_timing,
		'rtl'           => is_rtl(),
	);

	$data_slick_attr = _wp_specialchars( wp_json_encode( $data_slick_options ), ENT_QUOTES, 'UTF-8', true );
	$slide_count     = count( $slides );
	$slick_data_attr = $slide_count > 1 ? ' data-slick="' . $data_slick_attr . '"' : '';

	ob_start();
	?>
	<div<?php echo $slick_data_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="yith-slider <?php echo esc_attr( $slider_layout ); ?> yith-slider-<?php echo esc_attr( $slider_id ); ?>" style="<?php echo esc_attr( $slider_style ); ?>">
		<?php
		foreach ( $slides as $slide ) :
			$slide_id                  = $slide->ID;
			$style                     = 'style="';
			$slide_container_max_width = '';

			if ( has_post_thumbnail( $slide_id ) ) {
				$slide_bg = get_the_post_thumbnail_url( $slide_id );
				$style   .= 'background-image: url(\'' . esc_url( $slide_bg ) . '\'); ';

				$slide_bg_position = get_post_meta( $slide_id, 'single_slide_background_position', true );
				$style            .= 'background-position: ' . ( $slide_bg_position ? esc_attr( $slide_bg_position ) : 'center' ) . '; ';

				$slide_bg_repeat = get_post_meta( $slide_id, 'single_slide_background_repeat', true );
				$style          .= 'background-repeat: ' . ( $slide_bg_repeat ? esc_attr( $slide_bg_repeat ) : 'no-repeat' ) . '; ';

				$slide_bg_size = get_post_meta( $slide_id, 'single_slide_background_size', true );
				$style        .= 'background-size: ' . ( $slide_bg_size ? esc_attr( $slide_bg_size ) : 'cover' ) . '; ';
			}

			$slide_bg_color = get_post_meta( $slide_id, 'single_slide_background_color', true );
			if ( $slide_bg_color ) {
				$style .= 'background-color: ' . esc_attr( $slide_bg_color ) . '; ';
			}

			if ( 1 === $slide_count ) {
				$style .= 'display: block; float: none; ';
			}

			$style .= '"';

			$container_max_width = get_post_meta( $slider_id, 'yith_slider_control_container_max_width', true );
			if ( '' !== $container_max_width ) {
				$slide_container_max_width = '<style type="text/css"> .slide-id-' . esc_attr( $slide_id ) . ' .slide-container { max-width: ' . absint( $container_max_width ) . 'px;}</style>';
			}
			?>
			<div class="yith-slider-slide slide-id-<?php echo esc_attr( $slide_id ); ?> slick-slide" <?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo $slide_container_max_width; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="slide-container">
					<?php echo do_shortcode( do_blocks( $slide->post_content ) ); ?>
				</div>
			</div>
			<?php
		endforeach;
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Enqueue front-end slider assets when a slider is rendered.
 *
 * @return void
 */
function yith_slider_for_page_builders_enqueue_frontend_assets() {
	static $assets_enqueued = false;

	if ( $assets_enqueued ) {
		return;
	}

	wp_enqueue_script( 'yith-sliders-slick-script' );
	wp_enqueue_script( 'yith-sliders-frontend-script' );
	wp_enqueue_style( 'yith-sliders-slick-style' );
	wp_enqueue_style( 'yith-sliders-slider-style' );

	$assets_enqueued = true;
}

/**
 * Shortcode callback
 *
 * @param array $atts Shortcode atts.
 *
 * @return string
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_slider_sc( $atts ) {
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

add_shortcode( 'yith-slider', 'yith_slider_for_page_builders_slider_sc' );
