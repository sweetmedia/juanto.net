import { useBlockProps } from '@wordpress/block-editor';
import { TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Lab teaser block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div
			{ ...useBlockProps( {
				className: 'juanto-lab-teaser is-editing',
			} ) }
		>
			<TextControl
				label={ __( 'Title', 'juanto' ) }
				value={ attributes.title }
				onChange={ ( title ) => setAttributes( { title } ) }
				__nextHasNoMarginBottom
			/>
			<TextareaControl
				label={ __( 'Text', 'juanto' ) }
				value={ attributes.text }
				onChange={ ( text ) => setAttributes( { text } ) }
				__nextHasNoMarginBottom
			/>
		</div>
	);
}
