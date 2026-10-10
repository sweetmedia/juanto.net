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

/**
 * Lower-cased tool names of a tutorial.
 *
 * @param int $post_id Post ID.
 * @return string[]
 */
function juanto_tutorial_tool_names( int $post_id ): array {
	return array_map( static fn( array $t ) => strtolower( $t['name'] ), juanto_get_tutorial( $post_id )['tools'] );
}

/**
 * Other published tutorials: those sharing a tool first, then the newest.
 *
 * @param int $post_id Current tutorial.
 * @param int $limit   How many.
 * @return WP_Post[]
 */
function juanto_related_tutorials( int $post_id, int $limit = 3 ): array {
	$posts  = get_posts(
		[
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'category_name'    => 'tutorials',
			'post__not_in'     => [ $post_id ],
			'posts_per_page'   => 50,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'no_found_rows'    => true,
			'suppress_filters' => false,
		]
	);
	$mine   = juanto_tutorial_tool_names( $post_id );
	$shared = [];
	$rest   = [];
	foreach ( $posts as $candidate ) {
		if ( $mine && array_intersect( $mine, juanto_tutorial_tool_names( $candidate->ID ) ) ) {
			$shared[] = $candidate;
		} else {
			$rest[] = $candidate;
		}
	}
	return array_slice( array_merge( $shared, $rest ), 0, $limit );
}

// A Query Loop whose "query" attribute carries "juantoTutorials": true lists tutorials only.
// Category IDs differ between environments, so templates cannot hard-code taxQuery.
add_filter(
	'query_loop_block_query_vars',
	static function ( array $query, WP_Block $block ): array {
		if ( ! empty( $block->context['query']['juantoTutorials'] ) ) {
			$query['category_name'] = 'tutorials';
		}
		return $query;
	},
	10,
	2
);
