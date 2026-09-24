<?php
/**
 * Template Name: Contacto
 *
 * Formulario + datos del negocio (de Ajustes del sitio) + sedes si el módulo está activo.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html( get_the_title() ) ) );
	?>
	<div class="container section">
		<div class="split split--contact">
			<div>
				<?php if ( get_the_content() ) : ?>
					<div class="entry-content prose"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php
				if ( function_exists( 'feelo_contact_form' ) ) {
					echo feelo_contact_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- el plugin escapa.
				}
				?>
			</div>
			<aside class="contact-aside" aria-label="<?php esc_attr_e( 'Datos de contacto', 'feelolab' ); ?>">
				<h2 class="contact-aside__title"><?php esc_html_e( 'Datos de contacto', 'feelolab' ); ?></h2>
				<?php feelolab_contact_list(); ?>
				<?php feelolab_socials(); ?>
			</aside>
		</div>

		<?php
		$feelolab_sedes = function_exists( 'feelo_module_post_type' ) ? feelo_module_post_type( 'sedes' ) : null;
		if ( $feelolab_sedes ) :
			$feelolab_q = new WP_Query(
				array(
					'post_type'      => $feelolab_sedes,
					'posts_per_page' => 12,
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'no_found_rows'  => true,
				)
			);
			if ( $feelolab_q->have_posts() ) :
				?>
				<section class="section" aria-labelledby="sedes-title">
					<?php feelolab_section_header( __( 'Sedes', 'feelolab' ), '', 'sedes-title' ); ?>
					<div class="grid grid--3">
						<?php
						while ( $feelolab_q->have_posts() ) :
							$feelolab_q->the_post();
							get_template_part( 'template-parts/cards/card', 'sede' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
				<?php
			endif;
		endif;
		?>
	</div>
	<?php
endwhile;

get_footer();
