<?php
/**
 * Ficha de producto: imagen, precio, disponibilidad, consulta por WhatsApp y ficha técnica.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_price = (string) feelolab_field( 'precio' );
$feelolab_offer = (string) feelolab_field( 'precio_oferta' );
$feelolab_sku   = (string) feelolab_field( 'sku' );
$feelolab_avail = feelolab_field( 'disponible' );
$feelolab_in    = ! ( '0' === (string) $feelolab_avail || false === $feelolab_avail );
$feelolab_wa    = feelolab_field( 'consulta_whatsapp' );
$feelolab_specs = array();
foreach ( preg_split( '/\R/', (string) feelolab_field( 'ficha_tecnica' ) ) as $feelolab_line ) {
	if ( str_contains( $feelolab_line, ':' ) ) {
		$feelolab_specs[] = array_map( 'trim', explode( ':', $feelolab_line, 2 ) );
	}
}
$feelolab_wa_url = ( '' === $feelolab_wa || $feelolab_wa ) && function_exists( 'feelo_whatsapp_url' )
	/* translators: %s: nombre del producto */
	? feelo_whatsapp_url( sprintf( __( 'Hola, quiero consultar por: %s', 'feelolab' ), get_the_title() ) . ( $feelolab_sku ? ' (' . $feelolab_sku . ')' : '' ) )
	: '';
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<?php feelolab_breadcrumbs(); ?>
		<div class="product">
			<div class="product__media">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) );
				}
				?>
			</div>
			<div class="product__summary">
				<h1 class="product__title"><?php the_title(); ?></h1>
				<?php get_template_part( 'template-parts/price', null, array( 'price' => $feelolab_price, 'offer' => $feelolab_offer ) ); ?>
				<p class="product__stock <?php echo $feelolab_in ? 'is-in' : 'is-out'; ?>">
					<?php echo feelolab_icon( $feelolab_in ? 'check' : 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php $feelolab_in ? esc_html_e( 'Disponible', 'feelolab' ) : esc_html_e( 'Sin stock por el momento', 'feelolab' ); ?>
				</p>
				<?php if ( has_excerpt() ) : ?>
					<div class="product__excerpt"><?php the_excerpt(); ?></div>
				<?php endif; ?>
				<div class="product__actions">
					<?php
					echo $feelolab_wa_url
						? feelolab_button( __( 'Consultar por WhatsApp', 'feelolab' ), $feelolab_wa_url, 'primary' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						: feelolab_button( __( 'Consultar', 'feelolab' ), feelolab_contact_url(), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
				<?php if ( $feelolab_sku ) : ?>
					<p class="product__sku"><?php esc_html_e( 'Código:', 'feelolab' ); ?> <?php echo esc_html( $feelolab_sku ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="split split--aside section">
			<div class="entry-content prose"><?php the_content(); ?></div>
			<?php if ( $feelolab_specs ) : ?>
				<div>
					<h2><?php esc_html_e( 'Ficha técnica', 'feelolab' ); ?></h2>
					<table class="specs">
						<caption class="screen-reader-text">
							<?php
							/* translators: %s: producto */
							echo esc_html( sprintf( __( 'Características de %s', 'feelolab' ), get_the_title() ) );
							?>
						</caption>
						<tbody>
							<?php foreach ( $feelolab_specs as $feelolab_spec ) : ?>
								<tr><th scope="row"><?php echo esc_html( $feelolab_spec[0] ); ?></th><td><?php echo esc_html( $feelolab_spec[1] ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>
<?php
get_template_part( 'template-parts/related' );
