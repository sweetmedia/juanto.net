import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import {
	TextControl,
	TextareaControl,
	SelectControl,
	Button,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../blocks/_shared/rows';
import { TutorialPanel } from './tutorial-panel';
import './code-filename';

/**
 * The "Project" document sidebar panel.
 *
 * @return {Element|null} The panel, or null outside the project post type.
 */
function ProjectPanel() {
	const postType = useSelect(
		( s ) => s( 'core/editor' ).getCurrentPostType(),
		[]
	);
	const [ meta, setMeta ] = useEntityProp( 'postType', 'project', 'meta' );
	if ( postType !== 'project' || ! meta ) {
		return null;
	}
	const set = ( key ) => ( value ) => setMeta( { ...meta, [ key ]: value } );
	const text = ( key, label ) => (
		<TextControl
			label={ label }
			value={ meta[ key ] ?? '' }
			onChange={ set( key ) }
			__nextHasNoMarginBottom
		/>
	);
	return (
		<PluginDocumentSettingPanel
			name="juanto-project"
			title={ __( 'Project', 'juanto' ) }
		>
			{ text( 'juanto_summary', __( 'One-line summary', 'juanto' ) ) }
			<TextControl
				type="number"
				label={ __( 'Start year', 'juanto' ) }
				value={ meta.juanto_year_start || '' }
				onChange={ ( v ) =>
					set( 'juanto_year_start' )( parseInt( v, 10 ) || 0 )
				}
				__nextHasNoMarginBottom
			/>
			{ text(
				'juanto_year_end',
				__( 'End year or "present"', 'juanto' )
			) }
			{ text( 'juanto_role', __( 'Role', 'juanto' ) ) }
			{ text( 'juanto_team', __( 'Team', 'juanto' ) ) }
			{ text( 'juanto_stack', __( 'Stack', 'juanto' ) ) }
			<p>
				<strong>{ __( 'Links', 'juanto' ) }</strong>
			</p>
			<RowsEditor
				rows={ meta.juanto_links || [] }
				fields={ [
					{ key: 'label', label: __( 'Label', 'juanto' ) },
					{ key: 'url', label: 'URL' },
				] }
				onChange={ set( 'juanto_links' ) }
				addLabel={ __( 'Add link', 'juanto' ) }
			/>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ meta.juanto_preview_image }
					onSelect={ ( m ) => set( 'juanto_preview_image' )( m.id ) }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ meta.juanto_preview_image
								? __( 'Change hover preview (4:3)', 'juanto' )
								: __( 'Set hover preview (4:3)', 'juanto' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			<SelectControl
				label={ __( 'Lab framing', 'juanto' ) }
				value={ meta.juanto_lab_fit || 'cover' }
				options={ [
					{ label: 'Cover (photos)', value: 'cover' },
					{ label: 'Contain (logos)', value: 'contain' },
				] }
				onChange={ set( 'juanto_lab_fit' ) }
				__nextHasNoMarginBottom
			/>
			<TextareaControl
				label={ __( 'Lab note (pieces without an image)', 'juanto' ) }
				value={ meta.juanto_lab_note ?? '' }
				onChange={ set( 'juanto_lab_note' ) }
				__nextHasNoMarginBottom
			/>
		</PluginDocumentSettingPanel>
	);
}
registerPlugin( 'juanto-project', { render: ProjectPanel } );
registerPlugin( 'juanto-tutorial', { render: TutorialPanel } );
