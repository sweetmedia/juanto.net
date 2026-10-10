<?php
/**
 * "Now building" status bar.
 *
 * @package juanto-core
 */

$cfg = juanto_get_status();
if ( ! $cfg['enabled'] || '' === $cfg['project'] ) {
	return;
}
$done   = (int) $cfg['milestone_current'];
$total  = (int) $cfg['milestone_total'];
$commit = juanto_cached_commit_date( $cfg['repo'] );
$what   = trim( $cfg['project'] . ( $cfg['description'] ? ', ' . $cfg['description'] : '' ) );
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-status', 'role' => 'region', 'aria-label' => __( 'Now building', 'juanto' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="juanto-status__pulse" aria-hidden="true"></span><b>now</b><span class="juanto-status__sep" aria-hidden="true">/</span>
	<span class="juanto-status__what"><?php echo esc_html( 'building ' . $what ); ?></span>
	<span class="juanto-status__sep" aria-hidden="true">/</span>
	<span class="juanto-status__steps" role="img" aria-label="<?php echo esc_attr( sprintf( 'Milestone %d of %d', $done, $total ) ); ?>">
		<?php
		for ( $i = 1; $i <= $total; $i++ ) :
			?>
			<i class="<?php echo $i <= $done ? 'is-done' : ( $i === $done + 1 ? 'is-current' : '' ); ?>"></i><?php endfor; ?>
	</span>
	<span><?php echo esc_html( sprintf( 'M%d of %d', $done, $total ) ); ?></span>
	<?php
	if ( $commit ) :
		?>
		<span class="juanto-status__sep" aria-hidden="true">/</span><span><?php echo esc_html( 'last commit ' . juanto_short_date( $commit ) ); ?></span><?php endif; ?>
	<?php
	if ( $cfg['url'] ) :
		?>
		<a class="juanto-status__go" href="<?php echo esc_url( $cfg['url'] ); ?>"><?php esc_html_e( 'follow the build ↗', 'juanto' ); ?></a><?php endif; ?>
</div>
