import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Status bar block editor.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<div { ...useBlockProps( { className: 'juanto-status is-editing' } ) }>
			{ __( 'Now building: edit it in Settings → juanto.', 'juanto' ) }
		</div>
	);
}
