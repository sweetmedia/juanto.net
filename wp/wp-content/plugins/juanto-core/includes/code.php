<?php
/**
 * Code blocks: an optional file-name tab and a Copy button (progressive enhancement).
 *
 * @package juanto-core
 */

add_filter(
	'register_block_type_args',
	static function ( array $args, string $name ): array {
		if ( 'core/code' === $name ) {
			$args['attributes']['filename'] = [ 'type' => 'string', 'default' => '' ];
		}
		return $args;
	},
	10,
	2
);

add_action(
	'init',
	static function () {
		$asset_file = JUANTO_CORE_DIR . 'build/view/code-copy.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = include $asset_file;
		wp_register_script( 'juanto-code-copy', JUANTO_CORE_URL . 'build/view/code-copy.js', $asset['dependencies'], $asset['version'], [ 'in_footer' => true, 'strategy' => 'defer' ] );
	}
);

/**
 * Wraps every code block (the Copy button attaches to the wrapper) and adds the file tab.
 *
 * @param string $html  Rendered block.
 * @param array  $block Parsed block.
 */
function juanto_code_block_markup( string $html, array $block ): string {
	wp_enqueue_script( 'juanto-code-copy' );
	$file = trim( (string) ( $block['attrs']['filename'] ?? '' ) );
	if ( '' === $file ) {
		return '<div class="juanto-code">' . trim( $html ) . '</div>';
	}
	return '<div class="juanto-code has-file"><p class="juanto-code__file">' . esc_html( $file ) . '</p>' . trim( $html ) . '</div>';
}
add_filter( 'render_block_core/code', 'juanto_code_block_markup', 10, 2 );
