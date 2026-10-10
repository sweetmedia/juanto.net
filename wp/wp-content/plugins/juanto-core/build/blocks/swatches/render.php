<?php
/**
 * Brand swatches.
 *
 * @package juanto-core
 * @var array $attributes
 */

$rows = [];
foreach ( (array) ( $attributes['rows'] ?? [] ) as $row ) {
	$color = juanto_sanitize_css_color( (string) ( $row['value'] ?? '' ) );
	if ( $color ) {
		$rows[] = [ 'name' => (string) ( $row['name'] ?? '' ), 'value' => $color ];
	}
}
if ( ! $rows ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-swatches' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $rows as $row ) : ?>
		<div><i style="background:<?php echo esc_attr( $row['value'] ); ?>"></i><b><?php echo esc_html( $row['name'] ); ?></b><span class="juanto-label"><?php echo esc_html( $row['value'] ); ?></span></div>
	<?php endforeach; ?>
</div>
