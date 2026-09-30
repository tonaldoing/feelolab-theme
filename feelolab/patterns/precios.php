<?php
/**
 * Title: Tabla de precios
 * Slug: feelolab/precios
 * Categories: feelolab
 * Keywords: precios, planes, tarifas, abonos, paquetes
 * Description: Tres planes con lo que incluye cada uno; el del medio va destacado.
 * Viewport Width: 1200
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_plans = array(
	array( __( 'Básico', 'feelolab' ), '$ 25.000', array( __( 'Lo esencial para arrancar', 'feelolab' ), __( 'Atención por email', 'feelolab' ), __( 'Informe mensual', 'feelolab' ) ), false ),
	array( __( 'Completo', 'feelolab' ), '$ 45.000', array( __( 'Todo lo del plan Básico', 'feelolab' ), __( 'Atención por WhatsApp', 'feelolab' ), __( 'Reunión mensual', 'feelolab' ) ), true ),
	array( __( 'Empresas', 'feelolab' ), '$ 80.000', array( __( 'Para equipos grandes', 'feelolab' ), __( 'Persona asignada', 'feelolab' ), __( 'Reportes a medida', 'feelolab' ) ), false ),
);
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained","contentSize":"100%"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Planes', 'feelolab' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><?php foreach ( $feelolab_plans as $feelolab_plan ) : ?>
	<?php $feelolab_style = $feelolab_plan[3] ? 'is-style-feelolab-destacada' : 'is-style-feelolab-tarjeta'; ?><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"<?php echo esc_attr( $feelolab_style ); ?>","layout":{"type":"default"}} -->
<div class="wp-block-group <?php echo esc_attr( $feelolab_style ); ?>">
	<?php
	if ( $feelolab_plan[3] ) :
		?>
	<!-- wp:paragraph {"className":"is-style-feelolab-volanta"} -->
<p class="is-style-feelolab-volanta"><?php esc_html_e( 'El más elegido', 'feelolab' ); ?></p>
<!-- /wp:paragraph --><?php endif; ?>

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $feelolab_plan[0] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-feelolab-dato"} -->
<p class="is-style-feelolab-dato"><?php echo esc_html( $feelolab_plan[1] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"is-style-feelolab-check"} -->
<ul class="wp-block-list is-style-feelolab-check">
	<?php
	foreach ( $feelolab_plan[2] as $feelolab_item ) :
		?>
	<!-- wp:list-item -->
<li><?php echo esc_html( $feelolab_item ); ?></li>
<!-- /wp:list-item --><?php endforeach; ?></ul>
<!-- /wp:list -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button<?php echo $feelolab_plan[3] ? '' : ' {"className":"is-style-outline"}'; ?> -->
<div class="wp-block-button<?php echo $feelolab_plan[3] ? '' : ' is-style-outline'; ?>"><a class="wp-block-button__link wp-element-button" href="#contacto"><?php esc_html_e( 'Quiero este plan', 'feelolab' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column --><?php endforeach; ?></div>
<!-- /wp:columns -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Precios finales por mes. Podés cambiar de plan cuando quieras.', 'feelolab' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
