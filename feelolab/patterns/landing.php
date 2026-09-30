<?php
/**
 * Title: Landing completa
 * Slug: feelolab/landing
 * Categories: feelolab
 * Keywords: landing, campaña, página completa, anuncio
 * Description: Una página de campaña entera: título, beneficios, pasos, preguntas y cierre. Usala con la plantilla "Ancho completo" (trae su propio título principal).
 * Viewport Width: 1200
 * Template Types: page
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"align":"full","className":"is-style-feelolab-superficie","layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group alignfull is-style-feelolab-superficie"><!-- wp:paragraph {"className":"is-style-feelolab-volanta"} -->
<p class="is-style-feelolab-volanta"><?php esc_html_e( 'Oferta de lanzamiento', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Ordená las cuentas de tu negocio en 30 días', 'feelolab' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-feelolab-lead"} -->
<p class="is-style-feelolab-lead"><?php esc_html_e( 'Una promesa concreta: qué gana la persona y en cuánto tiempo. Una sola idea por landing.', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#contacto"><?php esc_html_e( 'Quiero empezar', 'feelolab' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"feelolab/valores"} /-->

<!-- wp:pattern {"slug":"feelolab/pasos"} /-->

<!-- wp:pattern {"slug":"feelolab/cifras"} /-->

<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:pattern {"slug":"feelolab/preguntas"} /--></div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"feelolab/llamada"} /-->
