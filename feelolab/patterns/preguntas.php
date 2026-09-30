<?php
/**
 * Title: Preguntas frecuentes
 * Slug: feelolab/preguntas
 * Categories: feelolab
 * Keywords: preguntas, faq, dudas, ayuda
 * Description: Preguntas que se abren al tocarlas. Para las preguntas que se repiten en todo el sitio conviene el módulo Preguntas frecuentes (suma schema para Google).
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_faqs = array(
	array( __( '¿Cuánto tarda una respuesta?', 'feelolab' ), __( 'Respondemos todas las consultas en menos de 24 horas hábiles.', 'feelolab' ) ),
	array( __( '¿Trabajan a distancia?', 'feelolab' ), __( 'Sí, atendemos por videollamada a clientes de todo el país.', 'feelolab' ) ),
	array( __( '¿Cómo se paga?', 'feelolab' ), __( 'Transferencia, tarjeta o Mercado Pago. Emitimos factura.', 'feelolab' ) ),
);
?>
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Preguntas frecuentes', 'feelolab' ); ?></h2>
<!-- /wp:heading -->
<?php foreach ( $feelolab_faqs as $feelolab_faq ) : ?>

<!-- wp:details -->
<details class="wp-block-details"><summary><?php echo esc_html( $feelolab_faq[0] ); ?></summary><!-- wp:paragraph -->
<p><?php echo esc_html( $feelolab_faq[1] ); ?></p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
<?php endforeach; ?>
