<?php
/**
 * Section head block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$label  = (string) ( $attributes['label'] ?? '' );
$anchor = sanitize_title( ( $attributes['anchor'] ?? '' ) ? $attributes['anchor'] : $label );
?>
<header <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-section-head', 'id' => $anchor, 'data-toc' => $label ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="juanto-section-head__num juanto-label"><?php echo esc_html( trim( ( $attributes['number'] ?? '' ) . ' · ' . $label, ' ·' ) ); ?></p>
	<h2 class="juanto-section-head__title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h2>
</header>
