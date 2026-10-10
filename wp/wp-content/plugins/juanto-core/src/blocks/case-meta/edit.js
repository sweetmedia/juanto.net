import { useBlockProps } from '@wordpress/block-editor';
import { TextareaControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Case study header block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @param {Object}   root0.context       Block context.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes, context } ) {
	const title = useSelect(
		( s ) => s( 'core/editor' ).getEditedPostAttribute( 'title' ),
		// eslint-disable-next-line react-hooks/exhaustive-deps -- re-run when the block's postId context changes, even though the selector itself always reads the currently edited post.
		[ context.postId ]
	);
	return (
		<div
			{ ...useBlockProps( { className: 'juanto-case-meta is-editing' } ) }
		>
			<h1 className="juanto-case-meta__title">{ title }</h1>
			<TextareaControl
				label={ __(
					'Lede override (optional; by default the lede is the project excerpt)',
					'juanto'
				) }
				value={ attributes.lede }
				onChange={ ( lede ) => setAttributes( { lede } ) }
				__nextHasNoMarginBottom
			/>
			<p className="juanto-label">
				{ __(
					'Role, team, timeline, stack and links come from the Project panel.',
					'juanto'
				) }
			</p>
		</div>
	);
}
