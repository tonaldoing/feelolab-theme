<?php
/**
 * Tarjeta de proyecto: imagen, tipo, título y resultado.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_types  = taxonomy_exists( 'feelo_proyecto_tipo' ) ? get_the_terms( get_the_ID(), 'feelo_proyecto_tipo' ) : false;
$feelolab_result = (string) feelolab_field( 'resultado' );
?>
<article class="card card--project">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="card__media"><?php the_post_thumbnail( 'feelolab-card', array( 'alt' => '' ) ); ?></div>
	<?php endif; ?>
	<div class="card__body">
		<?php if ( $feelolab_types && ! is_wp_error( $feelolab_types ) ) : ?>
			<p class="card__kicker"><?php echo esc_html( $feelolab_types[0]->name ); ?></p>
		<?php endif; ?>
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<p class="card__text"><?php echo esc_html( $feelolab_result ? $feelolab_result : get_the_excerpt() ); ?></p>
	</div>
</article>
