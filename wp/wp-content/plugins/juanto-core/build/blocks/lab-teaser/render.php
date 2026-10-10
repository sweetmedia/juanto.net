<?php
/**
 * Lab teaser: three prints in a fan, linking to /lab/.
 *
 * @package juanto-core
 * @var array $attributes
 */

$all    = juanto_get_projects( 'lab' );
$pieces = array_slice( array_values( array_filter( $all, 'has_post_thumbnail' ) ), 0, 3 );
if ( ! $pieces ) {
	return;
}
?>
<section class="juanto-list is-lab">
	<header class="juanto-list__head juanto-label"><span>lab</span><span class="juanto-list__aside"><?php echo esc_html( sprintf( '%d pieces', count( $all ) ) ); ?></span></header>
	<a <?php echo get_block_wrapper_attributes( [ 'class' => 'juanto-lab-teaser', 'href' => get_post_type_archive_link( 'project' ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<span class="juanto-lab-teaser__fan" aria-hidden="true">
			<?php
			foreach ( $pieces as $p ) :
				echo get_the_post_thumbnail( $p, 'medium_large', [ 'loading' => 'lazy', 'alt' => '', 'class' => 'juanto-print is-' . juanto_get_project_field( $p->ID, 'lab_fit' ) ] );
			endforeach;
			?>
		</span>
		<span class="juanto-lab-teaser__text"><span class="juanto-lab-teaser__title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></span><span class="juanto-lab-teaser__desc"><?php echo esc_html( $attributes['text'] ?? '' ); ?></span><span class="juanto-link"><?php esc_html_e( 'Open the lab →', 'juanto' ); ?></span></span>
	</a>
</section>
