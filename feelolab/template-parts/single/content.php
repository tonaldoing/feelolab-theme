<?php
/**
 * Nota del blog (y respaldo para cualquier tipo sin plantilla propia).
 * Medida de línea acotada (.prose) y la imagen destacada como LCP con prioridad alta.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_is_post = 'post' === get_post_type();
// Con barra lateral, todo (título, imagen, texto) va al ancho completo para quedar alineado.
$feelolab_wrap = $feelolab_is_post && is_active_sidebar( 'blog' ) ? 'container' : 'container container--narrow';
?>
<article <?php post_class( 'entry' ); ?>>
	<header class="page-header page-header--entry">
		<div class="<?php echo esc_attr( $feelolab_wrap ); ?>">
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
		<figure class="entry__hero <?php echo esc_attr( $feelolab_wrap ); ?>">
			<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<?php if ( get_the_post_thumbnail_caption() ) : ?>
				<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="<?php echo esc_attr( $feelolab_wrap ); ?><?php echo $feelolab_is_post && is_active_sidebar( 'blog' ) ? ' with-sidebar' : ''; ?> section section--tight">
		<div class="with-sidebar__main">
		<div class="entry-content prose">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>

		<?php if ( $feelolab_is_post ) : ?>
			<?php get_template_part( 'template-parts/share' ); ?>
			<?php get_template_part( 'template-parts/author-box' ); ?>
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
		<?php
		if ( $feelolab_is_post ) {
			get_template_part( 'template-parts/sidebar-blog' );
		}
		?>
	</div>
</article>
<?php
if ( $feelolab_is_post ) {
	get_template_part( 'template-parts/related' );
}
