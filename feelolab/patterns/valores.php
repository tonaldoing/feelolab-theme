<?php
/**
 * Title: Misión, visión y valores
 * Slug: feelolab/valores
 * Categories: feelolab
 * Keywords: misión, visión, valores, pilares, beneficios
 * Description: Tres tarjetas lado a lado. Sirve también para tres beneficios o tres diferenciales.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_cards = array(
	array( __( 'Misión', 'feelolab' ), __( 'Qué hacen todos los días y para quién, en una o dos frases.', 'feelolab' ) ),
	array( __( 'Visión', 'feelolab' ), __( 'Adónde quieren llegar: el cambio que buscan en sus clientes.', 'feelolab' ) ),
	array( __( 'Valores', 'feelolab' ), __( 'Tres o cuatro palabras que guían cómo trabajan, con un ejemplo concreto.', 'feelolab' ) ),
);
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Lo que nos guía', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ( $feelolab_cards as $feelolab_card ) :
	?>
	<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"is-style-feelolab-tarjeta","layout":{"type":"default"}} -->
<div class="wp-block-group is-style-feelolab-tarjeta"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $feelolab_card[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $feelolab_card[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
