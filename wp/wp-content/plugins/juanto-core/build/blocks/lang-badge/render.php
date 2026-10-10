<?php
/**
 * Language badge (EN or ES) for a post.
 *
 * @package juanto-core
 * @var WP_Block $block
 */

$badge_lang = juanto_post_lang( (int) ( $block->context['postId'] ?? get_the_ID() ) );
if ( '' === $badge_lang ) {
	return;
}
?>
<span <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-lang-badge' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( strtoupper( $badge_lang ) ); ?></span>
