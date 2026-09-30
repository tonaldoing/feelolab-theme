<?php
/**
 * Title: Llamada a la acción
 * Slug: feelolab/llamada
 * Categories: feelolab
 * Keywords: cta, contacto, botón, franja, cierre
 * Description: Franja del color de la marca a todo el ancho con título, texto y botón. Para cerrar una página.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"is-style-feelolab-franja","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull is-style-feelolab-franja"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( '¿Hablamos?', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Contanos qué necesitás y te respondemos en el día.', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#contacto"><?php esc_html_e( 'Escribinos', 'feelolab' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
