<?php
/**
 * Title: About photo
 * Slug: juanto/about-photo
 * Categories: juanto
 * Inserter: no
 *
 * @package juanto
 */

?>
<!-- wp:html -->
<figure class="juanto-about__photo juanto-frame">
	<picture>
		<source type="image/webp" srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/hero-900.webp' ) ); ?> 900w, <?php echo esc_url( get_theme_file_uri( 'assets/img/hero-1600.webp' ) ); ?> 1600w" sizes="(min-width: 900px) 560px, 100vw">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/hero-1600.jpg' ) ); ?>" width="1600" height="1600" alt="<?php echo esc_attr__( 'Juan Pérez working on a laptop, coffee in hand, in soft window light', 'juanto' ); ?>">
	</picture>
</figure>
<!-- /wp:html -->
