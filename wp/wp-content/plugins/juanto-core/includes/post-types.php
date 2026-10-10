<?php
/**
 * Juanto core post type definitions.
 *
 * @package juanto-core
 */

/**
 * Fixed tier and discipline slugs.
 */
const JUANTO_TIERS       = [ 'case', 'card', 'lab' ];
const JUANTO_DISCIPLINES = [ 'brand', 'ux', 'web', 'product', 'marketing', 'code', 'ios', 'print', 'apparel', 'hardware', 'community', 'social' ];

/**
 * Registers the project post type and the tier, discipline and language taxonomies.
 */
function juanto_register_content_types(): void {
	register_post_type(
		'project',
		[
			'labels'       => [
				'name'          => __( 'Projects', 'juanto' ),
				'singular_name' => __( 'Project', 'juanto' ),
				'add_new_item'  => __( 'Add project', 'juanto' ),
				'edit_item'     => __( 'Edit project', 'juanto' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-portfolio',
			'rewrite'      => [ 'slug' => 'work', 'with_front' => false ],
			'has_archive'  => 'lab',
			'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ],
		]
	);

	// publicly_queryable is deliberately false: these taxonomies have no
	// archive template of their own, so leaving it at the "public" default
	// makes ?tier=lab (etc.) resolve as a real front-end query and puts the
	// taxonomy in wp-sitemap.xml alongside content that does have pages.
	$common = [ 'public' => true, 'publicly_queryable' => false, 'show_in_rest' => true, 'show_admin_column' => true, 'hierarchical' => false ];

	register_taxonomy( 'tier', 'project', $common + [ 'label' => __( 'Tier', 'juanto' ), 'rewrite' => false ] );
	register_taxonomy( 'discipline', 'project', $common + [ 'label' => __( 'Disciplines', 'juanto' ), 'rewrite' => false ] );
	register_taxonomy( 'language', 'post', $common + [ 'label' => __( 'Language', 'juanto' ), 'rewrite' => false ] );
}
add_action( 'init', 'juanto_register_content_types' );

/**
 * Drops the built-in "users" XML sitemap provider: /wp-sitemap-users-1.xml
 * would otherwise list author archive URLs, exposing the admin login
 * ("juan") to anyone who looks.
 *
 * @param WP_Sitemaps_Provider|false $provider Provider instance, or false to skip registration.
 * @param string                     $name     Provider name.
 */
function juanto_drop_users_sitemap_provider( $provider, string $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'juanto_drop_users_sitemap_provider', 10, 2 );

/**
 * Creates the fixed terms. Safe to run repeatedly.
 */
function juanto_seed_terms(): void {
	$sets = [
		'tier'       => JUANTO_TIERS,
		'discipline' => JUANTO_DISCIPLINES,
		'language'   => [ 'en', 'es' ],
	];
	foreach ( $sets as $taxonomy => $slugs ) {
		foreach ( $slugs as $slug ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				$result = wp_insert_term( $slug, $taxonomy, [ 'slug' => $slug ] );
				if ( is_wp_error( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( sprintf( 'juanto_seed_terms: could not create %s/%s: %s', $taxonomy, $slug, $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- surfaces seeding failures in debug logs.
				}
			}
		}
	}

	if ( ! term_exists( 'tutorials', 'category' ) ) {
		$result = wp_insert_term( 'Tutorials', 'category', [ 'slug' => 'tutorials' ] );
		if ( is_wp_error( $result ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( 'juanto_seed_terms: could not create category/tutorials: %s', $result->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- surfaces seeding failures in debug logs.
		}
	}
}
