<?php
/**
 * Case study header: number, title, lede and facts from the project fields.
 *
 * @package juanto-core
 * @var array    $attributes
 * @var WP_Block $block
 */

$project_id = (int) ( $block->context['postId'] ?? get_the_ID() );
if ( ! $project_id ) {
	return;
}
$field  = static fn( string $f ) => juanto_get_project_field( $project_id, $f );
$number = juanto_case_number( $project_id );
$range  = juanto_year_range( (int) $field( 'year_start' ), (string) $field( 'year_end' ) );
$kicker = trim( ( $number ? "case study {$number}" : '' ) . ( $number && $range ? ' · ' : '' ) . $range );
$rows   = array_filter(
	[
		'role'     => $field( 'role' ),
		'team'     => $field( 'team' ),
		'timeline' => $range,
		'stack'    => $field( 'stack' ),
	]
);
$links  = (array) $field( 'links' );
// The block sits in the shared template, so the per-project lede is the post excerpt; the attribute only overrides it.
$lede = ! empty( $attributes['lede'] ) ? $attributes['lede'] : ( has_excerpt( $project_id ) ? get_the_excerpt( $project_id ) : '' );
?>
<header <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-case-meta' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="juanto-case-meta__main">
		<?php
		if ( $kicker ) :
			?>
			<p class="juanto-label"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
		<h1 class="juanto-case-meta__title"><?php echo esc_html( get_the_title( $project_id ) ); ?></h1>
		<?php
		if ( $lede ) :
			?>
			<p class="juanto-case-meta__lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
	</div>
	<dl class="juanto-case-meta__facts">
		<?php
		foreach ( $rows as $label => $value ) :
			?>
			<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div><?php endforeach; ?>
		<?php if ( $links ) : ?>
			<div><dt>links</dt><dd>
				<?php
				foreach ( $links as $item ) :
					?>
					<a href="<?php echo esc_url( $item['url'] ); ?>" rel="noopener"><?php echo esc_html( $item['label'] ); ?> ↗</a> <?php endforeach; ?>
			</dd></div>
		<?php endif; ?>
	</dl>
</header>
