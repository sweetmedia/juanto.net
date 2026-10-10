<?php
/**
 * Project list: homepage rows (case studies) or two columns (cards).
 *
 * @package juanto-core
 * @var array $attributes
 */

$tier     = in_array( $attributes['tier'] ?? 'case', [ 'case', 'card' ], true ) ? $attributes['tier'] : 'case';
$layout   = 'columns' === ( $attributes['layout'] ?? 'rows' ) ? 'columns' : 'rows';
$projects = juanto_get_projects( $tier, (int) ( $attributes['limit'] ?? -1 ) );
if ( ! $projects ) {
	return;
}

$years = array_filter( array_map( static fn( $p ) => (int) juanto_get_project_field( $p->ID, 'year_start' ), $projects ) );
$aside = 'rows' === $layout
	? ( $years ? ( min( $years ) === max( $years ) ? (string) min( $years ) : min( $years ) . ' / ' . max( $years ) ) : '' )
	: (string) count( $projects );

$has_preview = false;
foreach ( $projects as $p ) {
	if ( (int) juanto_get_project_field( $p->ID, 'preview_image' ) ) {
		$has_preview = true;
		break;
	}
}
?>
<section <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-list is-' . $layout ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="juanto-list__head juanto-label"><span><?php echo esc_html( $attributes['label'] ?? '' ); ?></span><span class="juanto-list__aside"><?php echo esc_html( $aside ); ?></span></header>
	<?php if ( 'rows' === $layout ) : ?>
		<div class="juanto-rows">
			<?php
			foreach ( $projects as $i => $p ) :
				$preview_id  = (int) juanto_get_project_field( $p->ID, 'preview_image' );
				$preview_url = $preview_id ? wp_get_attachment_image_url( $preview_id, 'large' ) : '';
				$tags        = juanto_project_tags( $p->ID );
				$row_year    = (int) juanto_get_project_field( $p->ID, 'year_start' );
				?>
				<a class="juanto-row" href="<?php echo esc_url( get_permalink( $p ) ); ?>"<?php echo $preview_url ? ' data-preview="' . esc_url( $preview_url ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<span class="juanto-row__n"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="juanto-row__main"><span class="juanto-row__title"><?php echo esc_html( get_the_title( $p ) ); ?></span><span class="juanto-row__summary"><?php echo esc_html( juanto_get_project_field( $p->ID, 'summary' ) ); ?></span></span>
					<span class="juanto-row__tags juanto-label"><?php echo esc_html( $tags ); ?><?php echo $row_year ? '<br>' . esc_html( (string) $row_year ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endforeach; ?>
		</div>
		<?php if ( $has_preview ) : ?>
			<div class="juanto-preview" aria-hidden="true"><div class="juanto-preview__box"></div></div>
		<?php endif; ?>
	<?php else : ?>
		<div class="juanto-more">
			<?php foreach ( $projects as $p ) : ?>
				<a class="juanto-more-item" href="<?php echo esc_url( get_permalink( $p ) ); ?>"><b><?php echo esc_html( get_the_title( $p ) ); ?></b><em class="juanto-label"><?php echo esc_html( (string) juanto_get_project_field( $p->ID, 'year_start' ) ); ?></em><span><?php echo esc_html( juanto_get_project_field( $p->ID, 'summary' ) ); ?></span></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
