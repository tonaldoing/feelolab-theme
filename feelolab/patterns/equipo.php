<?php
/**
 * Title: Equipo
 * Slug: feelolab/equipo
 * Categories: feelolab
 * Keywords: equipo, personas, staff, profesionales
 * Description: Tres personas con foto, nombre y cargo. Reemplazá las fotos de muestra y completá el texto alternativo con el nombre. Si el módulo Equipo está activo, cada persona también tiene su ficha propia.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_people = array(
	array( 'Carolina Díaz', __( 'Directora', 'feelolab' ) ),
	array( 'Martín López', __( 'Contador', 'feelolab' ) ),
	array( 'Lucía Fernández', __( 'Atención a clientes', 'feelolab' ) ),
);
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Nuestro equipo', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ( $feelolab_people as $feelolab_person ) :
	?>
	<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"aspectRatio":"1","scale":"cover"} -->
<figure class="wp-block-image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/persona.svg' ) ); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $feelolab_person[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $feelolab_person[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
