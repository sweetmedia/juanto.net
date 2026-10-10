import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Lab stack block editor.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<ServerSideRender block="juanto/lab-stack" />
		</div>
	);
}
