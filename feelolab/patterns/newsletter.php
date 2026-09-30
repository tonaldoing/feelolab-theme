<?php
/**
 * Title: Suscripción al newsletter
 * Slug: feelolab/newsletter
 * Categories: feelolab
 * Keywords: newsletter, suscripción, email, novedades
 * Description: Franja con el formulario de suscripción (se configura en Ajustes del sitio → Newsletter).
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"wide","className":"is-style-feelolab-superficie","layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group alignwide is-style-feelolab-superficie"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Recibí nuestras novedades', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Un email al mes con lo que te sirve. Sin spam: te das de baja con un clic.', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[feelo_newsletter]
<!-- /wp:shortcode --></div>
<!-- /wp:group -->
