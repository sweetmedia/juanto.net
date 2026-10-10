<?php
/**
 * Tutorial facts: badge, list row, or the full header (direction C): chips, the
 * result (a wide figure only when there is a result image), requirements, video.
 *
 * @package juanto-core
 * @var array    $attributes
 * @var WP_Block $block
 */

$meta_post = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $meta_post || ! juanto_is_tutorial( $meta_post ) ) {
	return;
}
$meta_variant = in_array( $attributes['variant'] ?? 'full', [ 'full', 'row', 'badge' ], true ) ? $attributes['variant'] : 'full';

if ( 'badge' === $meta_variant ) {
	printf( '<span %s>%s</span>', get_block_wrapper_attributes( [ 'class' => 'juanto-tut-badge' ] ), esc_html__( 'Tutorial', 'juanto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$tut   = juanto_get_tutorial( $meta_post );
$facts = juanto_tutorial_facts( $tut, $meta_variant );

if ( 'row' === $meta_variant ) {
	if ( $facts ) {
		printf( '<p %s>%s</p>', get_block_wrapper_attributes( [ 'class' => 'juanto-tut-meta is-row juanto-label' ] ), esc_html( implode( ' · ', $facts ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return;
}

$meta_image    = $tut['result_image'];
$meta_video    = juanto_youtube_id( $tut['video'] );
$meta_text_row = '' !== $tut['result'] && ! $meta_image;
if ( ! $facts && ! $tut['prereqs'] && '' === $tut['result'] && ! $meta_image && '' === $meta_video ) {
	return;
}
$meta_chips = '';
foreach ( $facts as $fact ) {
	$meta_chips .= '<li class="juanto-chip">' . esc_html( $fact ) . '</li>';
}
if ( $facts && juanto_tut_is_stale( $tut['tested'] ) ) {
	$meta_chips .= '<li class="juanto-chip is-stale">' . esc_html__( 'May be outdated', 'juanto' ) . '</li>';
}
$meta_title = get_the_title( $meta_post );
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-tut-meta' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $facts ) : ?>
		<ul class="juanto-tut-meta__chips" aria-label="<?php esc_attr_e( 'Tutorial facts', 'juanto' ); ?>"><?php echo $meta_chips; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></ul>
	<?php endif; ?>
	<?php if ( $meta_image ) : ?>
		<figure class="juanto-tut-meta__result">
			<?php echo wp_get_attachment_image( $meta_image, 'large', false, [ 'class' => 'attachment-large size-large wp-image-' . $meta_image, 'loading' => 'eager', 'fetchpriority' => 'high' ] ); ?>
			<?php if ( '' !== $tut['result'] ) : ?>
				<figcaption><span class="juanto-label"><?php esc_html_e( "You'll end up with", 'juanto' ); ?></span><span><?php echo esc_html( $tut['result'] ); ?></span></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>
	<?php if ( $meta_text_row || $tut['prereqs'] ) : ?>
		<dl class="juanto-tut-meta__rows">
			<?php if ( $meta_text_row ) : ?>
				<div><dt class="juanto-label"><?php esc_html_e( "You'll end up with", 'juanto' ); ?></dt><dd><?php echo esc_html( $tut['result'] ); ?></dd></div>
			<?php endif; ?>
			<?php if ( $tut['prereqs'] ) : ?>
				<div><dt class="juanto-label"><?php esc_html_e( 'Before you start', 'juanto' ); ?></dt><dd><ul>
				<?php foreach ( $tut['prereqs'] as $prereq ) : ?>
					<li><?php echo esc_html( $prereq ); ?></li>
				<?php endforeach; ?>
				</ul></dd></div>
			<?php endif; ?>
		</dl>
	<?php endif; ?>
	<?php if ( '' !== $meta_video ) : ?>
		<div class="juanto-video" data-video="<?php echo esc_attr( $meta_video ); ?>" data-title="<?php echo esc_attr( $meta_title ); ?>">
			<button type="button" class="juanto-video__play">
				<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $meta_video . '/hqdefault.jpg' ); ?>" alt="" width="480" height="360" loading="lazy" decoding="async">
				<span class="juanto-video__label"><?php esc_html_e( 'Prefer video?', 'juanto' ); ?><br><?php esc_html_e( 'Watch the walkthrough', 'juanto' ); ?><span class="screen-reader-text">: <?php echo esc_html( $meta_title ); ?></span></span>
			</button>
		</div>
	<?php endif; ?>
</div>
