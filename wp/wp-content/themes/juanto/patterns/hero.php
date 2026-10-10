<?php
/**
 * Title: Hero with photo background
 * Slug: juanto/hero
 * Categories: juanto
 * Inserter: no
 *
 * @package juanto
 */

$cfg = function_exists( 'juanto_get_status' ) ? juanto_get_status() : [];
?>
<!-- wp:html -->
<header class="juanto-hero">
	<picture class="juanto-hero__photo">
		<source type="image/webp" srcset="<?php echo esc_url( get_theme_file_uri( 'assets/img/hero-900.webp' ) ); ?> 900w, <?php echo esc_url( get_theme_file_uri( 'assets/img/hero-1600.webp' ) ); ?> 1600w" sizes="(min-width: 900px) 70vw, 100vw">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/hero-1600.jpg' ) ); ?>" width="1600" height="1600" fetchpriority="high" decoding="async" alt="<?php echo esc_attr__( 'Juan Pérez working on a laptop, coffee in hand, in soft window light', 'juanto' ); ?>">
	</picture>
	<div class="juanto-hero__nav">
		<a class="juanto-jp" href="/" aria-label="juanto.net home"></a>
		<nav class="juanto-hero__links juanto-label" aria-label="<?php esc_attr_e( 'Main', 'juanto' ); ?>"><a href="/" aria-current="page">work</a><a href="/lab/">lab</a><a href="/writing/">writing</a><a href="/about/">about</a></nav>
	</div>
	<div class="juanto-hero__copy">
		<h1 class="juanto-hero__name">Juan Pérez</h1>
		<p class="juanto-hero__role">Design engineer<span>.</span></p>
		<p class="juanto-hero__bio"><?php echo esc_html__( 'I design brands and build the products behind them: the identity, the interface and the code that runs it.', 'juanto' ); ?></p>
		<?php if ( ! empty( $cfg['availability'] ) ) : ?>
			<p class="juanto-hero__avail juanto-label"><i aria-hidden="true"></i><?php echo esc_html( $cfg['availability'] ); ?></p>
		<?php endif; ?>
		<p class="juanto-hero__btns">
			<?php if ( ! empty( $cfg['resume_url'] ) ) : ?>
				<a class="juanto-btn is-primary" href="<?php echo esc_url( $cfg['resume_url'] ); ?>">Résumé ↗</a>
			<?php endif; ?>
			<?php if ( ! empty( $cfg['github_url'] ) ) : ?>
				<a class="juanto-btn" href="<?php echo esc_url( $cfg['github_url'] ); ?>" rel="me noopener">GitHub</a>
			<?php endif; ?>
			<?php if ( ! empty( $cfg['linkedin_url'] ) ) : ?>
				<a class="juanto-btn" href="<?php echo esc_url( $cfg['linkedin_url'] ); ?>" rel="me noopener">LinkedIn</a>
			<?php endif; ?>
		</p>
	</div>
</header>
<!-- /wp:html -->
