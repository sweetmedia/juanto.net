<?php
/**
 * Case studies, cards and tutorials use their own single templates.
 *
 * @package juanto-core
 */

add_filter(
	'single_template_hierarchy',
	static function ( array $templates ): array {
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return $templates;
		}
		if ( 'post' === $post->post_type ) {
			return juanto_is_tutorial( $post->ID ) ? array_merge( [ 'single-tutorial.php' ], $templates ) : $templates;
		}
		if ( 'project' !== $post->post_type ) {
			return $templates;
		}
		$tier = juanto_project_tier( $post->ID );
		return $tier ? array_merge( [ "single-project-{$tier}.php" ], $templates ) : $templates;
	}
);
