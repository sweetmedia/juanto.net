<?php
/**
 * Last-commit date for the status bar.
 *
 * @package juanto-core
 */

/**
 * Read-only token from wp-config.php. Never stored in the database.
 */
function juanto_github_token(): string {
	$token = defined( 'JUANTO_GITHUB_TOKEN' ) ? (string) JUANTO_GITHUB_TOKEN : '';
	return (string) apply_filters( 'juanto_github_token', $token );
}

/**
 * Transient key for a repo's cached commit date. Shared by every function
 * below so the cache is always read from and written to the same key.
 *
 * @param string $repo "owner/repo".
 */
function juanto_commit_transient_key( string $repo ): string {
	return 'juanto_commit_' . md5( $repo );
}

/**
 * Always performs the GitHub HTTP request and (over)writes the cache,
 * regardless of what is currently cached. Successful lookups are cached for
 * a full day: WP-Cron is traffic-driven, so a short TTL tied to the hourly
 * refresh event tends to expire between visits and leave the status bar
 * without a "last commit" date for the next visitor. The cron handler below
 * overwrites the transient directly (no delete first), so readers never see
 * a cold cache while a refresh is in flight.
 *
 * @param string $repo "owner/repo".
 */
function juanto_fetch_commit_date( string $repo ): ?int {
	$headers = [
		'Accept'               => 'application/vnd.github+json',
		'User-Agent'           => 'juanto.net',
		'X-GitHub-Api-Version' => '2022-11-28',
	];
	$token   = juanto_github_token();
	if ( '' !== $token ) {
		$headers['Authorization'] = 'Bearer ' . $token;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . $repo . '/commits?per_page=1',
		[ 'headers' => $headers, 'timeout' => 5 ]
	);

	$date = null;
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$iso  = $body[0]['commit']['committer']['date'] ?? '';
		$ts   = $iso ? strtotime( $iso ) : false;
		$date = $ts ? (int) $ts : null;
	}

	set_transient( juanto_commit_transient_key( $repo ), $date ?? 0, $date ? DAY_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
	return $date;
}

/**
 * Timestamp of the latest commit on the default branch, or null. Uses the
 * cache when present; only calls GitHub on a cold cache.
 *
 * @param string $repo "owner/repo".
 */
function juanto_last_commit_date( string $repo ): ?int {
	if ( '' === $repo ) {
		return null;
	}
	$cached = get_transient( juanto_commit_transient_key( $repo ) );
	if ( false !== $cached ) {
		return $cached ? (int) $cached : null;
	}

	return juanto_fetch_commit_date( $repo );
}

/**
 * Cached commit timestamp only, never performs HTTP. Safe to call on every page render.
 *
 * @param string $repo "owner/repo".
 */
function juanto_cached_commit_date( string $repo ): ?int {
	if ( '' === $repo ) {
		return null;
	}
	$cached = get_transient( juanto_commit_transient_key( $repo ) );
	return $cached ? (int) $cached : null;
}

add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'juanto_refresh_commit_date' ) ) {
			wp_schedule_event( time(), 'hourly', 'juanto_refresh_commit_date' );
		}
	}
);

add_action(
	'juanto_refresh_commit_date',
	static function () {
		$repo = juanto_get_status()['repo'];
		if ( '' === $repo ) {
			return;
		}
		// Overwrites the transient directly; never deletes it first, so a
		// reader mid-refresh still gets the previous (stale but present)
		// cached value instead of a cold cache.
		juanto_fetch_commit_date( $repo );
	}
);
