<?php
/**
 * Galería en grilla (proyectos, sedes): sin JS, cada imagen enlaza a su versión grande.
 * Solo las imágenes de la galería: la destacada ya se muestra arriba como hero.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_ids = function_exists( 'feelo_gallery_ids' ) ? feelo_gallery_ids() : array();
if ( ! $feelolab_ids ) {
	return;
}
?>
<section class="section section--tight" aria-labelledby="galeria-title">
	<h2 id="galeria-title"><?php esc_html_e( 'Galería', 'feelolab' ); ?></h2>
	<ul class="gallery-grid">
		<?php foreach ( $feelolab_ids as $feelolab_id ) : ?>
			<?php
			$feelolab_full = wp_get_attachment_image_url( $feelolab_id, 'large' );
			$feelolab_alt  = trim( (string) get_post_meta( $feelolab_id, '_wp_attachment_image_alt', true ) );
			if ( ! $feelolab_full ) {
				continue;
			}
			?>
			<li>
				<a href="<?php echo esc_url( $feelolab_full ); ?>">
					<?php echo wp_get_attachment_image( $feelolab_id, 'feelolab-card', false, array( 'alt' => $feelolab_alt ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(ver imagen grande)', 'feelolab' ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
