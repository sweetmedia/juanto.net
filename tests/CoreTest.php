<?php
class CoreTest extends WP_UnitTestCase {
	public function test_plugin_constants_are_defined() {
		$this->assertSame( '0.1.0', JUANTO_CORE_VERSION );
		$this->assertStringEndsWith( 'juanto-core/', JUANTO_CORE_DIR );
	}

	public function test_theme_is_active() {
		$this->assertSame( 'juanto', get_stylesheet() );
	}
}
