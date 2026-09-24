<?php
/**
 * Tarjeta genérica (notas del blog y cualquier tipo sin tarjeta propia).
 * Todo el bloque es clickeable por CSS (::after del link), pero hay UN solo link: sin
 * links duplicados para lectores de pantalla ni tabulación repetida.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="card">
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="card__media"><?php the_post_thumbnail( 'feelolab-card', array( 'alt' => '' ) ); ?></div>
	<?php endif; ?>
	<div class="card__body">
		<?php if ( 'post' === get_post_type() ) : ?>
			<?php feelolab_posted_on(); ?>
		<?php endif; ?>
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<?php if ( has_excerpt() || 'post' === get_post_type() ) : ?>
			<p class="card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</article>
