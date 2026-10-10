import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Next case block editor.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<p { ...useBlockProps( { className: 'juanto-label' } ) }>
			{ __( 'Next case: picked automatically by order.', 'juanto' ) }
		</p>
	);
}
