<?php
/**
 * Recuadro del autor al pie de la nota. Solo si el autor completó su biografía (Usuarios → Perfil):
 * sin bio, un recuadro con el nombre solo no aporta. Suma señal de autoría (E-E-A-T) para Google.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'feelolab_blog_author', true ) ) {
	return;
}
$feelolab_author = (int) get_the_author_meta( 'ID' );
$feelolab_bio    = (string) get_the_author_meta( 'description', $feelolab_author );
if ( ! $feelolab_author || '' === trim( $feelolab_bio ) ) {
	return;
}
$feelolab_name = get_the_author_meta( 'display_name', $feelolab_author );
?>
<aside class="author-box" aria-labelledby="author-box-title">
	<?php echo get_avatar( $feelolab_author, 80, '', '', array( 'loading' => 'lazy', 'class' => 'author-box__avatar' ) ); ?>
	<div>
		<h2 class="author-box__name" id="author-box-title">
			<span class="author-box__label"><?php esc_html_e( 'Escrito por', 'feelolab' ); ?></span>
			<a href="<?php echo esc_url( get_author_posts_url( $feelolab_author ) ); ?>" rel="author"><?php echo esc_html( $feelolab_name ); ?></a>
		</h2>
		<p class="author-box__bio"><?php echo esc_html( $feelolab_bio ); ?></p>
	</div>
</aside>
