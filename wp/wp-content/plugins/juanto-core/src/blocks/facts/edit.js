import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../_shared/rows';

/**
 * Facts block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps( { className: 'juanto-facts is-editing' } ) }>
			<RowsEditor
				rows={ attributes.rows }
				fields={ [
					{ key: 'label', label: __( 'Label', 'juanto' ) },
					{ key: 'value', label: __( 'Value', 'juanto' ) },
				] }
				onChange={ ( rows ) => setAttributes( { rows } ) }
				addLabel={ __( 'Add fact', 'juanto' ) }
			/>
		</div>
	);
}
