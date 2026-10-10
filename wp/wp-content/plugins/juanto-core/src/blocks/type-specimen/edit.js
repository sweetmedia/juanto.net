import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../_shared/rows';

/**
 * Type specimen block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div
			{ ...useBlockProps( {
				className: 'juanto-type-specimen is-editing',
			} ) }
		>
			<RowsEditor
				rows={ attributes.rows }
				fields={ [
					{ key: 'label', label: __( 'Role (display)', 'juanto' ) },
					{ key: 'family', label: __( 'Family name', 'juanto' ) },
					{ key: 'sample', label: __( 'Sample text', 'juanto' ) },
					{
						key: 'fontUrl',
						label: __(
							'.woff2 URL from the media library',
							'juanto'
						),
					},
				] }
				onChange={ ( rows ) => setAttributes( { rows } ) }
				addLabel={ __( 'Add typeface', 'juanto' ) }
			/>
		</div>
	);
}
