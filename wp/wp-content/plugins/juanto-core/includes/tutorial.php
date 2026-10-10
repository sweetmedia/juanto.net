<?php
/**
 * Tutorials: posts in the "Tutorials" category. Identity, fields and helpers.
 *
 * @package juanto-core
 */

/**
 * Months after which a tutorial's "last tested" date is flagged as stale.
 */
const JUANTO_TUT_STALE_MONTHS = 18;

/**
 * Whether a post is a tutorial.
 *
 * @param int $post_id Post ID.
 */
function juanto_is_tutorial( int $post_id ): bool {
	return $post_id > 0 && 'post' === get_post_type( $post_id ) && has_category( 'tutorials', $post_id );
}

/**
 * Field definitions. Key => register_post_meta() args.
 */
function juanto_tutorial_meta_schema(): array {
	return [
		'juanto_tut_minutes'      => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'juanto_sanitize_tut_minutes' ],
		'juanto_tut_tools'        => [
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'juanto_sanitize_tut_tools',
			// Like juanto_links: no 'format' here, so a half-filled row reaches the sanitizer.
			'show_in_rest'      => [
				'schema' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'name'    => [ 'type' => 'string' ],
							'version' => [ 'type' => 'string' ],
						],
					],
				],
			],
		],
		'juanto_tut_tested'       => [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'juanto_sanitize_tut_date' ],
		'juanto_tut_prereqs'      => [
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => 'juanto_sanitize_tut_prereqs',
			'show_in_rest'      => [ 'schema' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ] ],
		],
		'juanto_tut_result'       => [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ],
		'juanto_tut_result_image' => [ 'type' => 'integer', 'default' => 0, 'sanitize_callback' => 'juanto_sanitize_tut_image' ],
		'juanto_tut_video'        => [ 'type' => 'string', 'default' => '', 'sanitize_callback' => 'juanto_sanitize_tut_video' ],
		'juanto_tut_audience'     => [
			'type'              => 'string',
			'default'           => 'builders',
			'sanitize_callback' => 'juanto_sanitize_tut_audience',
			'show_in_rest'      => [ 'schema' => [ 'enum' => [ 'clients', 'builders' ] ] ],
		],
	];
}

/**
 * Registers every field on posts.
 */
function juanto_register_tutorial_meta(): void {
	foreach ( juanto_tutorial_meta_schema() as $key => $args ) {
		register_post_meta(
			'post',
			$key,
			$args + [
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
			]
		);
	}
}
add_action( 'init', 'juanto_register_tutorial_meta' );

/**
 * Minutes, 0 to 600.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_minutes( $value ): int {
	return min( 600, absint( $value ) );
}

/**
 * Keeps { name, version } rows that have a name.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_tools( $value ): array {
	$out = [];
	foreach ( is_array( $value ) ? $value : [] as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$name = sanitize_text_field( (string) ( $row['name'] ?? '' ) );
		if ( '' !== $name ) {
			$out[] = [ 'name' => $name, 'version' => sanitize_text_field( (string) ( $row['version'] ?? '' ) ) ];
		}
	}
	return $out;
}

/**
 * A real YYYY-MM-DD date, or ''.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_date( $value ): string {
	$value = trim( (string) $value );
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
		return '';
	}
	return checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ? $value : '';
}

/**
 * Non-empty plain-text lines.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_prereqs( $value ): array {
	$lines = array_map(
		static fn( $line ) => is_scalar( $line ) ? sanitize_text_field( (string) $line ) : '',
		is_array( $value ) ? $value : []
	);
	return array_values( array_filter( $lines, static fn( $line ) => '' !== $line ) );
}

/**
 * An image attachment ID, or 0.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_image( $value ): int {
	$id = absint( $value );
	return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
}

/**
 * The 11-character video ID of a YouTube watch or youtu.be URL.
 *
 * @param string $url URL.
 */
