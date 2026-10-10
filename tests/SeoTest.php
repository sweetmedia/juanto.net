<?php
class SeoTest extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();
		juanto_seed_terms();
	}

	public function test_spanish_posts_get_lang_attribute() {
		$es = self::factory()->post->create( [ 'post_title' => 'De Diseñador a Emprendedor en Serie' ] );
		wp_set_object_terms( $es, 'es', 'language' );
		$this->assertSame( 'es', juanto_post_lang( $es ) );
		$marked = juanto_add_lang_attr( '<h1 class="t">Hola</h1>', 'es' );
		$this->assertStringStartsWith( '<h1 ', $marked );
		$this->assertStringContainsString( 'lang="es"', $marked );
		$this->assertStringContainsString( 'class="t"', $marked );
		$this->assertSame( '<p>x</p>', juanto_add_lang_attr( '<p>x</p>', '' ) );
		$this->go_to( get_permalink( $es ) ); // core/post-title reads the current post from the loop, not from block context.
		$this->assertStringContainsString( 'lang="es"', ( new WP_Block( [ 'blockName' => 'core/post-title', 'attrs' => [] ], [ 'postId' => $es ] ) )->render() );
	}

	public function test_redirect_targets() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'resume_url' => 'https://example.org/cv.pdf' ] ) );
		$this->assertSame( [ home_url( '/' ), 301 ], juanto_redirect_target( '/works/' ) );
		$this->assertSame( [ home_url( '/' ), 301 ], juanto_redirect_target( '/works' ) );
		$this->assertSame( [ 'https://example.org/cv.pdf', 302 ], juanto_redirect_target( '/resume.pdf' ) );
		$this->assertNull( juanto_redirect_target( '/lab/' ) );
	}

	public function test_schema_person_on_front_and_creative_work_on_projects() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'github_url' => 'https://github.com/jtperez' ] ) );
		$this->go_to( home_url( '/' ) );
		$person = juanto_schema();
		$this->assertSame( 'Person', $person['@type'] );
		$this->assertSame( 'Design engineer', $person['jobTitle'] );
		$this->assertContains( 'https://github.com/jtperez', $person['sameAs'] );

		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'NexConnect' ] );
		update_post_meta( $id, 'juanto_summary', 'E-invoicing API for the DGII' );
		update_post_meta( $id, 'juanto_year_start', 2026 );
		$this->go_to( get_permalink( $id ) );
		$work = juanto_schema();
		$this->assertSame( 'CreativeWork', $work['@type'] );
		$this->assertSame( 'NexConnect', $work['name'] );
		$this->assertSame( '2026', $work['dateCreated'] );
		$this->assertSame( 'Juan Pérez', $work['creator']['name'] );
	}

	public function test_og_tags_for_a_project() {
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'NexConnect' ] );
		update_post_meta( $id, 'juanto_summary', 'E-invoicing API for the DGII' );
		$this->go_to( get_permalink( $id ) );
		$og = juanto_og_tags();
		$this->assertSame( 'NexConnect · Juan Pérez', $og['og:title'] );
		$this->assertSame( 'E-invoicing API for the DGII', $og['og:description'] );
		$this->assertSame( 'article', $og['og:type'] );
	}

	public function test_non_singular_og_image_comes_from_the_default_og_image_filter() {
		// The theme registers this filter in functions.php with its hero image,
		// so the plugin itself never hard-codes a theme path.
		$this->go_to( home_url( '/' ) );
		$og = juanto_og_tags();
		$this->assertSame( get_theme_file_uri( 'assets/img/hero-1600.jpg' ), $og['og:image'] );
	}

	public function test_non_singular_og_image_is_omitted_without_a_default_image_filter() {
		remove_all_filters( 'juanto_default_og_image' );
		$this->go_to( home_url( '/' ) );
		$og = juanto_og_tags();
		$this->assertArrayNotHasKey( 'og:image', $og );
	}

	public function test_non_singular_og_url_is_the_current_request_not_always_home() {
		$archive_url = get_post_type_archive_link( 'project' ); // /lab/
		$this->go_to( $archive_url );
		$og = juanto_og_tags();
		$this->assertSame( untrailingslashit( $archive_url ), untrailingslashit( $og['og:url'] ) );
		$this->assertNotSame( home_url( '/' ), $og['og:url'] );
	}

	public function test_singular_project_without_a_thumbnail_falls_back_to_the_default_og_image() {
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'No cover yet' ] );
		$this->assertFalse( has_post_thumbnail( $id ) );
		$this->go_to( get_permalink( $id ) );
		$og = juanto_og_tags();
		$this->assertSame( get_theme_file_uri( 'assets/img/hero-1600.jpg' ), $og['og:image'] );
	}

	public function test_lab_tier_project_singles_are_noindexed() {
		$lab_id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'WIP sketch' ] );
		wp_set_object_terms( $lab_id, 'lab', 'tier' );
		$this->go_to( get_permalink( $lab_id ) );
		$this->assertTrue( juanto_noindex_lab_tier_singles( [] )['noindex'] );

		$case_id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'Shipped case' ] );
		wp_set_object_terms( $case_id, 'case', 'tier' );
		$this->go_to( get_permalink( $case_id ) );
		$this->assertArrayNotHasKey( 'noindex', juanto_noindex_lab_tier_singles( [] ) );
	}

	public function test_json_ld_escapes_script_tags_in_title() {
		// Create a project with the intended title using the factory.
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => 'Test' ] );

		// Now directly modify the database to inject the malicious content (bypasses sanitization).
		// This simulates a DB-level attack or API bypass.
		global $wpdb;
		$malicious_title = 'Project</script><script>alert(1)</script>';
		$wpdb->update( $wpdb->posts, [ 'post_title' => $malicious_title ], [ 'ID' => $id ], [ '%s' ], [ '%d' ] );
		wp_cache_delete( $id, 'posts' );  // Clear the post cache so get_the_title() gets fresh data.
		$this->assertSame( $malicious_title, get_the_title( $id ) );

		// Visit the project page.
		$this->go_to( get_permalink( $id ) );

		// Debug: check what the main query returns
		global $wp_query;
		$this->assertTrue( $wp_query->is_singular(), 'Main query should be singular after go_to' );
		$this->assertTrue( is_singular( 'project' ), 'is_singular(project) should be true after go_to' );
		$this->assertSame( $id, get_queried_object_id(), 'Queried object ID should match' );

		// Verify juanto_schema() returns CreativeWork for this project (not Person).
		$schema = juanto_schema();
		$this->assertIsArray( $schema );
		$this->assertSame( 'CreativeWork', $schema['@type'] );
		$this->assertSame( $malicious_title, $schema['name'] );

		// Now check how it's encoded in the HTML output.
		ob_start();
		do_action( 'wp_head' );
		$html = ob_get_clean();

		// Verify the JSON-LD content doesn't allow the </script> to break out.
		$this->assertStringContainsString( '<script type="application/ld+json">', $html );
		$this->assertStringContainsString( '"@type":"CreativeWork"', $html );

		// The key security check: if < and > are not escaped in the JSON, the HTML will contain
		// a literal </script> that breaks out of the JSON-LD tag, leaving <script>alert(1)</script>
		// as unescaped HTML. With proper escaping (JSON_HEX_TAG), < becomes < and > becomes >.
		$ld_start = strpos( $html, '<script type="application/ld+json">' );
		$ld_end   = strpos( $html, '</script>', $ld_start + 1 );
		$this->assertNotFalse( $ld_end, 'Found </script> after JSON-LD opening tag' );

		// Extract the JSON-LD content between the tags.
		$json_start = $ld_start + strlen( '<script type="application/ld+json">' );
		$json_end   = $ld_end;
		$json_content = substr( $html, $json_start, $json_end - $json_start );

		// CRITICAL SECURITY CHECK: The extracted JSON should NOT contain </script> as a substring.
		// If it does, it means the malicious title broke out of the JSON-LD tag (vulnerable).
		// With JSON_HEX_TAG, </script> becomes </script>, which is safe.
		// When the vulnerability exists, the extracted JSON will be incomplete (broken at the first </script>).
		$this->assertStringNotContainsString( '</script>', $json_content, 'No literal </script> inside JSON-LD; title did not break out of the script tag' );

		// Verify the JSON is valid and the name field contains the escaped/encoded title.
		$decoded = json_decode( $json_content, true );
		$this->assertIsArray( $decoded, 'JSON-LD is valid JSON' );
		$this->assertArrayHasKey( 'name', $decoded );
		$this->assertSame( $malicious_title, $decoded['name'], 'Decoded name matches original (properly encoded in JSON)' );
	}
}
