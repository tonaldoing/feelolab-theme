<?php
/**
 * Sin resultados.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="empty">
	<p><?php is_search() ? esc_html_e( 'No encontramos nada con esa búsqueda. Probá con otras palabras.', 'feelolab' ) : esc_html_e( 'Todavía no hay contenido acá.', 'feelolab' ); ?></p>
	<?php get_search_form(); ?>
</div>