function juanto_youtube_id( string $url ): string {
	$url = trim( $url );
	if ( preg_match( '#^https?://youtu\.be/([A-Za-z0-9_-]{11})(?:[?&\#].*)?$#', $url, $m ) ) {
		return $m[1];
	}
	if ( preg_match( '#^https?://(?:www\.|m\.)?youtube\.com/watch\?(?:[^\#]*&)?v=([A-Za-z0-9_-]{11})(?:[&\#].*)?$#', $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * A normalized YouTube watch URL, or ''.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_video( $value ): string {
	$id = juanto_youtube_id( (string) $value );
	return '' === $id ? '' : 'https://www.youtube.com/watch?v=' . $id;
}

/**
 * Who the call to action speaks to.
 *
 * @param mixed $value Raw value.
 */
function juanto_sanitize_tut_audience( $value ): string {
	return 'clients' === $value ? 'clients' : 'builders';
}

/**
 * All fields of a tutorial, typed, keyed without the juanto_tut_ prefix.
 *
 * @param int $post_id Post ID.
 */
function juanto_get_tutorial( int $post_id ): array {
	$out = [];
	foreach ( juanto_tutorial_meta_schema() as $key => $args ) {
		$value = metadata_exists( 'post', $post_id, $key ) ? get_post_meta( $post_id, $key, true ) : $args['default'];
		settype( $value, 'integer' === $args['type'] ? 'integer' : ( 'array' === $args['type'] ? 'array' : 'string' ) );
		$out[ substr( $key, strlen( 'juanto_tut_' ) ) ] = $value;
	}
	return $out;
}

/**
 * Whether a "last tested" date is older than JUANTO_TUT_STALE_MONTHS.
 *
 * @param string                 $tested YYYY-MM-DD or ''.
 * @param DateTimeImmutable|null $now    Clock, for tests.
 */
function juanto_tut_is_stale( string $tested, ?DateTimeImmutable $now = null ): bool {
	if ( '' === $tested ) {
		return false;
	}
	$tested_at = DateTimeImmutable::createFromFormat( '!Y-m-d', $tested, wp_timezone() );
	$now       = $now ?? new DateTimeImmutable( 'now', wp_timezone() );
	return $tested_at instanceof DateTimeImmutable && $tested_at < $now->modify( '-' . JUANTO_TUT_STALE_MONTHS . ' months' );
}

/**
 * Plain text of a parsed block's own HTML.
 *
 * @param array $block Parsed block.
 */
function juanto_block_plain_text( array $block ): string {
	return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) ( $block['innerHTML'] ?? '' ) ) ) );
}

/**
 * Every juanto/tutorial-step in document order (nested blocks included).
 * One source for the rendered numbers, the step index and the JSON-LD.
 *
 * @param string $content Post content.
 * @return array<int, array{n:int,title:string,anchor:string,text:string,image_id:int}>
 */
function juanto_tutorial_steps_from_content( string $content ): array {
	$steps = [];
	$seen  = [];
	$walk  = static function ( array $blocks ) use ( &$walk, &$steps, &$seen ) {
		foreach ( $blocks as $b ) {
			if ( 'juanto/tutorial-step' !== $b['blockName'] ) {
				if ( ! empty( $b['innerBlocks'] ) ) {
					$walk( $b['innerBlocks'] );
				}
				continue;
			}
			$n     = count( $steps ) + 1;
			$title = trim( (string) ( $b['attrs']['title'] ?? '' ) );
			$base  = sanitize_title( $title );
			$base  = '' !== $base ? $base : 'step-' . $n;
			$slug  = $base;
			for ( $i = 2; isset( $seen[ $slug ] ); $i++ ) {
				$slug = $base . '-' . $i;
			}
			$seen[ $slug ] = true;
			$text          = '';
			$image_id      = 0;
			foreach ( $b['innerBlocks'] as $inner ) {
				if ( '' === $text && 'core/paragraph' === $inner['blockName'] ) {
					$text = juanto_block_plain_text( $inner );
				}
				if ( 0 === $image_id && 'core/image' === $inner['blockName'] ) {
					$image_id = (int) ( $inner['attrs']['id'] ?? 0 );
				}
			}
			$steps[] = [ 'n' => $n, 'title' => $title, 'anchor' => $slug, 'text' => $text, 'image_id' => $image_id ];
		}
	};
	$walk( parse_blocks( $content ) );
	return $steps;
}

