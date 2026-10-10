<?php
/**
 * Marks Spanish posts with lang="es".
 *
 * @package juanto-core
 */

/**
 * Language slug of a post.
 *
 * @param int $post_id Post ID.
 */
function juanto_post_lang( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'language' );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : '';
}

/**
 * Adds lang to the first element of an HTML fragment.
 *
 * @param string $html Markup.
 * @param string $lang Language code.
 */
function juanto_add_lang_attr( string $html, string $lang ): string {
	if ( '' === $lang || '' === trim( $html ) ) {
		return $html;
	}
	$p = new WP_HTML_Tag_Processor( $html );
	if ( $p->next_tag() ) {
		$p->set_attribute( 'lang', $lang );
	}
	return $p->get_updated_html();
}

foreach ( [ 'core/post-title', 'core/post-content' ] as $juanto_block ) {
	add_filter(
		"render_block_{$juanto_block}",
		static function ( string $html, array $block, WP_Block $instance ): string {
			$post_id = (int) ( $instance->context['postId'] ?? get_the_ID() );
			$lang    = $post_id ? juanto_post_lang( $post_id ) : '';
			return 'es' === $lang ? juanto_add_lang_attr( $html, 'es' ) : $html;
		},
		10,
		3
	);
}
