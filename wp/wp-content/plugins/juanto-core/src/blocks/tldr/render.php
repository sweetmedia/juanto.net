<?php
/**
 * TL;DR block.
 *
 * @package juanto-core
 * @var array $attributes
 */

$cols = [
	'problem'    => $attributes['problem'] ?? '',
	'what I did' => $attributes['did'] ?? '',
	'outcome'    => $attributes['outcome'] ?? '',
];
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-tldr' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $cols as $label => $text ) : ?>
		<div><b class="juanto-label"><?php echo esc_html( $label ); ?></b><p><?php echo esc_html( $text ); ?></p></div>
	<?php endforeach; ?>
</div>
