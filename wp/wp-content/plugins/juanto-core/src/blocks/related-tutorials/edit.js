import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Related tutorials placeholder.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<section
			{ ...useBlockProps( { className: 'juanto-related is-editing' } ) }
		>
			{ __( 'Related tutorials (built automatically).', 'juanto' ) }
		</section>
	);
}
