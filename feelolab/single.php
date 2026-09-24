<?php
/**
 * Contenido individual. Cada tipo tiene su cuerpo en template-parts/single/content-{tipo}.php
 * (servicio, producto, sede…) y cae a content.php si no lo tiene.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/single/content', feelolab_type_slug() );
endwhile;

get_footer();
