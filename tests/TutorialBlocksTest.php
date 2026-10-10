<?php
class TutorialBlocksTest extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();
		juanto_seed_terms();
		juanto_register_tutorial_meta();
	}

	private function tutorial( string $content = '', array $args = [] ): int {
		$id = self::factory()->post->create( [ 'post_content' => wp_slash( $content ) ] + $args );
		wp_set_post_categories( $id, [ get_category_by_slug( 'tutorials' )->term_id ] );
		return $id;
	}

	private function step( string $title, string $inner = '<!-- wp:paragraph --><p>Do it.</p><!-- /wp:paragraph -->' ): string {
		return '<!-- wp:juanto/tutorial-step ' . wp_json_encode( [ 'title' => $title ] ) . " -->\n{$inner}\n<!-- /wp:juanto/tutorial-step -->\n\n";
	}

	private function rendered_content( int $id ): string {
		$this->go_to( get_permalink( $id ) );
		the_post();
		return apply_filters( 'the_content', get_the_content() );
	}

	public function test_steps_are_numbered_in_order_with_unique_anchors() {
		$content = $this->step( 'Copy the URL' ) . $this->step( 'Copy the URL' ) . $this->step( '' )
			. '<!-- wp:group --><div class="wp-block-group">' . $this->step( 'Nested one' ) . '</div><!-- /wp:group -->';
		$steps   = juanto_tutorial_steps_from_content( $content );
		$this->assertSame( [ 1, 2, 3, 4 ], array_column( $steps, 'n' ) );
		$this->assertSame( [ 'copy-the-url', 'copy-the-url-2', 'step-3', 'nested-one' ], array_column( $steps, 'anchor' ) );
	}

	public function test_step_text_and_image_come_from_the_first_paragraph_and_image() {
		$img   = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$inner = '<!-- wp:paragraph --><p>Open <strong>Media</strong>.</p><!-- /wp:paragraph -->'
			. '<!-- wp:image {"id":' . $img . '} --><figure class="wp-block-image"><img src="x.jpg" alt="" class="wp-image-' . $img . '"/></figure><!-- /wp:image -->'
			. '<!-- wp:paragraph --><p>Second.</p><!-- /wp:paragraph -->';
		$step  = juanto_tutorial_steps_from_content( $this->step( 'Upload', $inner ) )[0];
		$this->assertSame( 'Open Media.', $step['text'] );
		$this->assertSame( $img, $step['image_id'] );
	}

	public function test_reordering_steps_renumbers_them() {
		$a = $this->step( 'Alpha' );
		$b = $this->step( 'Beta' );
		$this->assertSame( [ 'Alpha', 'Beta' ], array_column( juanto_tutorial_steps_from_content( $a . $b ), 'title' ) );
		$this->assertSame( [ 'Beta', 'Alpha' ], array_column( juanto_tutorial_steps_from_content( $b . $a ), 'title' ) );
	}

	public function test_step_renders_number_heading_and_anchor() {
		$html = $this->rendered_content( $this->tutorial( $this->step( 'Copy the URL' ) . $this->step( 'Edit the link' ) ) );
		$this->assertStringContainsString( 'id="copy-the-url"', $html );
		$this->assertStringContainsString( 'aria-labelledby="edit-the-link-title"', $html );
		$this->assertMatchesRegularExpression( '#<span class="juanto-step__num" aria-hidden="true">02</span>#', $html );
		$this->assertStringContainsString( '<span class="screen-reader-text">Step 2: </span>Edit the link</h2>', $html );
		$this->assertStringContainsString( '>Do it.</p>', $html );
	}

	public function test_numbers_restart_on_every_content_pass() {
		$content = $this->step( 'One' ) . $this->step( 'Two' );
		$id      = $this->tutorial( $content );
		$this->go_to( get_permalink( $id ) );
		the_post();
		apply_filters( 'the_content', $content );
		$html = apply_filters( 'the_content', $content );
		$this->assertStringContainsString( 'aria-hidden="true">01</span>', $html );
		$this->assertStringContainsString( 'aria-hidden="true">02</span>', $html );
		$this->assertStringNotContainsString( 'aria-hidden="true">03</span>', $html );
	}

	public function test_same_titled_steps_get_distinct_ids_when_the_lookup_cannot_match() {
		unset( $GLOBALS['post'] );
		$html = apply_filters( 'the_content', $this->step( 'Same' ) . $this->step( 'Same' ) );
		$this->assertStringContainsString( 'id="same"', $html );
		$this->assertStringContainsString( 'id="same-2"', $html );
		$this->assertStringContainsString( 'aria-labelledby="same-2-title"', $html );
	}

	public function test_special_characters_are_escaped_once_and_slugged() {
		$html = $this->rendered_content( $this->tutorial( $this->step( 'Configura el botón "Descargar" & listo' ) ) );
		$this->assertStringContainsString( 'id="configura-el-boton-descargar-listo"', $html );
		$this->assertStringContainsString( 'Configura el botón &quot;Descargar&quot; &amp; listo</h2>', $html );
		$this->assertStringNotContainsString( '&amp;amp;', $html );
	}

	public function test_callout_renders_a_labelled_note() {
		$html = do_blocks( '<!-- wp:juanto/callout {"variant":"warning"} --><!-- wp:paragraph --><p>Back up first.</p><!-- /wp:paragraph --><!-- /wp:juanto/callout -->' );
		$this->assertMatchesRegularExpression( '#<aside class="[^"]*\bjuanto-callout is-warning\b[^"]*" role="note">#', $html );
		$this->assertStringContainsString( 'Warning</p>', $html );
		$this->assertStringContainsString( '<svg', $html );
		$this->assertStringContainsString( '>Back up first.</p>', $html );
	}

	public function test_callout_falls_back_to_tip_without_an_icon() {
		$html = do_blocks( '<!-- wp:juanto/callout {"variant":"shout"} --><!-- wp:paragraph --><p>x</p><!-- /wp:paragraph --><!-- /wp:juanto/callout -->' );
		$this->assertStringContainsString( 'juanto-callout is-tip', $html );
		$this->assertStringContainsString( 'Tip</p>', $html );
		$this->assertStringNotContainsString( '<svg', $html );
	}

	private function render_for( int $id, array $attrs ): string {
		global $post;
		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$html = juanto_render_block_markup( 'juanto/tutorial-meta', $attrs );
		wp_reset_postdata();
		return $html;
	}

	public function test_meta_renders_nothing_for_ordinary_posts() {
		$id = self::factory()->post->create();
		foreach ( [ 'full', 'row', 'badge' ] as $variant ) {
			$this->assertSame( '', $this->render_for( $id, [ 'variant' => $variant ] ), $variant );
		}
	}

	public function test_badge_says_tutorial() {
		$this->assertMatchesRegularExpression( '#<span class="[^"]*\bjuanto-tut-badge\b[^"]*">Tutorial</span>#', $this->render_for( $this->tutorial(), [ 'variant' => 'badge' ] ) );
	}

	public function test_full_meta_shows_facts_as_chips_without_empties() {
		$id = $this->tutorial();
		update_post_meta( $id, 'juanto_tut_tools', [ [ 'name' => 'Elementor Pro', 'version' => '' ], [ 'name' => 'WordPress', 'version' => '6.5' ] ] );
		// The meta sanitizer strips tags on save; store the raw row to prove the render escapes too.
		$GLOBALS['wpdb']->insert( $GLOBALS['wpdb']->postmeta, [ 'post_id' => $id, 'meta_key' => 'juanto_tut_prereqs', 'meta_value' => maybe_serialize( [ 'A file <in> the library' ] ) ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		wp_cache_delete( $id, 'post_meta' );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		// Minutes 0 and no tested date: exactly the two tool chips, nothing empty.
		$this->assertStringContainsString( '<li class="juanto-chip">Elementor Pro</li><li class="juanto-chip">WordPress 6.5</li></ul>', $html );
		$this->assertStringNotContainsString( 'min</li>', $html );
		$this->assertStringContainsString( '<li>A file &lt;in&gt; the library</li>', $html );
		$this->assertStringNotContainsString( 'juanto-video', $html );
	}

	public function test_row_order_and_staleness() {
		$id = $this->tutorial();
		update_post_meta( $id, 'juanto_tut_minutes', 10 );
		update_post_meta( $id, 'juanto_tut_tools', [ [ 'name' => 'Figma', 'version' => '' ] ] );
		update_post_meta( $id, 'juanto_tut_tested', ( new DateTimeImmutable( '-19 months', wp_timezone() ) )->format( 'Y-m-d' ) );
		$this->assertMatchesRegularExpression( '#Figma · 10 min · Tested [A-Z][a-z]{2} \d{4}#', $this->render_for( $id, [ 'variant' => 'row' ] ) );
		$this->assertStringContainsString( '<li class="juanto-chip is-stale">May be outdated</li>', $this->render_for( $id, [ 'variant' => 'full' ] ) );
		update_post_meta( $id, 'juanto_tut_tested', ( new DateTimeImmutable( '-17 months', wp_timezone() ) )->format( 'Y-m-d' ) );
		$this->assertStringNotContainsString( 'May be outdated', $this->render_for( $id, [ 'variant' => 'full' ] ) );
	}

	public function test_result_is_a_wide_figure_only_with_a_result_image() {
		$id  = $this->tutorial();
		$img = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		set_post_thumbnail( $id, $img );
		update_post_meta( $id, 'juanto_tut_result', 'A button that downloads' );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		// The featured image is not a stand-in for the result: the result stays a text row.
		$this->assertStringNotContainsString( '<figure', $html );
		$this->assertStringContainsString( 'juanto-tut-meta__rows', $html );
		$this->assertStringContainsString( 'A button that downloads', $html );
		update_post_meta( $id, 'juanto_tut_result_image', $img );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		$this->assertStringContainsString( '<figure class="juanto-tut-meta__result">', $html );
		$this->assertStringContainsString( 'wp-image-' . $img, $html );
		$this->assertStringContainsString( 'loading="eager"', $html );
		$this->assertStringContainsString( 'fetchpriority="high"', $html );
		$this->assertStringNotContainsString( 'loading="lazy"', $html );
		$this->assertSame( 1, substr_count( $html, 'A button that downloads' ) ); // in the caption, not repeated as a row
	}

	public function test_full_meta_shows_a_compact_video_facade_without_an_iframe() {
		$id = $this->tutorial();
		update_post_meta( $id, 'juanto_tut_video', 'https://www.youtube.com/watch?v=MLpWrANjFbI' );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		$this->assertStringContainsString( 'data-video="MLpWrANjFbI"', $html );
		$this->assertStringContainsString( 'https://i.ytimg.com/vi/MLpWrANjFbI/hqdefault.jpg', $html );
		$this->assertStringContainsString( 'Prefer video?', $html );
		$this->assertStringNotContainsString( '<iframe', $html );
	}

	public function test_video_button_name_contains_its_visible_label_and_the_title() {
		$id = $this->tutorial();
		wp_update_post( [ 'ID' => $id, 'post_title' => 'Elementor downloads' ] );
		update_post_meta( $id, 'juanto_tut_video', 'https://www.youtube.com/watch?v=MLpWrANjFbI' );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		preg_match( '#<button\b[^>]*>(.*?)</button>#s', $html, $m );
		$this->assertNotEmpty( $m );
		$this->assertStringNotContainsString( 'aria-label', $m[0] );
		$text = wp_strip_all_tags( $m[1] );
		$this->assertStringContainsString( 'Prefer video?', $text );
		$this->assertStringContainsString( 'Elementor downloads', $text );
	}

	private function cta_for( int $id ): string {
		global $post;
		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$html = juanto_render_block_markup( 'juanto/tutorial-cta', [] );
		wp_reset_postdata();
		return $html;
	}

	public function test_clients_cta_needs_the_sweet_media_url() {
		$id = $this->tutorial();
		update_post_meta( $id, 'juanto_tut_audience', 'clients' );
		update_option( 'juanto_status', juanto_sanitize_status( [] ) );
		$this->assertSame( '', $this->cta_for( $id ) );
		update_option( 'juanto_status', juanto_sanitize_status( [ 'sweetmedia_url' => 'https://sweetmedia.net' ] ) );
		$html = $this->cta_for( $id );
		$this->assertStringContainsString( 'Need a hand with your site? Sweet Media builds and maintains sites like this.', $html );
		$this->assertStringContainsString( 'href="https://sweetmedia.net"', $html );
	}

	public function test_builders_cta_links_work_lab_and_github_when_set() {
		$this->set_permalink_structure( '/%postname%/' );
		$id = $this->tutorial();
		update_option( 'juanto_status', juanto_sanitize_status( [] ) );
		$html = $this->cta_for( $id );
		$this->assertStringContainsString( 'More of what I build', $html );
		$this->assertStringContainsString( 'href="' . home_url( '/' ) . '"', $html );
		$this->assertStringContainsString( 'href="' . home_url( '/lab/' ) . '"', $html );
		$this->assertStringNotContainsString( 'GitHub', $html );
		update_option( 'juanto_status', juanto_sanitize_status( [ 'github_url' => 'https://github.com/jtperez' ] ) );
		$this->assertStringContainsString( 'https://github.com/jtperez', $this->cta_for( $id ) );
	}

	public function test_cta_is_silent_on_ordinary_posts() {
		$this->assertSame( '', $this->cta_for( self::factory()->post->create() ) );
	}

	public function test_related_block_lists_titles_and_hides_when_empty() {
		$a = $this->tutorial( '', [ 'post_title' => 'First guide' ] );
		global $post;
		$post = get_post( $a ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$this->assertSame( '', juanto_render_block_markup( 'juanto/related-tutorials', [] ) );
		$b = $this->tutorial( '', [ 'post_title' => 'Second <guide>' ] );
		$html = juanto_render_block_markup( 'juanto/related-tutorials', [] );
		wp_reset_postdata();
		$this->assertStringContainsString( 'Related tutorials</h2>', $html );
		$this->assertStringContainsString( 'href="' . get_permalink( $b ) . '"', $html );
		$this->assertStringNotContainsString( 'href="' . get_permalink( $a ) . '"', $html );
	}

	public function test_related_titles_are_escaped_not_stripped() {
		// KSES strips <guide> from titles saved by users without unfiltered_html; an admin keeps the raw title.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$a = $this->tutorial( '', [ 'post_title' => 'First guide' ] );
		$b = $this->tutorial( '', [ 'post_title' => 'Second <guide>' ] );
		$this->assertSame( 'Second <guide>', get_post_field( 'post_title', $b, 'raw' ) );
		global $post;
		$post = get_post( $a ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$html = juanto_render_block_markup( 'juanto/related-tutorials', [] );
		wp_reset_postdata();
		$this->assertStringContainsString( 'Second &lt;guide&gt;</a>', $html );
		$this->assertStringNotContainsString( 'Second <guide>', $html );
	}

	public function test_meta_video_title_is_escaped_not_stripped() {
		$id = $this->tutorial();
		wp_update_post( [ 'ID' => $id, 'post_title' => 'Use <b>bold</b> tags' ] );
		update_post_meta( $id, 'juanto_tut_video', 'https://www.youtube.com/watch?v=MLpWrANjFbI' );
		$html = $this->render_for( $id, [ 'variant' => 'full' ] );
		$this->assertStringContainsString( 'data-title="Use &lt;b&gt;bold&lt;/b&gt; tags"', $html );
		$this->assertStringContainsString( 'Use &lt;b&gt;bold&lt;/b&gt; tags</span>', $html );
		$this->assertStringNotContainsString( '<b>', $html );
	}

	private function toc_for( int $id, array $attrs ): string {
		global $post;
		$post = get_post( $id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );
		$html = juanto_render_block_markup( 'juanto/case-toc', $attrs );
		wp_reset_postdata();
		return $html;
	}

	public function test_step_nav_shows_the_count_and_lists_every_step() {
		$id   = $this->tutorial( $this->step( 'Copy the URL' ) . $this->step( 'Edit the link' ) . $this->step( '' ) );
		$html = $this->toc_for( $id, [ 'source' => 'tutorial-step' ] );
		$this->assertMatchesRegularExpression( '#<nav [^>]*class="[^"]*\bjuanto-step-nav\b#', $html );
		$this->assertStringContainsString( 'aria-label="Tutorial steps"', $html );
		$this->assertStringContainsString( 'Step <b data-step-current>1</b> of 3', $html );
		$this->assertStringContainsString( '<span class="juanto-step-nav__title" data-step-title>Copy the URL</span>', $html );
		$this->assertStringContainsString( '<details>', $html );
		$this->assertStringContainsString( '<summary>', $html );
		$this->assertStringContainsString( '<ol>', $html );
		$this->assertSame( 2, substr_count( $html, '<i>' ) );
		$this->assertSame( 1, substr_count( $html, '<i class="is-on">' ) );
		$this->assertStringContainsString( '<a href="#copy-the-url">Copy the URL</a>', $html );
		$this->assertStringContainsString( '<a href="#step-3">Step 3</a>', $html );
	}

	public function test_step_nav_is_absent_without_steps() {
		$content = '<!-- wp:juanto/section-head {"number":"01","label":"the problem","title":"x"} /-->';
		$this->assertSame( '', $this->toc_for( $this->tutorial( $content ), [ 'source' => 'tutorial-step' ] ) );
	}

	public function test_step_nav_is_absent_for_a_password_protected_tutorial() {
		$id = $this->tutorial( $this->step( 'Secret step' ) );
		wp_update_post( [ 'ID' => $id, 'post_password' => 'hunter2' ] );
		$this->assertSame( '', $this->toc_for( $id, [ 'source' => 'tutorial-step' ] ) );
	}

	public function test_default_source_still_reads_section_heads() {
		$id   = self::factory()->post->create( [ 'post_content' => '<!-- wp:juanto/section-head {"number":"01","label":"the problem","title":"x"} /-->' ] );
		$html = $this->toc_for( $id, [] );
		$this->assertStringContainsString( 'href="#the-problem"', $html );
		$this->assertStringNotContainsString( 'juanto-step-nav', $html );
	}

	public function test_images_inside_steps_get_the_lightbox_by_default() {
		$img  = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$src  = wp_get_attachment_url( $img );
		$inner = '<!-- wp:image {"id":' . $img . ',"sizeSlug":"full"} --><figure class="wp-block-image size-full"><img src="' . $src . '" alt="Media library" class="wp-image-' . $img . '"/></figure><!-- /wp:image -->';
		$html = $this->rendered_content( $this->tutorial( $this->step( 'Upload', $inner ) ) );
		$this->assertStringContainsString( 'wp-lightbox-container', $html );
		$plain = do_blocks( $inner );
		$this->assertStringNotContainsString( 'wp-lightbox-container', $plain );
	}

	public function test_lede_shows_only_a_manual_excerpt() {
		$block = '<!-- wp:post-excerpt {"className":"juanto-tutorial__lede"} /-->';
		$auto  = $this->tutorial( '<!-- wp:paragraph --><p>Body text that would become an automatic excerpt.</p><!-- /wp:paragraph -->', [ 'post_excerpt' => '' ] ); // The factory otherwise invents an excerpt.
		$this->go_to( get_permalink( $auto ) );
		the_post();
		$this->assertSame( '', trim( do_blocks( $block ) ) );
		$manual = $this->tutorial( '', [ 'post_excerpt' => 'Force downloads in Elementor.' ] );
		$this->go_to( get_permalink( $manual ) );
		the_post();
		$this->assertStringContainsString( 'Force downloads in Elementor.', do_blocks( $block ) );
	}
}
