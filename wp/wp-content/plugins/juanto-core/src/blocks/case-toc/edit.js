import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Contents block editor.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Attributes.
 * @param {Function} props.setAttributes Setter.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const steps = attributes.source === 'tutorial-step';
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Contents', 'juanto' ) }>
					<SelectControl
						label={ __( 'Build from', 'juanto' ) }
						value={ attributes.source }
						options={ [
							{
								label: __( 'Section heads', 'juanto' ),
								value: 'section-head',
							},
							{
								label: __( 'Tutorial steps', 'juanto' ),
								value: 'tutorial-step',
							},
						] }
						onChange={ ( source ) => setAttributes( { source } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<nav { ...useBlockProps( { className: 'juanto-toc is-editing' } ) }>
				{ steps
					? __( 'Contents: built from the tutorial steps.', 'juanto' )
					: __(
							'Contents: built from the Section head blocks.',
							'juanto'
					  ) }
			</nav>
		</>
	);
}
