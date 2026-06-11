( function( blocks, element, i18n, blockEditor, components, serverSideRender ) {
	const { registerBlockType } = blocks;
	const { createElement } = element;
	const { __ } = i18n;
	const { InspectorControls } = blockEditor;
	const { SelectControl, PanelBody } = components;
	const ServerSideRender = serverSideRender;

	const slidersData = window.yith_slider_for_page_builders_block_localized_array || {};
	const sliderOptions = [
		{
			label: __( 'Select a slider', 'yith-slider-for-page-builders' ),
			value: '',
		},
	].concat(
		Array.isArray( slidersData.slidersArray )
			? slidersData.slidersArray.map( function( item ) {
				return {
					label: item.label,
					value: String( item.value ),
				};
			} )
			: []
	);

	registerBlockType( 'yith-slider-for-page-builders/slider-block', {
		title: __( 'YITH Slider for page builders', 'yith-slider-for-page-builders' ),
		category: 'media',
		attributes: {
			slider: {
				type: 'string',
				default: '',
			},
		},
		icon: 'cover-image',
		edit( props ) {
			const attributes = props.attributes;
			const setAttributes = props.setAttributes;

			function changeId( slider ) {
				setAttributes( { slider: String( slider ) } );
			}

			return createElement(
				'div',
				{},
				createElement( ServerSideRender, {
					block: 'yith-slider-for-page-builders/slider-block',
					attributes: attributes,
					key: 'preview',
				} ),
				createElement(
					InspectorControls,
					{ key: 'inspector' },
					createElement(
						PanelBody,
						{
							title: __( 'Slider settings', 'yith-slider-for-page-builders' ),
							initialOpen: true,
						},
						createElement( SelectControl, {
							value: attributes.slider,
							options: sliderOptions,
							label: __( 'Slider to show', 'yith-slider-for-page-builders' ),
							onChange: changeId,
						} )
					)
				)
			);
		},
		save() {
			return null;
		},
	} );
}(
	window.wp.blocks,
	window.wp.element,
	window.wp.i18n,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.serverSideRender
) );
