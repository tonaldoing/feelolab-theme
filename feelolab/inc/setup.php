<?php
/**
 * Soportes del tema, menús, tamaños de imagen.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'feelolab', FEELOLAB_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'               => 120,
				'width'                => 400,
				'flex-height'          => true,
				'flex-width'           => true,
				'unlink-homepage-logo' => false,
			)
		);
		// Avisa al plugin que el tema estiliza sus salidas (formulario, botón de WhatsApp).
		add_theme_support( 'feelolab-core' );

		add_editor_style( 'assets/css/editor.css' );

		register_nav_menus(
			array(
				'primary' => __( 'Menú principal', 'feelolab' ),
				'footer'  => __( 'Menú del pie', 'feelolab' ),
				'legal'   => __( 'Menú legal (privacidad, términos)', 'feelolab' ),
			)
		);

		/*
		 * Tamaños a medida de los layouts: el navegador elige con srcset y nunca baja una imagen
		 * más grande que la que se ve. 16:10 para tarjetas, 16:9 para el hero.
		 */
		add_image_size( 'feelolab-card', 640, 400, true );
		add_image_size( 'feelolab-hero', 1600, 900, true );
		add_image_size( 'feelolab-square', 480, 480, true );
	}
);

/** Ancho de contenido para embeds. */
add_action(
	'after_setup_theme',
	static function (): void {
		$GLOBALS['content_width'] = 720; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	},
	0
);

add_action(
	'widgets_init',
	static function (): void {
		register_sidebar(
			array(
				'name'          => __( 'Barra lateral del blog', 'feelolab' ),
				'id'            => 'blog',
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget__title">',
				'after_title'   => '</h2>',
			)
		);
	}
);

/** Extracto corto y sin "[…]": las tarjetas lo muestran a 2-3 líneas. */
add_filter( 'excerpt_length', static fn() => 24 );
add_filter( 'excerpt_more', static fn() => '…' );

/** Los archivos de CPT muestran 12 ítems (grilla de 3 o 4 columnas sin huecos). */
add_action(
	'pre_get_posts',
	static function ( WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( $query->is_post_type_archive() || $query->is_tax() ) {
			$post_type = $query->get( 'post_type' );
			$post_type = is_array( $post_type ) ? reset( $post_type ) : $post_type;
			if ( $query->is_tax() || ( is_string( $post_type ) && str_starts_with( $post_type, 'feelo_' ) ) ) {
				$query->set( 'posts_per_page', 12 );
				$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
			}
		}
	}
);
