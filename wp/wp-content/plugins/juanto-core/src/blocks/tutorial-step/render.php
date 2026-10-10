<?php
/**
 * One tutorial step. The number comes from the step's position, never from saved data.
 *
 * @package juanto-core
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

$step_n     = juanto_tutorial_step_counter();
$step_post  = (int) ( $block->context['postId'] ?? get_the_ID() );
$step_title = trim( (string) ( $attributes['title'] ?? '' ) );
$step_info  = $step_post ? ( juanto_tutorial_steps( $step_post )[ $step_n - 1 ] ?? null ) : null;
$step_slug  = sanitize_title( $step_title );
$anchor     = juanto_tutorial_claim_anchor( ( $step_info && $step_info['title'] === $step_title ) ? $step_info['anchor'] : ( '' !== $step_slug ? $step_slug : 'step-' . $step_n ) );
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-step', 'id' => $anchor, 'aria-labelledby' => $anchor . '-title' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="juanto-step__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) $step_n, 2, '0', STR_PAD_LEFT ) ); ?></span>
	<h2 class="juanto-step__title" id="<?php echo esc_attr( $anchor . '-title' ); ?>"><span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %d: step number */ __( 'Step %d: ', 'juanto' ), $step_n ) ); ?></span><?php echo esc_html( $step_title ); ?></h2>
	<div class="juanto-step__body"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks ?></div>
</section>
