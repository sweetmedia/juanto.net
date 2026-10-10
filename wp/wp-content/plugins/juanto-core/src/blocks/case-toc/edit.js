import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Case study contents block editor.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<nav { ...useBlockProps( { className: 'juanto-toc is-editing' } ) }>
			{ __( 'Contents: built from the Section head blocks.', 'juanto' ) }
		</nav>
	);
}
