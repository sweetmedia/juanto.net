<?php
/**
 * Redirects, JSON-LD and Open Graph.
 *
 * @package juanto-core
 */

/**
 * Where an old or shortcut path should go.
 *
 * @param string $path Request path.
 */
function juanto_redirect_target( string $path ): ?array {
	$path = '/' . trim( strtolower( $path ), '/' );
	if ( '/works' === $path ) {
		return [ home_url( '/' ), 301 ];
	}
	if ( '/category/tutorials' === $path ) {
		return [ home_url( '/tutorials/' ), 301 ];
	}
	if ( '/resume.pdf' === $path ) {
		$url = juanto_get_status()['resume_url'];
		return $url ? [ $url, 302 ] : null;
	}
	return null;
}

add_action(
	'template_redirect',
	static function () {
		$path   = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$target = juanto_redirect_target( $path );
		if ( $target ) {
			wp_redirect( $target[0], $target[1] ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the résumé may live on a CDN
			exit;
		}
	},
	1
);

/**
 * JSON-LD for the current view.
 */
function juanto_schema(): ?array {
	$s      = juanto_get_status();
	$person = [
		'@type'    => 'Person',
		'name'     => 'Juan Pérez',
		'url'      => home_url( '/' ),
		'jobTitle' => 'Design engineer',
		'sameAs'   => array_values( array_filter( [ $s['github_url'], $s['linkedin_url'] ] ) ),
	];
	if ( is_front_page() ) {
		return [ '@context' => 'https://schema.org' ] + $person;
	}
	if ( is_singular( 'project' ) ) {
		$id   = get_queried_object_id();
		$work = [
			'@context'    => 'https://schema.org',
			'@type'       => 'CreativeWork',
			'name'        => get_the_title( $id ),
			'url'         => get_permalink( $id ),
			'description' => (string) juanto_get_project_field( $id, 'summary' ),
			'creator'     => $person,
		];
		$year = (int) juanto_get_project_field( $id, 'year_start' );
		if ( $year ) {
			$work['dateCreated'] = (string) $year;
		}
		if ( has_post_thumbnail( $id ) ) {
			$work['image'] = get_the_post_thumbnail_url( $id, 'large' );
		}
		return $work;
	}
	if ( is_singular( 'post' ) && juanto_is_tutorial( get_queried_object_id() ) ) {
		return juanto_tutorial_schema( get_queried_object_id(), $person );
	}
	return null;
}

/**
 * Plain text for JSON-LD: tags stripped, HTML entities (texturized quotes,
 * &gt;, &hellip;) decoded to real characters, trimmed.
 *
 * @param string $text Possibly marked-up text.
 */
function juanto_schema_text( string $text ): string {
	return trim( html_entity_decode( wp_strip_all_tags( $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * TechArticle + HowTo graph for a tutorial. Empty values are omitted; a
 * tutorial without steps gets no HowTo (one without steps is invalid), and a
 * password-protected one gets neither HowTo nor a description (no content leaks).
 * The description is the manual excerpt only; without one it is omitted.
 *
 * @param int   $post_id Tutorial.
 * @param array $person  The site's Person node.
 */
function juanto_tutorial_schema( int $post_id, array $person ): array {
	$keep  = static fn( array $node ): array => array_filter( $node, static fn( $v ) => '' !== $v && [] !== $v && 0 !== $v );
	$tut   = juanto_get_tutorial( $post_id );
	$url   = get_permalink( $post_id );
	$title = juanto_schema_text( get_the_title( $post_id ) );
	$post  = get_post( $post_id );
	$lock  = post_password_required( $post );
	$image = has_post_thumbnail( $post_id ) ? (string) get_the_post_thumbnail_url( $post_id, 'large' ) : ( $tut['result_image'] ? (string) wp_get_attachment_image_url( $tut['result_image'], 'large' ) : '' );
	$graph = [
		$keep(
			[
				'@type'         => 'TechArticle',
				'@id'           => $url . '#article',
				'url'           => $url,
				'headline'      => $title,
				'description'   => ( ! $lock && $post && has_excerpt( $post ) ) ? juanto_schema_text( $post->post_excerpt ) : '',
				'author'        => $person,
				'inLanguage'    => juanto_post_lang( $post_id ),
				'datePublished' => (string) get_the_date( 'c', $post_id ),
				'dateModified'  => (string) get_the_modified_date( 'c', $post_id ),
				'image'         => $image,
			]
		),
	];
	$steps = $lock ? [] : juanto_tutorial_steps( $post_id );
	if ( $steps ) {
		$graph[] = $keep(
			[
				'@type'     => 'HowTo',
				'@id'       => $url . '#howto',
				'name'      => $title,
				'totalTime' => $tut['minutes'] > 0 ? 'PT' . $tut['minutes'] . 'M' : '',
				'image'     => $image,
				'tool'      => array_map( static fn( array $t ) => [ '@type' => 'HowToTool', 'name' => trim( $t['name'] . ' ' . $t['version'] ) ], $tut['tools'] ),
				'step'      => array_map(
					static fn( array $s ) => $keep(
						[
							'@type'    => 'HowToStep',
							'position' => $s['n'],
							'name'     => '' !== $s['title'] ? juanto_schema_text( $s['title'] ) : 'Step ' . $s['n'],
							'url'      => $url . '#' . $s['anchor'],
							'text'     => juanto_schema_text( $s['text'] ),
							'image'    => $s['image_id'] ? (string) wp_get_attachment_image_url( $s['image_id'], 'large' ) : '',
						]
					),
					$steps
				),
			]
		);
	}
	return [ '@context' => 'https://schema.org', '@graph' => $graph ];
}

/**
 * Open Graph and Twitter tags for the current view.
 */
function juanto_og_tags(): array {
	$tags = [ 'og:site_name' => 'Juan Pérez', 'twitter:card' => 'summary_large_image' ];
	if ( is_singular() ) {
		$id                     = get_queried_object_id();
		$summary                = 'project' === get_post_type( $id ) ? (string) juanto_get_project_field( $id, 'summary' ) : '';
		$tags['og:type']        = 'article';
		$tags['og:title']       = get_the_title( $id ) . ' · Juan Pérez';
		$tags['og:description'] = $summary ? $summary : wp_strip_all_tags( get_the_excerpt( $id ) );
		$tags['og:url']         = get_permalink( $id );
		if ( has_post_thumbnail( $id ) ) {
			$tags['og:image'] = get_the_post_thumbnail_url( $id, 'large' );
		} else {
			$result_image  = juanto_is_tutorial( $id ) ? juanto_get_tutorial( $id )['result_image'] : 0;
			$default_image = $result_image ? (string) wp_get_attachment_image_url( $result_image, 'large' ) : (string) apply_filters( 'juanto_default_og_image', '' );
			if ( '' !== $default_image ) {
				$tags['og:image'] = $default_image;
			}
		}
	} else {
		$tags['og:type']        = 'website';
		$tags['og:title']       = 'Juan Pérez · Design engineer';
		$tags['og:description'] = 'I design brands and build the products behind them.';
		// The current request's URL, not always the homepage: this branch also
		// covers the lab archive, blog index, 404s, etc.
		$tags['og:url'] = esc_url( home_url( add_query_arg( [] ) ) );
		$default_image  = (string) apply_filters( 'juanto_default_og_image', '' );
		if ( '' !== $default_image ) {
			$tags['og:image'] = $default_image;
		}
	}
	return $tags;
}

/**
 * Lab-tier projects are thin, work-in-progress pieces (often just a photo
 * and a note); keep them out of search results without hiding them from
 * visitors browsing /lab/ directly.
 *
 * @param array $robots Robots directives.
 */
function juanto_noindex_lab_tier_singles( array $robots ): array {
	if ( is_singular( 'project' ) && has_term( 'lab', 'tier', get_queried_object_id() ) ) {
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'juanto_noindex_lab_tier_singles' );

add_action(
	'wp_head',
	static function () {
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}
		foreach ( juanto_og_tags() as $property => $content ) {
			$attr = str_starts_with( $property, 'twitter:' ) ? 'name' : 'property';
			printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $attr ), esc_attr( $property ), esc_attr( $content ) );
		}
		$schema = juanto_schema();
		if ( $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
		}
	}
);
