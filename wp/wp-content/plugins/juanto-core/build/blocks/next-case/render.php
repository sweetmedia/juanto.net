<?php
/**
 * Link to the next case study.
 *
 * @package juanto-core
 * @var WP_Block $block
 */

$next = juanto_next_case( (int) ( $block->context['postId'] ?? get_the_ID() ) );
if ( ! $next ) {
	return;
}
?>
<a <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-next-case', 'href' => get_permalink( $next ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="juanto-next-case__text"><span class="juanto-label">next case · <?php echo esc_html( juanto_case_number( $next->ID ) ); ?></span><span class="juanto-next-case__title"><?php echo esc_html( get_the_title( $next ) ); ?> →</span></span>
	<?php
	if ( has_post_thumbnail( $next ) ) :
		?>
		<span class="juanto-next-case__img"><?php echo get_the_post_thumbnail( $next, 'large', [ 'loading' => 'lazy', 'alt' => '' ] ); ?></span><?php endif; ?>
</a>
