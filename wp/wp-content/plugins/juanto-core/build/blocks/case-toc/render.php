<?php
/**
 * Contents: section heads of a case study, or the sticky step pill of a tutorial.
 *
 * @package juanto-core
 * @var array    $attributes
 * @var WP_Block $block
 */

$toc_post = get_post( (int) ( $block->context['postId'] ?? get_the_ID() ) );

if ( 'tutorial-step' === ( $attributes['source'] ?? '' ) ) {
	$toc_steps = $toc_post && ! post_password_required( $toc_post ) ? juanto_tutorial_steps( $toc_post->ID ) : [];
	if ( ! $toc_steps ) {
		return;
	}
	$toc_titles = array_map(
		static fn( array $s ) => '' !== $s['title'] ? $s['title'] : sprintf( /* translators: %d: step number */ __( 'Step %d', 'juanto' ), $s['n'] ),
		$toc_steps
	);
	?>
	<nav <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-step-nav', 'aria-label' => __( 'Tutorial steps', 'juanto' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<details>
			<summary>
				<span class="juanto-step-nav__count juanto-label"><?php printf( /* translators: 1: current step number, 2: number of steps */ esc_html__( 'Step %1$s of %2$d', 'juanto' ), '<b data-step-current>1</b>', count( $toc_steps ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup ?></span>
				<span class="juanto-step-nav__title" data-step-title><?php echo esc_html( $toc_titles[0] ); ?></span>
				<span class="juanto-step-nav__track" aria-hidden="true">
				<?php
				foreach ( array_keys( $toc_steps ) as $toc_i ) :
					?>
					<i<?php echo 0 === $toc_i ? ' class="is-on"' : ''; ?>></i><?php endforeach; ?></span>
			</summary>
			<ol>
				<?php foreach ( $toc_steps as $toc_i => $toc_step ) : ?>
					<li><a href="#<?php echo esc_attr( $toc_step['anchor'] ); ?>"><?php echo esc_html( $toc_titles[ $toc_i ] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</details>
	</nav>
	<?php
	return;
}

$items = $toc_post ? juanto_toc_items( $toc_post->post_content ) : [];
if ( ! $items ) {
	return;
}
?>
<nav <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-toc', 'aria-label' => __( 'Case study sections', 'juanto' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	foreach ( $items as $item ) :
		?>
		<a href="#<?php echo esc_attr( $item['anchor'] ); ?>"><span><?php echo esc_html( $item['number'] ); ?></span><?php echo esc_html( $item['label'] ); ?></a><?php endforeach; ?>
</nav>
