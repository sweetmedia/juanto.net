<?php
class GithubTest extends WP_UnitTestCase {
	private int $calls = 0;
	private array $last_args = [];

	private function fake( int $code, string $body ): void {
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $code, $body ) {
				if ( ! str_starts_with( $url, 'https://api.github.com/' ) ) {
					return $pre;
				}
				++$this->calls;
				$this->last_args = $args;
				return [ 'headers' => [], 'body' => $body, 'response' => [ 'code' => $code, 'message' => '' ], 'cookies' => [], 'filename' => null ];
			},
			10,
			3
		);
	}

	public function test_returns_committer_date_and_caches_it() {
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-08-30T23:45:42Z"}}}]' );
		$this->assertSame( strtotime( '2026-08-30T23:45:42Z' ), juanto_last_commit_date( 'sweetmedia/kinpop' ) );
		juanto_last_commit_date( 'sweetmedia/kinpop' );
		$this->assertSame( 1, $this->calls );
	}

	public function test_failure_returns_null_and_is_cached_briefly() {
		$this->fake( 404, '{"message":"Not Found"}' );
		$this->assertNull( juanto_last_commit_date( 'sweetmedia/missing' ) );
		$this->assertNull( juanto_last_commit_date( 'sweetmedia/missing' ) );
		$this->assertSame( 1, $this->calls );
		$this->assertSame( 0, get_transient( 'juanto_commit_' . md5( 'sweetmedia/missing' ) ) );
	}

	public function test_sends_bearer_token_when_configured_and_skips_empty_repo() {
		add_filter( 'juanto_github_token', static fn() => 'test-token' );
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-09-01T00:00:00Z"}}}]' );
		juanto_last_commit_date( 'sweetmedia/kinpop-token' );
		$this->assertSame( 'Bearer test-token', $this->last_args['headers']['Authorization'] );
		$this->assertNull( juanto_last_commit_date( '' ) );
	}

	public function test_status_bar_render_with_cold_cache_makes_no_http_request() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'enabled' => 1, 'project' => 'KinPop', 'repo' => 'sweetmedia/kinpop-cold' ] ) );
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-08-30T12:00:00Z"}}}]' );
		$html = juanto_render_block_markup( 'juanto/status-bar', [] );
		$this->assertSame( 0, $this->calls );
		$this->assertStringNotContainsString( 'last commit', $html );
	}

	public function test_commit_refresh_is_scheduled_hourly_on_init() {
		// 'init' already ran once during bootstrap (muplugins_loaded loads the
		// plugin before WP fires it), so the event is scheduled by now.
		$this->assertNotFalse( wp_next_scheduled( 'juanto_refresh_commit_date' ) );
	}

	public function test_refresh_action_performs_one_request_and_populates_cache() {
		update_option( 'juanto_status', juanto_sanitize_status( [ 'enabled' => 1, 'project' => 'KinPop', 'repo' => 'sweetmedia/kinpop-refresh' ] ) );
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-09-01T00:00:00Z"}}}]' );
		do_action( 'juanto_refresh_commit_date' );
		$this->assertSame( 1, $this->calls );
		$this->assertSame( strtotime( '2026-09-01T00:00:00Z' ), juanto_cached_commit_date( 'sweetmedia/kinpop-refresh' ) );
	}

	public function test_transient_key_helper_is_used_for_reads_and_writes() {
		$repo = 'sweetmedia/kinpop-key';
		$this->assertSame( 'juanto_commit_' . md5( $repo ), juanto_commit_transient_key( $repo ) );

		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-09-01T00:00:00Z"}}}]' );
		juanto_last_commit_date( $repo );
		$this->assertSame( strtotime( '2026-09-01T00:00:00Z' ), get_transient( juanto_commit_transient_key( $repo ) ) );
	}

	public function test_successful_lookup_is_cached_for_a_full_day() {
		$repo = 'sweetmedia/kinpop-ttl';
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-09-01T00:00:00Z"}}}]' );
		juanto_last_commit_date( $repo );

		$timeout = (int) get_option( '_transient_timeout_' . juanto_commit_transient_key( $repo ) );
		// A 1 hour TTL (the old value, equal to the cron interval) would put
		// the timeout at time()+HOUR_IN_SECONDS; assert it is well past that,
		// consistent with DAY_IN_SECONDS.
		$this->assertGreaterThan( time() + HOUR_IN_SECONDS, $timeout );
		$this->assertLessThanOrEqual( time() + DAY_IN_SECONDS + 5, $timeout );
	}

	public function test_failure_cache_ttl_is_unchanged() {
		$repo = 'sweetmedia/kinpop-fail-ttl';
		$this->fake( 404, '{"message":"Not Found"}' );
		juanto_last_commit_date( $repo );

		$timeout = (int) get_option( '_transient_timeout_' . juanto_commit_transient_key( $repo ) );
		$this->assertLessThanOrEqual( time() + 10 * MINUTE_IN_SECONDS + 5, $timeout );
		$this->assertGreaterThan( time(), $timeout );
	}

	public function test_refresh_overwrites_the_transient_without_deleting_it_first() {
		$repo = 'sweetmedia/kinpop-overwrite';
		update_option( 'juanto_status', juanto_sanitize_status( [ 'enabled' => 1, 'project' => 'KinPop', 'repo' => $repo ] ) );
		$this->fake( 200, '[{"commit":{"committer":{"date":"2026-09-01T00:00:00Z"}}}]' );

		$deleted = [];
		add_action(
			'deleted_transient',
			static function ( $transient ) use ( &$deleted ) {
				$deleted[] = $transient;
			}
		);

		do_action( 'juanto_refresh_commit_date' );

		$this->assertNotContains( juanto_commit_transient_key( $repo ), $deleted );
		$this->assertSame( strtotime( '2026-09-01T00:00:00Z' ), juanto_cached_commit_date( $repo ) );
	}
}
