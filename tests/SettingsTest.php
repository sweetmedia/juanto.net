<?php
class SettingsTest extends WP_UnitTestCase {
	public function test_sanitize_clamps_milestones_and_validates_repo_and_urls() {
		$s = juanto_sanitize_status(
			[
				'enabled'           => '1',
				'project'           => ' KinPop <script>',
				'description'       => 'realtime family celebrations',
				'milestone_current' => '14',
				'milestone_total'   => '10',
				'url'               => 'javascript:void(0)',
				'repo'              => 'sweetmedia/kinpop',
				'resume_url'        => 'https://juanto.net/resume.pdf',
				'availability'      => 'open to product, brand and engineering roles',
			]
		);
		$this->assertTrue( $s['enabled'] );
		$this->assertSame( 'KinPop', $s['project'] );
		$this->assertSame( 10, $s['milestone_current'] );
		$this->assertSame( '', $s['url'] );
		$this->assertSame( 'sweetmedia/kinpop', $s['repo'] );
	}

	public function test_invalid_repo_is_dropped_and_missing_checkbox_means_disabled() {
		$s = juanto_sanitize_status( [ 'repo' => 'not a repo', 'milestone_total' => '0' ] );
		$this->assertSame( '', $s['repo'] );
		$this->assertFalse( $s['enabled'] );
		$this->assertSame( 1, $s['milestone_total'] );
	}

	public function test_get_status_merges_defaults() {
		update_option( 'juanto_status', [ 'project' => 'KinPop' ] );
		$s = juanto_get_status();
		$this->assertSame( 'KinPop', $s['project'] );
		$this->assertSame( 10, $s['milestone_total'] );
	}

	public function test_sweetmedia_url_accepts_http_links_only() {
		$this->assertSame( 'https://sweetmedia.net', juanto_sanitize_status( [ 'sweetmedia_url' => 'https://sweetmedia.net' ] )['sweetmedia_url'] );
		$this->assertSame( '', juanto_sanitize_status( [ 'sweetmedia_url' => 'javascript:alert(1)' ] )['sweetmedia_url'] );
		$this->assertSame( '', juanto_status_defaults()['sweetmedia_url'] );
	}
}
