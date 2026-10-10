<?php
/**
 * Facts block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$rows = array_filter( (array) ( $attributes['rows'] ?? [] ), static fn( $r ) => ! empty( $r['label'] ) || ! empty( $r['value'] ) );
if ( ! $rows ) {
	return;
}
?>
<dl <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-facts' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	foreach ( $rows as $row ) :
		?>
		<div><dt><?php echo esc_html( $row['label'] ?? '' ); ?></dt><dd><?php echo esc_html( $row['value'] ?? '' ); ?></dd></div><?php endforeach; ?>
</dl>
