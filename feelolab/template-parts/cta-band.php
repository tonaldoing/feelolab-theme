<?php
/**
 * Franja de llamada a la acción reutilizable (home, servicios, productos).
 *
 * @package Feelolab
 *
 * @var array{title?: string, text?: string, button?: string, url?: string, id?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_title  = $args['title'] ?? __( '¿Hablamos?', 'feelolab' );
$feelolab_text   = $args['text'] ?? '';
$feelolab_button = $args['button'] ?? __( 'Escribinos', 'feelolab' );
$feelolab_url    = ! empty( $args['url'] ) ? $args['url'] : feelolab_contact_url();
$feelolab_id     = $args['id'] ?? wp_unique_id( 'cta-' );
?>
<section class="cta-band" aria-labelledby="<?php echo esc_attr( $feelolab_id ); ?>">
	<div class="container cta-band__inner">
		<div>
			<h2 class="cta-band__title" id="<?php echo esc_attr( $feelolab_id ); ?>"><?php echo esc_html( $feelolab_title ); ?></h2>
			<?php if ( $feelolab_text ) : ?>
				<p class="cta-band__text"><?php echo esc_html( $feelolab_text ); ?></p>
			<?php endif; ?>
		</div>
		<?php echo feelolab_button( $feelolab_button, $feelolab_url, 'inverse' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</section>
