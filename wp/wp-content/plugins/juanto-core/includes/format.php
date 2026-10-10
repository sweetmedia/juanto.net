<?php
/**
 * Formatting helpers. No em dashes in output.
 *
 * @package juanto-core
 */

/**
 * "2026 to present", "2023 to 2024" or "2025".
 *
 * @param int    $start First year.
 * @param string $end   "present", a year, or "".
 */
function juanto_year_range( int $start, string $end ): string {
	if ( $start <= 0 ) {
		return '';
	}
	if ( '' === $end || (string) $start === $end ) {
		return (string) $start;
	}
	return sprintf( '%d to %s', $start, $end );
}

/**
 * Lowercase "aug 30" in UTC (the GitHub commit timestamp this formats
 * is already an absolute instant; there's no reason to convert it to
 * the site timezone).
 *
 * @param int $timestamp Unix time.
 */
function juanto_short_date( int $timestamp ): string {
	return strtolower( wp_date( 'M j', $timestamp, new DateTimeZone( 'UTC' ) ) );
}

/**
 * Discipline names joined with a middle dot.
 *
 * @param int $post_id Project ID.
 * @param int $max     Maximum number of tags.
 */
function juanto_project_tags( int $post_id, int $max = 3 ): string {
	$terms = get_the_terms( $post_id, 'discipline' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$names = wp_list_pluck( $terms, 'name' );
	sort( $names );
	return implode( ' · ', array_slice( $names, 0, $max ) );
}
