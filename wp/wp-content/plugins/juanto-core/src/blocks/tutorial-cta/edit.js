import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Tutorial call to action placeholder.
 *
 * @return {Element} The block editor UI.
 */
export default function Edit() {
	return (
		<aside
			{ ...useBlockProps( { className: 'juanto-tut-cta is-editing' } ) }
		>
			{ __(
				'Call to action: depends on "Who is it for?" in the Tutorial panel.',
				'juanto'
			) }
		</aside>
	);
}
