<?php
/**
 * Llamada a la acción (también la segunda, que reusa esta plantilla).
 *
 * @package Feelolab
 *
 * @var array{key?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_key = $args['key'] ?? 'cta';

get_template_part(
	'template-parts/cta-band',
	null,
	array(
		'title'  => (string) feelolab_home( $feelolab_key, 'title' ),
		'text'   => (string) feelolab_home( $feelolab_key, 'text' ),
		'button' => (string) feelolab_home( $feelolab_key, 'btn_text' ),
		'url'    => (string) feelolab_home( $feelolab_key, 'btn_url' ),
		'id'     => 'cta' === $feelolab_key ? 'cta-home' : str_replace( '_', '-', $feelolab_key ) . '-home',
	)
);
