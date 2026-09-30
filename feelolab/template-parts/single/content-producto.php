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
// Opciones para elegir ("Plataforma: Acrílico | Metálica"): lo elegido se suma al mensaje de la consulta.
$feelolab_options = array();
foreach ( preg_split( '/\R/', (string) feelolab_field( 'opciones' ) ) as $feelolab_line ) {
	if ( ! str_contains( $feelolab_line, ':' ) ) {
		continue;
	}
	list( $feelolab_opt_name, $feelolab_opt_values ) = array_map( 'trim', explode( ':', $feelolab_line, 2 ) );
	$feelolab_opt_values                             = array_values( array_filter( array_map( 'trim', explode( '|', $feelolab_opt_values ) ), 'strlen' ) );
	if ( $feelolab_opt_name && $feelolab_opt_values ) {
		$feelolab_options[ $feelolab_opt_name ] = $feelolab_opt_values;
	}
}
/* translators: %s: nombre del producto */
$feelolab_message = sprintf( __( 'Hola, quiero consultar por: %s', 'feelolab' ), get_the_title() ) . ( $feelolab_sku ? ' (' . $feelolab_sku . ')' : '' );
$feelolab_wa_url  = ( '' === $feelolab_wa || $feelolab_wa ) && function_exists( 'feelo_whatsapp_url' ) ? feelo_whatsapp_url( $feelolab_message ) : '';
$feelolab_ask_url = $feelolab_wa_url ? $feelolab_wa_url : add_query_arg( 'consulta', rawurlencode( $feelolab_message ), feelolab_contact_page_url() );
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<?php feelolab_breadcrumbs(); ?>
		<div class="product">
			<div class="product__media">
				<?php get_template_part( 'template-parts/gallery' ); ?>
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
				<?php if ( $feelolab_options ) : ?>
					<fieldset class="product-options" data-feelo-options data-message="<?php echo esc_attr( $feelolab_message ); ?>">
						<legend class="product-options__legend"><?php esc_html_e( 'Elegí para tu consulta', 'feelolab' ); ?></legend>
						<?php foreach ( $feelolab_options as $feelolab_opt_name => $feelolab_opt_values ) : ?>
							<?php $feelolab_opt_id = wp_unique_id( 'opcion-' ); ?>
							<p class="product-options__field">
								<label for="<?php echo esc_attr( $feelolab_opt_id ); ?>"><?php echo esc_html( $feelolab_opt_name ); ?></label>
								<select id="<?php echo esc_attr( $feelolab_opt_id ); ?>" data-option="<?php echo esc_attr( $feelolab_opt_name ); ?>">
									<option value=""><?php esc_html_e( 'Sin elegir', 'feelolab' ); ?></option>
									<?php foreach ( $feelolab_opt_values as $feelolab_opt_value ) : ?>
										<option><?php echo esc_html( $feelolab_opt_value ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
						<?php endforeach; ?>
					</fieldset>
				<?php endif; ?>
				<div class="product__actions">
					<?php
					echo feelolab_button( $feelolab_wa_url ? __( 'Consultar por WhatsApp', 'feelolab' ) : __( 'Consultar', 'feelolab' ), $feelolab_ask_url, 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
