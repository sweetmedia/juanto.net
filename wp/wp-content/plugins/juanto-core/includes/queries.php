<?php
/**
 * Project queries.
 *
 * @package juanto-core
 */

/**
 * Tier slug of a project.
 *
 * @param int $post_id Project ID.
 */
function juanto_project_tier( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'tier' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
}

/**
 * Published projects of one tier in manual order.
 *
 * @param string $tier  case, card or lab.
 * @param int    $limit -1 for all.
 * @return WP_Post[]
 */
function juanto_get_projects( string $tier, int $limit = -1 ): array {
	return get_posts(
		[
			'post_type'        => 'project',
			'post_status'      => 'publish',
			'posts_per_page'   => $limit,
			'orderby'          => [ 'menu_order' => 'ASC', 'date' => 'DESC' ],
			'tax_query'        => [ [ 'taxonomy' => 'tier', 'field' => 'slug', 'terms' => $tier ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);
}

/**
 * Next case study, wrapping to the first.
 *
 * @param int $post_id Current project ID.
 */
function juanto_next_case( int $post_id ): ?WP_Post {
	if ( 'case' !== juanto_project_tier( $post_id ) ) {
		return null;
	}
	$ids = wp_list_pluck( juanto_get_projects( 'case' ), 'ID' );
	$at  = array_search( $post_id, $ids, true );
	if ( false === $at || count( $ids ) < 2 ) {
		return null;
	}
	return get_post( $ids[ ( $at + 1 ) % count( $ids ) ] );
}
