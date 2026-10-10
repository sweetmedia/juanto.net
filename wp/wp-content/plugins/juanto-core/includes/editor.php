<?php
/**
 * Project sidebar panel in the block editor.
 *
 * @package juanto-core
 */

add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( 'project' !== get_current_screen()?->post_type ) {
			return;
		}
		$asset = include JUANTO_CORE_DIR . 'build/editor/index.asset.php';
		wp_enqueue_script( 'juanto-editor', JUANTO_CORE_URL . 'build/editor/index.js', $asset['dependencies'], $asset['version'], true );
	}
);
