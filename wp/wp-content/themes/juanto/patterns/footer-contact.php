<?php
/**
 * Title: Footer contact
 * Slug: juanto/footer-contact
 * Categories: juanto
 * Inserter: no
 *
 * @package juanto
 */

$contact = function_exists( 'juanto_get_status' ) ? juanto_get_status() : [];
?>
<!-- wp:html -->
<div class="juanto-footer alignwide">
	<div><h2><?php echo esc_html__( 'Hiring, or have a project?', 'juanto' ); ?></h2><p><?php echo esc_html__( "Tell me what you're building.", 'juanto' ); ?></p></div>
	<div class="juanto-footer__btns">
		<?php if ( ! empty( $contact['contact_email'] ) ) : ?>
			<a class="juanto-btn is-primary" href="<?php echo esc_url( 'mailto:' . $contact['contact_email'] ); ?>"><?php echo esc_html__( 'Email me', 'juanto' ); ?></a>
		<?php endif; ?>
		<?php if ( ! empty( $contact['resume_url'] ) ) : ?>
			<a class="juanto-btn" href="<?php echo esc_url( $contact['resume_url'] ); ?>"><?php echo esc_html__( 'Résumé (PDF)', 'juanto' ); ?></a>
		<?php endif; ?>
		<?php if ( ! empty( $contact['linkedin_url'] ) ) : ?>
			<a class="juanto-btn" href="<?php echo esc_url( $contact['linkedin_url'] ); ?>" rel="me noopener">LinkedIn</a>
		<?php endif; ?>
	</div>
</div>
<!-- /wp:html -->
