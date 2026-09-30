<?php
/**
 * Índice del blog y respaldo general.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

$feelolab_blog = (int) get_option( 'page_for_posts' );
get_template_part(
	'template-parts/page-header',
	null,
	array( 'title' => is_home() && $feelolab_blog ? get_the_title( $feelolab_blog ) : __( 'Blog', 'feelolab' ) )
);
?>
<div class="container section<?php echo is_active_sidebar( 'blog' ) ? ' with-sidebar' : ''; ?>">
	<div class="with-sidebar__main">
	<?php if ( have_posts() ) : ?>
		<div class="grid <?php echo is_active_sidebar( 'blog' ) ? 'grid--2' : 'grid--3'; ?>">
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
	<?php get_template_part( 'template-parts/sidebar-blog' ); ?>
</div>
<?php
get_footer();
