<?php
/**
 * Proyectos destacados.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_type = feelo_module_post_type( 'proyectos' );
$feelolab_q    = new WP_Query(
	array(
		'post_type'      => $feelolab_type,
		'posts_per_page' => max( 1, (int) feelolab_home( 'proyectos', 'count' ) ),
		'no_found_rows'  => true,
	)
);
if ( ! $feelolab_q->have_posts() ) {
	return;
}
?>
<section class="section" id="proyectos" aria-labelledby="proyectos-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'proyectos', 'title' ), '', 'proyectos-title' ); ?>
		<div class="grid grid--3">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				get_template_part( 'template-parts/cards/card', 'proyecto' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<p class="section__more"><?php echo feelolab_button( __( 'Ver todos los proyectos', 'feelolab' ), (string) get_post_type_archive_link( $feelolab_type ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	</div>
</section>
