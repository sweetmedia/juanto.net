<?php
/**
 * Registers every built block. All blocks are dynamic.
 *
 * @package juanto-core
 */

/**
 * Registers build/blocks/<name>/block.json.
 */
function juanto_register_blocks(): void {
	foreach ( (array) glob( JUANTO_CORE_DIR . 'build/blocks/*/block.json' ) as $json ) {
		register_block_type( dirname( $json ) );
	}
}
add_action( 'init', 'juanto_register_blocks' );

/**
 * Renders a block by name with attributes.
 *
 * @param string $name  Block name.
 * @param array  $attrs Attributes.
 */
function juanto_render_block_markup( string $name, array $attrs ): string {
	return trim( render_block( [ 'blockName' => $name, 'attrs' => $attrs, 'innerBlocks' => [], 'innerHTML' => '', 'innerContent' => [] ] ) );
}

/**
 * TOC entries from every juanto/section-head, nested blocks included.
 *
 * @param string $content Post content.
 */
function juanto_toc_items( string $content ): array {
	$items = [];
	$walk  = static function ( array $blocks ) use ( &$walk, &$items ) {
		foreach ( $blocks as $b ) {
			if ( 'juanto/section-head' === $b['blockName'] ) {
				$label   = (string) ( $b['attrs']['label'] ?? '' );
				$anchor  = (string) ( $b['attrs']['anchor'] ?? '' );
				$items[] = [
					'anchor' => sanitize_title( '' !== $anchor ? $anchor : $label ),
					'number' => (string) ( $b['attrs']['number'] ?? '' ),
					'label'  => $label,
				];
			}
			if ( ! empty( $b['innerBlocks'] ) ) {
				$walk( $b['innerBlocks'] );
			}
		}
	};
	$walk( parse_blocks( $content ) );
	return $items;
}

/**
 * Hex or oklch() colors only.
 *
 * @param string $value Raw color.
 */
function juanto_sanitize_css_color( string $value ): string {
	$value = trim( $value );
	if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $value ) || preg_match( '/^oklch\(\s*[\d.]+%?\s+[\d.]+\s+[\d.]+(\s*\/\s*[\d.]+%?)?\s*\)$/', $value ) ) {
		return $value;
	}
	return '';
}

/**
 * 1-based position among case studies, zero-padded.
 *
 * @param int $post_id Project ID.
 */
function juanto_case_number( int $post_id ): string {
	$ids = wp_list_pluck( juanto_get_projects( 'case' ), 'ID' );
	$at  = array_search( $post_id, $ids, true );
	return false === $at ? '' : str_pad( (string) ( $at + 1 ), 2, '0', STR_PAD_LEFT );
}

add_filter(
	'upload_mimes',
	static function ( array $mimes ): array {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['woff2'] = 'font/woff2';
		}
		return $mimes;
	}
);

/**
 * Hides the header's "Résumé" nav-link item when no résumé URL is
 * configured, rather than linking to a /resume.pdf that just 404s.
 *
 * @param string $block_content Rendered nav-link markup.
 * @param array  $block         Parsed block, including its attrs.
 */
function juanto_hide_empty_resume_nav_link( string $block_content, array $block ): string {
	if ( '/resume.pdf' !== ( $block['attrs']['url'] ?? '' ) ) {
		return $block_content;
	}
	return '' !== juanto_get_status()['resume_url'] ? $block_content : '';
}
add_filter( 'render_block_core/navigation-link', 'juanto_hide_empty_resume_nav_link', 10, 2 );
