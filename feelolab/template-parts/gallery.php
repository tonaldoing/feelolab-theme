<?php
/**
 * Visor de galería (productos): imagen principal + miniaturas.
 *
 * Sin JS: cada miniatura es un link a la imagen grande (funciona siempre).
 * Con JS (assets/js/gallery.js, solo se carga acá): la miniatura cambia la imagen principal
 * sin salir de la página, con aria-current en la activa y el cambio anunciado.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_ids = array_values( array_filter( array_merge( array( (int) get_post_thumbnail_id() ), function_exists( 'feelo_gallery_ids' ) ? feelo_gallery_ids() : array() ) ) );
$feelolab_ids = array_values( array_unique( $feelolab_ids ) );
if ( ! $feelolab_ids ) {
	return;
}
$feelolab_total = count( $feelolab_ids );
$feelolab_title = get_the_title();
?>
<div class="gallery" data-gallery>
	<figure class="gallery__main">
		<?php
		echo wp_get_attachment_image(
			$feelolab_ids[0],
			'large',
			false,
			array(
				'loading'           => 'eager',
				'fetchpriority'     => 'high',
				'sizes'             => '(min-width: 960px) 50vw, 100vw',
				'data-gallery-main' => '',
			)
		);
		?>
	</figure>
	<?php if ( $feelolab_total > 1 ) : ?>
		<?php wp_enqueue_script( 'feelolab-gallery', FEELOLAB_URI . '/assets/js/gallery.js', array(), feelolab_asset_version( 'assets/js/gallery.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) ); ?>
		<p class="screen-reader-text" aria-live="polite" data-gallery-status></p>
		<ul class="gallery__thumbs" aria-label="<?php esc_attr_e( 'Imágenes del producto', 'feelolab' ); ?>">
			<?php foreach ( $feelolab_ids as $feelolab_i => $feelolab_id ) : ?>
				<?php
				$feelolab_large = wp_get_attachment_image_src( $feelolab_id, 'large' );
				$feelolab_alt   = trim( (string) get_post_meta( $feelolab_id, '_wp_attachment_image_alt', true ) );
				if ( ! $feelolab_large ) {
					continue;
				}
				/* translators: 1: número de imagen, 2: total, 3: producto */
				$feelolab_label = sprintf( __( 'Imagen %1$d de %2$d de %3$s', 'feelolab' ), $feelolab_i + 1, $feelolab_total, $feelolab_title );
				?>
				<li>
					<a class="gallery__thumb" href="<?php echo esc_url( $feelolab_large[0] ); ?>"
						data-src="<?php echo esc_url( $feelolab_large[0] ); ?>"
						data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $feelolab_id, 'large' ) ); ?>"
						data-width="<?php echo esc_attr( (string) $feelolab_large[1] ); ?>"
						data-height="<?php echo esc_attr( (string) $feelolab_large[2] ); ?>"
						data-alt="<?php echo esc_attr( $feelolab_alt ); ?>"
						<?php echo 0 === $feelolab_i ? 'aria-current="true"' : ''; ?>>
						<?php echo wp_get_attachment_image( $feelolab_id, 'thumbnail', false, array( 'alt' => '' ) ); ?>
						<span class="screen-reader-text"><?php echo esc_html( $feelolab_label . ( $feelolab_alt ? ': ' . $feelolab_alt : '' ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
