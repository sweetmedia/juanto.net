import { useBlockProps } from '@wordpress/block-editor';
import { TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * TL;DR block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const field = ( key, label ) => (
		<TextareaControl
			label={ label }
			value={ attributes[ key ] }
			onChange={ ( v ) => setAttributes( { [ key ]: v } ) }
			__nextHasNoMarginBottom
		/>
	);
	return (
		<div { ...useBlockProps( { className: 'juanto-tldr is-editing' } ) }>
			{ field( 'problem', __( 'Problem', 'juanto' ) ) }
			{ field( 'did', __( 'What I did', 'juanto' ) ) }
			{ field( 'outcome', __( 'Outcome', 'juanto' ) ) }
		</div>
	);
}
