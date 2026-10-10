import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../_shared/rows';

/**
 * Step track block editor.
 *
 * @param {Object}   root0               Block edit props.
 * @param {Object}   root0.attributes    Block attributes.
 * @param {Function} root0.setAttributes Updates block attributes.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const num = ( key, label ) => (
		<TextControl
			type="number"
			label={ label }
			value={ attributes[ key ] || '' }
			onChange={ ( v ) =>
				setAttributes( { [ key ]: parseInt( v, 10 ) || 0 } )
			}
			__nextHasNoMarginBottom
		/>
	);
	return (
		<div { ...useBlockProps( { className: 'juanto-steps is-editing' } ) }>
			<TextControl
				label={ __( 'Caption', 'juanto' ) }
				value={ attributes.caption }
				onChange={ ( caption ) => setAttributes( { caption } ) }
				__nextHasNoMarginBottom
			/>
			{ num( 'steps', __( 'Number of steps', 'juanto' ) ) }
			<p>
				<strong>{ __( 'Failed attempts', 'juanto' ) }</strong>
			</p>
			<RowsEditor
				rows={ attributes.fails }
				fields={ [
					{ key: 'step', label: __( 'Step', 'juanto' ) },
					{
						key: 'count',
						label: __( 'Times rejected', 'juanto' ),
					},
				] }
				onChange={ ( fails ) => setAttributes( { fails } ) }
				addLabel={ __( 'Add failed step', 'juanto' ) }
			/>
			{ num( 'bracketFrom', __( 'Bracket from step', 'juanto' ) ) }
			{ num( 'bracketTo', __( 'Bracket to step', 'juanto' ) ) }
			<TextControl
				label={ __( 'Bracket label', 'juanto' ) }
				value={ attributes.bracketLabel }
				onChange={ ( bracketLabel ) =>
					setAttributes( { bracketLabel } )
				}
				__nextHasNoMarginBottom
			/>
			<p>
				<strong>{ __( 'Footer', 'juanto' ) }</strong>
			</p>
			<RowsEditor
				rows={ attributes.foot }
				fields={ [
					{ key: 'label', label: __( 'Label', 'juanto' ) },
					{ key: 'value', label: __( 'Value', 'juanto' ) },
				] }
				onChange={ ( foot ) => setAttributes( { foot } ) }
				addLabel={ __( 'Add footer item', 'juanto' ) }
			/>
		</div>
	);
}
