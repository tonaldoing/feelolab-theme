<?php
/**
 * Encabezado: skip link, barra superior opcional, logo, buscador opcional, menú accesible y botón.
 *
 * Opciones en Personalizar → Marca → Encabezado: logo a la izquierda o centrado, barra superior con
 * teléfono, WhatsApp, email y redes, buscador, y transparente sobre una portada con imagen de fondo.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_cta_text = (string) get_theme_mod( 'feelolab_header_cta_text', '' );
$feelolab_cta_url  = (string) get_theme_mod( 'feelolab_header_cta_url', '' );
if ( $feelolab_cta_text && ! $feelolab_cta_url ) {
	$feelolab_cta_url = feelolab_contact_url();
}
$feelolab_header_class = array( 'site-header', 'site-header--' . feelolab_header_layout() );
if ( get_theme_mod( 'feelolab_header_sticky', true ) ) {
	$feelolab_header_class[] = 'is-sticky';
}
if ( feelolab_header_is_transparent() ) {
	$feelolab_header_class[] = 'is-transparent';
}
$feelolab_search     = (bool) get_theme_mod( 'feelolab_header_search', false );
$feelolab_logo_light = (int) get_theme_mod( 'feelolab_logo_light', 0 );
?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'feelolab' ); ?></a>

<?php get_template_part( 'template-parts/topbar' ); ?>

<header class="<?php echo esc_attr( implode( ' ', $feelolab_header_class ) ); ?>">
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() && $feelolab_logo_light && feelolab_header_is_transparent() ) : ?>
				<?php // Dos logos en el mismo link: el claro se ve sobre la portada y el normal al hacer scroll. ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link" rel="home">
					<?php
					// Se ve uno solo por vez (el otro va con display:none), así que los dos llevan el nombre.
					$feelolab_logo_alt = function_exists( 'feelo_business_name' ) ? feelo_business_name() : get_bloginfo( 'name' );
					echo wp_get_attachment_image( (int) get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'custom-logo custom-logo--dark', 'alt' => $feelolab_logo_alt, 'loading' => 'eager' ) );
					echo wp_get_attachment_image( $feelolab_logo_light, 'full', false, array( 'class' => 'custom-logo custom-logo--light', 'alt' => $feelolab_logo_alt, 'loading' => 'eager' ) );
					?>
				</a>
			<?php elseif ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( function_exists( 'feelo_business_name' ) ? feelo_business_name() : get_bloginfo( 'name' ) ); ?></a>
			<?php endif; ?>
		</div>

		<?php
		$feelolab_cart = function_exists( 'feelolab_cart_link' ) ? feelolab_cart_link() : '';
		if ( $feelolab_cart ) {
			echo '<div class="site-cart">' . $feelolab_cart . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función.
		}
		?>

		<?php if ( $feelolab_search ) : ?>
			<div class="site-search">
				<button type="button" class="site-search__toggle" aria-expanded="false" aria-controls="site-search-panel">
					<?php echo feelolab_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Buscar', 'feelolab' ); ?></span>
				</button>
				<div class="site-search__panel" id="site-search-panel">
					<?php get_search_form(); ?>
				</div>
			</div>
		<?php endif; ?>

		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Principal', 'feelolab' ); ?>">
			<button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav-menu">
				<?php echo feelolab_icon( 'menu', 'nav-toggle__open' ) . feelolab_icon( 'close', 'nav-toggle__close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="nav-toggle__label"><?php esc_html_e( 'Menú', 'feelolab' ); ?></span>
			</button>
			<div class="site-nav__panel" id="site-nav-menu">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'menu',
						'depth'          => 2,
						'fallback_cb'    => 'feelolab_fallback_menu',
					)
				);
				if ( $feelolab_cta_text ) {
					echo feelolab_button( $feelolab_cta_text, $feelolab_cta_url, 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función.
				}
				?>
			</div>
		</nav>
	</div>
</header>

<main id="contenido" class="site-main" tabindex="-1">
