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

	private function tut( string $title, array $tools, string $date, string $status = 'publish' ): int {
		juanto_seed_terms();
		$id = self::factory()->post->create( [ 'post_title' => $title, 'post_date' => $date, 'post_status' => $status ] );
		wp_set_post_categories( $id, [ get_category_by_slug( 'tutorials' )->term_id ] );
		update_post_meta( $id, 'juanto_tut_tools', array_map( static fn( $n ) => [ 'name' => $n, 'version' => '' ], $tools ) );
		return $id;
	}

	public function test_related_tutorials_prefer_shared_tools_then_recency() {
		$current = $this->tut( 'Current', [ 'Elementor Pro' ], '2024-01-01 00:00:00' );
		$shared  = $this->tut( 'Shared', [ 'elementor pro' ], '2023-01-01 00:00:00' );
		$newest  = $this->tut( 'Newest', [ 'Figma' ], '2025-01-01 00:00:00' );
		$older   = $this->tut( 'Older', [], '2022-01-01 00:00:00' );
		$this->tut( 'Draft', [ 'Elementor Pro' ], '2025-06-01 00:00:00', 'draft' );
		self::factory()->post->create( [ 'post_title' => 'Not a tutorial' ] );
		$this->assertSame( [ $shared, $newest, $older ], wp_list_pluck( juanto_related_tutorials( $current ), 'ID' ) );
		$this->assertSame( [ $shared ], wp_list_pluck( juanto_related_tutorials( $current, 1 ), 'ID' ) );
	}

	public function test_no_other_tutorials_means_no_related() {
		$this->assertSame( [], juanto_related_tutorials( $this->tut( 'Alone', [], '2024-01-01 00:00:00' ) ) );
	}
}
