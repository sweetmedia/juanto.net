<?php
class FormatTest extends WP_UnitTestCase {
	public function test_year_range() {
		$this->assertSame( '2026 to present', juanto_year_range( 2026, 'present' ) );
		$this->assertSame( '2023 to 2024', juanto_year_range( 2023, '2024' ) );
		$this->assertSame( '2025', juanto_year_range( 2025, '2025' ) );
		$this->assertSame( '2025', juanto_year_range( 2025, '' ) );
		$this->assertSame( '', juanto_year_range( 0, 'present' ) );
	}

	public function test_short_date_is_lowercase_month_day() {
		$this->assertSame( 'aug 30', juanto_short_date( strtotime( '2026-08-30 23:45:42 UTC' ) ) );
	}

	public function test_project_tags_join_with_middot_and_cap() {
		juanto_seed_terms();
		$id = self::factory()->post->create( [ 'post_type' => 'project' ] );
		wp_set_object_terms( $id, [ 'brand', 'product', 'code', 'web' ], 'discipline' );
		$this->assertSame( 'brand · code · product', juanto_project_tags( $id ) );
		$this->assertSame( '', juanto_project_tags( self::factory()->post->create( [ 'post_type' => 'project' ] ) ) );
	}
}
