<?php
/**
 * Llamada a la acción.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_template_part(
	'template-parts/cta-band',
	null,
	array(
		'title'  => (string) feelolab_home( 'cta', 'title' ),
		'text'   => (string) feelolab_home( 'cta', 'text' ),
		'button' => (string) feelolab_home( 'cta', 'btn_text' ),
		'url'    => (string) feelolab_home( 'cta', 'btn_url' ),
		'id'     => 'cta-home',
	)
);
