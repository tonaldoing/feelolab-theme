<?php
/**
 * Nota del blog (y respaldo para cualquier tipo sin plantilla propia).
 * Medida de línea acotada (.prose) y la imagen destacada como LCP con prioridad alta.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_is_post = 'post' === get_post_type();
?>
<article <?php post_class( 'entry' ); ?>>
	<header class="page-header page-header--entry">
		<div class="container container--narrow">
			<?php feelolab_breadcrumbs(); ?>
			<h1 class="page-header__title"><?php the_title(); ?></h1>
			<?php
			if ( $feelolab_is_post ) {
				feelolab_posted_on();
			}
			?>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__hero container container--narrow">
			<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<?php if ( get_the_post_thumbnail_caption() ) : ?>
				<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="container container--narrow section section--tight">
		<div class="entry-content prose">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>

		<?php if ( $feelolab_is_post ) : ?>
			<?php the_tags( '<p class="entry-tags"><span class="screen-reader-text">' . esc_html__( 'Etiquetas:', 'feelolab' ) . ' </span>', ' ', '</p>' ); ?>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="post-nav__label">' . esc_html__( 'Anterior', 'feelolab' ) . '</span> <span class="post-nav__title">%title</span>',
					'next_text' => '<span class="post-nav__label">' . esc_html__( 'Siguiente', 'feelolab' ) . '</span> <span class="post-nav__title">%title</span>',
				)
			);
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		<?php endif; ?>
	</div>
</article>
