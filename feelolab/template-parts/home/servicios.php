<?php
/**
 * Grilla de servicios.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_q = new WP_Query(
	array(
		'post_type'      => feelo_module_post_type( 'servicios' ),
		'posts_per_page' => max( 1, (int) feelolab_home( 'servicios', 'count' ) ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'no_found_rows'  => true,
	)
);
if ( ! $feelolab_q->have_posts() ) {
	return;
}
?>
<section class="section" id="servicios" aria-labelledby="servicios-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'servicios', 'title' ), (string) feelolab_home( 'servicios', 'text' ), 'servicios-title' ); ?>
		<div class="grid grid--3">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				get_template_part( 'template-parts/cards/card', 'servicio' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<p class="section__more"><?php echo feelolab_button( __( 'Ver todos los servicios', 'feelolab' ), (string) get_post_type_archive_link( feelo_module_post_type( 'servicios' ) ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	</div>
</section>
