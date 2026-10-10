<?php
/**
 * Type specimen with self-hosted client fonts.
 *
 * @package juanto-core
 * @var array $attributes
 */

/*
 * $row['family'] ends up both inside a "style" HTML attribute and, raw,
 * inside a <style> block's @font-face src string. esc_attr()/esc_url()
 * alone don't cover either: a browser decodes the attribute's HTML
 * entities before handing the value to the CSS parser (so an escaped
 * quote can still terminate the font-family string and inject CSS), and
 * <style> element content is never HTML-entity-decoded at all, so a
 * literal backslash or newline in $row['family'] can break out of the
 * quoted @font-face src string. Whitelisting the characters a family name
 * needs sidesteps both: skip the row entirely if it doesn't match.
 */
$rows = array_filter(
	(array) ( $attributes['rows'] ?? [] ),
	static fn( $r ) => ! empty( $r['family'] ) && ! empty( $r['sample'] ) && preg_match( '/^[A-Za-z0-9 _-]{1,60}$/', (string) $r['family'] )
);
if ( ! $rows ) {
	return;
}
$faces = '';
foreach ( $rows as $row ) {
	$url = esc_url_raw( (string) ( $row['fontUrl'] ?? '' ) );
	if ( $url && str_starts_with( $url, home_url( '/' ) ) && str_ends_with( wp_parse_url( $url, PHP_URL_PATH ), '.woff2' ) ) {
		$faces .= sprintf( "@font-face{font-family:'%s';src:url('%s') format('woff2');font-display:swap}", esc_attr( $row['family'] ), esc_url( $url ) );
	}
}
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-type-specimen' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	if ( $faces ) :
		?>
		<style><?php echo $faces; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts ?></style><?php endif; ?>
	<?php foreach ( $rows as $row ) : ?>
		<div><span class="juanto-label"><?php echo esc_html( ( $row['label'] ?? '' ) . ' · ' . strtolower( $row['family'] ) ); ?></span><span class="juanto-type-specimen__sample" style="<?php echo esc_attr( 'font-family:"' . $row['family'] . '", system-ui, sans-serif' ); ?>"><?php echo esc_html( $row['sample'] ); ?></span></div>
	<?php endforeach; ?>
</div>
