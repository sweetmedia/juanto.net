import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Reveal image block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const url = useSelect(
		( select ) =>
			attributes.id
				? select( 'core' ).getMedia( attributes.id )?.source_url
				: null,
		[ attributes.id ]
	);
	return (
		<figure
			{ ...useBlockProps( { className: 'juanto-reveal is-editing' } ) }
		>
			{ url && <img src={ url } alt="" /> }
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ attributes.id }
					onSelect={ ( media ) => setAttributes( { id: media.id } ) }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ url
								? __( 'Replace image', 'juanto' )
								: __( 'Choose image', 'juanto' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			<TextControl
				label={ __( 'Caption (optional)', 'juanto' ) }
				value={ attributes.caption }
				onChange={ ( caption ) => setAttributes( { caption } ) }
				__nextHasNoMarginBottom
			/>
		</figure>
	);
}
