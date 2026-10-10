<?php
/**
 * Up to three related tutorials.
 *
 * @package juanto-core
 * @var WP_Block $block
 */

$related_post = (int) ( $block->context['postId'] ?? get_the_ID() );
$related      = $related_post ? juanto_related_tutorials( $related_post ) : [];
if ( ! $related ) {
	return;
}
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-related', 'aria-labelledby' => 'juanto-related-title' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h2 class="juanto-related__title" id="juanto-related-title"><?php esc_html_e( 'Related tutorials', 'juanto' ); ?></h2>
	<ul class="juanto-related__list">
		<?php
		foreach ( $related as $related_item ) :
			$related_facts = juanto_tutorial_facts( juanto_get_tutorial( $related_item->ID ), 'row' );
			?>
			<li>
				<a href="<?php echo esc_url( get_permalink( $related_item ) ); ?>"><?php echo esc_html( get_the_title( $related_item ) ); ?></a>
				<?php if ( $related_facts ) : ?>
					<span class="juanto-label"><?php echo esc_html( implode( ' · ', $related_facts ) ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
