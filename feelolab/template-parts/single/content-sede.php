<?php
/**
 * Ficha de sede: datos de contacto propios, horarios y cómo llegar.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html( get_the_title() ) ) );
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<div class="split split--aside">
			<div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="entry__hero"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
				<?php endif; ?>
				<div class="entry-content prose"><?php the_content(); ?></div>
				<?php get_template_part( 'template-parts/gallery-grid' ); ?>
				<?php
				$feelolab_show_map = feelolab_field( 'mostrar_mapa' );
				if ( '' === $feelolab_show_map || null === $feelolab_show_map || $feelolab_show_map ) :
					$feelolab_sede_address = trim( implode( ', ', array_filter( array( feelolab_field( 'direccion' ), feelolab_field( 'ciudad' ) ) ) ) );
					if ( $feelolab_sede_address ) :
						?>
						<section class="section section--tight" aria-labelledby="mapa-title">
							<h2 id="mapa-title"><?php esc_html_e( 'Cómo llegar', 'feelolab' ); ?></h2>
							<?php feelolab_map( $feelolab_sede_address, (string) feelolab_field( 'mapa_url' ) ); ?>
						</section>
						<?php
					endif;
				endif;
				?>
			</div>
			<div class="sticky-box">
				<?php get_template_part( 'template-parts/cards/card', 'sede', array( 'heading' => 'h2' ) ); ?>
			</div>
		</div>
	</div>
</article>
