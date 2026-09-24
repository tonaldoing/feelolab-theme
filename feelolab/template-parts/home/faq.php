<?php
/**
 * Preguntas frecuentes (acordeón nativo + schema FAQPage).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_faqs = get_posts(
	array(
		'post_type'      => feelo_module_post_type( 'faq' ),
		'posts_per_page' => max( 1, (int) feelolab_home( 'faq', 'count' ) ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	)
);
if ( ! $feelolab_faqs ) {
	return;
}
?>
<section class="section" id="preguntas" aria-labelledby="faq-title">
	<div class="container container--narrow">
		<?php feelolab_section_header( (string) feelolab_home( 'faq', 'title' ), '', 'faq-title' ); ?>
		<?php feelolab_faq_list( $feelolab_faqs ); ?>
	</div>
</section>
