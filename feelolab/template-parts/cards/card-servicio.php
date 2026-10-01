<?php
/**
 * Tarjeta de servicio: ícono o imagen, título, bajada, precio desde.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_show  = array_merge(
	array(
		'show_media' => true,
		'show_text'  => true,
		'show_price' => true,
	),
	(array) ( $args ?? array() )
);
$feelolab_icon  = $feelolab_show['show_media'] ? (int) feelolab_field( 'icono' ) : 0;
$feelolab_lead  = (string) feelolab_field( 'bajada' );
$feelolab_price = (string) feelolab_field( 'precio_desde' );
?>
<article class="card card--service">
	<?php if ( $feelolab_icon ) : ?>
		<div class="card__icon"><?php echo wp_get_attachment_image( $feelolab_icon, 'thumbnail', false, array( 'alt' => '', 'width' => 48, 'height' => 48 ) ); ?></div>
	<?php elseif ( $feelolab_show['show_media'] && has_post_thumbnail() ) : ?>
		<div class="card__media"><?php the_post_thumbnail( 'feelolab-card', array( 'alt' => '' ) ); ?></div>
	<?php endif; ?>
	<div class="card__body">
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<?php if ( $feelolab_show['show_text'] ) : ?>
			<p class="card__text"><?php echo esc_html( $feelolab_lead ? $feelolab_lead : get_the_excerpt() ); ?></p>
		<?php endif; ?>
		<?php if ( $feelolab_show['show_price'] && $feelolab_price ) : ?>
			<p class="card__price"><?php esc_html_e( 'Desde', 'feelolab' ); ?> <strong><?php echo esc_html( $feelolab_price ); ?></strong></p>
		<?php endif; ?>
		<span class="card__more" aria-hidden="true"><?php esc_html_e( 'Ver más', 'feelolab' ); ?> <?php echo feelolab_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
</article>
