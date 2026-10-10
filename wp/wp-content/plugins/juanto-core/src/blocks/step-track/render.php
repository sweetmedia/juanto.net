<?php
/**
 * Step track. Renders the final state; view.js replays it.
 *
 * @package juanto-core
 * @var array $attributes
 */

$steps = max( 1, min( 30, (int) ( $attributes['steps'] ?? 15 ) ) );
$fails = [];
foreach ( (array) ( $attributes['fails'] ?? [] ) as $f ) {
	$step_no = (int) ( $f['step'] ?? 0 );
	$n       = (int) ( $f['count'] ?? 0 );
	if ( $step_no >= 1 && $step_no <= $steps && $n > 0 ) {
		$fails[ $step_no ] = min( 9, $n );
	}
}
$from    = (int) ( $attributes['bracketFrom'] ?? 0 );
$to      = (int) ( $attributes['bracketTo'] ?? 0 );
$bracket = ( $from >= 1 && $to >= $from && $to <= $steps ) ? [ $from, $to ] : null;
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-steps', 'data-steps' => (string) $steps, 'data-fails' => wp_json_encode( (object) $fails ), 'data-bracket' => $bracket ? wp_json_encode( $bracket ) : '' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="juanto-steps__top juanto-label"><span><?php echo esc_html( $attributes['caption'] ?? '' ); ?></span><button type="button" class="juanto-steps__replay"><?php esc_html_e( 'replay', 'juanto' ); ?></button></div>
	<div class="juanto-steps__track" style="--steps:<?php echo (int) $steps; ?>">
		<?php for ( $i = 1; $i <= $steps; $i++ ) : ?>
			<div class="juanto-steps__step is-done<?php echo $i === $steps ? ' is-last' : ''; ?>" data-step="<?php echo (int) $i; ?>">
				<i></i><span><?php echo (int) $i; ?></span>
				<?php if ( isset( $fails[ $i ] ) ) : ?>
					<div class="juanto-steps__rej">
					<?php
					for ( $r = 1; $r <= $fails[ $i ]; $r++ ) :
						?>
						<em>R<?php echo (int) $r; ?></em><?php endfor; ?></div>
				<?php endif; ?>
			</div>
		<?php endfor; ?>
		<?php if ( $bracket ) : ?>
			<div class="juanto-steps__brace is-on" style="--from:<?php echo (int) $bracket[0]; ?>;--to:<?php echo (int) $bracket[1]; ?>"><b><?php echo esc_html( $attributes['bracketLabel'] ?? '' ); ?></b></div>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $attributes['foot'] ) ) : ?>
		<div class="juanto-steps__foot">
			<?php
			foreach ( (array) $attributes['foot'] as $item ) :
				?>
				<span><?php echo esc_html( $item['label'] ?? '' ); ?> · <b><?php echo esc_html( $item['value'] ?? '' ); ?></b></span><?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
