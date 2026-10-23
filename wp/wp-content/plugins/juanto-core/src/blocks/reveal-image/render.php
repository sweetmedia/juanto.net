<?php
/**
 * Reveal image block. Visible without JS; view.js arms and reveals it.
 *
 * @package juanto-core
 * @var array $attributes
 */

$image_id = (int) ( $attributes['id'] ?? 0 );
if ( ! $image_id || ! wp_attachment_is_image( $image_id ) ) {
	return;
}
?>
<figure <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-reveal' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="juanto-frame">
	<?php
	/**
	 * The lead reveal image is typically the case's LCP candidate, so it keeps
	 * core's defaults; every later one is lazy (see media.php).
	 */
	echo wp_get_attachment_image( $image_id, $attributes['size'] ?? 'large', false, juanto_reveal_image_loading_attrs() );
	?>
	</div>
	<?php
	if ( ! empty( $attributes['caption'] ) ) :
		?>
		<figcaption><?php echo esc_html( $attributes['caption'] ); ?></figcaption><?php endif; ?>
</figure>
