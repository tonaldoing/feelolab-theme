<?php
/**
 * Encabezado: skip link, logo, menú accesible y botón de contacto.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_cta_text = (string) get_theme_mod( 'feelolab_header_cta_text', '' );
$feelolab_cta_url  = (string) get_theme_mod( 'feelolab_header_cta_url', '' );
if ( $feelolab_cta_text && ! $feelolab_cta_url ) {
	$feelolab_cta_url = feelolab_contact_url();
}
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

<header class="site-header<?php echo get_theme_mod( 'feelolab_header_sticky', true ) ? ' is-sticky' : ''; ?>">
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html( function_exists( 'feelo_business_name' ) ? feelo_business_name() : get_bloginfo( 'name' ) ); ?></a>
			<?php endif; ?>
		</div>

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
