<?php
/**
 * WooCommerce: la tienda con el diseño del tema. Se carga solo si WooCommerce está activo.
 *
 * - Declara soporte (tamaños de imagen y galería deslizable).
 * - Reemplaza los contenedores de WooCommerce por los del tema: un solo <main> (el del header),
 *   sin barra lateral, con el ancho del sitio.
 * - Carrito en el encabezado con la cantidad, que se actualiza al agregar sin recargar.
 * - Estilos (assets/css/woocommerce.css) solo en las páginas de la tienda, con los colores de la
 *   marca: botones, precios, avisos, formularios y el checkout de bloques.
 *
 * El módulo Productos de FeeloLab Core sigue sirviendo para catálogos "con consulta"; WooCommerce
 * es para vender online (carrito y pago).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 600,
				'single_image_width'    => 900,
				'product_grid'          => array(
					'default_rows'    => 4,
					'min_rows'        => 1,
					'default_columns' => 3,
					'min_columns'     => 1,
					'max_columns'     => 4,
				),
			)
		);
		// Galería deslizable, sin visor ampliado: el visor (PhotoSwipe) suma ~32 KB de JS y CSS en
		// cada ficha y casi no se usa en el celular. Se activa con este filtro si un cliente lo pide.
		add_theme_support( 'wc-product-gallery-slider' );
		if ( apply_filters( 'feelolab_wc_lightbox', false ) ) {
			add_theme_support( 'wc-product-gallery-lightbox' );
		}
	}
);

// Contenedores: el <main> ya lo abre header.php. WooCommerce abriría otro (dos "main" es un error de accesibilidad).
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action(
	'woocommerce_before_main_content',
	static function (): void {
		echo '<div class="container section woo-main">';
	},
	5
);
add_action(
	'woocommerce_after_main_content',
	static function (): void {
		echo '</div>';
	},
	50
);

/** ¿Página de la tienda? (para cargar el CSS solo donde hace falta). */
function feelolab_is_shop_page(): bool {
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( feelolab_is_shop_page() ) {
			wp_enqueue_style( 'feelolab-woocommerce', FEELOLAB_URI . '/assets/css/woocommerce.css', array( 'feelolab' ), feelolab_asset_version( 'assets/css/woocommerce.css' ) );
		}
	},
	20
);

/** Productos por fila y relacionados: 3 en escritorio (las tarjetas respiran), 2 en celular por CSS. */
add_filter( 'loop_shop_columns', static fn() => 3 );
add_filter(
	'woocommerce_output_related_products_args',
	static function ( array $args ): array {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;
		return $args;
	}
);

/** Link al carrito para el encabezado, con la cantidad (texto para lectores de pantalla incluido). */
function feelolab_cart_link(): string {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}
	$count = (int) WC()->cart->get_cart_contents_count();
	return sprintf(
		'<a class="header-cart" href="%1$s">%2$s<span class="header-cart__count"%3$s>%4$s</span><span class="screen-reader-text">%5$s</span></a>',
		esc_url( wc_get_cart_url() ),
		feelolab_icon( 'cart' ),
		$count ? '' : ' hidden',
		esc_html( (string) $count ),
		esc_html(
			sprintf(
				/* translators: %d: productos en el carrito */
				_n( 'Carrito: %d producto', 'Carrito: %d productos', $count, 'feelolab' ),
				$count
			)
		)
	);
}

// Al agregar al carrito sin recargar, WooCommerce reemplaza este link por el nuevo.
add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( array $fragments ): array {
		$fragments['a.header-cart'] = feelolab_cart_link();
		return $fragments;
	}
);
