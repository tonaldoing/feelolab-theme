<?php
/**
 * Resultados de búsqueda.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part(
	'template-parts/page-header',
	null,
	array(
		/* translators: %s: búsqueda */
		'title' => esc_html( sprintf( __( 'Resultados para "%s"', 'feelolab' ), get_search_query() ) ),
	)
);
?>
<div class="container section">
	<?php if ( have_posts() ) : ?>
		<p class="results-count" role="status">
			<?php
			/* translators: %d: cantidad de resultados */
			echo esc_html( sprintf( _n( '%d resultado', '%d resultados', (int) $wp_query->found_posts, 'feelolab' ), (int) $wp_query->found_posts ) );
			?>
		</p>
		<div class="grid grid--3">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/cards/card', feelolab_type_slug(), array( 'heading' => 'h2' ) );
			endwhile;
			?>
		</div>
		<?php feelolab_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
