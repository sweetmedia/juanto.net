<?php
/**
 * Closing call to action, by audience.
 *
 * @package juanto-core
 * @var WP_Block $block
 */

$cta_post = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $cta_post || ! juanto_is_tutorial( $cta_post ) ) {
	return;
}
$cta_status = juanto_get_status();

if ( 'clients' === juanto_get_tutorial( $cta_post )['audience'] ) {
	if ( '' === $cta_status['sweetmedia_url'] ) {
		return;
	}
	?>
	<aside <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-tut-cta is-clients' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<p class="juanto-tut-cta__text"><?php esc_html_e( 'Need a hand with your site? Sweet Media builds and maintains sites like this.', 'juanto' ); ?></p>
		<a class="juanto-btn is-primary" href="<?php echo esc_url( $cta_status['sweetmedia_url'] ); ?>">Sweet Media ↗</a>
	</aside>
	<?php
	return;
}

$cta_links = [
	[ __( 'Work', 'juanto' ), home_url( '/' ), '' ],
	[ __( 'Lab', 'juanto' ), (string) get_post_type_archive_link( 'project' ), '' ],
];
if ( '' !== $cta_status['github_url'] ) {
	$cta_links[] = [ 'GitHub ↗', $cta_status['github_url'], 'me noopener' ];
}
?>
<aside <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-tut-cta is-builders' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="juanto-tut-cta__text"><?php esc_html_e( 'More of what I build', 'juanto' ); ?></p>
	<div class="juanto-tut-cta__links">
		<?php foreach ( $cta_links as [ $cta_label, $cta_url, $cta_rel ] ) : ?>
			<a class="juanto-btn" href="<?php echo esc_url( $cta_url ); ?>"<?php echo $cta_rel ? ' rel="' . esc_attr( $cta_rel ) . '"' : ''; ?>><?php echo esc_html( $cta_label ); ?></a>
		<?php endforeach; ?>
	</div>
</aside>
