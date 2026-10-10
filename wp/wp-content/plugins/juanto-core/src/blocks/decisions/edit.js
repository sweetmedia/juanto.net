import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../_shared/rows';

/**
 * Decisions block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div
			{ ...useBlockProps( { className: 'juanto-decisions is-editing' } ) }
		>
			<RowsEditor
				rows={ attributes.rows }
				fields={ [
					{ key: 'title', label: __( 'Decision', 'juanto' ) },
					{
						key: 'text',
						label: __( 'Why', 'juanto' ),
						multiline: true,
					},
				] }
				onChange={ ( rows ) => setAttributes( { rows } ) }
				addLabel={ __( 'Add decision', 'juanto' ) }
			/>
		</div>
	);
}
