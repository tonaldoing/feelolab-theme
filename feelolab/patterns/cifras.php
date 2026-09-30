<?php
/**
 * Title: Cifras
 * Slug: feelolab/cifras
 * Categories: feelolab
 * Keywords: números, cifras, datos, estadísticas, logros
 * Description: Cuatro números grandes con su texto. Solo datos reales y verificables.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_stats = array(
	array( '+15', __( 'años de experiencia', 'feelolab' ) ),
	array( '+200', __( 'clientes', 'feelolab' ) ),
	array( '24 h', __( 'tiempo de respuesta', 'feelolab' ) ),
	array( '4,9', __( 'de calificación en Google', 'feelolab' ) ),
);
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group alignwide"><!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ( $feelolab_stats as $feelolab_stat ) :
	?>
	<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"is-style-feelolab-dato"} -->
<p class="is-style-feelolab-dato"><?php echo esc_html( $feelolab_stat[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $feelolab_stat[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
