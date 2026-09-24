<?php
/**
 * Ficha de servicio: bajada, contenido, precio, CTA lateral, preguntas del servicio y relacionados.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_lead  = (string) feelolab_field( 'bajada' );
$feelolab_price = (string) feelolab_field( 'precio_desde' );
$feelolab_cta   = feelolab_field( 'cta' );
$feelolab_faqs  = array_filter( array_map( 'get_post', (array) feelolab_field( 'faqs' ) ) );
$feelolab_btn_t = is_array( $feelolab_cta ) && ! empty( $feelolab_cta['title'] ) ? $feelolab_cta['title'] : __( 'Consultar', 'feelolab' );
$feelolab_btn_u = is_array( $feelolab_cta ) && ! empty( $feelolab_cta['url'] ) ? $feelolab_cta['url'] : feelolab_contact_url();

get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html( get_the_title() ), 'lead' => $feelolab_lead ? '<p>' . esc_html( $feelolab_lead ) . '</p>' : '' ) );
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<div class="split split--aside">
			<div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="entry__hero"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
				<?php endif; ?>
				<div class="entry-content prose"><?php the_content(); ?></div>

				<?php if ( $feelolab_faqs ) : ?>
					<section class="section section--tight" aria-labelledby="faq-servicio">
						<h2 id="faq-servicio"><?php esc_html_e( 'Preguntas frecuentes', 'feelolab' ); ?></h2>
						<?php feelolab_faq_list( $feelolab_faqs ); ?>
					</section>
				<?php endif; ?>
			</div>

			<aside class="sticky-box" aria-label="<?php esc_attr_e( 'Contratar este servicio', 'feelolab' ); ?>">
				<?php if ( $feelolab_price ) : ?>
					<p class="sticky-box__price"><?php esc_html_e( 'Desde', 'feelolab' ); ?> <strong><?php echo esc_html( $feelolab_price ); ?></strong></p>
				<?php endif; ?>
				<?php echo feelolab_button( $feelolab_btn_t, $feelolab_btn_u, 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php feelolab_contact_list(); ?>
			</aside>
		</div>
	</div>
</article>
<?php
get_template_part( 'template-parts/related' );
