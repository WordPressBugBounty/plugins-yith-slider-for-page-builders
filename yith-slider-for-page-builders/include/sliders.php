<?php
/**
 * Manage slider post type
 *
 * @package YITH Slider for page builders
 */

/**
 * Validate a slider post ID for front-end rendering.
 *
 * @param int|string $slider_id Slider post ID.
 * @return int|false Valid slider ID or false.
 */
function yith_slider_for_page_builders_get_valid_slider_id( $slider_id ) {
	$slider_id = absint( $slider_id );
	if ( ! $slider_id ) {
		return false;
	}

	$slider = get_post( $slider_id );
	if ( ! $slider || 'yith_slider' !== $slider->post_type ) {
		return false;
	}

	if ( 'publish' !== $slider->post_status && ! current_user_can( 'edit_post', $slider_id ) ) {
		return false;
	}

	return $slider_id;
}

/**
 * Validate a parent slider for slide association.
 *
 * @param int|string $parent_id Parent slider post ID.
 * @return int|false Valid parent slider ID or false.
 */
function yith_slider_for_page_builders_get_valid_parent_slider_id( $parent_id ) {
	$parent_id = absint( $parent_id );
	if ( ! $parent_id ) {
		return false;
	}

	$parent = get_post( $parent_id );
	if ( ! $parent || 'yith_slider' !== $parent->post_type ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $parent_id ) ) {
		return false;
	}

	return $parent_id;
}

/**
 * Sanitize a CSS background color value.
 *
 * @param string $color Raw color value.
 * @return string Sanitized hex color or empty string.
 */
function yith_slider_for_page_builders_sanitize_background_color( $color ) {
	$color = trim( (string) $color );
	if ( '' === $color ) {
		return '';
	}

	$hex = sanitize_hex_color( $color );
	if ( $hex ) {
		return $hex;
	}

	if ( preg_match( '/^rgba?\(\s*[0-9,\s.]+\s*\)$/i', $color ) ) {
		return sanitize_text_field( $color );
	}

	return '';
}

/**
 * Common pre-save validation for plugin post types.
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Expected post type slug.
 * @return bool
 */
function yith_slider_for_page_builders_can_save_post( $post_id, $post_type ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return false;
	}

	if ( get_post_type( $post_id ) !== $post_type ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	return true;
}

/**
 * Validate save requests for slider or slide posts.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function yith_slider_for_page_builders_can_save_slider_or_slide( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return false;
	}

	$post_type = get_post_type( $post_id );
	if ( ! in_array( $post_type, array( 'yith_slider', 'yith_slide' ), true ) ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	return true;
}

/**
 * Sanitize background size option.
 *
 * @param string $value Raw value.
 * @return string
 */
function yith_slider_for_page_builders_sanitize_background_size( $value ) {
	$allowed = array( 'cover', 'contain', 'original' );

	return in_array( $value, $allowed, true ) ? $value : 'cover';
}

/**
 * Sanitize background position option.
 *
 * @param string $value Raw value.
 * @return string
 */
function yith_slider_for_page_builders_sanitize_background_position( $value ) {
	$allowed = array(
		'center',
		'left top',
		'left center',
		'left bottom',
		'right top',
		'right center',
		'right bottom',
		'center top',
		'center bottom',
	);

	return in_array( $value, $allowed, true ) ? $value : 'center';
}

/**
 * Sanitize background repeat option.
 *
 * @param string $value Raw value.
 * @return string
 */
function yith_slider_for_page_builders_sanitize_background_repeat( $value ) {
	$allowed = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );

	return in_array( $value, $allowed, true ) ? $value : 'no-repeat';
}

/**
 * Map slide background size meta to a CSS background-size value.
 *
 * @param string $size Stored background size option.
 * @return string
 */
function yith_slider_for_page_builders_get_css_background_size( $size ) {
	$map = array(
		'cover'    => 'cover',
		'contain'  => 'contain',
		'original' => 'auto',
	);

	return isset( $map[ $size ] ) ? $map[ $size ] : 'cover';
}

/**
 * Get preview data for a slide in the slider admin list.
 *
 * @param int $slide_id  Slide post ID.
 * @param int $slider_id Parent slider post ID.
 * @return array
 */
function yith_slider_for_page_builders_get_slide_preview_data( $slide_id, $slider_id ) {
	$slide_id  = absint( $slide_id );
	$slider_id = absint( $slider_id );

	$bg_image = get_the_post_thumbnail_url( $slide_id, 'yith_slider_thumb' );
	if ( ! $bg_image ) {
		$bg_image = get_the_post_thumbnail_url( $slide_id, 'medium' );
	}
	if ( ! $bg_image && $slider_id ) {
		$bg_image = get_the_post_thumbnail_url( $slider_id, 'yith_slider_thumb' );
	}

	$bg_color = get_post_meta( $slide_id, 'single_slide_background_color', true );
	if ( ! $bg_color && $slider_id ) {
		$bg_color = get_post_meta( $slider_id, 'single_slide_background_color', true );
	}
	$bg_color = $bg_color ? yith_slider_for_page_builders_sanitize_background_color( $bg_color ) : '#ffffff';

	$bg_size     = get_post_meta( $slide_id, 'single_slide_background_size', true );
	$bg_position = get_post_meta( $slide_id, 'single_slide_background_position', true );
	$bg_repeat   = get_post_meta( $slide_id, 'single_slide_background_repeat', true );

	$canvas_width  = absint( get_post_meta( $slider_id, 'yith_slider_control_container_max_width', true ) );
	$canvas_height = absint( get_post_meta( $slider_id, 'yith_slider_control_heigth', true ) );
	if ( ! $canvas_width ) {
		$canvas_width = 1540;
	}
	if ( ! $canvas_height ) {
		$canvas_height = 550;
	}

	$preview_width  = 300;
	$preview_height = 150;
	$scale          = min( $preview_width / $canvas_width, $preview_height / $canvas_height );

	return array(
		'bg_image'       => $bg_image ? esc_url( $bg_image ) : '',
		'bg_color'       => esc_attr( $bg_color ),
		'bg_size'        => esc_attr( yith_slider_for_page_builders_get_css_background_size( $bg_size ) ),
		'bg_position'    => esc_attr( $bg_position ? $bg_position : 'center' ),
		'bg_repeat'      => esc_attr( $bg_repeat ? yith_slider_for_page_builders_sanitize_background_repeat( $bg_repeat ) : 'no-repeat' ),
		'title'          => get_the_title( $slide_id ),
		'canvas_width'   => $canvas_width,
		'canvas_height'  => $canvas_height,
		'preview_scale'  => $scale,
		'has_content'    => yith_slider_for_page_builders_slide_has_preview_content( $slide_id ),
	);
}

