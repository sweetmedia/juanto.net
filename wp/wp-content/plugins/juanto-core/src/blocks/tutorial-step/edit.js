import {
	useBlockProps,
	useInnerBlocksProps,
	PlainText,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { stepNumber } from '../../lib/tutorial';

const ALLOWED = [
	'core/paragraph',
	'core/image',
	'core/code',
	'core/list',
	'core/heading',
	'core/embed',
	'juanto/callout',
];
const TEMPLATE = [ [ 'core/paragraph' ] ];

/**
 * Tutorial step editor: live number, plain-text title, inner content.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Attributes.
 * @param {Function} props.setAttributes Setter.
 * @param {string}   props.clientId      Block client ID.
 * @return {Element} The block editor UI.
 */
export default function Edit( { attributes, setAttributes, clientId } ) {
	const n = useSelect(
		( select ) =>
			stepNumber(
				select( blockEditorStore ).getBlocksByName(
					'juanto/tutorial-step'
				),
				clientId
			),
		[ clientId ]
	);
	const blockProps = useBlockProps( { className: 'juanto-step' } );
	const innerProps = useInnerBlocksProps(
		{ className: 'juanto-step__body' },
		{ allowedBlocks: ALLOWED, template: TEMPLATE }
	);
	return (
		<section { ...blockProps }>
			<span className="juanto-step__num" aria-hidden="true">
				{ String( n ).padStart( 2, '0' ) }
			</span>
			<PlainText
				className="juanto-step__title"
				value={ attributes.title }
				onChange={ ( title ) => setAttributes( { title } ) }
				placeholder={ __( 'Step title', 'juanto' ) }
				aria-label={ __( 'Step title', 'juanto' ) }
			/>
			<div { ...innerProps } />
		</section>
	);
}
