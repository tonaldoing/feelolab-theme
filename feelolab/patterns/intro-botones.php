<?php
/**
 * Title: Introducción con botones
 * Slug: feelolab/intro-botones
 * Categories: feelolab
 * Keywords: intro, bajada, botones, comienzo
 * Description: Volanta, una bajada grande y dos botones. Para abrir cualquier página interior.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:paragraph {"className":"is-style-feelolab-volanta"} -->
<p class="is-style-feelolab-volanta"><?php esc_html_e( 'Quiénes somos', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"is-style-feelolab-lead"} -->
<p class="is-style-feelolab-lead"><?php esc_html_e( 'Contá en dos o tres líneas qué hacen, para quién y qué los diferencia. Es lo primero que se lee: que se entienda sin conocerlos.', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#contacto"><?php esc_html_e( 'Contactanos', 'feelolab' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Ver servicios', 'feelolab' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
