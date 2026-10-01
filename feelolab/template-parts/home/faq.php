<?php
/**
 * Preguntas frecuentes (acordeón nativo + schema FAQPage).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_faqs = get_posts( feelolab_home_query_args( 'faq', (string) feelo_module_post_type( 'faq' ) ) );
if ( ! $feelolab_faqs ) {
	feelolab_home_placeholder( 'faq' );
	return;
}
?>
<section class="<?php echo esc_attr( feelolab_home_section_class( 'faq' ) ); ?>" id="preguntas" aria-labelledby="faq-title">
	<div class="container container--narrow">
		<?php feelolab_section_header( (string) feelolab_home( 'faq', 'title' ), (string) feelolab_home( 'faq', 'text' ), 'faq-title' ); ?>
		<?php feelolab_faq_list( $feelolab_faqs ); ?>
	</div>
</section>
