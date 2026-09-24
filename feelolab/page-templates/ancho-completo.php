<?php
/**
 * Template Name: Ancho completo
 *
 * Para landings armadas con bloques: sin cabecera de página y sin medida de línea.
 * El h1 lo pone el contenido (un bloque Título de nivel 1).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="entry-content entry-content--full">
		<?php the_content(); ?>
	</div>
	<?php
endwhile;

get_footer();
