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

	public function test_autoplay_videos_wait_for_the_viewport_instead_of_downloading_at_load() {
		$html = render_block(
			parse_blocks( '<!-- wp:video --><figure class="wp-block-video"><video autoplay loop muted poster="p.webp" src="clip.mp4" playsinline></video></figure><!-- /wp:video -->' )[0]
		);
		$this->assertDoesNotMatchRegularExpression( '#<video[^>]*\sautoplay#', $html, 'autoplay forces a full download at load' );
		$this->assertMatchesRegularExpression( '#<video[^>]*\spreload="none"#', $html );
		$this->assertMatchesRegularExpression( '#<video[^>]*\sdata-juanto-autoplay#', $html );
		$this->assertMatchesRegularExpression( '#<video[^>]*\scontrols#', $html, 'without JS the visitor can still play it' );
		$this->assertMatchesRegularExpression( '#<video[^>]*\smuted#', $html );
		$this->assertMatchesRegularExpression( '#<video[^>]*\sposter="p.webp"#', $html );
		$this->assertTrue( wp_script_is( 'juanto-video-autoplay', 'enqueued' ) );
	}

	public function test_videos_without_autoplay_are_left_alone() {
		wp_dequeue_script( 'juanto-video-autoplay' );
		$source = '<!-- wp:video --><figure class="wp-block-video"><video controls src="talk.mp4"></video></figure><!-- /wp:video -->';
		$html   = render_block( parse_blocks( $source )[0] );
		$this->assertStringNotContainsString( 'data-juanto-autoplay', $html );
		$this->assertStringNotContainsString( 'preload="none"', $html );
		$this->assertFalse( wp_script_is( 'juanto-video-autoplay', 'enqueued' ) );
	}
}
