<?php
class TutorialTest extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();
		juanto_seed_terms();
		juanto_register_tutorial_meta();
	}

	private function tutorial( array $args = [] ): int {
		$id = self::factory()->post->create( $args );
		wp_set_post_categories( $id, [ get_category_by_slug( 'tutorials' )->term_id ] );
		return $id;
	}

	public function test_tutorials_category_is_seeded() {
		$cat = get_category_by_slug( 'tutorials' );
		$this->assertInstanceOf( WP_Term::class, $cat );
		$this->assertSame( 'Tutorials', $cat->name );
	}

	public function test_a_post_is_a_tutorial_only_with_the_category() {
		$this->assertTrue( juanto_is_tutorial( $this->tutorial() ) );
		$this->assertFalse( juanto_is_tutorial( self::factory()->post->create() ) );
		$this->assertFalse( juanto_is_tutorial( 0 ) );
		$page = self::factory()->post->create( [ 'post_type' => 'page' ] );
		$this->assertFalse( juanto_is_tutorial( $page ) );
	}

	public function test_all_fields_are_registered_on_posts_and_exposed_in_rest() {
		$keys = get_registered_meta_keys( 'post', 'post' );
		foreach ( [ 'minutes', 'tools', 'tested', 'prereqs', 'result', 'result_image', 'video', 'audience' ] as $f ) {
			$this->assertArrayHasKey( 'juanto_tut_' . $f, $keys, $f );
			$this->assertNotEmpty( $keys[ 'juanto_tut_' . $f ]['show_in_rest'], $f );
		}
	}

	public function test_minutes_are_clamped() {
		$this->assertSame( 10, juanto_sanitize_tut_minutes( '10' ) );
		$this->assertSame( 0, juanto_sanitize_tut_minutes( 'soon' ) );
		$this->assertSame( 600, juanto_sanitize_tut_minutes( 9999 ) );
		$this->assertSame( 5, juanto_sanitize_tut_minutes( -5 ) ); // absint
	}

	public function test_tools_keep_named_rows_only() {
		$clean = juanto_sanitize_tut_tools(
			[
				[ 'name' => ' Elementor Pro <b>', 'version' => '' ],
				[ 'name' => 'WordPress', 'version' => ' 6.5 ' ],
				[ 'name' => '', 'version' => '3.2' ],
				'not-a-row',
			]
		);
		$this->assertSame( [ [ 'name' => 'Elementor Pro', 'version' => '' ], [ 'name' => 'WordPress', 'version' => '6.5' ] ], $clean );
		$this->assertSame( [], juanto_sanitize_tut_tools( 'nope' ) );
	}

	public function test_tested_date_must_be_a_real_iso_date() {
		$this->assertSame( '2024-02-27', juanto_sanitize_tut_date( ' 2024-02-27 ' ) );
		$this->assertSame( '', juanto_sanitize_tut_date( '2024-02-30' ) );
		$this->assertSame( '', juanto_sanitize_tut_date( '27/02/2024' ) );
		$this->assertSame( '', juanto_sanitize_tut_date( '' ) );
	}

	public function test_prereqs_drop_empty_lines() {
		$this->assertSame( [ 'Elementor Pro', 'A file in the media library' ], juanto_sanitize_tut_prereqs( [ ' Elementor Pro ', '', [ 'x' ], 'A file in the media library' ] ) );
	}

	public function test_result_image_must_be_an_image_attachment() {
		$img = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$this->assertSame( $img, juanto_sanitize_tut_image( (string) $img ) );
		$this->assertSame( 0, juanto_sanitize_tut_image( self::factory()->post->create() ) );
	}

	public function test_video_accepts_youtube_only_and_normalizes() {
		$want = 'https://www.youtube.com/watch?v=MLpWrANjFbI';
		foreach ( [ 'https://youtu.be/MLpWrANjFbI', 'https://youtu.be/MLpWrANjFbI?si=abc123', 'https://www.youtube.com/watch?v=MLpWrANjFbI&t=30s', 'https://m.youtube.com/watch?feature=share&v=MLpWrANjFbI', 'http://youtube.com/watch?v=MLpWrANjFbI' ] as $url ) {
			$this->assertSame( $want, juanto_sanitize_tut_video( $url ), $url );
		}
		foreach ( [ 'https://vimeo.com/123', 'javascript:alert(1)', 'https://youtu.be/short', '' ] as $bad ) {
			$this->assertSame( '', juanto_sanitize_tut_video( $bad ), $bad );
		}
	}

	public function test_audience_is_a_closed_enum() {
		$this->assertSame( 'clients', juanto_sanitize_tut_audience( 'clients' ) );
		$this->assertSame( 'builders', juanto_sanitize_tut_audience( 'everyone' ) );
	}

	public function test_get_tutorial_returns_typed_defaults() {
		$t = juanto_get_tutorial( $this->tutorial() );
		$this->assertSame( [ 'minutes' => 0, 'tools' => [], 'tested' => '', 'prereqs' => [], 'result' => '', 'result_image' => 0, 'video' => '', 'audience' => 'builders' ], $t );
	}

	public function test_get_tutorial_casts_stored_values_to_their_types() {
		$id    = $this->tutorial();
		$tools = [ [ 'name' => 'Elementor Pro', 'version' => '3.2' ], [ 'name' => 'WordPress', 'version' => '6.5' ] ];
		update_post_meta( $id, 'juanto_tut_minutes', '12' );
		update_post_meta( $id, 'juanto_tut_tools', $tools );
		$t = juanto_get_tutorial( $id );
		$this->assertSame( 12, $t['minutes'] );
		$this->assertIsInt( $t['minutes'] );
		$this->assertSame( $tools, $t['tools'] );
	}

	public function test_staleness_threshold_is_eighteen_months() {
		$now = new DateTimeImmutable( '2026-10-10', wp_timezone() );
		$this->assertFalse( juanto_tut_is_stale( '2025-05-10', $now ) ); // 17 months
		$this->assertTrue( juanto_tut_is_stale( '2025-03-10', $now ) );  // 19 months
		$this->assertFalse( juanto_tut_is_stale( '', $now ) );
	}

	public function test_a_half_filled_tool_row_saves_via_rest() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
		$id      = $this->tutorial();
		$request = new WP_REST_Request( 'PUT', '/wp/v2/posts/' . $id );
		$request->set_body_params( [ 'meta' => [ 'juanto_tut_tools' => [ [ 'name' => 'Elementor Pro', 'version' => '' ], [ 'name' => '', 'version' => '' ] ] ] ] );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( [ [ 'name' => 'Elementor Pro', 'version' => '' ] ], get_post_meta( $id, 'juanto_tut_tools', true ) );
	}

	public function test_editor_bundle_loads_on_posts_with_the_category_id() {
		set_current_screen( 'post' );
		do_action( 'enqueue_block_editor_assets' );
		$this->assertTrue( wp_script_is( 'juanto-editor', 'enqueued' ) );
		$before = implode( '', (array) wp_scripts()->get_data( 'juanto-editor', 'before' ) );
		$this->assertStringContainsString( '"tutorialsCategory":' . get_category_by_slug( 'tutorials' )->term_id, $before );
		set_current_screen( 'front' );
	}
}
