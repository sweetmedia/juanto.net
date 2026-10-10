<?php
/**
 * Project and tutorial sidebar panels, code block file names.
 *
 * @package juanto-core
 */

add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( ! in_array( get_current_screen()?->post_type, [ 'project', 'post' ], true ) ) {
			return;
		}
		$asset = include JUANTO_CORE_DIR . 'build/editor/index.asset.php';
		wp_enqueue_script( 'juanto-editor', JUANTO_CORE_URL . 'build/editor/index.js', $asset['dependencies'], $asset['version'], true );
		$tutorials = get_category_by_slug( 'tutorials' );
		wp_add_inline_script( 'juanto-editor', 'window.juantoEditor = ' . wp_json_encode( [ 'tutorialsCategory' => $tutorials ? (int) $tutorials->term_id : 0 ] ) . ';', 'before' );
	}
);
