<?php
/**
 * Compartir la nota: links directos a cada red (sin scripts de terceros ni cookies) y "Copiar link".
 * En el celular, "Compartir" abre el menú nativo del teléfono (Web Share API) si está disponible.
 * Los clics se miden como evento "share" de GA4 (Feelolab Core → Tracking).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'feelolab_blog_share', true ) ) {
	return;
}
$feelolab_url   = (string) get_permalink();
$feelolab_title = wp_strip_all_tags( get_the_title() );
$feelolab_nets  = array(
	'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $feelolab_title . ' ' . $feelolab_url ) ),
	'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $feelolab_url ) ),
	'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $feelolab_url ) ),
	'x'        => array( 'X', 'https://x.com/intent/post?text=' . rawurlencode( $feelolab_title ) . '&url=' . rawurlencode( $feelolab_url ) ),
	'mail'     => array( __( 'Email', 'feelolab' ), 'mailto:?subject=' . rawurlencode( $feelolab_title ) . '&body=' . rawurlencode( $feelolab_url ) ),
);
?>
<div class="share">
	<h2 class="share__title"><?php esc_html_e( 'Compartir', 'feelolab' ); ?></h2>
	<ul class="share__list">
		<li class="share__native" hidden>
			<button type="button" class="share__btn" data-feelo-share="nativo" data-share-title="<?php echo esc_attr( $feelolab_title ); ?>" data-share-url="<?php echo esc_url( $feelolab_url ); ?>">
				<?php echo feelolab_icon( 'share' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Compartir', 'feelolab' ); ?></span>
			</button>
		</li>
		<?php foreach ( $feelolab_nets as $feelolab_key => $feelolab_net ) : ?>
			<li>
				<a class="share__btn" href="<?php echo esc_url( $feelolab_net[1] ); ?>" data-feelo-share="<?php echo esc_attr( $feelolab_key ); ?>"<?php echo 'mail' === $feelolab_key ? '' : ' target="_blank" rel="noopener"'; ?>>
					<?php echo feelolab_icon( $feelolab_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="screen-reader-text">
						<?php
						echo esc_html(
							'mail' === $feelolab_key
								? __( 'Enviar por email', 'feelolab' )
								/* translators: %s: red social */
								: sprintf( __( 'Compartir en %s (se abre en otra pestaña)', 'feelolab' ), $feelolab_net[0] )
						);
						?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
		<li class="share__copy" hidden>
			<button type="button" class="share__btn" data-feelo-share="copiar" data-share-url="<?php echo esc_url( $feelolab_url ); ?>" data-copied="<?php esc_attr_e( 'Link copiado', 'feelolab' ); ?>">
				<?php echo feelolab_icon( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Copiar link', 'feelolab' ); ?></span>
			</button>
		</li>
	</ul>
	<p class="share__status screen-reader-text" role="status"></p>
</div>
<?php // Antes de pintar: si no, los botones aparecen con el JS diferido y empujan el contenido (CLS). ?>
<script>(function(b){if(navigator.share){b.querySelector('.share__native').hidden=false;}if(navigator.clipboard){b.querySelector('.share__copy').hidden=false;}})(document.currentScript.previousElementSibling);</script>
