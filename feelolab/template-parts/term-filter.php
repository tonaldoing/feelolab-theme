<?php
/**
 * Filtro por categoría en archivos de CPT: links comunes (indexables, sin JS).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_post_type = is_post_type_archive() ? (string) get_query_var( 'post_type' ) : '';
if ( is_tax() ) {
	$feelolab_tax       = get_taxonomy( get_queried_object()->taxonomy );
	$feelolab_post_type = $feelolab_tax->object_type[0] ?? '';
}
if ( ! $feelolab_post_type || ! str_starts_with( $feelolab_post_type, 'feelo_' ) ) {
	return;
}

// La primera taxonomía jerárquica del tipo (la "categoría").
$feelolab_taxonomy = '';
foreach ( get_object_taxonomies( $feelolab_post_type, 'objects' ) as $feelolab_t ) {
	if ( $feelolab_t->hierarchical && $feelolab_t->public ) {
		$feelolab_taxonomy = $feelolab_t->name;
		break;
	}
}
if ( ! $feelolab_taxonomy ) {
	return;
}
$feelolab_terms = get_terms(
	array(
		'taxonomy'   => $feelolab_taxonomy,
		'hide_empty' => true,
		'parent'     => 0,
	)
);
if ( is_wp_error( $feelolab_terms ) || count( $feelolab_terms ) < 2 ) {
	return;
}
$feelolab_current = is_tax( $feelolab_taxonomy ) ? get_queried_object_id() : 0;
?>
<nav class="term-filter" aria-label="<?php esc_attr_e( 'Filtrar por categoría', 'feelolab' ); ?>">
	<ul>
		<li><a href="<?php echo esc_url( get_post_type_archive_link( $feelolab_post_type ) ); ?>" <?php echo $feelolab_current ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'Todos', 'feelolab' ); ?></a></li>
		<?php foreach ( $feelolab_terms as $feelolab_term ) : ?>
			<li><a href="<?php echo esc_url( get_term_link( $feelolab_term ) ); ?>" <?php echo $feelolab_current === $feelolab_term->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $feelolab_term->name ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>
