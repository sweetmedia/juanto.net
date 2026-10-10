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
	 * No forced loading="lazy" here: a case study's lead reveal image is
	 * typically its LCP candidate, so this defers to core's own
	 * first-few-content-images-are-eager default (wp_get_loading_attr_default()).
	 */
	echo wp_get_attachment_image( $image_id, $attributes['size'] ?? 'large' );
	?>
	</div>
	<?php
	if ( ! empty( $attributes['caption'] ) ) :
		?>
		<figcaption><?php echo esc_html( $attributes['caption'] ); ?></figcaption><?php endif; ?>
</figure>
