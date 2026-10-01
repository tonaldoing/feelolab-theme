<?php
/**
 * Texto con imagen ("Sobre nosotros" y el segundo bloque, que reusa esta plantilla).
 *
 * @package Feelolab
 *
 * @var array{key?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_key   = $args['key'] ?? 'nosotros';
$feelolab_text  = (string) feelolab_home( $feelolab_key, 'text' );
$feelolab_image = (int) feelolab_home( $feelolab_key, 'image' );
if ( ! $feelolab_text && ! $feelolab_image ) {
	feelolab_home_placeholder( $feelolab_key );
	return;
}
$feelolab_id    = str_replace( '_', '-', $feelolab_key );
$feelolab_class = 'izquierda' === feelolab_home( $feelolab_key, 'side' ) ? 'container split split--reverse' : 'container split';
?>
<section class="<?php echo esc_attr( feelolab_home_section_class( $feelolab_key ) ); ?>" id="<?php echo esc_attr( $feelolab_id ); ?>" aria-labelledby="<?php echo esc_attr( $feelolab_id ); ?>-title">
	<div class="<?php echo esc_attr( $feelolab_class ); ?>">
		<div>
			<h2 class="section__title" id="<?php echo esc_attr( $feelolab_id ); ?>-title"><?php echo esc_html( feelolab_home( $feelolab_key, 'title' ) ); ?></h2>
			<div class="prose"><?php echo wp_kses_post( wpautop( $feelolab_text ) ); ?></div>
			<?php echo feelolab_button( (string) feelolab_home( $feelolab_key, 'link_text' ), (string) feelolab_home( $feelolab_key, 'link_url' ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php if ( $feelolab_image ) : ?>
			<div class="split__media"><?php echo wp_get_attachment_image( $feelolab_image, 'large', false, array( 'sizes' => '(min-width: 960px) 50vw, 100vw' ) ); ?></div>
		<?php endif; ?>
	</div>
</section>
