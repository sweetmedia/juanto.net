import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { TextControl, RadioControl, Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { RowsEditor } from '../blocks/_shared/rows';
import { isTutorialPost } from '../lib/tutorial';

/**
 * The "Tutorial" document sidebar panel, shown only for posts in Tutorials.
 *
 * @return {Element|null} The panel.
 */
export function TutorialPanel() {
	const { postType, categories } = useSelect(
		( s ) => ( {
			postType: s( 'core/editor' ).getCurrentPostType(),
			categories:
				s( 'core/editor' ).getEditedPostAttribute( 'categories' ),
		} ),
		[]
	);
	const [ meta, setMeta ] = useEntityProp( 'postType', 'post', 'meta' );
	const tutorialsId = window.juantoEditor?.tutorialsCategory ?? 0;
	if (
		postType !== 'post' ||
		! meta ||
		! isTutorialPost( categories, tutorialsId )
	) {
		return null;
	}
	const set = ( key ) => ( value ) => setMeta( { ...meta, [ key ]: value } );
	return (
		<PluginDocumentSettingPanel
			name="juanto-tutorial"
			title={ __( 'Tutorial', 'juanto' ) }
		>
			<TextControl
				type="number"
				min={ 0 }
				max={ 600 }
				label={ __( 'Minutes', 'juanto' ) }
				value={ meta.juanto_tut_minutes || '' }
				onChange={ ( v ) =>
					set( 'juanto_tut_minutes' )( parseInt( v, 10 ) || 0 )
				}
				__nextHasNoMarginBottom
			/>
			<p>
				<strong>{ __( 'Tools', 'juanto' ) }</strong>
			</p>
			<RowsEditor
				rows={ meta.juanto_tut_tools || [] }
				fields={ [
					{ key: 'name', label: __( 'Tool', 'juanto' ) },
					{
						key: 'version',
						label: __( 'Version (optional)', 'juanto' ),
					},
				] }
				onChange={ set( 'juanto_tut_tools' ) }
				addLabel={ __( 'Add tool', 'juanto' ) }
			/>
			<TextControl
				type="date"
				label={ __( 'Last tested', 'juanto' ) }
				value={ meta.juanto_tut_tested || '' }
				onChange={ set( 'juanto_tut_tested' ) }
				__nextHasNoMarginBottom
			/>
			<p>
				<strong>{ __( 'Before you start', 'juanto' ) }</strong>
			</p>
			<RowsEditor
				rows={ ( meta.juanto_tut_prereqs || [] ).map( ( text ) => ( {
					text,
				} ) ) }
				fields={ [
					{ key: 'text', label: __( 'Requirement', 'juanto' ) },
				] }
				onChange={ ( rows ) =>
					set( 'juanto_tut_prereqs' )( rows.map( ( r ) => r.text ) )
				}
				addLabel={ __( 'Add requirement', 'juanto' ) }
			/>
			<TextControl
				label={ __( "You'll end up with", 'juanto' ) }
				value={ meta.juanto_tut_result || '' }
				onChange={ set( 'juanto_tut_result' ) }
				__nextHasNoMarginBottom
			/>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ meta.juanto_tut_result_image }
					onSelect={ ( m ) =>
						set( 'juanto_tut_result_image' )( m.id )
					}
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ meta.juanto_tut_result_image
								? __( 'Change result image', 'juanto' )
								: __( 'Set result image', 'juanto' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ !! meta.juanto_tut_result_image && (
				<Button
					variant="link"
					isDestructive
					onClick={ () => set( 'juanto_tut_result_image' )( 0 ) }
				>
					{ __( 'Remove result image', 'juanto' ) }
				</Button>
			) }
			<TextControl
				type="url"
				label={ __( 'YouTube video URL', 'juanto' ) }
				value={ meta.juanto_tut_video || '' }
				onChange={ set( 'juanto_tut_video' ) }
				__nextHasNoMarginBottom
			/>
			<RadioControl
				label={ __( 'Who is it for?', 'juanto' ) }
				selected={ meta.juanto_tut_audience || 'builders' }
				options={ [
					{
						label: __(
							'Builders (links to Work, Lab, GitHub)',
							'juanto'
						),
						value: 'builders',
					},
					{
						label: __(
							'Clients (Sweet Media call to action)',
							'juanto'
						),
						value: 'clients',
					},
				] }
				onChange={ set( 'juanto_tut_audience' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}
