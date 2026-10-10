import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

addFilter(
	'blocks.registerBlockType',
	'juanto/code-filename',
	( settings, name ) =>
		name !== 'core/code'
			? settings
			: {
					...settings,
					attributes: {
						...settings.attributes,
						filename: { type: 'string', default: '' },
					},
			  }
);

const withFilename = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( props.name !== 'core/code' ) {
			return <BlockEdit { ...props } />;
		}
		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'File', 'juanto' ) }>
						<TextControl
							label={ __( 'File name or language', 'juanto' ) }
							help={ __(
								'Shown as a tab above the code, for example functions.php or bash.',
								'juanto'
							) }
							value={ props.attributes.filename || '' }
							onChange={ ( filename ) =>
								props.setAttributes( { filename } )
							}
							__nextHasNoMarginBottom
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	},
	'withFilename'
);
addFilter( 'editor.BlockEdit', 'juanto/code-filename', withFilename );
