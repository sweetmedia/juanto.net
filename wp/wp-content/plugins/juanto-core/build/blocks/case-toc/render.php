<?php
/**
 * Case study contents.
 *
 * @package juanto-core
 * @var WP_Block $block
 */

$toc_post = get_post( (int) ( $block->context['postId'] ?? get_the_ID() ) );
$items    = $toc_post ? juanto_toc_items( $toc_post->post_content ) : [];
if ( ! $items ) {
	return;
}
?>
<nav <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-toc', 'aria-label' => __( 'Case study sections', 'juanto' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	foreach ( $items as $item ) :
		?>
		<a href="#<?php echo esc_attr( $item['anchor'] ); ?>"><span><?php echo esc_html( $item['number'] ); ?></span><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?>
</nav>
