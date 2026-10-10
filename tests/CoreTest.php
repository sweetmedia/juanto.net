<?php
class CoreTest extends WP_UnitTestCase {
	public function test_plugin_constants_are_defined() {
		$this->assertSame( '0.1.0', JUANTO_CORE_VERSION );
		$this->assertStringEndsWith( 'juanto-core/', JUANTO_CORE_DIR );
	}

	public function test_theme_is_active() {
		$this->assertSame( 'juanto', get_stylesheet() );
	}

	public function test_project_post_type_is_registered_for_rest_under_work() {
		$pt = get_post_type_object( 'project' );
		$this->assertNotNull( $pt );
		$this->assertTrue( $pt->show_in_rest );
		$this->assertSame( 'work', $pt->rewrite['slug'] );
		$this->assertSame( 'lab', $pt->has_archive );
		$this->assertTrue( post_type_supports( 'project', 'page-attributes' ) );
	}

	public function test_taxonomies_are_attached() {
		$this->assertTrue( is_object_in_taxonomy( 'project', 'tier' ) );
		$this->assertTrue( is_object_in_taxonomy( 'project', 'discipline' ) );
		$this->assertTrue( is_object_in_taxonomy( 'post', 'language' ) );
		$this->assertFalse( get_taxonomy( 'tier' )->hierarchical );
	}

	public function test_taxonomies_are_not_publicly_queryable() {
		// public stays true (so show_admin_column/REST keep working), but
		// publicly_queryable is false: no ?tier=lab front-end archive, and
		// (via is_taxonomy_viewable(), which the taxonomies sitemap provider
		// filters on) they are left out of wp-sitemap.xml.
		foreach ( [ 'tier', 'discipline', 'language' ] as $taxonomy ) {
			$tax = get_taxonomy( $taxonomy );
			$this->assertTrue( $tax->public, $taxonomy );
			$this->assertFalse( $tax->publicly_queryable, $taxonomy );
			$this->assertFalse( is_taxonomy_viewable( $tax ), $taxonomy );
		}
	}

	public function test_users_sitemap_provider_is_dropped() {
		$server = wp_get_sitemap_providers();
		$this->assertArrayNotHasKey( 'users', $server );
	}

	public function test_seed_terms_creates_tiers_disciplines_and_languages_once() {
		juanto_seed_terms();
		juanto_seed_terms();
		$this->assertSame( [ 'card', 'case', 'lab' ], wp_list_pluck( get_terms( [ 'taxonomy' => 'tier', 'hide_empty' => false, 'orderby' => 'slug' ] ), 'slug' ) );
		$this->assertCount( count( JUANTO_DISCIPLINES ), get_terms( [ 'taxonomy' => 'discipline', 'hide_empty' => false ] ) );
		$this->assertNotFalse( term_exists( 'es', 'language' ) );
	}

	public function test_seed_terms_logs_wp_insert_term_failure_in_debug_mode() {
		// Check if WP_DEBUG is enabled; if not, skip the test.
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$this->markTestSkipped( 'WP_DEBUG is not enabled' );
		}

		// Delete the 'lab' term if it exists, so we can test the creation failure.
		$existing_term = term_exists( 'lab', 'tier' );
		if ( $existing_term ) {
			wp_delete_term( $existing_term, 'tier' );
		}

		// Capture error_log output to a temp file.
		$old_error_log = ini_get( 'error_log' );
		$tmpfile        = tempnam( sys_get_temp_dir(), 'juanto_test_' );

		try {
			ini_set( 'error_log', $tmpfile );

			// Add a filter to force wp_insert_term to fail for the 'lab' slug in 'tier' taxonomy.
			add_filter(
				'pre_insert_term',
				static function ( $term_id, $taxonomy, $args ) {
					if ( 'tier' === $taxonomy && 'lab' === $args['slug'] ) {
						return new WP_Error( 'forced', 'forced failure' );
					}
					return $term_id;
				},
				10,
				3
			);

			// Call juanto_seed_terms() which should log the failure.
			juanto_seed_terms();

			// Read the temp file to verify the error was logged.
			$log_contents = file_get_contents( $tmpfile );
			$this->assertStringContainsString( 'juanto_seed_terms: could not create tier/lab', $log_contents );
		} finally {
			// Clean up: restore the original error_log value and remove the temp file.
			ini_set( 'error_log', $old_error_log );
			if ( file_exists( $tmpfile ) ) {
				unlink( $tmpfile );
			}
			// Remove the filter we added.
			remove_filter( 'pre_insert_term', 'juanto_seed_terms_test_filter' );
		}
	}

	public function test_hero_css_is_registered_for_core_inlining_without_wp_filesystem() {
		$this->go_to( home_url( '/' ) );
		$this->assertTrue( is_front_page() );

		wp_dequeue_style( 'juanto-hero' );
		wp_deregister_style( 'juanto-hero' );
		juanto_theme_enqueue();

		$styles = wp_styles();
		$this->assertTrue( wp_style_is( 'juanto-hero', 'registered' ) );

		// A real src (not false) plus 'path' data is what lets WordPress core's
		// wp_maybe_inline_styles() (wp_head/wp_footer) decide whether to inline
		// the stylesheet. Registering with src=false and reading the file
		// ourselves through WP_Filesystem() is what broke on non-direct-FS
		// hosts and pulled in wp-admin/includes/file.php on the front end.
		$registered = $styles->registered['juanto-hero'];
		$this->assertNotFalse( $registered->src );

		$expected_path = get_theme_file_path( 'assets/css/hero.css' );
		$this->assertSame( $expected_path, $styles->get_data( 'juanto-hero', 'path' ) );
		$this->assertFileExists( $expected_path );

		$this->assertFalse( function_exists( 'juanto_read_theme_file' ) );
	}
}
