import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const LABELS = {
	tip: __( 'Tip', 'juanto' ),
	note: __( 'Note', 'juanto' ),
	warning: __( 'Warning', 'juanto' ),
};

/**
 * Callout editor.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Attributes.
 * @param {Function} props.setAttributes Setter.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes } ) {
	const variant = LABELS[ attributes.variant ] ? attributes.variant : 'tip';
	const blockProps = useBlockProps( {
		className: `juanto-callout is-${ variant }`,
	} );
	const innerProps = useInnerBlocksProps(
		{ className: 'juanto-callout__body' },
		{
			allowedBlocks: [ 'core/paragraph', 'core/list', 'core/code' ],
			template: [ [ 'core/paragraph' ] ],
		}
	);
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Callout', 'juanto' ) }>
					<SelectControl
						label={ __( 'Kind', 'juanto' ) }
						value={ variant }
						options={ Object.entries( LABELS ).map(
							( [ value, label ] ) => ( { value, label } )
						) }
						onChange={ ( v ) => setAttributes( { variant: v } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<aside { ...blockProps }>
				<p className="juanto-callout__label">{ LABELS[ variant ] }</p>
				<div { ...innerProps } />
			</aside>
		</>
	);
}
