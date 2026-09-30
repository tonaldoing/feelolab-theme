<?php
/**
 * Patrones de bloques para páginas interiores (Nosotros, precios, equipo, landings…) y los
 * estilos de bloque que usan. Los patrones están en /patterns y WordPress los registra solo.
 *
 * Cada estilo trae su CSS como inline_style: con la carga de CSS por bloque (inc/performance.php)
 * WordPress lo imprime solo en las páginas donde aparece el bloque. Colores siempre de las
 * variables de marca, así el contraste queda garantizado como en el resto del tema.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		register_block_pattern_category(
			'feelolab',
			array(
				'label'       => __( 'FeeloLab', 'feelolab' ),
				'description' => __( 'Secciones listas con el diseño del sitio.', 'feelolab' ),
			)
		);

		$styles = array(
			'core/group'     => array(
				'feelolab-tarjeta'    => array(
					__( 'Tarjeta', 'feelolab' ),
					'.is-style-feelolab-tarjeta{height:100%;padding:1.5rem;border:1px solid var(--c-border);border-radius:var(--radius);background:var(--c-bg)}.is-style-feelolab-tarjeta>:first-child{margin-top:0}.is-style-feelolab-tarjeta>:last-child{margin-bottom:0}',
				),
				'feelolab-destacada'  => array(
					__( 'Tarjeta destacada', 'feelolab' ),
					'.is-style-feelolab-destacada{height:100%;padding:1.5rem;border:2px solid var(--c-primary-text);border-radius:var(--radius);background:var(--c-bg)}.is-style-feelolab-destacada>:first-child{margin-top:0}.is-style-feelolab-destacada>:last-child{margin-bottom:0}',
				),
				'feelolab-superficie' => array(
					__( 'Fondo suave', 'feelolab' ),
					'.is-style-feelolab-superficie{padding:clamp(1.5rem,1rem + 2vw,3rem);border-radius:var(--radius);background:var(--c-surface)}.is-style-feelolab-superficie.alignfull{border-radius:0}.is-style-feelolab-superficie a:not(.wp-block-button__link){color:var(--c-primary-on-surface)}.is-style-feelolab-superficie .is-style-feelolab-volanta,.is-style-feelolab-superficie .is-style-feelolab-dato{color:var(--c-primary-on-surface)}.is-style-feelolab-superficie :focus-visible{box-shadow:0 0 0 5px var(--c-surface)}',
				),
				'feelolab-franja'     => array(
					__( 'Franja de marca', 'feelolab' ),
					'.is-style-feelolab-franja{padding:clamp(2rem,1.5rem + 3vw,4rem) clamp(1.25rem,1rem + 2vw,3rem);border-radius:var(--radius);background:var(--c-secondary);color:var(--c-on-secondary)}.is-style-feelolab-franja.alignfull{border-radius:0}.is-style-feelolab-franja :where(h1,h2,h3,h4,p){color:inherit}.is-style-feelolab-franja a:not(.wp-block-button__link){color:inherit}.is-style-feelolab-franja .wp-block-button__link{background:var(--c-on-secondary);color:var(--c-secondary);border-color:var(--c-on-secondary)}.is-style-feelolab-franja .wp-block-button__link:hover{background:transparent;color:var(--c-on-secondary)}.is-style-feelolab-franja .is-style-outline .wp-block-button__link{background:transparent;color:var(--c-on-secondary)}.is-style-feelolab-franja .is-style-feelolab-volanta,.is-style-feelolab-franja .is-style-feelolab-dato{color:var(--c-accent-on-secondary)}.is-style-feelolab-franja :focus-visible{outline-color:var(--c-on-secondary);box-shadow:0 0 0 5px var(--c-secondary)}',
				),
			),
			'core/paragraph' => array(
				'feelolab-volanta' => array(
					__( 'Volanta', 'feelolab' ),
					'.is-style-feelolab-volanta{margin-bottom:.5rem;color:var(--c-primary-text);font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase}',
				),
				'feelolab-dato'    => array(
					__( 'Dato grande', 'feelolab' ),
					'.is-style-feelolab-dato{margin:0 0 .35rem;color:var(--c-primary-text);font-family:var(--f-heading);font-size:clamp(2.25rem,1.8rem + 2vw,3.25rem);font-weight:800;line-height:1;font-variant-numeric:tabular-nums}',
				),
				'feelolab-lead'    => array(
					__( 'Bajada', 'feelolab' ),
					'.is-style-feelolab-lead{font-size:clamp(1.1rem,1rem + .5vw,1.3rem);line-height:1.5}',
				),
			),
			'core/list'      => array(
				'feelolab-check' => array(
					__( 'Con tildes', 'feelolab' ),
					'.is-style-feelolab-check{list-style:none;padding-left:0!important}.is-style-feelolab-check li{position:relative;padding-left:1.75rem}.is-style-feelolab-check li+li{margin-top:.5rem}.is-style-feelolab-check li::before{content:"";position:absolute;left:0;top:.3em;width:1.1rem;height:1.1rem;background:var(--c-primary-text);-webkit-mask:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23000\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M20 6 9 17l-5-5\'/%3E%3C/svg%3E") center/contain no-repeat;mask:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23000\' stroke-width=\'3\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M20 6 9 17l-5-5\'/%3E%3C/svg%3E") center/contain no-repeat}.is-style-feelolab-franja .is-style-feelolab-check li::before{background:var(--c-accent-on-secondary)}',
				),
			),
		);
		foreach ( $styles as $block => $list ) {
			foreach ( $list as $name => $style ) {
				register_block_style(
					$block,
					array(
						'name'         => $name,
						'label'        => $style[0],
						'inline_style' => $style[1],
					)
				);
			}
		}
	}
);
