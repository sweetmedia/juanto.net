<?php
/**
 * Ledger block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$rows = array_filter( (array) ( $attributes['rows'] ?? [] ), static fn( $r ) => ! empty( $r['value'] ) );
if ( ! $rows ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-ledger' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $rows as $row ) : ?>
		<?php $is_next = 'next' === strtolower( trim( $row['value'] ) ); ?>
		<div class="juanto-ledger__row<?php echo $is_next ? ' is-next' : ''; ?>"><span class="juanto-ledger__value"><?php echo esc_html( $row['value'] ); ?></span><p><?php echo esc_html( $row['text'] ?? '' ); ?></p></div>
	<?php endforeach; ?>
</div>
