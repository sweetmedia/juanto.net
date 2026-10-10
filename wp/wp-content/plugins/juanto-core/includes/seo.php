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
	return null;
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
			$default_image = (string) apply_filters( 'juanto_default_og_image', '' );
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
