<?php
/**
 * Tarjeta de servicio: ícono o imagen, título, bajada, precio desde.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_icon  = (int) feelolab_field( 'icono' );
$feelolab_lead  = (string) feelolab_field( 'bajada' );
$feelolab_price = (string) feelolab_field( 'precio_desde' );
?>
<article class="card card--service">
	<?php if ( $feelolab_icon ) : ?>
		<div class="card__icon"><?php echo wp_get_attachment_image( $feelolab_icon, 'thumbnail', false, array( 'alt' => '', 'width' => 48, 'height' => 48 ) ); ?></div>
	<?php elseif ( has_post_thumbnail() ) : ?>
		<div class="card__media"><?php the_post_thumbnail( 'feelolab-card', array( 'alt' => '' ) ); ?></div>
	<?php endif; ?>
	<div class="card__body">
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<p class="card__text"><?php echo esc_html( $feelolab_lead ? $feelolab_lead : get_the_excerpt() ); ?></p>
		<?php if ( $feelolab_price ) : ?>
			<p class="card__price"><?php esc_html_e( 'Desde', 'feelolab' ); ?> <strong><?php echo esc_html( $feelolab_price ); ?></strong></p>
		<?php endif; ?>
		<span class="card__more" aria-hidden="true"><?php esc_html_e( 'Ver más', 'feelolab' ); ?> <?php echo feelolab_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>
</article>
