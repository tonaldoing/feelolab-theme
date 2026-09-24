<?php
/**
 * Archivos: categorías, etiquetas, CPTs (servicios, productos…) y sus taxonomías.
 * Cada tipo usa su tarjeta: template-parts/cards/card-{tipo}.php.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

$feelolab_title = is_post_type_archive() ? post_type_archive_title( '', false ) : single_term_title( '', false );
if ( ! $feelolab_title ) {
	$feelolab_title = wp_strip_all_tags( get_the_archive_title() );
}
$feelolab_desc = is_post_type_archive() ? ( get_queried_object()->description ?? '' ) : get_the_archive_description();

get_template_part(
	'template-parts/page-header',
	null,
	array(
		'title' => esc_html( $feelolab_title ),
		'lead'  => $feelolab_desc ? wpautop( $feelolab_desc ) : '',
	)
);

$feelolab_type = feelolab_type_slug( (string) ( get_query_var( 'post_type' ) ?: get_post_type() ) );
$feelolab_cols = in_array( $feelolab_type, array( 'producto', 'miembro' ), true ) ? 'grid--4' : 'grid--3';
?>
<div class="container section">
	<?php get_template_part( 'template-parts/term-filter' ); ?>
	<?php if ( have_posts() ) : ?>
		<div class="grid <?php echo esc_attr( $feelolab_cols ); ?>">
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
