jQuery( function( $ ) {
	var sliderHeigth = yith_slider_for_page_builders_localized_array.sliderHeigth,
		editSliderHeightUrl = yith_slider_for_page_builders_localized_array.editSliderHeightUrl,
		sliderHeightString = yith_slider_for_page_builders_localized_array.sliderHeightString,
		editSliderHeightString = yith_slider_for_page_builders_localized_array.editSliderHeightString,
		changeSliderHeightMessage = yith_slider_for_page_builders_localized_array.changeSliderHeightMessage;

	var notice_message = sliderHeightString + ' ' + sliderHeigth + 'px.\n\n' + changeSliderHeightMessage;

	function getEditorStylesWrapper() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( iframe && iframe.contentDocument ) {
			return iframe.contentDocument.querySelector( '.editor-styles-wrapper' );
		}

		return document.querySelector( '.editor-styles-wrapper' );
	}

	function setEditorBackground( property, value ) {
		var editorWrapper = getEditorStylesWrapper();
		if ( editorWrapper ) {
			editorWrapper.style[ property ] = value;
		}
	}

	( function ( wp ) {
		wp.data.dispatch( 'core/notices' ).createNotice(
			'warning yith-slider-for-page-builders-height-notice',
			notice_message,
			{
				isDismissible: false,
				actions: [
					{
						url: editSliderHeightUrl,
						label: editSliderHeightString,
					},
				],
			}
		);
	} )( window.wp );


	( function( window, wp ){

		var link_id = 'back_to_parent_slider',
			link_html = '<a id="' + link_id + '" class="components-button is-tertiary" style="margin-left: 10px" href="'  + yith_slider_for_page_builders_localized_array.backToParentSliderLinkUrl +  '" >' + yith_slider_for_page_builders_localized_array.backToParentSliderLinkText + '</a>';

		// check if gutenberg's editor root element is present.
		var editorEl = document.getElementById( 'editor' );
		if( !editorEl ){ // do nothing if there's no gutenberg root element on page.
			return;
		}

		var unsubscribe = wp.data.subscribe( function () {
			setTimeout( function () {
				if ( !document.getElementById( link_id ) ) {
					var toolbalEl = editorEl.querySelector( '.edit-post-header__toolbar' );
					if( toolbalEl instanceof HTMLElement ){
						toolbalEl.insertAdjacentHTML( 'afterbegin', link_html );
					}
				}
			}, 1 )
		} );

	} )( window, wp );

	$('#single_slide_background_color').wpColorPicker({

		change: function (event, ui) {
			setEditorBackground( 'backgroundColor', ui.color.toString() );
		},

		clear: function (event) {
			setEditorBackground( 'backgroundColor', yith_slider_for_page_builders_localized_array.sliderBgColor || '' );
		}
	});

	$(document).ajaxComplete(function (event, xhr, settings) {
		var background_image = $('.editor-post-featured-image__preview').find('img').attr('src');
		setEditorBackground( 'backgroundImage', background_image ? 'url(' + background_image + ')' : 'none' );
	});

	$(document).on('click', '.editor-post-featured-image .is-destructive', function(){
		var sliderBgImage = yith_slider_for_page_builders_localized_array.sliderBgImage;
		setEditorBackground( 'backgroundImage', sliderBgImage ? 'url(' + sliderBgImage + ')' : 'none' );
	});

});
