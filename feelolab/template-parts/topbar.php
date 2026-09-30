<?php
/**
 * Barra superior: teléfono, WhatsApp, email y redes, en una franja del color secundario.
 * Se prende en Personalizar → Marca → Encabezado. En mobile quedan solo los íconos de contacto
 * (con su texto para lectores de pantalla) para no robar alto de pantalla.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! get_theme_mod( 'feelolab_topbar', false ) ) {
	return;
}
$feelolab_phone = (string) feelolab_setting( 'telefono' );
$feelolab_wa    = function_exists( 'feelo_whatsapp_url' ) ? feelo_whatsapp_url() : '';
$feelolab_email = (string) feelolab_setting( 'email' );
$feelolab_text  = (string) get_theme_mod( 'feelolab_topbar_text', '' );
if ( ! $feelolab_phone && ! $feelolab_wa && ! $feelolab_email && ! $feelolab_text ) {
	return;
}
?>
<div class="topbar">
	<div class="container topbar__inner">
		<?php if ( $feelolab_text ) : ?>
			<p class="topbar__text"><?php echo esc_html( $feelolab_text ); ?></p>
		<?php endif; ?>
		<ul class="topbar__contact" aria-label="<?php esc_attr_e( 'Contacto', 'feelolab' ); ?>">
			<?php if ( $feelolab_phone && function_exists( 'feelo_tel_href' ) ) : ?>
				<li><a href="<?php echo esc_url( feelo_tel_href( $feelolab_phone ) ); ?>"><?php echo feelolab_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="topbar__label"><?php echo esc_html( $feelolab_phone ); ?></span></a></li>
			<?php endif; ?>
			<?php if ( $feelolab_wa ) : ?>
				<li><a href="<?php echo esc_url( $feelolab_wa ); ?>" target="_blank" rel="noopener"><?php echo feelolab_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="topbar__label">WhatsApp</span><span class="screen-reader-text"> <?php esc_html_e( '(se abre en otra pestaña)', 'feelolab' ); ?></span></a></li>
			<?php endif; ?>
			<?php if ( $feelolab_email ) : ?>
				<li><a href="<?php echo esc_url( 'mailto:' . antispambot( $feelolab_email ) ); ?>"><?php echo feelolab_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="topbar__label"><?php echo esc_html( antispambot( $feelolab_email ) ); ?></span></a></li>
			<?php endif; ?>
		</ul>
		<div class="topbar__socials"><?php feelolab_socials(); ?></div>
	</div>
</div>
