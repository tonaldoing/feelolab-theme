<?php
/**
 * Sobre nosotros: texto + imagen.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_text  = (string) feelolab_home( 'nosotros', 'text' );
$feelolab_image = (int) feelolab_home( 'nosotros', 'image' );
if ( ! $feelolab_text && ! $feelolab_image ) {
	return;
}
?>
<section class="section section--surface" id="nosotros" aria-labelledby="nosotros-title">
	<div class="container split">
		<div>
			<h2 class="section__title" id="nosotros-title"><?php echo esc_html( feelolab_home( 'nosotros', 'title' ) ); ?></h2>
			<div class="prose"><?php echo wp_kses_post( wpautop( $feelolab_text ) ); ?></div>
			<?php echo feelolab_button( (string) feelolab_home( 'nosotros', 'link_text' ), (string) feelolab_home( 'nosotros', 'link_url' ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $feelolab_image ) : ?>
			<div class="split__media"><?php echo wp_get_attachment_image( $feelolab_image, 'large', false, array( 'sizes' => '(min-width: 960px) 50vw, 100vw' ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