/**
 * Steps of a post, parsed once per request per content version.
 *
 * @param int $post_id Post ID.
 */
function juanto_tutorial_steps( int $post_id ): array {
	static $cache = [];
	$post         = get_post( $post_id );
	if ( ! $post ) {
		return [];
	}
	$key = $post_id . ':' . md5( $post->post_content );
	if ( ! isset( $cache[ $key ] ) ) {
		$cache[ $key ] = juanto_tutorial_steps_from_content( $post->post_content );
	}
	return $cache[ $key ];
}

/**
 * Running step number for the content pass being rendered.
 *
 * @param bool $reset Start a new pass.
 */
function juanto_tutorial_step_counter( bool $reset = false ): int {
	static $n = 0;
	$n        = $reset ? 0 : $n + 1;
	return $n;
}

/**
 * Claims a unique anchor for the content pass being rendered.
 *
 * @param string $base  Wanted anchor.
 * @param bool   $reset Start a new pass.
 */
function juanto_tutorial_claim_anchor( string $base, bool $reset = false ): string {
	static $seen = [];
	if ( $reset ) {
		$seen = [];
		return '';
	}
	$slug = $base;
	for ( $i = 2; isset( $seen[ $slug ] ); $i++ ) {
		$slug = $base . '-' . $i;
	}
	$seen[ $slug ] = true;
	return $slug;
}

// do_blocks() runs on the_content at priority 9: restart numbering just before every pass.
add_filter(
	'the_content',
	static function ( $content ) {
		juanto_tutorial_step_counter( true );
		juanto_tutorial_claim_anchor( '', true );
		return $content;
	},
	8
);

/**
 * Short facts for the header (full) or a list row (row). Empty facts are skipped.
 *
 * @param array  $tut     juanto_get_tutorial() output.
 * @param string $variant full or row.
 * @return string[]
 */
function juanto_tutorial_facts( array $tut, string $variant = 'full' ): array {
	$minutes = $tut['minutes'] > 0 ? [ sprintf( /* translators: %d: minutes */ __( '%d min', 'juanto' ), $tut['minutes'] ) ] : [];
	$tools   = array_map( static fn( array $t ) => trim( $t['name'] . ' ' . $t['version'] ), $tut['tools'] );
	$tested  = '' !== $tut['tested'] ? [ sprintf( /* translators: %s: month and year */ __( 'Tested %s', 'juanto' ), wp_date( 'M Y', strtotime( $tut['tested'] . ' 12:00:00 UTC' ), new DateTimeZone( 'UTC' ) ) ) ] : [];
	return 'row' === $variant ? array_merge( $tools, $minutes, $tested ) : array_merge( $minutes, $tools, $tested );
}

/**
 * Screenshots inside a tutorial step open in the core lightbox unless the
 * author turned it off on that image.
 *
 * @param array         $parsed Parsed block.
 * @param array         $source Source block.
 * @param WP_Block|null $parent_block Parent block.
 */
function juanto_step_image_lightbox( array $parsed, array $source, $parent_block = null ): array {
	if ( 'core/image' === ( $parsed['blockName'] ?? '' ) && $parent_block instanceof WP_Block && 'juanto/tutorial-step' === $parent_block->name && ! isset( $parsed['attrs']['lightbox'] ) ) {
		$parsed['attrs']['lightbox'] = [ 'enabled' => true ];
	}
	return $parsed;
}
add_filter( 'render_block_data', 'juanto_step_image_lightbox', 10, 3 );

/**
 * The tutorial lede is the manual excerpt only (an automatic one would repeat the intro).
 *
 * @param string   $html     Rendered excerpt.
 * @param array    $block    Parsed block.
 * @param WP_Block $instance Block instance.
 */
function juanto_tutorial_lede( string $html, array $block, WP_Block $instance ): string {
	if ( ! str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'juanto-tutorial__lede' ) ) {
		return $html;
	}
	$post_id = (int) ( $instance->context['postId'] ?? get_the_ID() );
	return has_excerpt( $post_id ) ? $html : '';
}
add_filter( 'render_block_core/post-excerpt', 'juanto_tutorial_lede', 10, 3 );
