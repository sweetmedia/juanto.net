<?php
/**
 * Generated image sub-sizes as WebP (spec §4.7: AVIF/WebP, not plain JPEG/PNG).
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
