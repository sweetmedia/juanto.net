<?php
class MediaTest extends WP_UnitTestCase {
	public function test_jpeg_and_png_sub_sizes_map_to_webp_gif_is_untouched() {
		// This environment's GD build can write WebP (confirmed: imagewebp() exists,
		// imageavif() does not), so the real wp_image_editor_supports() check passes here
		// exactly as it would on the live site.
		$mapped = juanto_image_editor_output_format( [ 'image/gif' => 'image/gif' ] );
		$this->assertSame( 'image/webp', $mapped['image/jpeg'] );
		$this->assertSame( 'image/webp', $mapped['image/png'] );
		$this->assertSame( 'image/gif', $mapped['image/gif'] );
	}
}
