<?php
/**
 * Buscador con label real (visible para lectores de pantalla).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $feelolab_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Buscar en el sitio', 'feelolab' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $feelolab_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar…', 'feelolab' ); ?>">
	<button type="submit" class="btn btn--primary"><?php echo feelolab_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Buscar', 'feelolab' ); ?></span></button>
</form>
