<?php
/**
 * Home por secciones (ver inc/home.php). Cada sección prendida en el Personalizador
 * se renderiza en su orden desde template-parts/home/{clave}.php.
 *
 * Si la home es una página con contenido propio y no hay secciones prendidas, se muestra la página.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

$feelolab_sections = feelolab_home_active_sections();

if ( $feelolab_sections ) {
	// Solo la portada lleva el h1. Si el hero está apagado, el h1 va oculto para no romper la jerarquía.
	if ( ! in_array( 'hero', $feelolab_sections, true ) ) {
		echo '<h1 class="screen-reader-text">' . esc_html( function_exists( 'feelo_business_name' ) ? feelo_business_name() : get_bloginfo( 'name' ) ) . '</h1>';
	}
	foreach ( $feelolab_sections as $feelolab_i => $feelolab_key ) {
		get_template_part( 'template-parts/home/' . $feelolab_key, null, array( 'index' => $feelolab_i ) );
	}
} else {
	while ( have_posts() ) :
		the_post();
		?>
		<div class="entry-content entry-content--full"><?php the_content(); ?></div>
		<?php
	endwhile;
}

get_footer();
