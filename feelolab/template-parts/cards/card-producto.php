<?php
/**
 * Tarjeta de producto: imagen cuadrada, marca, título y precio (tachado si hay oferta).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_price = (string) feelolab_field( 'precio' );
$feelolab_offer = (string) feelolab_field( 'precio_oferta' );
$feelolab_avail = feelolab_field( 'disponible' );
$feelolab_brand = taxonomy_exists( 'feelo_marca' ) ? get_the_terms( get_the_ID(), 'feelo_marca' ) : false;
?>
<article class="card card--product">
	<div class="card__media card__media--square">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'feelolab-square', array( 'alt' => '' ) );
		}
		?>
		<?php if ( '0' === (string) $feelolab_avail || false === $feelolab_avail ) : ?>
			<span class="badge"><?php esc_html_e( 'Sin stock', 'feelolab' ); ?></span>
		<?php elseif ( $feelolab_offer ) : ?>
			<span class="badge badge--primary"><?php esc_html_e( 'Oferta', 'feelolab' ); ?></span>
		<?php endif; ?>
	</div>
	<div class="card__body">
		<?php if ( $feelolab_brand && ! is_wp_error( $feelolab_brand ) ) : ?>
			<p class="card__kicker"><?php echo esc_html( $feelolab_brand[0]->name ); ?></p>
		<?php endif; ?>
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<?php get_template_part( 'template-parts/price', null, array( 'price' => $feelolab_price, 'offer' => $feelolab_offer ) ); ?>
	</div>
</article>
