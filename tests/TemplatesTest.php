<?php
class TemplatesTest extends WP_UnitTestCase {
	public function test_case_projects_prefer_the_case_template() {
		juanto_seed_terms();
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_name' => 'nexconnect' ] );
		wp_set_object_terms( $id, 'case', 'tier' );
		$this->go_to( get_permalink( $id ) );
		$templates = apply_filters( 'single_template_hierarchy', [ 'single-project-nexconnect.php', 'single-project.php', 'single.php' ] );
		$this->assertSame( 'single-project-case.php', $templates[0] );
	}

	public function test_other_post_types_are_untouched() {
		$id = self::factory()->post->create();
		$this->go_to( get_permalink( $id ) );
		$this->assertSame( [ 'single.php' ], apply_filters( 'single_template_hierarchy', [ 'single.php' ] ) );
	}

	public function test_theme_json_exposes_design_tokens() {
		$palette = wp_get_global_settings( [ 'color', 'palette', 'theme' ] );
		$slugs   = wp_list_pluck( $palette, 'slug' );
		foreach ( [ 'paper', 'ink', 'amber', 'rule', 'wall' ] as $s ) {
			$this->assertContains( $s, $slugs );
		}
		$amber = $palette[ array_search( 'amber', $slugs, true ) ]['color'];
		$this->assertSame( 'oklch(62% 0.17 45)', $amber );
	}

	public function test_fonts_are_self_hosted() {
		$families = wp_list_pluck( wp_get_global_settings( [ 'typography', 'fontFamilies', 'theme' ] ), 'slug' );
		$this->assertContains( 'schibsted', $families );
		foreach ( glob( get_theme_file_path( 'assets/fonts/*.woff2' ) ) as $f ) {
			$this->assertGreaterThan( 5000, filesize( $f ) );
		}
		$this->assertCount( 6, glob( get_theme_file_path( 'assets/fonts/*.woff2' ) ) );
		$this->assertStringNotContainsString( 'fonts.googleapis.com', file_get_contents( get_theme_file_path( 'theme.json' ) ) );
	}

	public function test_block_templates_exist() {
		foreach ( [ 'index', 'single', 'page', 'home', '404' ] as $slug ) {
			$this->assertNotNull( get_block_template( 'juanto//' . $slug ), $slug );
		}
		$this->assertNotNull( get_block_template( 'juanto//header', 'wp_template_part' ) );
	}

	public function test_resume_nav_link_is_hidden_when_resume_url_is_empty() {
		update_option( 'juanto_status', juanto_sanitize_status( [] ) );
		$html = do_blocks( get_block_template( 'juanto//header', 'wp_template_part' )->content );
		$this->assertStringNotContainsString( 'resume.pdf', $html );
		$this->assertStringNotContainsString( 'Résumé', $html );
	}

