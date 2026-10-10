<?php
class QueriesTest extends WP_UnitTestCase {
	private function project( string $tier, int $order, string $status = 'publish' ): int {
		$id = self::factory()->post->create( [ 'post_type' => 'project', 'menu_order' => $order, 'post_status' => $status ] );
		wp_set_object_terms( $id, $tier, 'tier' );
		return $id;
	}

	public function set_up(): void {
		parent::set_up();
		juanto_seed_terms();
	}

	public function test_get_projects_filters_by_tier_and_orders_by_menu_order() {
		$b     = $this->project( 'case', 2 );
		$a     = $this->project( 'case', 1 );
		$this->project( 'card', 0 );
		$this->project( 'case', 0, 'draft' );
		$this->assertSame( [ $a, $b ], wp_list_pluck( juanto_get_projects( 'case' ), 'ID' ) );
		$this->assertCount( 1, juanto_get_projects( 'case', 1 ) );
		$this->assertSame( 'card', juanto_project_tier( juanto_get_projects( 'card' )[0]->ID ) );
	}

	public function test_next_case_wraps_and_ignores_cards() {
		$one  = $this->project( 'case', 1 );
		$two  = $this->project( 'case', 2 );
		$card = $this->project( 'card', 1 );
		$this->assertSame( $two, juanto_next_case( $one )->ID );
		$this->assertSame( $one, juanto_next_case( $two )->ID );
		$this->assertNull( juanto_next_case( $card ) );
	}
}
