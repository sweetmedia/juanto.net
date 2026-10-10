<?php
class BlocksTest extends WP_UnitTestCase {
	private function image(): int {
		return self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
	}

	public function test_blocks_are_registered() {
		foreach ( [ 'tldr', 'facts', 'section-head', 'decisions', 'ledger', 'figure-pair', 'reveal-image', 'case-meta', 'next-case', 'swatches', 'type-specimen' ] as $b ) {
			$this->assertTrue( WP_Block_Type_Registry::get_instance()->is_registered( "juanto/{$b}" ), $b );
		}
	}

	public function test_block_styles_load_only_where_the_block_is_rendered() {
		wp_dequeue_style( 'juanto-block-step-track' );
		wp_dequeue_style( 'juanto-block-swatches' );

		$html = juanto_render_block_markup( 'juanto/step-track', [ 'total' => 4, 'done' => 2 ] );
		$this->assertNotSame( '', $html );

		// Rendering juanto/step-track enqueues its own stylesheet...
		$this->assertTrue( wp_style_is( 'juanto-block-step-track', 'enqueued' ) );
		// ...but not an unrelated block's, which never rendered this request.
		$this->assertFalse( wp_style_is( 'juanto-block-swatches', 'enqueued' ) );
	}

	private function case_project( string $title, int $order ): int {
		juanto_seed_terms();
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => $title, 'menu_order' => $order ] );
		wp_set_object_terms( $id, 'case', 'tier' );
		return $id;
	}

	public function test_tldr_renders_three_labelled_columns_escaped() {
		$html = juanto_render_block_markup( 'juanto/tldr', [ 'problem' => 'Ley 32-23 <b>x</b>', 'did' => 'Built it', 'outcome' => 'Certified' ] );
		$this->assertMatchesRegularExpression( '#class="[^"]*\bjuanto-tldr\b#', $html ); // wrapper also carries wp-block-juanto-tldr
		$this->assertStringContainsString( '>problem<', $html );
		$this->assertStringContainsString( 'Ley 32-23 &lt;b&gt;x&lt;/b&gt;', $html );
		$this->assertSame( 3, substr_count( $html, '<p>' ) );
	}

	public function test_facts_render_a_definition_list() {
		$html = juanto_render_block_markup( 'juanto/facts', [ 'rows' => [ [ 'label' => 'law', 'value' => 'Ley 32-23' ] ] ] );
		$this->assertMatchesRegularExpression( '#<dl class="[^"]*\bjuanto-facts\b[^"]*">\s*<div><dt>law</dt><dd>Ley 32-23</dd></div>#', $html );
		$this->assertSame( '', juanto_render_block_markup( 'juanto/facts', [ 'rows' => [] ] ) );
	}

	public function test_section_head_exposes_anchor_and_toc_label() {
		$html = juanto_render_block_markup( 'juanto/section-head', [ 'number' => '01', 'label' => 'the problem', 'title' => 'A law with a deadline' ] );
		$this->assertStringContainsString( 'id="the-problem"', $html );
		$this->assertStringContainsString( 'data-toc="the problem"', $html );
		$this->assertStringContainsString( '<h2 class="juanto-section-head__title">A law with a deadline</h2>', $html );
	}

	public function test_decisions_render_ordered_titles_and_text() {
		$html = juanto_render_block_markup( 'juanto/decisions', [ 'rows' => [ [ 'title' => 'A hand-written XML signer', 'text' => 'The DGII rejected signxml.' ] ] ] );
		$this->assertMatchesRegularExpression( '#<ol class="[^"]*\bjuanto-decisions\b[^"]*">\s*<li><b>A hand-written XML signer</b><p>The DGII rejected signxml\.</p></li>#', $html );
	}

	public function test_ledger_marks_the_next_row() {
		$html = juanto_render_block_markup( 'juanto/ledger', [ 'rows' => [ [ 'value' => '14 days', 'text' => 'to certification' ], [ 'value' => 'Next', 'text' => 'first production invoice' ] ] ] );
		$this->assertStringContainsString( '<div class="juanto-ledger__row"><span class="juanto-ledger__value">14 days</span>', $html );
		$this->assertStringContainsString( 'juanto-ledger__row is-next', $html );
	}

	public function test_figure_pair_renders_two_images_with_captions() {
		$a    = $this->image();
		$b    = $this->image();
		$html = juanto_render_block_markup( 'juanto/figure-pair', [ 'items' => [ [ 'id' => $a, 'title' => 'Dashboard.', 'caption' => 'Keys and webhooks.' ], [ 'id' => $b, 'title' => 'NexFacturas.', 'caption' => 'Office app.' ] ] ] );
		$this->assertSame( 2, substr_count( $html, '<figure' ) );
		$this->assertStringContainsString( '<figcaption><b>Dashboard.</b> Keys and webhooks.</figcaption>', $html );
		$this->assertStringContainsString( 'loading="lazy"', $html );
	}

	public function test_figure_pair_tolerates_an_item_with_no_title_key() {
		// A missing (not just empty) 'title' key used to trigger an
		// "Undefined array key" warning from $item['title'] directly.
		$html = juanto_render_block_markup( 'juanto/figure-pair', [ 'items' => [ [ 'id' => $this->image(), 'caption' => 'No title on this one.' ] ] ] );
		$this->assertStringContainsString( '<figcaption>No title on this one.</figcaption>', $html );
	}

	public function test_reveal_image_is_visible_without_js_and_skips_missing_images() {
		$html = juanto_render_block_markup( 'juanto/reveal-image', [ 'id' => $this->image() ] );
		$this->assertMatchesRegularExpression( '#<figure class="[^"]*\bjuanto-reveal\b#', $html );
		$this->assertStringNotContainsString( 'data-armed', $html );
		$this->assertSame( '', juanto_render_block_markup( 'juanto/reveal-image', [ 'id' => 999999 ] ) );
	}

	public function test_reveal_image_leaves_loading_to_core_so_the_lcp_image_is_not_lazy() {
		// wp_get_loading_attr_default()'s content-media counter is a plain static,
		// not reset between tests by WP_UnitTestCase; zero it so this test doesn't
		// depend on how many images earlier tests in the run already rendered.
		wp_increase_content_media_count( -wp_increase_content_media_count( 0 ) );

		// A case study's lead reveal image is typically its LCP candidate; forcing
		// loading="lazy" on it (as opposed to leaving it to core's own
		// first-content-images-are-eager default, which only engages inside the
		// real post loop) delays its discovery and inflates LCP.
		$content = '<!-- wp:juanto/reveal-image {"id":' . $this->image() . '} /-->';
		$id      = self::factory()->post->create( [ 'post_type' => 'project', 'post_content' => $content ] );

		$this->go_to( get_permalink( $id ) );
		the_post();
		ob_start();
		the_content();
		$html = ob_get_clean();

		$this->assertStringNotContainsString( 'loading="lazy"', $html, 'the lead (likely LCP) image must not be lazy-loaded' );
	}

	public function test_case_meta_renders_number_title_and_fields() {
		$this->case_project( 'Other', 0 );
		$id = $this->case_project( 'NexConnect', 1 );
		update_post_meta( $id, 'juanto_year_start', 2026 );
		update_post_meta( $id, 'juanto_year_end', 'present' );
		update_post_meta( $id, 'juanto_team', 'Solo, Claude Code assisted' );
		update_post_meta( $id, 'juanto_links', [ [ 'label' => 'nexconnect.do', 'url' => 'https://nexconnect.do' ] ] );
		$block = new WP_Block( [ 'blockName' => 'juanto/case-meta', 'attrs' => [ 'lede' => 'An e-invoicing API.' ] ], [ 'postId' => $id ] );
		$html  = $block->render();
		$this->assertStringContainsString( 'case study 02 · 2026 to present', $html );
		$this->assertStringContainsString( '<h1 class="juanto-case-meta__title">NexConnect</h1>', $html );
		$this->assertStringContainsString( '<dt>team</dt><dd>Solo, Claude Code assisted</dd>', $html );
		$this->assertStringContainsString( 'href="https://nexconnect.do"', $html );
		$this->assertStringNotContainsString( '<dt>role</dt>', $html ); // empty fields are skipped

		wp_update_post( [ 'ID' => $id, 'post_excerpt' => 'From the excerpt.' ] );
		$from_excerpt = ( new WP_Block( [ 'blockName' => 'juanto/case-meta', 'attrs' => [] ], [ 'postId' => $id ] ) )->render();
		$this->assertStringContainsString( '<p class="juanto-case-meta__lede">From the excerpt.</p>', $from_excerpt );
	}

	public function test_next_case_links_to_the_following_case() {
		$a    = $this->case_project( 'NexConnect', 1 );
		$b    = $this->case_project( 'Embajada', 2 );
		$html = ( new WP_Block( [ 'blockName' => 'juanto/next-case', 'attrs' => [] ], [ 'postId' => $a ] ) )->render();
		$this->assertStringContainsString( 'href="' . get_permalink( $b ) . '"', $html );
		$this->assertStringContainsString( 'next case · 02', $html );
	}

	public function test_css_color_sanitizer_and_swatches() {
		$this->assertSame( '#66FF99', juanto_sanitize_css_color( '#66FF99' ) );
		$this->assertSame( 'oklch(62% 0.17 45)', juanto_sanitize_css_color( 'oklch(62% 0.17 45)' ) );
		$this->assertSame( '', juanto_sanitize_css_color( 'red;background:url(x)' ) );
		$html = juanto_render_block_markup( 'juanto/swatches', [ 'rows' => [ [ 'name' => 'Mint 400', 'value' => '#66FF99' ], [ 'name' => 'Bad', 'value' => 'url(x)' ] ] ] );
		$this->assertStringContainsString( 'style="background:#66FF99"', $html );
		$this->assertStringNotContainsString( 'Bad', $html );
	}

	public function test_type_specimen_only_self_hosts_same_site_woff2() {
		$good = home_url( '/wp-content/uploads/space-grotesk-700.woff2' );
		$html = juanto_render_block_markup( 'juanto/type-specimen', [ 'rows' => [ [ 'label' => 'display', 'family' => 'Space Grotesk', 'sample' => 'Deja de pelear con la DGII.', 'fontUrl' => $good ], [ 'label' => 'body', 'family' => 'Inter', 'sample' => 'Body', 'fontUrl' => 'https://fonts.gstatic.com/x.woff2' ] ] ] );
		$this->assertStringContainsString( "src:url('{$good}') format('woff2')", $html );
		$this->assertStringNotContainsString( 'gstatic', $html );
		$this->assertStringContainsString( 'font-family:&quot;Space Grotesk&quot;', $html );
	}

	public function test_type_specimen_skips_rows_with_an_unsafe_family() {
		// A quote could terminate the style="" attribute's font-family string
		// once the browser decodes esc_attr()'s HTML entities and hands the
		// value to the CSS parser; a backslash or literal newline can break
		// out of the quoted @font-face src string in the raw <style> block
		// (which never gets HTML-entity-decoded at all).
		$rows = [
			[ 'label' => 'quote', 'family' => 'Evil";}body{display:none' , 'sample' => 'x' ],
			[ 'label' => 'backslash', 'family' => "Evil\\", 'sample' => 'x' ],
			[ 'label' => 'style-close', 'family' => 'Evil</style><script>1</script>', 'sample' => 'x' ],
			[ 'label' => 'newline', 'family' => "Evil\nbody{display:none}", 'sample' => 'x' ],
			[ 'label' => 'good', 'family' => 'Space Grotesk', 'sample' => 'ok' ],
		];
		$html = juanto_render_block_markup( 'juanto/type-specimen', [ 'rows' => $rows ] );
		$this->assertStringNotContainsString( 'Evil', $html );
		$this->assertStringContainsString( 'Space Grotesk', $html );
	}

	public function test_type_specimen_renders_nothing_when_every_row_is_unsafe() {
		$html = juanto_render_block_markup( 'juanto/type-specimen', [ 'rows' => [ [ 'label' => 'x', 'family' => 'Bad"Name', 'sample' => 'x' ] ] ] );
		$this->assertSame( '', $html );
	}

	public function test_step_track_renders_the_final_state() {
		$html = juanto_render_block_markup(
			'juanto/step-track',
			[
				'caption'      => 'dgii certification',
				'steps'        => 15,
				'fails'        => [ [ 'step' => '6', 'count' => '4' ] ],
				'bracketFrom'  => 7,
				'bracketTo'    => 11,
				'bracketLabel' => 'one 12-hour session',
				'foot'         => [ [ 'label' => 'finalized', 'value' => 'may 22, 2026' ] ],
			]
		);
		$this->assertSame( 15, substr_count( $html, 'class="juanto-steps__step is-done' ) );
		$this->assertSame( 4, preg_match_all( '#<em>R\d</em>#', $html ) );
		$this->assertStringContainsString( 'data-fails="{&quot;6&quot;:4}"', $html );
		$this->assertStringContainsString( 'data-bracket="[7,11]"', $html );
		$this->assertStringContainsString( 'one 12-hour session', $html );
		$this->assertStringContainsString( 'replay', $html );
	}

	public function test_status_bar_renders_milestones_and_cached_commit_date() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'enabled' => 1, 'project' => 'KinPop', 'description' => 'realtime family celebrations', 'milestone_current' => 5, 'milestone_total' => 10, 'url' => 'https://example.org/build', 'repo' => 'sweetmedia/kinpop' ] ) );
		set_transient( 'juanto_commit_' . md5( 'sweetmedia/kinpop' ), strtotime( '2026-08-30T12:00:00Z' ), HOUR_IN_SECONDS );
		$html = juanto_render_block_markup( 'juanto/status-bar', [] );
		$this->assertStringContainsString( 'building KinPop, realtime family celebrations', $html );
		$this->assertSame( 5, substr_count( $html, '<i class="is-done"></i>' ) );
		$this->assertSame( 1, substr_count( $html, '<i class="is-current"></i>' ) );
		$this->assertStringContainsString( 'M5 of 10', $html );
		$this->assertStringContainsString( 'last commit aug 30', $html );
		$this->assertStringContainsString( 'href="https://example.org/build"', $html );
	}

	public function test_status_bar_hides_when_disabled_and_skips_unknown_commit() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'project' => 'KinPop' ] ) );
		$this->assertSame( '', juanto_render_block_markup( 'juanto/status-bar', [] ) );
		update_option( 'juanto_status', juanto_sanitize_status( [ 'enabled' => 1, 'project' => 'KinPop' ] ) );
		$this->assertStringNotContainsString( 'last commit', juanto_render_block_markup( 'juanto/status-bar', [] ) );
	}

	private function listed( string $tier, string $title, int $order, int $year, array $disciplines = [] ): int {
		juanto_seed_terms();
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'post_title' => $title, 'menu_order' => $order ] );
		wp_set_object_terms( $id, $tier, 'tier' );
		wp_set_object_terms( $id, $disciplines, 'discipline' );
		update_post_meta( $id, 'juanto_year_start', $year );
		update_post_meta( $id, 'juanto_summary', "{$title} summary" );
		return $id;
	}

	public function test_project_list_rows_are_numbered_ordered_and_carry_previews() {
		$b = $this->listed( 'case', 'Embajada', 2, 2025, [ 'web', 'code' ] );
		$a = $this->listed( 'case', 'NexConnect', 1, 2026, [ 'brand', 'product' ] );
		$this->listed( 'card', 'Recargame', 1, 2025 );
		$img = $this->image();
		update_post_meta( $a, 'juanto_preview_image', $img );
		$html = juanto_render_block_markup( 'juanto/project-list', [ 'tier' => 'case', 'layout' => 'rows', 'label' => 'selected work' ] );
		$this->assertMatchesRegularExpression( '#NexConnect.*Embajada#s', $html );
		$this->assertStringNotContainsString( 'Recargame', $html );
		$this->assertStringContainsString( '<span class="juanto-row__n">01</span>', $html );
		$this->assertStringContainsString( 'brand · product', $html );
		$this->assertStringContainsString( '2025 / 2026', $html );
		$this->assertStringContainsString( 'data-preview="' . wp_get_attachment_image_url( $img, 'large' ) . '"', $html );
		$this->assertStringContainsString( 'class="juanto-preview"', $html );
	}

	public function test_project_list_columns_show_count_and_summary() {
		$this->listed( 'card', 'Recargame', 1, 2025 );
		$this->listed( 'card', 'CrediFlux', 2, 2025 );
		$html = juanto_render_block_markup( 'juanto/project-list', [ 'tier' => 'card', 'layout' => 'columns', 'label' => 'more projects' ] );
		$this->assertStringContainsString( 'is-columns', $html );
		$this->assertStringContainsString( '<span class="juanto-list__aside">2</span>', $html );
		$this->assertStringContainsString( 'Recargame summary', $html );
		$this->assertStringNotContainsString( 'juanto-preview', $html );
	}

	public function test_lab_teaser_fans_three_pieces_and_links_to_the_archive() {
		for ( $i = 1; $i <= 4; $i++ ) {
			$id = $this->listed( 'lab', "Piece {$i}", $i, 2024 );
			set_post_thumbnail( $id, $this->image() );
		}
		$html = juanto_render_block_markup( 'juanto/lab-teaser', [ 'title' => 'Logos, labels, merch and weekend builds' ] );
		$this->assertSame( 3, substr_count( $html, '<img' ) );
		$this->assertStringContainsString( 'href="' . get_post_type_archive_link( 'project' ) . '"', $html );
		$this->assertStringContainsString( '4 pieces', $html );
	}

	public function test_toc_items_come_from_section_heads_in_order() {
		$content = '<!-- wp:juanto/section-head {"number":"01","label":"the problem","title":"A"} /-->'
			. '<!-- wp:group --><div class="wp-block-group"><!-- wp:juanto/section-head {"number":"02","label":"brand","title":"B","anchor":"brand-x"} /--></div><!-- /wp:group -->';
		$this->assertSame(
			[ [ 'anchor' => 'the-problem', 'number' => '01', 'label' => 'the problem' ], [ 'anchor' => 'brand-x', 'number' => '02', 'label' => 'brand' ] ],
			juanto_toc_items( $content )
		);
		$id   = self::factory()->post->create( [ 'post_type' => 'project', 'post_content' => $content ] );
		$html = ( new WP_Block( [ 'blockName' => 'juanto/case-toc', 'attrs' => [] ], [ 'postId' => $id ] ) )->render();
		$this->assertStringContainsString( '<a href="#brand-x"><span>02</span>brand</a>', $html );
	}

	public function test_lang_badge_renders_the_posts_language_and_nothing_without_one() {
		juanto_seed_terms();
		$render = static function ( int $id ): string {
			return trim( ( new WP_Block( [ 'blockName' => 'juanto/lang-badge', 'attrs' => [] ], [ 'postId' => $id ] ) )->render() );
		};

		$en = self::factory()->post->create();
		wp_set_object_terms( $en, 'en', 'language' );
		$this->assertMatchesRegularExpression( '#^<span class="[^"]*\bjuanto-lang-badge\b[^"]*">EN</span>$#', $render( $en ) );

		$es = self::factory()->post->create();
		wp_set_object_terms( $es, 'es', 'language' );
		$this->assertMatchesRegularExpression( '#^<span class="[^"]*\bjuanto-lang-badge\b[^"]*">ES</span>$#', $render( $es ) );

		$none = self::factory()->post->create();
		$this->assertSame( '', $render( $none ) );
	}

	public function test_lab_stack_renders_prints_tiles_and_notes() {
		$photo = $this->listed( 'lab', '#TeamChamo', 1, 2024, [ 'apparel', 'brand' ] );
		set_post_thumbnail( $photo, $this->image() );
		$logo = $this->listed( 'lab', 'Gils Home Services', 2, 2023, [ 'brand' ] );
		set_post_thumbnail( $logo, $this->image() );
		update_post_meta( $logo, 'juanto_lab_fit', 'contain' );
		$note = $this->listed( 'lab', 'MemoryFrame', 3, 2026, [ 'hardware', 'code' ] );
		update_post_meta( $note, 'juanto_lab_note', 'A 7" smart photo frame.' );
		$this->listed( 'lab', 'No image, no note', 4, 2026 ); // skipped: nothing to show

		$html = juanto_render_block_markup( 'juanto/lab-stack', [] );
		$this->assertSame( 3, substr_count( $html, 'class="juanto-print' ) );
		$this->assertSame( 3, substr_count( $html, 'class="juanto-tile' ) );
		$this->assertStringContainsString( 'juanto-frame is-contain', $html );
		$this->assertStringContainsString( '<div class="juanto-frame is-note"><b>MemoryFrame</b><p>A 7&quot; smart photo frame.</p>', $html );
		$this->assertStringContainsString( 'data-meta="apparel · brand · 2024"', $html );
		$this->assertStringNotContainsString( 'No image, no note', $html );
		// A tabindex="0" div with only aria-label carries no implicit role, so
		// assistive tech announces it as a plain, unlabelled group of content;
		// role="group" plus aria-roledescription identify it as a "stack".
		$this->assertMatchesRegularExpression( '#class="juanto-lab__pile" tabindex="0" role="group" aria-roledescription="stack"#', $html );
	}

	public function test_lab_stack_top_print_and_first_tile_are_eager_everything_else_stays_lazy() {
		// The top pile print (stack layout) and the first "all" grid tile (grid
		// layout, the default at mobile widths) are each what's actually visible
		// at load on their respective view -- one of them is the LCP candidate
		// depending on viewport width. Forcing either lazy delays discovery.
		// Only the pile's top print also gets fetchpriority="high": it's the
		// stack view's sole visible image, while the grid shows several tiles
		// at once.
		$photo = $this->listed( 'lab', '#TeamChamo', 1, 2024, [ 'apparel', 'brand' ] );
		set_post_thumbnail( $photo, $this->image() );
		$logo = $this->listed( 'lab', 'Gils Home Services', 2, 2023, [ 'brand' ] );
		set_post_thumbnail( $logo, $this->image() );

		$html = juanto_render_block_markup( 'juanto/lab-stack', [] );

		$pile_start   = strpos( $html, 'juanto-lab__pile' );
		$all_start    = strpos( $html, 'juanto-lab__all' );
		$pile_html    = substr( $html, $pile_start, $all_start - $pile_start );
		$all_html     = substr( $html, $all_start );
		$first_print  = substr( $pile_html, strpos( $pile_html, 'data-i="0"' ) );
		$first_print  = substr( $first_print, 0, strpos( $first_print, 'data-i="1"' ) );
		$second_print = substr( $pile_html, strpos( $pile_html, 'data-i="1"' ) );
		$first_tile   = substr( $all_html, strpos( $all_html, 'data-i="0"' ) );
		$first_tile   = substr( $first_tile, 0, strpos( $first_tile, 'data-i="1"' ) );
		$second_tile  = substr( $all_html, strpos( $all_html, 'data-i="1"' ) );

		$this->assertStringContainsString( 'loading="eager"', $first_print );
		$this->assertStringContainsString( 'fetchpriority="high"', $first_print );
		$this->assertStringNotContainsString( 'loading="lazy"', $first_print );

		$this->assertStringContainsString( 'loading="lazy"', $second_print );
		$this->assertStringNotContainsString( 'fetchpriority="high"', $second_print );

		$this->assertStringContainsString( 'loading="eager"', $first_tile );
		$this->assertStringNotContainsString( 'fetchpriority="high"', $first_tile );
		$this->assertStringNotContainsString( 'loading="lazy"', $first_tile );

		$this->assertStringContainsString( 'loading="lazy"', $second_tile );
		$this->assertStringNotContainsString( 'fetchpriority="high"', $second_tile );
	}
}
