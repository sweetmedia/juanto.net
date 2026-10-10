<?php
/**
 * Decisions block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$rows = array_filter( (array) ( $attributes['rows'] ?? [] ), static fn( $r ) => ! empty( $r['title'] ) );
if ( ! $rows ) {
	return;
}
?>
<ol <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-decisions' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	foreach ( $rows as $row ) :
		?>
		<li><b><?php echo esc_html( $row['title'] ); ?></b><p><?php echo esc_html( $row['text'] ?? '' ); ?></p></li><?php endforeach; ?>
</ol>
