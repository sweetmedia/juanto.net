<?php
class TutorialMigrationTest extends WP_UnitTestCase {
	private function fixture( string $name ): string {
		return (string) file_get_contents( __DIR__ . "/fixtures/{$name}.html" );
	}

	private function names( string $content ): array {
		return array_values( array_filter( array_column( parse_blocks( $content ), 'blockName' ) ) );
	}

	public function test_403_becomes_three_steps_without_the_manual_index() {
		$out   = juanto_migrate_tutorial_content(
			$this->fixture( 'tutorial-403' ),
			[
				'drop_embed'     => 'https://youtu.be/MLpWrANjFbI',
				'code_filenames' => [ 'download|true' => 'Custom attributes' ],
				'alts'           => [ 412 => 'WordPress media library with the file URL highlighted' ],
			]
		);
		$steps = juanto_tutorial_steps_from_content( $out );
		$this->assertSame( [ 'Copy the URL', 'Edit the Button Link', 'Add a Custom Attribute' ], array_column( $steps, 'title' ) );
		$this->assertStringNotContainsString( 'Tutorial Steps', $out );
		$this->assertStringNotContainsString( 'wp:spacer', $out );
		$this->assertStringNotContainsString( 'youtu.be/MLpWrANjFbI', $out );
		$this->assertStringContainsString( '"filename":"Custom attributes"', $out );
		$this->assertStringContainsString( 'alt="WordPress media library with the file URL highlighted"', $out );
		$this->assertStringNotContainsString( 'id="Step1"', $out );
		foreach ( parse_blocks( $out ) as $block ) {
			$this->assertNotSame( 'core/image', $block['blockName'], 'every screenshot sits inside a step' );
		}
	}

	public function test_368_becomes_four_steps_with_trimmed_titles() {
		$steps = juanto_tutorial_steps_from_content( juanto_migrate_tutorial_content( $this->fixture( 'tutorial-368' ) ) );
		$this->assertCount( 4, $steps );
		$this->assertSame( 'Design Your Elementor Popup', $steps[0]['title'] );
		$this->assertStringEndsWith( '"Don\'t Show Again" will appear.', $steps[3]['title'] );
	}

	public function test_the_transform_is_idempotent() {
		$options = [
			'drop_embed'     => 'https://youtu.be/MLpWrANjFbI',
			'code_filenames' => [ 'download|true' => 'Custom attributes' ],
			'alts'           => [ 412 => 'Alt text' ],
			'outro_starts'   => [ 'In conclusion' ],
		];
		foreach ( [ 'tutorial-403', 'tutorial-368' ] as $name ) {
			$once = juanto_migrate_tutorial_content( $this->fixture( $name ), $options );
			$this->assertSame( $once, juanto_migrate_tutorial_content( $once, $options ), $name );
		}
	}

	public function test_outro_starts_keeps_the_closing_paragraphs_outside_the_last_step() {
		$out    = juanto_migrate_tutorial_content( $this->fixture( 'tutorial-368' ), [ 'outro_starts' => [ 'in conclusion' ] ] );
		$blocks = array_values( array_filter( parse_blocks( $out ), static fn( $b ) => null !== $b['blockName'] ) );
		$tail   = array_slice( $blocks, -3 );
		foreach ( $tail as $block ) {
			$this->assertSame( 'core/paragraph', $block['blockName'] );
		}
		$this->assertStringStartsWith( 'In conclusion', juanto_block_plain_text( $tail[0] ) );
		$steps = array_values( array_filter( $blocks, static fn( $b ) => 'juanto/tutorial-step' === $b['blockName'] ) );
		$this->assertCount( 4, $steps );
		// Fixture step 4 holds image, p, image, p, p, image (6 blocks) before the three closing paragraphs.
		$this->assertCount( 6, $steps[3]['innerBlocks'] );
	}

	public function test_a_step_heading_after_the_outro_opens_a_new_step() {
		$in = '<!-- wp:paragraph --><p>Outro here.</p><!-- /wp:paragraph -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Step 1: One</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>a</p><!-- /wp:paragraph -->';
		$this->assertSame( [ 'core/paragraph', 'juanto/tutorial-step' ], $this->names( juanto_migrate_tutorial_content( $in, [ 'outro_starts' => [ 'Outro' ] ] ) ) );
	}

	public function test_content_is_preserved_apart_from_what_the_spec_removes() {
		// Counts of <img, <p and <code taken from the fixtures. The spec removes spacers, the embed,
		// the "Tutorial Steps" index group (no img/p/code) and the Step headings: nothing else.
		$expected = [
			'tutorial-403' => [ 3, 6, 2 ],
			'tutorial-368' => [ 8, 12, 0 ],
		];
		foreach ( $expected as $name => $counts ) {
			$out = juanto_migrate_tutorial_content( $this->fixture( $name ), [ 'drop_embed' => 'https://youtu.be/MLpWrANjFbI' ] );
			$this->assertSame( $counts[0], preg_match_all( '/<img/', $out ), "$name img" );
			$this->assertSame( $counts[1], preg_match_all( '/<p[ >]/', $out ), "$name p" );
			$this->assertSame( $counts[2], preg_match_all( '/<code/', $out ), "$name code" );
		}
	}

	public function test_content_without_step_headings_keeps_its_blocks() {
		$in = "<!-- wp:paragraph -->\n<p>Intro.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Why</h2>\n<!-- /wp:heading -->";
		$this->assertSame( [ 'core/paragraph', 'core/heading' ], $this->names( juanto_migrate_tutorial_content( $in ) ) );
	}

	public function test_a_plain_h2_closes_the_open_step() {
		$in  = '<!-- wp:heading --><h2 class="wp-block-heading">Step 1: One</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>a</p><!-- /wp:paragraph -->'
			. '<!-- wp:heading --><h2 class="wp-block-heading">Troubleshooting</h2><!-- /wp:heading -->'
			. '<!-- wp:paragraph --><p>b</p><!-- /wp:paragraph -->';
		$this->assertSame( [ 'juanto/tutorial-step', 'core/heading', 'core/paragraph' ], $this->names( juanto_migrate_tutorial_content( $in ) ) );
	}
}
