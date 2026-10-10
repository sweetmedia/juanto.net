import { Button, TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Edits an array of flat objects.
 *
 * @param {Object}   root0          Rows editor parameters.
 * @param {Array}    root0.rows     Current rows.
 * @param {Array}    root0.fields   Field definitions: [{ key, label, multiline }].
 * @param {Function} root0.onChange Called with the updated rows.
 * @param {string}   root0.addLabel Label for the add button.
 * @return {Element} The rows editor.
 */
export function RowsEditor( {
	rows = [],
	fields,
	onChange,
	addLabel = __( 'Add row', 'juanto' ),
} ) {
	const update = ( i, key, value ) =>
		onChange(
			rows.map( ( r, j ) => ( j === i ? { ...r, [ key ]: value } : r ) )
		);
	const remove = ( i ) => onChange( rows.filter( ( _, j ) => j !== i ) );
	const add = () =>
		onChange( [
			...rows,
			Object.fromEntries( fields.map( ( f ) => [ f.key, '' ] ) ),
		] );
	return (
		<div className="juanto-rows">
			{ rows.map( ( row, i ) => (
				<fieldset key={ i } className="juanto-rows__row">
					{ fields.map( ( f ) => {
						const Control = f.multiline
							? TextareaControl
							: TextControl;
						return (
							<Control
								key={ f.key }
								label={ f.label }
								value={ row[ f.key ] ?? '' }
								onChange={ ( v ) => update( i, f.key, v ) }
								__nextHasNoMarginBottom
							/>
						);
					} ) }
					<Button
						variant="link"
						isDestructive
						onClick={ () => remove( i ) }
					>
						{ __( 'Remove', 'juanto' ) }
					</Button>
				</fieldset>
			) ) }
			<Button variant="secondary" onClick={ add }>
				{ addLabel }
			</Button>
		</div>
	);
}