	public function test_resume_nav_link_shows_when_resume_url_is_set() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'resume_url' => 'https://example.org/cv.pdf' ] ) );
		$html = do_blocks( get_block_template( 'juanto//header', 'wp_template_part' )->content );
		$this->assertStringContainsString( 'resume.pdf', $html );
		$this->assertStringContainsString( 'Résumé', $html );
	}

	public function test_footer_pattern_uses_settings() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'contact_email' => 'hello@example.org', 'resume_url' => 'https://example.org/cv.pdf' ] ) );
		$html = do_blocks( '<!-- wp:pattern {"slug":"juanto/footer-contact"} /-->' );
		$this->assertStringContainsString( 'mailto:hello@example.org', $html );
		$this->assertStringContainsString( 'https://example.org/cv.pdf', $html );
		$this->assertStringContainsString( 'Hiring, or have a project?', $html );
	}

	public function test_front_page_template_stacks_hero_sheet_and_lists() {
		$tpl = get_block_template( 'juanto//front-page' );
		$this->assertNotNull( $tpl );
		foreach ( [ 'juanto/status-bar', '"slug":"juanto/hero"', '"tier":"case","layout":"rows"', '"tier":"card","layout":"columns"', 'juanto/lab-teaser', 'juanto-sheet' ] as $needle ) {
			$this->assertStringContainsString( $needle, $tpl->content, $needle );
		}
	}

	public function test_case_and_card_templates_are_wired() {
		$case = get_block_template( 'juanto//single-project-case' );
		$card = get_block_template( 'juanto//single-project-card' );
		$this->assertNotNull( $case );
		$this->assertNotNull( $card );
		foreach ( [ 'juanto/case-meta', 'juanto/case-toc', 'wp:post-content', 'juanto/next-case', 'juanto-case' ] as $needle ) {
			$this->assertStringContainsString( $needle, $case->content, $needle );
		}
		$this->assertStringNotContainsString( 'juanto/case-toc', $card->content );
	}

	public function test_lab_archive_template_uses_the_stack() {
		$this->set_permalink_structure( '/%postname%/' );
		$tpl = get_block_template( 'juanto//archive-project' );
		$this->assertNotNull( $tpl );
		$this->assertStringContainsString( 'juanto/lab-stack', $tpl->content );
		$this->assertSame( home_url( '/lab/' ), get_post_type_archive_link( 'project' ) );
	}

	public function test_base_stylesheet_is_versioned_by_file_mtime() {
		global $wp_styles;
		$wp_styles = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );
		$style = wp_styles()->registered['juanto-base'];
		$expected = (string) filemtime( get_theme_file_path( 'assets/css/base.css' ) );
		$this->assertSame( $expected, (string) $style->ver );
	}

	public function test_hero_pattern_renders_identity_and_settings_links() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'resume_url' => 'https://example.org/cv.pdf', 'github_url' => 'https://github.com/jtperez', 'availability' => 'open to product, brand and engineering roles' ] ) );
		$html = do_blocks( '<!-- wp:pattern {"slug":"juanto/hero"} /-->' );
		$this->assertStringContainsString( '<h1 class="juanto-hero__name">Juan Pérez</h1>', $html );
		$this->assertStringContainsString( 'fetchpriority="high"', $html );
		$this->assertStringContainsString( 'https://example.org/cv.pdf', $html );
		$this->assertStringContainsString( 'open to product, brand and engineering roles', $html );
		$this->assertStringNotContainsString( 'LinkedIn', $html ); // not configured, not shown
	}

	public function test_block_styles_print_after_the_theme_sheets_they_override() {
		juanto_render_block_markup( 'juanto/step-track', [ 'total' => 4, 'done' => 2 ] );
		juanto_render_block_markup( 'juanto/section-head', [ 'number' => '01', 'label' => 'x', 'title' => 'y' ] );
		$styles = wp_styles()->registered;
		$this->assertSame( [ 'juanto-base', 'juanto-case' ], $styles['juanto-block-step-track']->deps );
		$this->assertSame( [ 'juanto-base', 'juanto-case' ], $styles['juanto-block-section-head']->deps );
	}

	public function test_theme_sheets_are_registered_on_every_request() {
		foreach ( [ 'juanto-base', 'juanto-case', 'juanto-lab', 'juanto-tutorial' ] as $handle ) {
			$this->assertArrayHasKey( $handle, wp_styles()->registered, $handle );
		}
	}

	public function test_tutorials_prefer_the_tutorial_template() {
		juanto_seed_terms();
		$id = self::factory()->post->create();
		wp_set_post_categories( $id, [ get_category_by_slug( 'tutorials' )->term_id ] );
		$this->go_to( get_permalink( $id ) );
		$this->assertSame( 'single-tutorial.php', apply_filters( 'single_template_hierarchy', [ 'single-post.php', 'single.php' ] )[0] );
	}

	public function test_tutorial_template_is_wired() {
		$tpl = get_block_template( 'juanto//single-tutorial' );
		$this->assertNotNull( $tpl );
		foreach ( [ '"variant":"badge"', 'juanto/lang-badge', 'wp:post-title', 'juanto-tutorial__lede', 'wp:juanto/tutorial-meta /', '"source":"tutorial-step"', 'wp:post-content', 'juanto/tutorial-cta', 'juanto/related-tutorials' ] as $needle ) {
			$this->assertStringContainsString( $needle, $tpl->content, $needle );
		}
	}

	public function test_tutorial_starter_pattern_is_offered_for_new_posts() {
		$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'juanto/tutorial-starter' );
		$this->assertNotNull( $pattern );
		$this->assertContains( 'core/post-content', $pattern['blockTypes'] );
		$this->assertContains( 'post', $pattern['postTypes'] );
		$this->assertSame( 3, substr_count( $pattern['content'], '<!-- wp:juanto/tutorial-step' ) );
		$this->assertStringContainsString( 'wp:juanto/callout', $pattern['content'] );
		$this->assertStringContainsString( 'Troubleshooting', $pattern['content'] );
		$this->assertStringNotContainsString( '—', $pattern['content'] );
	}

	public function test_hub_lists_only_tutorials() {
		juanto_seed_terms();
		$tut = self::factory()->post->create( [ 'post_title' => 'A tutorial title' ] );
		wp_set_post_categories( $tut, [ get_category_by_slug( 'tutorials' )->term_id ] );
		self::factory()->post->create( [ 'post_title' => 'An essay title' ] );
		$tpl = get_block_template( 'juanto//page-tutorials' );
		$this->assertNotNull( $tpl );
		$html = do_blocks( $tpl->content );
		$this->assertStringContainsString( 'A tutorial title', $html );
		$this->assertStringNotContainsString( 'An essay title', $html );
		$this->assertStringContainsString( 'Step-by-step guides from client work and my own builds.', $html );
	}

	public function test_writing_index_marks_tutorials_and_links_the_hub() {
		$tpl = get_block_template( 'juanto//home' );
		$this->assertStringContainsString( '"variant":"badge"', $tpl->content );
		$this->assertStringContainsString( 'href="/tutorials/"', $tpl->content );
	}

	public function test_footer_links_the_hub() {
		$this->assertStringContainsString( home_url( '/tutorials/' ), do_blocks( '<!-- wp:pattern {"slug":"juanto/footer-contact"} /-->' ) );
	}
}
