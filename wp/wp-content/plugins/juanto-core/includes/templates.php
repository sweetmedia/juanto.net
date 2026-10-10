<?php
/**
 * Case studies and cards use different single templates.
 *
 * @package juanto-core
 */

add_filter(
	'single_template_hierarchy',
	static function ( array $templates ): array {
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post || 'project' !== $post->post_type ) {
			return $templates;
		}
		$tier = juanto_project_tier( $post->ID );
		return $tier ? array_merge( [ "single-project-{$tier}.php" ], $templates ) : $templates;
	}
);
