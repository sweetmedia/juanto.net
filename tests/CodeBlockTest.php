<?php
class CodeBlockTest extends WP_UnitTestCase {
	public function test_code_without_filename_is_wrapped_and_loads_the_copy_script() {
		wp_dequeue_script( 'juanto-code-copy' );
		$html = do_blocks( '<!-- wp:code --><pre class="wp-block-code"><code>npm run build</code></pre><!-- /wp:code -->' );
		$this->assertMatchesRegularExpression( '#^<div class="juanto-code"><pre class="wp-block-code"><code>npm run build</code></pre></div>$#', trim( $html ) );
		$this->assertTrue( wp_script_is( 'juanto-code-copy', 'enqueued' ) );
	}

	public function test_code_with_filename_shows_an_escaped_tab() {
		$html = do_blocks( '<!-- wp:code {"filename":"functions.php <x>"} --><pre class="wp-block-code"><code>add_filter();</code></pre><!-- /wp:code -->' );
		$this->assertStringContainsString( '<div class="juanto-code has-file"><p class="juanto-code__file">functions.php &lt;x&gt;</p><pre', $html );
	}

	public function test_filename_attribute_is_registered_on_core_code() {
		$attrs = WP_Block_Type_Registry::get_instance()->get_registered( 'core/code' )->attributes;
		$this->assertSame( [ 'type' => 'string', 'default' => '' ], $attrs['filename'] );
	}
}
