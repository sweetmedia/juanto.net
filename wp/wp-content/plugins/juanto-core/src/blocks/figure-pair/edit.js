import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * A single figure slot: image picker plus bold lead and caption fields.
 *
 * @param {Object}   root0          Slot props.
 * @param {Object}   root0.item     The figure item ({ id, title, caption }).
 * @param {Function} root0.onChange Called with the updated item.
 * @return {Element} The figure slot editor.
 */
function Slot( { item = {}, onChange } ) {
	const url = useSelect(
		( select ) =>
			item.id ? select( 'core' ).getMedia( item.id )?.source_url : null,
		[ item.id ]
	);
	return (
		<div className="juanto-figure-pair__slot">
			{ url && <img src={ url } alt="" /> }
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ item.id }
					onSelect={ ( media ) =>
						onChange( { ...item, id: media.id } )
					}
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
				label={ __( 'Bold lead', 'juanto' ) }
				value={ item.title ?? '' }
				onChange={ ( title ) => onChange( { ...item, title } ) }
				__nextHasNoMarginBottom
			/>
			<TextControl
				label={ __( 'Caption', 'juanto' ) }
				value={ item.caption ?? '' }
				onChange={ ( caption ) => onChange( { ...item, caption } ) }
				__nextHasNoMarginBottom
			/>
		</div>
	);
}

/**
 * Figure pair block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const items = [ attributes.items[ 0 ] ?? {}, attributes.items[ 1 ] ?? {} ];
	const set = ( i ) => ( v ) =>
		setAttributes( {
			items: items.map( ( it, j ) => ( j === i ? v : it ) ),
		} );
	return (
		<div
			{ ...useBlockProps( {
				className: 'juanto-figure-pair is-editing',
			} ) }
		>
			<Slot item={ items[ 0 ] } onChange={ set( 0 ) } />
			<Slot item={ items[ 1 ] } onChange={ set( 1 ) } />
		</div>
	);
}
