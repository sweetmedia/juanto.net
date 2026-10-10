<?php
/**
 * Tip, note or warning.
 *
 * @package juanto-core
 * @var array  $attributes
 * @var string $content
 */

$callout_labels  = [
	'tip'     => __( 'Tip', 'juanto' ),
	'note'    => __( 'Note', 'juanto' ),
	'warning' => __( 'Warning', 'juanto' ),
];
$callout_variant = isset( $callout_labels[ $attributes['variant'] ?? '' ] ) ? $attributes['variant'] : 'tip';
?>
<aside <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-callout is-' . $callout_variant, 'role' => 'note' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="juanto-callout__label">
	<?php if ( 'warning' === $callout_variant ) : ?>
		<svg class="juanto-callout__icon" viewBox="0 0 16 16" width="14" height="14" aria-hidden="true" focusable="false"><path fill="currentColor" d="M8 1.5 15 14.5H1L8 1.5Zm-.75 5v4h1.5v-4h-1.5Zm0 5.25v1.5h1.5v-1.5h-1.5Z"/></svg>
	<?php endif; ?>
	<?php echo esc_html( $callout_labels[ $callout_variant ] ); ?></p>
	<div class="juanto-callout__body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks ?></div>
</aside>
