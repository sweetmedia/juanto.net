<?php
/**
 * Lab: a stack of prints plus an "all" grid. Grid only without JS.
 *
 * @package juanto-core
 */

$rot    = [ -4, 3, -2, 5, -5, 2, -3, 4, -1, 3, -4, 1 ];
$pieces = [];
foreach ( juanto_get_projects( 'lab' ) as $p ) {
	$note = (string) juanto_get_project_field( $p->ID, 'lab_note' );
	if ( ! has_post_thumbnail( $p ) && '' === $note ) {
		continue;
	}
	$start_year = (int) juanto_get_project_field( $p->ID, 'year_start' );
	$pieces[]   = [
		'post' => $p,
		'note' => $note,
		'fit'  => juanto_get_project_field( $p->ID, 'lab_fit' ),
		'meta' => trim( juanto_project_tags( $p->ID ) . ( $start_year ? ' · ' . $start_year : '' ), ' ·' ),
	];
}
if ( ! $pieces ) {
	return;
}
$frame = static function ( array $piece, string $size, bool $eager = false, bool $high = false ): string {
	if ( ! has_post_thumbnail( $piece['post'] ) ) {
		return sprintf( '<div class="juanto-frame is-note"><b>%s</b><p>%s</p><span class="juanto-label">%s</span></div>', esc_html( get_the_title( $piece['post'] ) ), esc_html( $piece['note'] ), esc_html( $piece['meta'] ) );
	}
	// The top pile print and the first "all" grid tile are what's visible at
	// load, on stack and grid layouts respectively -- one of them is the page's
	// LCP candidate depending on viewport width. Everything else stays lazy.
	$attr = [ 'draggable' => 'false' ];
	if ( $eager ) {
		$attr['loading'] = 'eager';
		if ( $high ) {
			$attr['fetchpriority'] = 'high';
		}
	} else {
		$attr['loading'] = 'lazy';
	}
	return '<div class="juanto-frame is-' . esc_attr( $piece['fit'] ) . '">' . get_the_post_thumbnail( $piece['post'], $size, $attr ) . '</div>';
};
$count = count( $pieces );
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-lab' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="juanto-lab__views" role="group" aria-label="<?php esc_attr_e( 'View', 'juanto' ); ?>" hidden>
		<button type="button" data-view="stack" aria-pressed="true">stack</button><button type="button" data-view="all" aria-pressed="false">all</button>
	</div>
	<div class="juanto-lab__stage" hidden>
		<div class="juanto-lab__pile" tabindex="0" role="group" aria-roledescription="<?php esc_attr_e( 'stack', 'juanto' ); ?>" aria-label="<?php esc_attr_e( 'Lab stack. Arrow right for the next piece, arrow left for the previous one.', 'juanto' ); ?>">
			<?php foreach ( $pieces as $i => $piece ) : ?>
				<div class="juanto-print" data-i="<?php echo (int) $i; ?>" data-rot="<?php echo (int) $rot[ $i % count( $rot ) ]; ?>" data-name="<?php echo esc_attr( get_the_title( $piece['post'] ) ); ?>" data-meta="<?php echo esc_attr( $piece['meta'] ); ?>"><?php echo $frame( $piece, 'large', 0 === $i, 0 === $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endforeach; ?>
		</div>
		<div class="juanto-lab__info" aria-live="polite">
			<div class="juanto-lab__swap"><div class="juanto-lab__count juanto-label"></div><div class="juanto-lab__name"></div><div class="juanto-lab__meta juanto-label"></div></div>
			<div class="juanto-lab__nav"><button type="button" data-go="back" aria-label="<?php esc_attr_e( 'Previous piece', 'juanto' ); ?>">← back</button><button type="button" data-go="next" aria-label="<?php esc_attr_e( 'Next piece', 'juanto' ); ?>">next →</button></div>
			<p class="juanto-lab__hint juanto-label"><?php esc_html_e( 'drag the top print and flick it away', 'juanto' ); ?></p>
		</div>
	</div>
	<div class="juanto-lab__all" data-count="<?php echo (int) $count; ?>">
		<?php foreach ( $pieces as $i => $piece ) : ?>
			<button type="button" class="juanto-tile" data-i="<?php echo (int) $i; ?>"><?php echo $frame( $piece, 'medium_large', 0 === $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><b><?php echo esc_html( get_the_title( $piece['post'] ) ); ?></b><span class="juanto-label"><?php echo esc_html( $piece['meta'] ); ?></span></button>
		<?php endforeach; ?>
	</div>
</div>
