<?php
/**
 * Title: Texto con imagen
 * Slug: feelolab/texto-imagen
 * Categories: feelolab
 * Keywords: nosotros, historia, imagen, foto
 * Description: Un bloque de texto con una foto al costado. En el celular la foto va arriba.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:media-text {"align":"wide","mediaLink":"","mediaType":"image","verticalAlignment":"center"} -->
<div class="wp-block-media-text alignwide is-stacked-on-mobile is-vertically-aligned-center"><figure class="wp-block-media-text__media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/foto.svg' ) ); ?>" alt=""/></figure><div class="wp-block-media-text__content"><!-- wp:paragraph {"className":"is-style-feelolab-volanta"} -->
<p class="is-style-feelolab-volanta"><?php esc_html_e( 'Nuestra historia', 'feelolab' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Más de 15 años al lado de pymes', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Contá cómo empezaron, qué aprendieron y qué los mueve hoy. Dos párrafos cortos alcanzan. Reemplazá la imagen con una foto real del equipo o del lugar.', 'feelolab' ); ?></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:media-text -->
