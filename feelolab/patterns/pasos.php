<?php
/**
 * Title: Cómo trabajamos (pasos)
 * Slug: feelolab/pasos
 * Categories: feelolab
 * Keywords: pasos, proceso, cómo trabajamos, metodología
 * Description: Cuatro pasos numerados sobre fondo suave: explica el proceso y baja la ansiedad antes de contactar.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_steps = array(
	array( __( 'Nos contás', 'feelolab' ), __( 'Una charla corta para entender qué necesitás.', 'feelolab' ) ),
	array( __( 'Te proponemos', 'feelolab' ), __( 'Un plan claro con plazos y costos cerrados.', 'feelolab' ) ),
	array( __( 'Lo hacemos', 'feelolab' ), __( 'Trabajamos y te mantenemos al tanto en cada etapa.', 'feelolab' ) ),
	array( __( 'Te acompañamos', 'feelolab' ), __( 'Seguimos disponibles después de la entrega.', 'feelolab' ) ),
);
?>
<!-- wp:group {"align":"wide","className":"is-style-feelolab-superficie","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group alignwide is-style-feelolab-superficie"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Cómo trabajamos', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns">
<?php
foreach ( $feelolab_steps as $feelolab_i => $feelolab_step ) :
	?>
	<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"className":"is-style-feelolab-dato"} -->
<p class="is-style-feelolab-dato"><?php echo esc_html( str_pad( (string) ( $feelolab_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $feelolab_step[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $feelolab_step[1] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
