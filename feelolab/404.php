<?php
/**
 * Página no encontrada: salida clara, buscador y camino a la home.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html__( 'No encontramos esta página', 'feelolab' ) ) );
?>
<div class="container section">
	<div class="empty">
		<p><?php esc_html_e( 'Puede que el link esté mal escrito o que la página ya no exista. Probá buscando o volvé al inicio.', 'feelolab' ); ?></p>
		<?php get_search_form(); ?>
		<p><?php echo feelolab_button( __( 'Ir al inicio', 'feelolab' ), home_url( '/' ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	</div>
</div>
<?php
get_footer();
