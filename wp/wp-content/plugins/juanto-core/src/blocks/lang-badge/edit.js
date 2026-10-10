import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Language badge block editor.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<span { ...useBlockProps( { className: 'juanto-lang-badge' } ) }>
			{ __( 'EN', 'juanto' ) }
		</span>
	);
}
