import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

/**
 * Project list block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'List', 'juanto' ) }>
					<SelectControl
						label={ __( 'Tier', 'juanto' ) }
						value={ attributes.tier }
						options={ [
							{ label: 'Case studies', value: 'case' },
							{ label: 'Project cards', value: 'card' },
						] }
						onChange={ ( tier ) => setAttributes( { tier } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Layout', 'juanto' ) }
						value={ attributes.layout }
						options={ [
							{ label: 'Rows', value: 'rows' },
							{ label: 'Two columns', value: 'columns' },
						] }
						onChange={ ( layout ) => setAttributes( { layout } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Label', 'juanto' ) }
						value={ attributes.label }
						onChange={ ( label ) => setAttributes( { label } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block="juanto/project-list"
				attributes={ attributes }
			/>
		</div>
	);
}
