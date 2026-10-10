import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Tutorial facts placeholder; the content comes from the Tutorial panel.
 *
 * @param {Object} props            Block props.
 * @param {Object} props.attributes Attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes } ) {
	return (
		<div
			{ ...useBlockProps( { className: 'juanto-tut-meta is-editing' } ) }
		>
			{ attributes.variant === 'badge'
				? __( 'Tutorial', 'juanto' )
				: __(
						'Tutorial facts: filled from the Tutorial panel.',
						'juanto'
				  ) }
		</div>
	);
}
