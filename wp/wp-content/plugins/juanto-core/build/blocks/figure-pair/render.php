<?php
/**
 * Figure pair block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$items = array_slice( array_filter( (array) ( $attributes['items'] ?? [] ), static fn( $i ) => ! empty( $i['id'] ) && wp_attachment_is_image( (int) $i['id'] ) ), 0, 2 );
if ( ! $items ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-figure-pair' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $items as $item ) : ?>
		<figure>
			<div class="juanto-frame"><?php echo wp_get_attachment_image( (int) $item['id'], 'large', false, [ 'loading' => 'lazy', 'sizes' => '(min-width: 1100px) 460px, 90vw' ] ); ?></div>
			<figcaption><?php echo ( $item['title'] ?? '' ) ? '<b>' . esc_html( $item['title'] ) . '</b> ' : ''; ?><?php echo esc_html( $item['caption'] ?? '' ); ?></figcaption>
		</figure>
	<?php endforeach; ?>
</div>
