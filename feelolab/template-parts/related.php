<?php
/**
 * Relacionados: mismo tipo y misma categoría principal, 3 ítems. Una consulta, sin found_rows.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_post_type = get_post_type();
$feelolab_tax_query = array();
foreach ( get_object_taxonomies( $feelolab_post_type, 'objects' ) as $feelolab_tax ) {
	if ( ! $feelolab_tax->hierarchical ) {
		continue;
	}
	$feelolab_terms = wp_get_post_terms( get_the_ID(), $feelolab_tax->name, array( 'fields' => 'ids' ) );
	if ( $feelolab_terms && ! is_wp_error( $feelolab_terms ) ) {
		$feelolab_tax_query[] = array(
			'taxonomy' => $feelolab_tax->name,
			'terms'    => $feelolab_terms,
		);
	}
	break;
}

$feelolab_related = new WP_Query(
	array(
		'post_type'           => $feelolab_post_type,
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'tax_query'           => $feelolab_tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);
if ( ! $feelolab_related->have_posts() ) {
	return;
}
$feelolab_object = get_post_type_object( $feelolab_post_type );
?>
<section class="section section--surface" aria-labelledby="relacionados">
	<div class="container">
		<?php
		/* translators: %s: nombre del tipo de contenido en plural */
		feelolab_section_header( sprintf( __( 'Más %s', 'feelolab' ), mb_strtolower( $feelolab_object->labels->name ) ), '', 'relacionados' );
		?>
		<div class="grid grid--3">
			<?php
			while ( $feelolab_related->have_posts() ) :
				$feelolab_related->the_post();
				get_template_part( 'template-parts/cards/card', feelolab_type_slug() );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
