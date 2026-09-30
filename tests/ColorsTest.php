<?php
/**
 * Colores: el sitio promete contraste WCAG AA con cualquier combinación que elija el cliente.
 *
 * @package Feelolab
 */

use PHPUnit\Framework\TestCase;

final class ColorsTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['feelo_test_mods'] = array();
	}

	/** @return array<int, array{string}> Colores al azar, con semilla fija (el test es reproducible). */
	public static function randomColors(): array {
		mt_srand( 20260930 );
		$colors = array( array( '#ffffff' ), array( '#000000' ), array( '#777777' ), array( '#ffff00' ), array( '#00ffff' ) );
		for ( $i = 0; $i < 60; $i++ ) {
			$colors[] = array( sprintf( '#%06x', mt_rand( 0, 0xffffff ) ) );
		}
		return $colors;
	}

	public function test_contrast_extremes(): void {
		$this->assertEqualsWithDelta( 21.0, feelolab_contrast( '#000000', '#ffffff' ), 0.01 );
		$this->assertEqualsWithDelta( 1.0, feelolab_contrast( '#336699', '#336699' ), 0.001 );
		$this->assertEqualsWithDelta( feelolab_contrast( '#123456', '#abcdef' ), feelolab_contrast( '#abcdef', '#123456' ), 0.0001 );
	}

	/** @dataProvider randomColors */
	public function test_ensure_contrast_always_reaches_target( string $bg ): void {
		foreach ( array( '#2447d8', '#ffffff', '#000000', '#e11d48', '#facc15' ) as $color ) {
			$fixed = feelolab_ensure_contrast( $color, $bg, 4.5 );
			$this->assertGreaterThanOrEqual( 4.5, round( feelolab_contrast( $fixed, $bg ), 2 ), "$color sobre $bg dio $fixed" );
		}
	}

	/** @dataProvider randomColors */
	public function test_button_text_is_readable_on_any_button( string $primary ): void {
		$text = feelolab_button_text_color( $primary );
		$this->assertGreaterThanOrEqual( 4.5, round( feelolab_contrast( $text, $primary ), 2 ), "botón $primary con texto $text" );
	}

	public function test_chosen_button_text_is_kept_when_it_reads_well(): void {
		$GLOBALS['feelo_test_mods']['feelolab_color_button_text'] = '#fffbeb';
		$this->assertSame( '#fffbeb', feelolab_button_text_color( '#1e3a8a' ) );
	}

	public function test_white_primary_links_fall_back_to_secondary(): void {
		$link = feelolab_link_base_color( '#ffffff', '#0075ad', '#ffffff', '#111111' );
		$this->assertSame( '#0075ad', $link );
	}

	public function test_brand_css_has_readable_variables_and_border_for_white_button(): void {
		$GLOBALS['feelo_test_mods'] = array(
			'feelolab_color_primary' => '#ffffff',
			'feelolab_color_bg'      => '#ffffff',
			'feelolab_color_text'    => '#999999',
		);
		$css = feelolab_brand_css();
		preg_match_all( '/(--[a-z-]+):([^;]+);/', $css, $m );
		$vars = array_combine( $m[1], $m[2] );
		$this->assertNotSame( 'transparent', $vars['--c-btn-border'], 'botón blanco sobre blanco necesita borde' );
		$this->assertGreaterThanOrEqual( 7, round( feelolab_contrast( $vars['--c-text'], $vars['--c-bg'] ), 2 ), 'el texto se oscurece a 7:1' );
		$this->assertGreaterThanOrEqual( 4.5, round( feelolab_contrast( $vars['--c-primary-text'], $vars['--c-bg'] ), 2 ) );
		$this->assertGreaterThanOrEqual( 4.5, round( feelolab_contrast( $vars['--c-on-secondary'], $vars['--c-secondary'] ), 2 ) );
	}
}
