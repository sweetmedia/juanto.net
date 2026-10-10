import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../_shared/rows';

/**
 * Swatches block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div
			{ ...useBlockProps( { className: 'juanto-swatches is-editing' } ) }
		>
			<RowsEditor
				rows={ attributes.rows }
				fields={ [
					{ key: 'name', label: __( 'Name', 'juanto' ) },
					{
						key: 'value',
						label: __( 'Color (#hex or oklch())', 'juanto' ),
					},
				] }
				onChange={ ( rows ) => setAttributes( { rows } ) }
				addLabel={ __( 'Add color', 'juanto' ) }
			/>
		</div>
	);
}
