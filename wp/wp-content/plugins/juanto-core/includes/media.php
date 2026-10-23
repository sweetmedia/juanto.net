<?php
/**
 * Media delivery: WebP sub-sizes (spec §4.7: AVIF/WebP, not plain JPEG/PNG),
 * lazy images after the lead one, and autoplay clips that wait for the viewport.
 *
 * @package juanto-core
 */

/**
 * Maps JPEG/PNG sources to WebP output for every generated sub-size.
 *
 * Skips AVIF: this environment's GD build has no AVIF encoder. Guarded so it
 * only applies where the image editor can actually write WebP.
 *
 * @param array<string, string> $output_format Source mime type => destination mime type.
 */
function juanto_image_editor_output_format( array $output_format ): array {
	if ( ! wp_image_editor_supports( [ 'mime_type' => 'image/webp' ] ) ) {
		return $output_format;
	}
	$output_format['image/jpeg'] = 'image/webp';
	$output_format['image/png']  = 'image/webp';
	return $output_format;
}
add_filter( 'image_editor_output_format', 'juanto_image_editor_output_format' );

/**
 * Loading attributes for the next reveal image on the page: none for the first
 * (it is often the LCP candidate, so core decides), lazy for every one after it.
 * Core's own content-image counter does not see images a dynamic block renders,
 * so without this every case screenshot downloads at load and competes with the
 * fonts the LCP text is waiting for.
 *
 * @param bool $reset Start counting again (tests, or a second loop on one page).
 * @return array<string, string> Attributes for wp_get_attachment_image().
 */
function juanto_reveal_image_loading_attrs( bool $reset = false ): array {
	static $rendered = 0;
	if ( $reset ) {
		$rendered = 0;
		return [];
	}
	return 0 === $rendered++ ? [] : [ 'loading' => 'lazy', 'fetchpriority' => 'auto' ];
}

add_action(
	'init',
	static function () {
		$asset_file = JUANTO_CORE_DIR . 'build/view/video-autoplay.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = include $asset_file;
		wp_register_script( 'juanto-video-autoplay', JUANTO_CORE_URL . 'build/view/video-autoplay.js', $asset['dependencies'], $asset['version'], [ 'in_footer' => true, 'strategy' => 'defer' ] );
	}
);

/**
 * Swaps a video block's `autoplay` for preload="none" + controls and marks it
 * for video-autoplay.js, which plays it only near the viewport. `autoplay`
 * makes the browser download the whole clip at load, even far below the fold.
 *
 * @param string $html Rendered video block.
 */
function juanto_video_block_markup( string $html ): string {
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( ! $tags->next_tag( 'video' ) || null === $tags->get_attribute( 'autoplay' ) ) {
		return $html;
	}
	$tags->remove_attribute( 'autoplay' );
	$tags->set_attribute( 'preload', 'none' );
	$tags->set_attribute( 'controls', true );
	$tags->set_attribute( 'data-juanto-autoplay', true );
	wp_enqueue_script( 'juanto-video-autoplay' );
	return $tags->get_updated_html();
}
add_filter( 'render_block_core/video', 'juanto_video_block_markup' );
