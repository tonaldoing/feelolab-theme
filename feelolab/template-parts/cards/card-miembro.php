<?php
/**
 * Tarjeta de integrante del equipo.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="card card--person">
	<div class="card__media card__media--square">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'feelolab-square', array( 'alt' => '' ) );
		}
		?>
	</div>
	<div class="card__body">
		<<?php echo esc_attr( feelolab_card_heading( $args ) ); ?> class="card__title"><a class="card__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo esc_attr( feelolab_card_heading( $args ) ); ?>>
		<?php $feelolab_role = (string) feelolab_field( 'cargo' ); ?>
		<?php if ( $feelolab_role ) : ?>
			<p class="card__text"><?php echo esc_html( $feelolab_role ); ?></p>
		<?php endif; ?>
	</div>
</article>
