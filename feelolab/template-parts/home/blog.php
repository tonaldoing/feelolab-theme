<?php
/**
 * Últimas notas.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_q = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => max( 1, (int) feelolab_home( 'blog', 'count' ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( ! $feelolab_q->have_posts() ) {
	return;
}
$feelolab_blog = (int) get_option( 'page_for_posts' );
?>
<section class="section section--surface" id="novedades" aria-labelledby="blog-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'blog', 'title' ), '', 'blog-title' ); ?>
		<div class="grid grid--3">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				get_template_part( 'template-parts/cards/card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<?php if ( $feelolab_blog ) : ?>
			<p class="section__more"><?php echo feelolab_button( __( 'Ver todas las notas', 'feelolab' ), (string) get_permalink( $feelolab_blog ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>
</section>
