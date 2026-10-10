import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Section head block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const field = ( key, label ) => (
		<TextControl
			label={ label }
			value={ attributes[ key ] }
			onChange={ ( v ) => setAttributes( { [ key ]: v } ) }
			__nextHasNoMarginBottom
		/>
	);
	return (
		<div
			{ ...useBlockProps( {
				className: 'juanto-section-head is-editing',
			} ) }
		>
			{ field( 'number', __( 'Number (01)', 'juanto' ) ) }
			{ field( 'label', __( 'Label (the problem)', 'juanto' ) ) }
			{ field( 'title', __( 'Title', 'juanto' ) ) }
			{ field( 'anchor', __( 'Anchor (optional)', 'juanto' ) ) }
		</div>
	);
}
