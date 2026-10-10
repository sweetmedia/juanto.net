<?php
class MetaTest extends WP_UnitTestCase {
	private int $id;

	public function set_up(): void {
		parent::set_up();
		// Re-register meta as WP_UnitTestCase may reset it between tests
		juanto_register_project_meta();
		$this->id = self::factory()->post->create( [ 'post_type' => 'project' ] );
	}

	public function test_all_fields_are_registered_and_exposed_in_rest() {
		$keys = array_keys( get_registered_meta_keys( 'post', 'project' ) );
		foreach ( [ 'summary', 'year_start', 'year_end', 'role', 'team', 'stack', 'links', 'preview_image', 'lab_fit', 'lab_note' ] as $f ) {
			$this->assertContains( 'juanto_' . $f, $keys, $f );
		}
		$this->assertTrue( (bool) get_registered_meta_keys( 'post', 'project' )['juanto_links']['show_in_rest'] );
	}

	public function test_year_end_accepts_present_or_four_digits_only() {
		$this->assertSame( 'present', juanto_sanitize_year_end( ' Present ' ) );
		$this->assertSame( '2026', juanto_sanitize_year_end( '2026' ) );
		$this->assertSame( '', juanto_sanitize_year_end( 'soon' ) );
	}

	public function test_links_keep_valid_urls_and_drop_the_rest() {
		$clean = juanto_sanitize_links(
			[
				[ 'label' => ' nexconnect.do <b>', 'url' => 'https://nexconnect.do' ],
				[ 'label' => 'bad', 'url' => 'javascript:alert(1)' ],
				[ 'label' => 'empty', 'url' => '' ],
				'not-an-array',
			]
		);
		$this->assertSame( [ [ 'label' => 'nexconnect.do', 'url' => 'https://nexconnect.do' ] ], $clean );
	}

	public function test_links_with_an_empty_row_save_via_rest() {
		// The bug this guards against happens before juanto_sanitize_links()
		// ever runs: the REST schema's 'format' => 'uri' validation used to
		// reject a blank url outright, so a user editing links in the block
		// editor (which sends every row, including one they're still filling
		// in or have just cleared) got a 400 instead of the row being
		// silently dropped like the sanitizer already does on its own.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );

		$request = new WP_REST_Request( 'PUT', '/wp/v2/project/' . $this->id );
		$request->set_body_params(
			[
				'meta' => [
					'juanto_links' => [
						[ 'label' => 'nexconnect.do', 'url' => 'https://nexconnect.do' ],
						[ 'label' => 'empty', 'url' => '' ],
					],
				],
			]
		);
		$response = $wp_rest_server->dispatch( $request );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
		$this->assertSame(
			[ [ 'label' => 'nexconnect.do', 'url' => 'https://nexconnect.do' ] ],
			get_post_meta( $this->id, 'juanto_links', true )
		);
	}

	public function test_lab_fit_falls_back_to_cover() {
		$this->assertSame( 'contain', juanto_sanitize_lab_fit( 'contain' ) );
		$this->assertSame( 'cover', juanto_sanitize_lab_fit( 'stretch' ) );
	}

	public function test_sanitizers_run_on_save_and_getter_returns_defaults() {
		update_post_meta( $this->id, 'juanto_year_end', 'someday' );
		update_post_meta( $this->id, 'juanto_lab_fit', 'weird' );
		$this->assertSame( '', juanto_get_project_field( $this->id, 'year_end' ) );
		$this->assertSame( 'cover', juanto_get_project_field( $this->id, 'lab_fit' ) );
		$this->assertSame( [], juanto_get_project_field( $this->id, 'links' ) );
		$this->assertSame( 0, juanto_get_project_field( $this->id, 'year_start' ) );
	}
}
