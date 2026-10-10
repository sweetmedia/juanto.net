<?php
/**
 * Theme setup and assets.
 *
 * @package juanto
 */

/**
 * Registers the theme's view stylesheets on every request, so block
 * stylesheets can depend on them (a dependency on an unregistered handle
 * makes WordPress drop the dependent sheet). Enqueueing stays per view.
 */
function juanto_register_theme_styles(): void {
	$v = wp_get_theme()->get( 'Version' );
	foreach ( [ 'base', 'case', 'lab', 'tutorial' ] as $name ) {
		$path = get_theme_file_path( "assets/css/{$name}.css" );
		wp_register_style( "juanto-{$name}", get_theme_file_uri( "assets/css/{$name}.css" ), [], file_exists( $path ) ? filemtime( $path ) : $v );
	}
}
add_action( 'init', 'juanto_register_theme_styles', 5 );

/**
 * Enqueue base CSS everywhere and view CSS where it is used.
 */
function juanto_theme_enqueue(): void {
	juanto_register_theme_styles(); // No-op when already registered; keeps tests that reset wp_styles() working.
	$v = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'juanto-base' );
	if ( is_front_page() ) {
		// Register the hero stylesheet normally and let WordPress core decide
		// whether to inline it (wp_maybe_inline_styles(), hooked on wp_head
		// and wp_footer): if the file is small enough it prints as an inline
		// <style> tag for faster first paint, otherwise it stays a normal
		// external stylesheet. This avoids WP_Filesystem() on the front end
		// (which returns false on non-direct-FS hosts, leaving the page
		// unstyled) and avoids loading wp-admin/includes/file.php outside wp-admin.
		$hero_path = get_theme_file_path( 'assets/css/hero.css' );
		$hero_ver  = file_exists( $hero_path ) ? filemtime( $hero_path ) : $v;
		wp_register_style( 'juanto-hero', get_theme_file_uri( 'assets/css/hero.css' ), [], $hero_ver );
		wp_style_add_data( 'juanto-hero', 'path', $hero_path );
		wp_enqueue_style( 'juanto-hero' );
	}
	if ( is_singular( 'project' ) || is_front_page() ) {
		wp_enqueue_style( 'juanto-case' );
	}
	if ( is_post_type_archive( 'project' ) ) {
		wp_enqueue_style( 'juanto-lab' );
	}
	if ( function_exists( 'juanto_is_tutorial' ) && ( ( is_singular( 'post' ) && juanto_is_tutorial( get_queried_object_id() ) ) || is_page( 'tutorials' ) ) ) {
		wp_enqueue_style( 'juanto-tutorial' );
	}
}
add_action( 'wp_enqueue_scripts', 'juanto_theme_enqueue' );

/**
 * Block names (juanto/<name>) whose styles live one-per-block under
 * assets/css/blocks/<name>.css, mapped to the theme sheets each one
 * overrides. Listing them as deps makes WordPress print those sheets first,
 * so a block's own rules win ties of equal specificity, wherever it renders.
 */
const JUANTO_BLOCK_STYLES = [
	'case-meta'         => [ 'juanto-base', 'juanto-case' ],
	'case-toc'          => [ 'juanto-base', 'juanto-case' ],
	'section-head'      => [ 'juanto-base', 'juanto-case' ],
	'tldr'              => [ 'juanto-base', 'juanto-case' ],
	'facts'             => [ 'juanto-base', 'juanto-case' ],
	'figure-pair'       => [ 'juanto-base', 'juanto-case' ],
	'reveal-image'      => [ 'juanto-base', 'juanto-case' ],
	'decisions'         => [ 'juanto-base', 'juanto-case' ],
	'ledger'            => [ 'juanto-base', 'juanto-case' ],
	'swatches'          => [ 'juanto-base', 'juanto-case' ],
	'type-specimen'     => [ 'juanto-base', 'juanto-case' ],
	'step-track'        => [ 'juanto-base', 'juanto-case' ],
	'next-case'         => [ 'juanto-base', 'juanto-case' ],
	'lab-stack'         => [ 'juanto-base', 'juanto-lab' ],
	'project-list'      => [ 'juanto-base', 'juanto-case' ],
	'lab-teaser'        => [ 'juanto-base', 'juanto-case' ],
	'tutorial-step'     => [ 'juanto-base', 'juanto-tutorial' ],
	'callout'           => [ 'juanto-base', 'juanto-tutorial' ],
	'tutorial-meta'     => [ 'juanto-base', 'juanto-tutorial' ],
	'tutorial-cta'      => [ 'juanto-base', 'juanto-tutorial' ],
	'related-tutorials' => [ 'juanto-base', 'juanto-tutorial' ],
];

/**
 * Load block styles on demand (only on pages that actually render the
 * block), the mechanism wp_enqueue_block_style() below relies on. Core
 * defaults this to false, which would otherwise enqueue every block's
 * stylesheet on every page regardless of whether the block is present.
 */
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Registers each block's own stylesheet with wp_enqueue_block_style(), which
 * WordPress core only prints when that block is actually present on the page.
 */
function juanto_register_block_styles(): void {
	$v = wp_get_theme()->get( 'Version' );
	foreach ( JUANTO_BLOCK_STYLES as $name => $deps ) {
		$path = get_theme_file_path( "assets/css/blocks/{$name}.css" );
		wp_enqueue_block_style(
			"juanto/{$name}",
			[
				'handle' => "juanto-block-{$name}",
				'src'    => get_theme_file_uri( "assets/css/blocks/{$name}.css" ),
				'path'   => $path,
				'deps'   => $deps,
				'ver'    => file_exists( $path ) ? filemtime( $path ) : $v,
			]
		);
	}
}
add_action( 'init', 'juanto_register_block_styles' );

/**
 * The theme's own hero photo as the default Open Graph image fallback.
 * Keeps includes/seo.php free of a hard-coded theme path.
 */
add_filter(
	'juanto_default_og_image',
	static function () {
		return get_theme_file_uri( 'assets/img/hero-1600.jpg' );
	}
);

add_action(
	'wp_head',
	static function () {
		foreach ( [ 'schibsted-grotesk-400', 'schibsted-grotesk-800' ] as $font ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( "assets/fonts/{$font}.woff2" ) ) );
		}
	},
	1
);

add_action(
	'init',
	static function () {
		register_block_pattern_category( 'juanto', [ 'label' => 'juanto' ] );
	}
);

add_action(
	'wp_head',
	static function () {
		if ( ! is_front_page() ) {
			return;
		}
		printf(
			'<link rel="preload" as="image" type="image/webp" imagesrcset="%1$s 900w, %2$s 1600w" imagesizes="(min-width: 900px) 70vw, 100vw" fetchpriority="high">' . "\n",
			esc_url( get_theme_file_uri( 'assets/img/hero-900.webp' ) ),
			esc_url( get_theme_file_uri( 'assets/img/hero-1600.webp' ) )
		);
	},
	2
);
