<?php
/**
 * Project fields.
 *
 * @package juanto-core
 */

/**
 * Field definitions. Key => register_post_meta() args.
 */
function juanto_project_meta_schema(): array {
	$string = [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ];

	return [
		'juanto_summary'       => $string,
		'juanto_year_start'    => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
		'juanto_year_end'      => [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'juanto_sanitize_year_end' ],
		'juanto_role'          => $string,
		'juanto_team'          => $string,
		'juanto_stack'         => $string,
		'juanto_links'         => [
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'juanto_sanitize_links',
			'show_in_rest'      => [
				'schema' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'label' => [ 'type' => 'string' ],
							// No 'format' => 'uri' here: the REST schema's format
							// validation runs before juanto_sanitize_links() and
							// rejects an empty/blank url outright (a row a user is
							// still filling in, or clearing), even though the
							// sanitizer already enforces http(s) and drops rows
							// without a valid URL.
							'url'   => [ 'type' => 'string' ],
						],
					],
				],
			],
		],
		'juanto_preview_image' => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'absint' ],
		'juanto_lab_fit'       => [ 'type' => 'string', 'default' => 'cover', 'sanitize_callback' => 'juanto_sanitize_lab_fit', 'show_in_rest' => [ 'schema' => [ 'enum' => [ 'cover', 'contain' ] ] ] ],
		'juanto_lab_note'      => [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ],
	];
}

/**
 * Registers every field on the project post type.
 */
function juanto_register_project_meta(): void {
	foreach ( juanto_project_meta_schema() as $key => $args ) {
		register_post_meta(
			'project',
			$key,
			$args + [
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
			]
		);
	}
}

/**
 * Accepts "present" or a four-digit year.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_year_end( $value ): string {
	$value = strtolower( trim( (string) $value ) );
	return ( 'present' === $value || preg_match( '/^\d{4}$/', $value ) ) ? $value : '';
}

/**
 * Keeps { label, url } pairs with an http(s) URL.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_links( $value ): array {
	$clean = [];
	foreach ( (array) $value as $link ) {
		if ( ! is_array( $link ) ) {
			continue;
		}
		$url = esc_url_raw( (string) ( $link['url'] ?? '' ), [ 'http', 'https' ] );
		if ( '' === $url ) {
			continue;
		}
		$clean[] = [
			'label' => sanitize_text_field( wp_strip_all_tags( (string) ( $link['label'] ?? '' ) ) ),
			'url'   => $url,
		];
	}
	return $clean;
}

/**
 * Cover or contain.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_lab_fit( $value ): string {
	return 'contain' === $value ? 'contain' : 'cover';
}

/**
 * Typed read with the schema default.
 *
 * @param int    $post_id Project ID.
 * @param string $field   Field name without the juanto_ prefix.
 */
function juanto_get_project_field( int $post_id, string $field ) {
	$key    = 'juanto_' . $field;
	$schema = juanto_project_meta_schema()[ $key ] ?? null;
	if ( ! $schema ) {
		return null;
	}
	$raw = get_post_meta( $post_id, $key, true );
	if ( '' === $raw || null === $raw || false === $raw ) {
		return $schema['default'];
	}
	return 'integer' === $schema['type'] ? (int) $raw : $raw;
}

add_action( 'init', 'juanto_register_project_meta' );
