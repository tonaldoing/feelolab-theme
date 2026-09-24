<?php
/**
 * Migas de pan visibles + BreadcrumbList en el schema.
 * Si hay Yoast o Rank Math, se usan las suyas (y su schema) para no duplicar.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/** @return array<int, array{label: string, url: string}> */
function feelolab_breadcrumb_trail(): array {
	$trail = array(
		array(
			'label' => __( 'Inicio', 'feelolab' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular() ) {
		$post      = get_queried_object();
		$post_type = get_post_type( $post );

		if ( 'post' === $post_type ) {
			$blog = (int) get_option( 'page_for_posts' );
			if ( $blog ) {
				$trail[] = array(
					'label' => get_the_title( $blog ),
					'url'   => get_permalink( $blog ),
				);
			}
			$cats = get_the_category( $post->ID );
			if ( $cats ) {
				$trail[] = array(
					'label' => $cats[0]->name,
					'url'   => get_category_link( $cats[0] ),
				);
			}
		} elseif ( 'page' === $post_type ) {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
				$trail[] = array(
					'label' => get_the_title( $ancestor ),
					'url'   => get_permalink( $ancestor ),
				);
			}
		} else {
			$object = get_post_type_object( $post_type );
			$link   = get_post_type_archive_link( $post_type );
			if ( $object && $link ) {
				$trail[] = array(
					'label' => $object->labels->name,
					'url'   => $link,
				);
			}
		}
		$trail[] = array(
			'label' => get_the_title( $post ),
			'url'   => '',
		);
	} elseif ( is_post_type_archive() ) {
		$trail[] = array(
			'label' => post_type_archive_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$tax  = get_taxonomy( $term->taxonomy );
		if ( $tax && ! in_array( $term->taxonomy, array( 'category', 'post_tag' ), true ) ) {
			$post_type = $tax->object_type[0] ?? '';
			$link      = $post_type ? get_post_type_archive_link( $post_type ) : '';
			if ( $link ) {
				$trail[] = array(
					'label' => get_post_type_object( $post_type )->labels->name,
					'url'   => $link,
				);
			}
		}
		foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $term->taxonomy );
			$trail[]  = array(
				'label' => $ancestor->name,
				'url'   => get_term_link( $ancestor ),
			);
		}
		$trail[] = array(
			'label' => $term->name,
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$trail[] = array(
			/* translators: %s: búsqueda */
			'label' => sprintf( __( 'Resultados para "%s"', 'feelolab' ), get_search_query() ),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$trail[] = array(
			'label' => __( 'Página no encontrada', 'feelolab' ),
			'url'   => '',
		);
	} elseif ( is_home() ) {
		$blog    = (int) get_option( 'page_for_posts' );
		$trail[] = array(
			'label' => $blog ? get_the_title( $blog ) : __( 'Blog', 'feelolab' ),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		$trail[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	}

	return apply_filters( 'feelolab_breadcrumb_trail', $trail );
}

function feelolab_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}
	if ( function_exists( 'yoast_breadcrumb' ) && defined( 'WPSEO_VERSION' ) ) {
		yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Migas de pan', 'feelolab' ) . '"><p>', '</p></nav>' );
		return;
	}
	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Migas de pan', 'feelolab' ) . '">';
		rank_math_the_breadcrumbs();
		echo '</nav>';
		return;
	}

	$trail = feelolab_breadcrumb_trail();
	if ( count( $trail ) < 2 ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Migas de pan', 'feelolab' ) . '"><ol>';
	$items = array();
	foreach ( $trail as $i => $crumb ) {
		$is_last = ( count( $trail ) - 1 === $i );
		echo '<li>';
		if ( $crumb['url'] && ! $is_last ) {
			echo '<a href="' . esc_url( $crumb['url'] ) . '">' . esc_html( $crumb['label'] ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( $crumb['label'] ) . '</span>';
		}
		echo '</li>';

		$item = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => wp_strip_all_tags( $crumb['label'] ),
		);
		if ( $crumb['url'] ) {
			$item['item'] = $crumb['url'];
		}
		$items[] = $item;
	}
	echo '</ol></nav>';

	if ( class_exists( 'Feelo\\Core\\Schema\\Schema' ) ) {
		Feelo\Core\Schema\Schema::add(
			array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $items,
			)
		);
	}
}
