<?php
/**
 * "Now building" status bar settings.
 *
 * @package juanto-core
 */

/**
 * Default values.
 */
function juanto_status_defaults(): array {
	return [
		'enabled'           => true,
		'project'           => '',
		'description'       => '',
		'milestone_current' => 0,
		'milestone_total'   => 10,
		'url'               => '',
		'repo'              => '',
		'resume_url'        => '',
		'availability'      => '',
		'contact_email'     => '',
		'github_url'        => '',
		'linkedin_url'      => '',
	];
}

/**
 * Sanitizes the settings form.
 *
 * @param mixed $input Raw option.
 */
function juanto_sanitize_status( $input ): array {
	$in    = is_array( $input ) ? $input : [];
	$total = max( 1, min( 20, absint( $in['milestone_total'] ?? 10 ) ) );
	$repo  = trim( (string) ( $in['repo'] ?? '' ) );

	return [
		'enabled'           => ! empty( $in['enabled'] ),
		'project'           => sanitize_text_field( wp_strip_all_tags( (string) ( $in['project'] ?? '' ) ) ),
		'description'       => sanitize_text_field( (string) ( $in['description'] ?? '' ) ),
		'milestone_current' => min( $total, absint( $in['milestone_current'] ?? 0 ) ),
		'milestone_total'   => $total,
		'url'               => esc_url_raw( (string) ( $in['url'] ?? '' ), [ 'http', 'https' ] ),
		'repo'              => preg_match( '#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo ) ? $repo : '',
		'resume_url'        => esc_url_raw( (string) ( $in['resume_url'] ?? '' ), [ 'http', 'https' ] ),
		'availability'      => sanitize_text_field( (string) ( $in['availability'] ?? '' ) ),
		'contact_email'     => sanitize_email( (string) ( $in['contact_email'] ?? '' ) ),
		'github_url'        => esc_url_raw( (string) ( $in['github_url'] ?? '' ), [ 'https' ] ),
		'linkedin_url'      => esc_url_raw( (string) ( $in['linkedin_url'] ?? '' ), [ 'https' ] ),
	];
}

/**
 * Current settings merged over defaults.
 */
function juanto_get_status(): array {
	$saved = get_option( 'juanto_status', [] );
	return array_merge( juanto_status_defaults(), is_array( $saved ) ? $saved : [] );
}

add_action(
	'admin_init',
	static function () {
		register_setting( 'juanto', 'juanto_status', [ 'type' => 'array', 'sanitize_callback' => 'juanto_sanitize_status', 'default' => juanto_status_defaults() ] );
	}
);

add_action(
	'admin_menu',
	static function () {
		add_options_page( 'juanto', 'juanto', 'manage_options', 'juanto', 'juanto_render_settings_page' );
	}
);

/**
 * Settings screen.
 */
function juanto_render_settings_page(): void {
	$s      = juanto_get_status();
	$fields = [
		'project'           => [ __( 'Project', 'juanto' ), 'text' ],
		'description'       => [ __( 'Description', 'juanto' ), 'text' ],
		'milestone_current' => [ __( 'Current milestone', 'juanto' ), 'number' ],
		'milestone_total'   => [ __( 'Total milestones', 'juanto' ), 'number' ],
		'url'               => [ __( '"Follow the build" URL', 'juanto' ), 'url' ],
		'repo'              => [ __( 'GitHub repo (owner/repo)', 'juanto' ), 'text' ],
		'resume_url'        => [ __( 'Résumé PDF URL', 'juanto' ), 'url' ],
		'availability'      => [ __( 'Availability line', 'juanto' ), 'text' ],
		'contact_email'     => [ __( 'Contact email (footer)', 'juanto' ), 'email' ],
		'github_url'        => [ __( 'GitHub profile URL', 'juanto' ), 'url' ],
		'linkedin_url'      => [ __( 'LinkedIn profile URL', 'juanto' ), 'url' ],
	];
	echo '<div class="wrap"><h1>juanto</h1><form method="post" action="options.php">';
	settings_fields( 'juanto' );
	echo '<table class="form-table" role="presentation">';
	printf(
		'<tr><th scope="row">%s</th><td><label><input type="checkbox" name="juanto_status[enabled]" value="1" %s> %s</label></td></tr>',
		esc_html__( 'Status bar', 'juanto' ),
		checked( $s['enabled'], true, false ),
		esc_html__( 'Show "now building" on every page', 'juanto' )
	);
	foreach ( $fields as $key => [ $label, $type ] ) {
		printf(
			'<tr><th scope="row"><label for="juanto-%1$s">%2$s</label></th><td><input class="regular-text" id="juanto-%1$s" type="%3$s" name="juanto_status[%1$s]" value="%4$s"></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( (string) $s[ $key ] )
		);
	}
	echo '</table>';
	submit_button();
	echo '</form></div>';
}
