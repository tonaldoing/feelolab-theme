<?php
/**
 * Sección que lista contenido con tarjetas: servicios, productos, proyectos, equipo y notas del blog.
 * Cantidad, categoría, orden, columnas, fondo y botón "ver todos" salen del Personalizador.
 *
 * @package Feelolab
 *
 * @var array{key?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_key      = $args['key'] ?? 'servicios';
$feelolab_sections = feelolab_home_sections();
$feelolab_section  = $feelolab_sections[ $feelolab_key ] ?? array();
$feelolab_list     = $feelolab_section['list'] ?? array();
$feelolab_type     = (string) ( $feelolab_list['post_type'] ?? ( ! empty( $feelolab_section['module'] ) && function_exists( 'feelo_module_post_type' ) ? feelo_module_post_type( (string) $feelolab_section['module'] ) : '' ) );
if ( ! $feelolab_type || ! post_type_exists( $feelolab_type ) ) {
	return;
}

$feelolab_q = new WP_Query( feelolab_home_query_args( $feelolab_key, $feelolab_type ) );
if ( ! $feelolab_q->have_posts() ) {
	feelolab_home_placeholder( $feelolab_key );
	return;
}

$feelolab_id   = (string) ( $feelolab_list['id'] ?? str_replace( '_', '-', $feelolab_key ) );
$feelolab_more = (string) feelolab_home( $feelolab_key, 'more_text' );
$feelolab_link = 'post' === $feelolab_type
	? ( get_option( 'page_for_posts' ) ? (string) get_permalink( (int) get_option( 'page_for_posts' ) ) : '' )
	: (string) get_post_type_archive_link( $feelolab_type );
// Qué muestra cada tarjeta (por ahora, opciones de la tarjeta de servicio).
$feelolab_card_args = array();
foreach ( array( 'show_media', 'show_text', 'show_price' ) as $feelolab_opt ) {
	if ( isset( $feelolab_section['fields'][ $feelolab_opt ] ) ) {
		$feelolab_card_args[ $feelolab_opt ] = (bool) feelolab_home( $feelolab_key, $feelolab_opt );
	}
}
?>
<section class="<?php echo esc_attr( feelolab_home_section_class( $feelolab_key ) ); ?>" id="<?php echo esc_attr( $feelolab_id ); ?>" aria-labelledby="<?php echo esc_attr( $feelolab_id ); ?>-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( $feelolab_key, 'title' ), (string) feelolab_home( $feelolab_key, 'text' ), $feelolab_id . '-title' ); ?>
		<div class="<?php echo esc_attr( feelolab_home_grid_class( $feelolab_key ) ); ?>">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				get_template_part( 'template-parts/cards/card', (string) ( $feelolab_list['card'] ?? '' ), $feelolab_card_args );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php if ( $feelolab_more && $feelolab_link ) : ?>
			<p class="section__more"><?php echo feelolab_button( $feelolab_more, $feelolab_link, 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función. ?></p>
		<?php endif; ?>
	</div>
</section>