/**
 * Check whether a slide has renderable block content.
 *
 * @param int $slide_id Slide post ID.
 * @return bool
 */
function yith_slider_for_page_builders_slide_has_preview_content( $slide_id ) {
	$slide = get_post( $slide_id );
	if ( ! $slide || empty( trim( $slide->post_content ) ) ) {
		return false;
	}

	return has_blocks( $slide->post_content ) || ! empty( wp_strip_all_tags( $slide->post_content ) );
}

/**
 * Render slide block content for admin previews.
 *
 * @param int $slide_id Slide post ID.
 * @return string
 */
function yith_slider_for_page_builders_render_slide_preview_content( $slide_id ) {
	if ( ! yith_slider_for_page_builders_slide_has_preview_content( $slide_id ) ) {
		return '';
	}

	yith_slider_for_page_builders_enqueue_slide_preview_assets();

	$slide = get_post( $slide_id );
	if ( ! $slide ) {
		return '';
	}

	ob_start();
	echo '<div class="slide-container">';
	echo do_shortcode( do_blocks( $slide->post_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</div>';

	return ob_get_clean();
}

/**
 * Enqueue assets needed to style rendered slide previews in admin.
 *
 * @return void
 */
function yith_slider_for_page_builders_enqueue_slide_preview_assets() {
	static $assets_enqueued = false;

	if ( $assets_enqueued ) {
		return;
	}

	wp_enqueue_style( 'wp-block-library' );
	wp_enqueue_style( 'wp-block-library-theme' );
	wp_enqueue_style( 'yith-sliders-slider-style' );

	$assets_enqueued = true;
}

/**
 * Render the thumbnail preview for a slide in the slider edit screen.
 *
 * @param int $slide_id  Slide post ID.
 * @param int $slider_id Parent slider post ID.
 * @param int $index     Slide position in the list (1-based).
 * @return string
 */
function yith_slider_for_page_builders_render_slide_preview_thumb( $slide_id, $slider_id, $index = 0 ) {
	$preview      = yith_slider_for_page_builders_get_slide_preview_data( $slide_id, $slider_id );
	$content_html = yith_slider_for_page_builders_render_slide_preview_content( $slide_id );

	$style = sprintf(
		'background-color:%1$s;background-size:%2$s;background-position:%3$s;background-repeat:%4$s;',
		$preview['bg_color'],
		$preview['bg_size'],
		$preview['bg_position'],
		$preview['bg_repeat']
	);

	if ( $preview['bg_image'] ) {
		$style .= 'background-image:url(' . $preview['bg_image'] . ');';
	}

	$scaler_style = sprintf(
		'width:%1$dpx;height:%2$dpx;transform:scale(%3$s);',
		$preview['canvas_width'],
		$preview['canvas_height'],
		$preview['preview_scale']
	);

	$html  = '<div class="yith-slide-preview-thumb" style="' . esc_attr( $style ) . '">';
	$html .= '<span class="yith-slide-preview-index">' . esc_html( $index ) . '</span>';

	if ( $content_html ) {
		$html .= '<div class="yith-slide-preview-viewport">';
		$html .= '<div class="yith-slide-preview-scaler" style="' . esc_attr( $scaler_style ) . '">';
		$html .= '<div class="yith-slide-preview-content">' . $content_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$html .= '</div></div>';
	} elseif ( ! $preview['bg_image'] ) {
		$html .= '<span class="yith-slide-preview-empty">' . esc_html__( 'Empty slide', 'yith-slider-for-page-builders' ) . '</span>';
	}

	if ( $preview['title'] ) {
		$html .= '<span class="yith-slide-preview-title">' . esc_html( $preview['title'] ) . '</span>';
	} elseif ( $index ) {
		/* translators: %d: slide number in the slider list */
		$html .= '<span class="yith-slide-preview-title">' . esc_html( sprintf( __( 'Slide %d', 'yith-slider-for-page-builders' ), $index ) ) . '</span>';
	}

	$html .= '</div>';

	return $html;
}

/**
 * Restrict REST API access to authenticated users with edit capability.
 *
 * @param mixed            $result  Response to replace the requested version with.
 * @param WP_REST_Server   $server  Server instance.
 * @param WP_REST_Request  $request Request used to generate the response.
 * @return mixed
 */
function yith_slider_for_page_builders_restrict_rest_api( $result, $server, $request ) {
	if ( ! empty( $result ) ) {
		return $result;
	}

	$route = $request->get_route();
	if ( ! preg_match( '#/wp/v2/yith_(slider|slide)(?:/|$)#', $route ) ) {
		return $result;
	}

	if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
		return new WP_Error(
			'rest_forbidden',
			esc_html__( 'Sorry, you are not allowed to access slider content.', 'yith-slider-for-page-builders' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $result;
}

add_action( 'rest_pre_dispatch', 'yith_slider_for_page_builders_restrict_rest_api', 10, 3 );

/**
 * Enqueue custom admin css for slider management
 */
function yith_slider_for_page_builders_include_admin_css() {
	$current_post_type = '';
	$current_post      = '';

	if ( isset( $_GET['post'] ) ) {
		$current_post      = intval( wp_unslash( $_GET['post'] ) );
		$current_post_type = get_post_type( $current_post );
	} elseif ( isset( $_GET['post_type'] ) ) {
		$current_post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
	}

	if ( $current_post_type && in_array( $current_post_type, array( 'yith_slider', 'yith_slide' ), true ) ) {

		wp_enqueue_style( 'yith_slider_admin_css', YITH_SLIDER_FOR_PAGE_BUILDERS_URL . 'assets/slider-admin-css.css', array(), YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION );

		if ( 'yith_slide' === $current_post_type && $current_post ) {
			$slide_editor_admin_css = yith_slider_for_page_builders_get_slide_editor_admin_css( $current_post );
			if ( $slide_editor_admin_css ) {
				wp_register_style( 'yith-slider-slide-editor-admin', false, array(), YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION );
				wp_enqueue_style( 'yith-slider-slide-editor-admin' );
				wp_add_inline_style( 'yith-slider-slide-editor-admin', $slide_editor_admin_css );
			}
		}

		if ( 'yith_slider' === $current_post_type ) {
			yith_slider_for_page_builders_enqueue_slide_preview_assets();
		}

		// Add the color picker css file.
		wp_enqueue_style( 'wp-color-picker' );

		// Include custom jQuery script with WordPress Color Picker dependency.
		wp_enqueue_script( 'yith_slider_admin_js', YITH_SLIDER_FOR_PAGE_BUILDERS_URL . 'assets/slider-admin-js.js', array( 'wp-color-picker' ), YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION, true );

		wp_enqueue_script( 'yith_slider_admin_js' );

		$disable_fullscreen_editor_by_default = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
		wp_add_inline_script( 'wp-blocks', $disable_fullscreen_editor_by_default );

	}
}

add_action( 'admin_enqueue_scripts', 'yith_slider_for_page_builders_include_admin_css' );


add_action( 'enqueue_block_editor_assets', 'yith_slider_for_page_builders_enqueue_block_scripts', 99 );

/**
 * Add block editor notices
 */
function yith_slider_for_page_builders_enqueue_block_scripts() {
	$current_post_type = '';
	$current_post      = '';

	if ( isset( $_GET['post'] ) ) {
		$current_post      = intval( wp_unslash( $_GET['post'] ) );
		$current_post_type = get_post_type( $current_post );
	} elseif ( isset( $_GET['post_type'] ) ) {
		$current_post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
	}
	if ( $current_post_type && 'yith_slide' === $current_post_type ) {
		$parent_slider = isset( $_GET['parent_slider'] ) ? absint( wp_unslash( $_GET['parent_slider'] ) ) : wp_get_post_parent_id( $current_post );
		$parent_slider = yith_slider_for_page_builders_get_valid_parent_slider_id( $parent_slider );
		if ( ! $parent_slider ) {
			$parent_slider = wp_get_post_parent_id( $current_post );
		}

		wp_register_script(
			'yith-slider-gutenberg-js',
			YITH_SLIDER_FOR_PAGE_BUILDERS_URL . 'assets/slider-gutenberg-js.js',
			array(),
			YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION,
			true
		);

		$slider_height = get_post_meta( $parent_slider, 'yith_slider_control_heigth', true );
		if ( ! $slider_height || '' === $slider_height ) {
			$slider_height = '550';
		}

		$localize = array(
			'sliderHeigth'               => $slider_height,
			'sliderBgColor'              => get_post_meta( $parent_slider, 'single_slide_background_color', true ),
			'sliderBgImage'              => get_the_post_thumbnail_url( $parent_slider, 'full' ),
			'editSliderHeightUrl'        => get_edit_post_link( $parent_slider ),
			'sliderHeightString'         => esc_html__( 'Slider height:', 'yith-slider-for-page-builders' ),
			'editSliderHeightString'     => esc_html__( 'Change slider height >', 'yith-slider-for-page-builders' ),
			'changeSliderHeightMessage'  => esc_html__( 'Content below the dotted line will be hidden.', 'yith-slider-for-page-builders' ),
			'backToParentSliderLinkText' => esc_html__( '< Back to parent slider', 'yith-slider-for-page-builders' ),
			'backToParentSliderLinkUrl'  => get_edit_post_link( $parent_slider ),
			'slidersArray'               => yith_slider_for_page_builders_get_sliders_list_array(),
			'isRtl'                      => is_rtl(),
		);

		wp_localize_script( 'yith-slider-gutenberg-js', 'yith_slider_for_page_builders_localized_array', $localize );

		wp_enqueue_script( 'yith-slider-gutenberg-js' );
	}
	wp_enqueue_style( 'yith-sliders-slider-style', YITH_SLIDER_FOR_PAGE_BUILDERS_URL . 'assets/slider-style.css', '', YITH_SLIDER_FOR_PAGE_BUILDERS_VERSION, 'screen' );
}

/**
 * Add features to currently active theme
 *
 * @return void
 */
function yith_slider_for_page_builders_after_setup_theme() {
	// Gutenberg aligning options.
	add_theme_support( 'align-wide' );

	// Add image size slider specific.
	add_image_size( 'yith_slider_thumb', 300, 150, true );

	// Add support to custom editor stylesheets.
	add_theme_support( 'editor-styles' );
}

add_action( 'after_setup_theme', 'yith_slider_for_page_builders_after_setup_theme' );

/**
 * Force enable Gutenberg if disabled
 *
 * @param bool $can_edit User capability check.
 * @param int  $post Current post ID.
 *
 * @return bool
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_editor_enabler( $can_edit, $post ) {
	global $pagenow;

	if ( 'post.php' === $pagenow && isset( $_GET['post'] ) && ( ( 'yith_slide' === get_post_type( intval( wp_unslash( $_GET['post'] ) ) ) ) ) ) {
		return true;
	}

	return $can_edit;

}

// Enable Gutenberg for WP < 5.0 beta.
add_filter( 'gutenberg_can_edit_post_type', 'yith_slider_for_page_builders_editor_enabler', 10, 2 );

// Enable Gutenberg for WordPress >= 5.0.
add_filter( 'use_block_editor_for_post', 'yith_slider_for_page_builders_editor_enabler', 10, 2 );

/**
 * Build editor-canvas CSS for a slide (injected into the block editor iframe).
 *
 * @param int $slide_id Slide post ID.
 * @return string
 */
function yith_slider_for_page_builders_get_slide_editor_css( $slide_id ) {
	$slide_id = absint( $slide_id );
	if ( ! $slide_id || 'yith_slide' !== get_post_type( $slide_id ) ) {
		return '';
	}

	$slider = wp_get_post_parent_id( $slide_id );
	if ( ! $slider ) {
		return '';
	}

	$slider_height = get_post_meta( $slider, 'yith_slider_control_heigth', true );
	if ( ! $slider_height || '' === $slider_height ) {
		$slider_height = 550;
	}
	$slider_height = absint( $slider_height );

	$slider_background_img   = get_the_post_thumbnail_url( $slider, 'full' );
	$slide_background_img    = get_the_post_thumbnail_url( $slide_id, 'full' );
	$slide_bg_img            = $slide_background_img ? $slide_background_img : $slider_background_img;
	$slider_background_color = get_post_meta( $slider, 'single_slide_background_color', true );
	$slide_background_color  = get_post_meta( $slide_id, 'single_slide_background_color', true );
	$slide_bg_color          = $slide_background_color ? $slide_background_color : $slider_background_color;

	$background_image = $slide_bg_img ? sprintf( 'url(%s)', esc_url( $slide_bg_img ) ) : 'none';

	return sprintf(
		'.editor-styles-wrapper {
			position: relative;
			background-image: %1$s;
			background-color: %2$s;
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
			min-height: %3$dpx;
		}
		.editor-styles-wrapper::after {
			content: "";
			position: absolute;
			top: %3$dpx;
			left: 0;
			right: 0;
			bottom: 0;
			border-top: 5px dashed red;
			background: rgba(0, 0, 0, 0.7);
			z-index: 1;
			pointer-events: none;
			min-height: 300px;
		}',
		$background_image,
		esc_attr( $slide_bg_color ? $slide_bg_color : '#ffffff' ),
		$slider_height
	);
}

/**
 * Build admin-shell CSS for slide editor UI (notices, layout).
 *
 * @param int $slide_id Slide post ID.
 * @return string
 */
function yith_slider_for_page_builders_get_slide_editor_admin_css( $slide_id ) {
	$slide_id = absint( $slide_id );
	if ( ! $slide_id || 'yith_slide' !== get_post_type( $slide_id ) ) {
		return '';
	}

	$slider = wp_get_post_parent_id( $slide_id );
	if ( ! $slider ) {
		return '';
	}

	$slider_height = get_post_meta( $slider, 'yith_slider_control_heigth', true );
	if ( ! $slider_height || '' === $slider_height ) {
		$slider_height = 550;
	}
	$slider_height = absint( $slider_height );

	return sprintf(
		'.post-type-yith_slide .components-editor-notices__dismissible {
			position: absolute;
			left: 0;
		}
		.post-type-yith_slide .edit-post-layout .interface-interface-skeleton__content,
		.post-type-yith_slide .edit-post-layout__content {
			background-color: #ffffff;
		}
		.post-type-yith_slide .yith-slider-for-page-builders-height-notice {
			position: absolute;
			top: calc(%1$dpx + 90px);
			left: 0;
			right: 0;
			background-color: transparent !important;
			text-align: center;
			color: #ffffff;
			font-weight: 600;
			font-size: 16px;
			border: none !important;
			z-index: 2;
		}
		.post-type-yith_slide .yith-slider-for-page-builders-height-notice .components-notice__action.components-button {
			text-align: center;
			display: table;
			width: auto;
			margin: 0 auto;
			text-decoration: none;
			font-size: 16px;
			color: #f0b849;
		}',
		$slider_height
	);
}

/**
 * Inject slide editor styles into the block editor canvas iframe.
 *
 * @param array                   $editor_settings Editor settings.
 * @param WP_Block_Editor_Context $editor_context  Editor context.
 * @return array
 */
function yith_slider_for_page_builders_add_slide_editor_styles( $editor_settings, $editor_context ) {
	$post = isset( $editor_context->post ) ? $editor_context->post : null;
	if ( ! $post || 'yith_slide' !== $post->post_type ) {
		return $editor_settings;
	}

	$css = yith_slider_for_page_builders_get_slide_editor_css( $post->ID );
	if ( ! $css ) {
		return $editor_settings;
	}

	if ( ! isset( $editor_settings['styles'] ) || ! is_array( $editor_settings['styles'] ) ) {
		$editor_settings['styles'] = array();
	}

	$editor_settings['styles'][] = array(
		'css'            => $css,
		'__unstableType' => 'plugin',
		'isGlobalStyles' => false,
	);

	return $editor_settings;
}

add_filter( 'block_editor_settings_all', 'yith_slider_for_page_builders_add_slide_editor_styles', 10, 2 );

/**
 * Print slide custom style retrieved from options
 *
 * @param int $current_post the slide post id.
 * @deprecated 1.1.0 Kept for backwards compatibility; styles are injected via block_editor_settings_all.
 */
function yith_slider_for_page_builders_custom_admin_css( $current_post ) {
	return yith_slider_for_page_builders_get_slide_editor_admin_css( $current_post );
}


/**
 * Register Slider Post Type.
 */
function yith_slider_generate_slider() {

	$labels = array(
		'name'                  => esc_html_x( 'Sliders', 'Post Type General Name', 'yith-slider-for-page-builders' ),
		'singular_name'         => esc_html_x( 'Slider', 'Post Type Singular Name', 'yith-slider-for-page-builders' ),
		'menu_name'             => esc_html__( 'Sliders', 'yith-slider-for-page-builders' ),
		'name_admin_bar'        => esc_html__( 'Slider', 'yith-slider-for-page-builders' ),
		'archives'              => esc_html__( 'Slider Archives', 'yith-slider-for-page-builders' ),
		'attributes'            => esc_html__( 'Slider Attributes', 'yith-slider-for-page-builders' ),
		'parent_item_colon'     => esc_html__( 'Parent Slider:', 'yith-slider-for-page-builders' ),
		'all_items'             => esc_html__( 'All Sliders', 'yith-slider-for-page-builders' ),
		'add_new_item'          => esc_html__( 'Add New Slider', 'yith-slider-for-page-builders' ),
		'add_new'               => esc_html__( 'Add New Slider', 'yith-slider-for-page-builders' ),
		'new_item'              => esc_html__( 'New Slider', 'yith-slider-for-page-builders' ),
		'edit_item'             => esc_html__( 'Edit Slider', 'yith-slider-for-page-builders' ),
		'update_item'           => esc_html__( 'Update Slider', 'yith-slider-for-page-builders' ),
		'view_item'             => esc_html__( 'View Slider', 'yith-slider-for-page-builders' ),
		'view_items'            => esc_html__( 'View Sliders', 'yith-slider-for-page-builders' ),
		'search_items'          => esc_html__( 'Search Slider', 'yith-slider-for-page-builders' ),
		'not_found'             => esc_html__( 'Slider not found', 'yith-slider-for-page-builders' ),
		'not_found_in_trash'    => esc_html__( 'Slider not found in Trash', 'yith-slider-for-page-builders' ),
		'insert_into_item'      => esc_html__( 'Insert into Slider', 'yith-slider-for-page-builders' ),
		'items_list'            => esc_html__( 'Sliders list', 'yith-slider-for-page-builders' ),
		'items_list_navigation' => esc_html__( 'Sliders list navigation', 'yith-slider-for-page-builders' ),
		'filter_items_list'     => esc_html__( 'Filter Sliders list', 'yith-slider-for-page-builders' ),
		'featured_image'        => esc_html__( 'Slider background image', 'yith-slider-for-page-builders' ),
		'set_featured_image'    => esc_html__( 'Set background image', 'yith-slider-for-page-builders' ),
		'remove_featured_image' => esc_html_x( 'Remove background image', 'Slider post type background image', 'yith-slider-for-page-builders' ),
		'use_featured_image'    => esc_html_x( 'Use as background image', 'Slider post type background image', 'yith-slider-for-page-builders' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Slider', 'yith-slider-for-page-builders' ),
		'description'         => esc_html__( 'Slider post type', 'yith-slider-for-page-builders' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail' ),
		'taxonomies'          => array( '' ),
		'hierarchical'        => true,
		'public'              => false,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'show_in_menu'        => true,
		'menu_position'       => 10,
		'menu_icon'           => 'dashicons-slides',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'yith_slider', $args );

}

add_action( 'init', 'yith_slider_generate_slider', 0 );

/**
 * Register Slide Post Type.
 */
function yith_slider_generate_slide() {

	$labels = array(
		'name'                  => esc_html_x( 'Slides', 'Post Type General Name', 'yith-slider-for-page-builders' ),
		'singular_name'         => esc_html_x( 'Slide', 'Post Type Singular Name', 'yith-slider-for-page-builders' ),
		'menu_name'             => esc_html__( 'Slides', 'yith-slider-for-page-builders' ),
		'name_admin_bar'        => esc_html__( 'Slide', 'yith-slider-for-page-builders' ),
		'archives'              => esc_html__( 'Slide Archives', 'yith-slider-for-page-builders' ),
		'attributes'            => esc_html__( 'Slide Attributes', 'yith-slider-for-page-builders' ),
		'parent_item_colon'     => esc_html__( 'Parent Slider:', 'yith-slider-for-page-builders' ),
		'all_items'             => esc_html__( 'All Slides', 'yith-slider-for-page-builders' ),
		'add_new_item'          => esc_html__( 'Add New Slide', 'yith-slider-for-page-builders' ),
		'add_new'               => esc_html__( 'Add New Slide', 'yith-slider-for-page-builders' ),
		'new_item'              => esc_html__( 'New Slide', 'yith-slider-for-page-builders' ),
		'edit_item'             => esc_html__( 'Edit Slide', 'yith-slider-for-page-builders' ),
		'update_item'           => esc_html__( 'Update Slide', 'yith-slider-for-page-builders' ),
		'view_item'             => esc_html__( 'View Slide', 'yith-slider-for-page-builders' ),
		'view_items'            => esc_html__( 'View Slides', 'yith-slider-for-page-builders' ),
		'search_items'          => esc_html__( 'Search Slide', 'yith-slider-for-page-builders' ),
		'not_found'             => esc_html__( 'Slide not found', 'yith-slider-for-page-builders' ),
		'not_found_in_trash'    => esc_html__( 'Slide not found in Trash', 'yith-slider-for-page-builders' ),
		'insert_into_item'      => esc_html__( 'Insert into Slider', 'yith-slider-for-page-builders' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this Slide', 'yith-slider-for-page-builders' ),
		'items_list'            => esc_html__( 'Slides list', 'yith-slider-for-page-builders' ),
		'items_list_navigation' => esc_html__( 'Slides list navigation', 'yith-slider-for-page-builders' ),
		'filter_items_list'     => esc_html__( 'Filter Slides list', 'yith-slider-for-page-builders' ),
		'featured_image'        => esc_html__( 'Background Image', 'yith-slider-for-page-builders' ),
		'set_featured_image'    => esc_html__( 'Set background image', 'yith-slider-for-page-builders' ),
		'remove_featured_image' => esc_html_x( 'Remove background image', 'single slide background image', 'yith-slider-for-page-builders' ),
		'use_featured_image'    => esc_html_x( 'Use as background image', 'single slide background image', 'yith-slider-for-page-builders' ),
	);
	$args   = array(
		'label'               => esc_html__( 'Slide', 'yith-slider-for-page-builders' ),
		'description'         => esc_html__( 'Slide post type', 'yith-slider-for-page-builders' ),
		'labels'              => $labels,
		'show_in_rest'        => true,
		'supports'            => array( 'editor', 'thumbnail' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-slides',
		'show_in_admin_bar'   => false,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'yith_slide', $args );

}

add_action( 'init', 'yith_slider_generate_slide', 0 );


/**
 * Single slide metabox (used for hidden post meta)
 */
function yith_slider_for_page_builders_single_slide_add_meta() {
	add_meta_box(
		'single_slide_background_settings',
		esc_html__( 'Background Settings', 'yith-slider-for-page-builders' ),
		'yith_slider_for_page_builders_single_slide_background_settings_html',
		'yith_slide',
		'side',
		'default'
	);
	add_meta_box(
		'parent_id',
		esc_html__( 'Parent slider', 'yith-slider-for-page-builders' ),
		'yith_slider_for_page_builders_parent_slider_meta_html',
		'yith_slide',
		'side',
		'default'
	);

}

add_action( 'add_meta_boxes', 'yith_slider_for_page_builders_single_slide_add_meta' );

/**
 * Single slide parent slider metabox html
 *
 * @param object $post Single slide post object.
 *
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_parent_slider_meta_html( $post ) {
	wp_nonce_field( '_parent_slider_nonce', 'parent_slider_nonce' );

	$parent_slider = false;
	if ( isset( $_GET['parent_slider'] ) ) {
		$parent_slider = yith_slider_for_page_builders_get_valid_parent_slider_id( intval( wp_unslash( $_GET['parent_slider'] ) ) );
	} elseif ( $post->ID ) {
		$parent_slider = wp_get_post_parent_id( $post->ID );
	}
	?>

	<p>
		<?php if ( $parent_slider ) : ?>
			<input type="hidden" name="parent_id" id="parent_id" value="<?php echo esc_attr( $parent_slider ); ?>">
			<a href="<?php echo esc_url( get_edit_post_link( $parent_slider ) ); ?>"><?php echo esc_html( get_the_title( $parent_slider ) ); ?></a>

		<?php else : ?>
			<input type="hidden" name="parent_id" id="parent_id" value="<?php echo esc_attr( wp_get_post_parent_id( $post->ID ) ); ?>">
			<a href="<?php echo esc_url( get_edit_post_link( wp_get_post_parent_id( $post->ID ) ) ); ?>"><?php echo esc_html( get_the_title( wp_get_post_parent_id( $post->ID ) ) ); ?></a>
			<?php
		endif;
		?>
	</p>
	<?php
}

/**
 * Single slide background html
 *
 * @param object $post Single slide post object.
 *
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_single_slide_background_settings_html( $post ) {
	wp_nonce_field( '_single_slide_nonce', 'single_slide_nonce' );
	$background_size     = get_post_meta( $post->ID, 'single_slide_background_size', true );
	$background_position = get_post_meta( $post->ID, 'single_slide_background_position', true );
	$background_repeat   = get_post_meta( $post->ID, 'single_slide_background_repeat', true );
	$background_color    = get_post_meta( $post->ID, 'single_slide_background_color', true );
	?>
	<p>
		<label for="single_slide_background_size"><?php esc_html_e( 'Background size', 'yith-slider-for-page-builders' ); ?></label>
		<select name="single_slide_background_size" id="single_slide_background_size">
			<option value="cover" <?php selected( 'cover', $background_size ); ?>>
				cover
			</option>
			<option value="contain" <?php selected( 'contain', $background_size ); ?>>
				contain
			</option>
			<option value="original" <?php selected( 'original', $background_size ); ?>>
				original
			</option>
		</select>
	</p>
	<p>
		<label for="single_slide_background_position"><?php esc_html_e( 'Background position', 'yith-slider-for-page-builders' ); ?></label>
		<select name="single_slide_background_position" id="single_slide_background_position">
			<option value="center" <?php selected( 'center', $background_position ); ?>>
				center
			</option>
			<option value="left top" <?php selected( 'left top', $background_position ); ?>>
				left top
			</option>
			<option value="left center" <?php selected( 'left center', $background_position ); ?>>
				left center
			</option>
			<option value="left bottom" <?php selected( 'left bottom', $background_position ); ?>>
				left bottom
			</option>
			<option value="right top" <?php selected( 'right top', $background_position ); ?>>
				right top
			</option>
			<option value="right center" <?php selected( 'right center', $background_position ); ?>>
				right center
			</option>
			<option value="right bottom" <?php selected( 'right bottom', $background_position ); ?>>
				right bottom
			</option>
			<option value="center top" <?php selected( 'center top', $background_position ); ?>>
				center top
			</option>
			<option value="center bottom" <?php selected( 'center bottom', $background_position ); ?>>
				center bottom
			</option>
		</select>
	</p>
	<p>
		<label for="single_slide_background_repeat"><?php esc_html_e( 'Background repeat', 'yith-slider-for-page-builders' ); ?></label>
		<select name="single_slide_background_repeat" id="single_slide_background_repeat">
			<option value="no-repeat" <?php selected( 'no-repeat', $background_repeat ); ?>>
				no-repeat
			</option>
			<option value="repeat" <?php selected( 'repeat', $background_repeat ); ?>>
				repeat
			</option>
			<option value="repeat-x" <?php selected( 'repeat-x', $background_repeat ); ?>>
				repeat-x
			</option>
			<option value="repeat-y" <?php selected( 'repeat-y', $background_repeat ); ?>>
				repeat-y
			</option>
		</select>
	</p>
	<p>
		<label for="single_slide_background_color"><?php esc_html_e( 'background color', 'yith-slider-for-page-builders' ); ?></label>
		<input type="text" class="color-field" name="single_slide_background_color" id="single_slide_background_color" value="<?php echo esc_attr( $background_color ); ?>">
	</p>

	<?php
}

/**
 * This save functions is not used since we only save the post parent ID field and WP handles it for us
 *
 * @param int $post_id slide post id.
 *
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_parent_id_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['parent_slider_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['parent_slider_nonce'] ) ), '_parent_slider_nonce' ) ) {
		return;
	}

	if ( ! yith_slider_for_page_builders_can_save_post( $post_id, 'yith_slide' ) ) {
		return;
	}

	if ( ! isset( $_POST['parent_id'] ) ) {
		return;
	}

	$parent_id = yith_slider_for_page_builders_get_valid_parent_slider_id( intval( wp_unslash( $_POST['parent_id'] ) ) );
	if ( ! $parent_id ) {
		return;
	}

	remove_action( 'save_post', 'yith_slider_for_page_builders_parent_id_save' );
	wp_update_post(
		array(
			'ID'          => $post_id,
			'post_parent' => $parent_id,
		)
	);
	add_action( 'save_post', 'yith_slider_for_page_builders_parent_id_save' );
}

add_action( 'save_post', 'yith_slider_for_page_builders_parent_id_save' );

/**
 * Save background settings shared by sliders and slides.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function yith_slider_for_page_builders_save_background_settings( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['single_slide_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['single_slide_nonce'] ) ), '_single_slide_nonce' ) ) {
		return;
	}

	if ( ! yith_slider_for_page_builders_can_save_slider_or_slide( $post_id ) ) {
		return;
	}

	if ( isset( $_POST['single_slide_background_size'] ) ) {
		update_post_meta(
			$post_id,
			'single_slide_background_size',
			yith_slider_for_page_builders_sanitize_background_size( sanitize_text_field( wp_unslash( $_POST['single_slide_background_size'] ) ) )
		);
	}

	if ( isset( $_POST['single_slide_background_position'] ) ) {
		update_post_meta(
			$post_id,
			'single_slide_background_position',
			yith_slider_for_page_builders_sanitize_background_position( sanitize_text_field( wp_unslash( $_POST['single_slide_background_position'] ) ) )
		);
	}

	if ( isset( $_POST['single_slide_background_repeat'] ) ) {
		update_post_meta(
			$post_id,
			'single_slide_background_repeat',
			yith_slider_for_page_builders_sanitize_background_repeat( sanitize_text_field( wp_unslash( $_POST['single_slide_background_repeat'] ) ) )
		);
	}

	if ( isset( $_POST['single_slide_background_color'] ) ) {
		update_post_meta(
			$post_id,
			'single_slide_background_color',
			yith_slider_for_page_builders_sanitize_background_color( wp_unslash( $_POST['single_slide_background_color'] ) )
		);
	}
}

/**
 * Save single slide function
 *
 * @param int $post_id Single slide post ID.
 *
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_for_page_builders_single_slide_save_meta( $post_id ) {
	yith_slider_for_page_builders_save_background_settings( $post_id );

	if ( ! yith_slider_for_page_builders_can_save_post( $post_id, 'yith_slide' ) ) {
		return;
	}

	if ( '' === get_post_meta( $post_id, 'slide_order', true ) ) {
		update_post_meta( $post_id, 'slide_order', '999' );
	}
}

add_action( 'save_post', 'yith_slider_for_page_builders_single_slide_save_meta' );


/**
 * Add shortcode column to all sliders list table
 *
 * @param array $columns Table columns.
 *
 * @return mixed
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function set_yith_slider_posts_column( $columns ) {
	$columns['shortcode'] = esc_html__( 'Shortcode', 'yith-slider-for-page-builders' );

	return $columns;
}

add_filter( 'manage_yith_slider_posts_columns', 'set_yith_slider_posts_column' );


/**
 * Move shortcode column before date column
 *
 * @param array $columns Table columns.
 *
 * @return array
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_column_order( $columns ) {
	$n_columns = array();
	$before    = 'date'; // move before this.

	foreach ( $columns as $key => $value ) {
		if ( $key == $before ) {
			$n_columns['shortcode'] = '';
		}
		$n_columns[ $key ] = $value;
	}

	return $n_columns;

}

add_filter( 'manage_yith_slider_posts_columns', 'yith_slider_column_order' );


/**
 * Add shortcode column to help using sliders
 *
 * @param array $column Table columns.
 * @param int   $post_id Slider ID.
 *
 * @author Francesco Grasso <francgrasso@yithemes.com>
 */
function yith_slider_column( $column, $post_id ) {
	switch ( $column ) {
		case 'shortcode':
			echo '[yith-slider slider="' . esc_attr( $post_id ) . '"]';
			break;

	}
}

add_action( 'manage_yith_slider_posts_custom_column', 'yith_slider_column', 10, 2 );


add_action( 'trashed_post', 'yith_slider_for_page_builders_redirect_to_slider_after_slide_delete', 10, 1 );

/**
 * Redirect to parent slider after slide deletion
 *
 * @param int $slide_id ID of the slide that will be deleted.
 *
 * @return void
 */
function yith_slider_for_page_builders_redirect_to_slider_after_slide_delete( $slide_id ) {

	$current_post_type = get_post_type( $slide_id );

	if ( $current_post_type && 'yith_slide' === $current_post_type ) {
		$parent_slider_id = wp_get_post_parent_id( $slide_id );
		$redirect_url     = admin_url( 'post.php?post=' . $parent_slider_id ) . '&action=edit';

		wp_safe_redirect( $redirect_url );
		exit();
	}
}

add_action( 'admin_init', 'yith_slider_for_page_builders_redirect_to_sliders_manage_page' );

/**
 * Redirect to sliders post type page if needed
 *
 * @return void
 */
function yith_slider_for_page_builders_redirect_to_sliders_manage_page() {
	global $pagenow;
	if ( 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'yith_slide' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=yith_slider' ) );
		exit();
	}
}


/**
 * Duplicate slide callback function
 *
 * @return void
 */
function yith_slider_duplicate_slide() {
	if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) ) ) {
		wp_die( esc_html__( 'No post to duplicate has been found!', 'yith-slider-for-page-builders' ) );
	}

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] );

	if ( ! $post_id ) {
		wp_die( esc_html__( 'No post to duplicate has been found!', 'yith-slider-for-page-builders' ) );
	}

	check_admin_referer( 'yith_slider_duplicate_slide_' . $post_id );

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'yith-slider-for-page-builders' ) );
	}

	$post = get_post( $post_id );

	if ( ! $post || 'yith_slide' !== $post->post_type ) {
		wp_die( esc_html__( 'Post creation failed, could not find original post', 'yith-slider-for-page-builders' ) );
	}

	// Set post author.
	$current_user    = wp_get_current_user();
	$new_post_author = $current_user->ID;

	$args = array(
		'comment_status' => $post->comment_status,
		'ping_status'    => $post->ping_status,
		'post_author'    => $new_post_author,
		'post_content'   => $post->post_content,
		'post_excerpt'   => $post->post_excerpt,
		'post_name'      => $post->post_name,
		'post_parent'    => $post->post_parent,
		'post_password'  => $post->post_password,
		'post_status'    => 'publish',
		'post_title'     => $post->post_title,
		'post_type'      => $post->post_type,
		'to_ping'        => $post->to_ping,
		'menu_order'     => $post->menu_order,
	);

	// Insert the post.
	$new_post_id = wp_insert_post( $args );

	// Duplicate all post meta.
	$post_meta = get_post_meta( $post_id );
	if ( is_array( $post_meta ) ) {
		foreach ( $post_meta as $meta_key => $meta_values ) {
			if ( '_wp_old_slug' === $meta_key ) {
				continue;
			}
			foreach ( $meta_values as $meta_value ) {
				update_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}
	}
	wp_cache_delete( $new_post_id );

	// Redirect to the post edit screen.
	wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . wp_get_post_parent_id( $new_post_id ) ) );

	exit;
}
add_action( 'admin_action_yith_slider_duplicate_slide', 'yith_slider_duplicate_slide' );

/**
 * Retrieve a list of saved sliders
 */
function yith_slider_for_page_builders_get_sliders_list_array() {

	$all_sliders = get_posts(
		array(
			'post_type'   => 'yith_slider',
			'post_status' => 'publish',
			'numberposts' => -1,
			'order'       => 'DESC',
		)
	);

	$sliders_array = array();

	foreach ( $all_sliders as $slider ) {
		$sliders_array[] = array(
			'label' => esc_html( $slider->post_title ),
			'value' => (string) $slider->ID,
		);
	}

	return $sliders_array;
}
