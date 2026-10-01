<?php
/**
 * Testimonios en grilla (no carrusel: todo visible, sin JS, sin movimiento automático).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_q = new WP_Query( feelolab_home_query_args( 'testimonios', (string) feelo_module_post_type( 'testimonios' ) ) );
if ( ! $feelolab_q->have_posts() ) {
	feelolab_home_placeholder( 'testimonios' );
	return;
}
?>
<section class="<?php echo esc_attr( feelolab_home_section_class( 'testimonios' ) ); ?>" id="testimonios" aria-labelledby="testimonios-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'testimonios', 'title' ), (string) feelolab_home( 'testimonios', 'text' ), 'testimonios-title' ); ?>
		<div class="<?php echo esc_attr( feelolab_home_grid_class( 'testimonios' ) ); ?>">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				$feelolab_author = (string) feelolab_field( 'autor' );
				$feelolab_role   = (string) feelolab_field( 'cargo' );
				?>
				<figure class="testimonial">
					<?php echo feelolab_stars( (int) feelolab_field( 'estrellas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<blockquote class="testimonial__quote"><?php the_content(); ?></blockquote>
					<figcaption class="testimonial__author">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'thumbnail', array( 'alt' => '', 'class' => 'testimonial__avatar', 'width' => 48, 'height' => 48 ) );
						}
						?>
						<span>
							<strong><?php echo esc_html( $feelolab_author ? $feelolab_author : get_the_title() ); ?></strong>
							<?php if ( $feelolab_role ) : ?>
								<span class="testimonial__role"><?php echo esc_html( $feelolab_role ); ?></span>
							<?php endif; ?>
						</span>
					</figcaption>
				</figure>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
