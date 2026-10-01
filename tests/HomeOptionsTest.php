<?php
/**
 * Tema: opciones de las secciones de la home (fondo, columnas, campos comunes) y tipografía
 * separada para títulos y textos.
 *
 * @package Feelolab
 */

use PHPUnit\Framework\TestCase;

final class HomeOptionsTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['feelo_test_mods'] = array();
	}

	public function test_list_sections_share_the_same_options(): void {
		$sections = feelolab_home_sections();
		foreach ( array( 'servicios', 'productos', 'proyectos', 'equipo', 'blog' ) as $key ) {
			foreach ( array( 'title', 'text', 'count', 'columns', 'category', 'more_text', 'bg' ) as $field ) {
				$this->assertArrayHasKey( $field, $sections[ $key ]['fields'], "$key sin $field" );
			}
			$this->assertSame( 'coleccion', feelolab_home_template( $key ) );
			$this->assertNotEmpty( $sections[ $key ]['list']['empty'], "$key sin aviso de vacío" );
		}
		$this->assertArrayNotHasKey( 'orderby', $sections['blog']['fields'], 'las notas van siempre por fecha' );
		$this->assertArrayNotHasKey( 'more_text', $sections['testimonios']['fields'] );
	}

	public function test_old_settings_keep_their_meaning(): void {
		// Sitios que ya tenían título y cantidad guardados: mismas claves, mismos valores por defecto.
		$this->assertSame( 'Servicios', feelolab_home( 'servicios', 'title' ) );
		$this->assertSame( 6, feelolab_home( 'servicios', 'count' ) );
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_count'] = 2;
		$this->assertSame( 2, feelolab_home( 'servicios', 'count' ) );
		// Sobre nosotros y testimonios seguían en gris: siguen igual sin tocar nada.
		$this->assertSame( 'section section--surface', feelolab_home_section_class( 'nosotros' ) );
		$this->assertSame( 'section section--surface', feelolab_home_section_class( 'testimonios' ) );
		$this->assertSame( 'section section--dark', feelolab_home_section_class( 'cifras' ) );
		$this->assertSame( 'section', feelolab_home_section_class( 'servicios' ) );
	}

	public function test_background_and_columns_follow_the_setting_and_ignore_garbage(): void {
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_bg']      = 'oscuro';
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_columns'] = '4';
		$this->assertSame( 'section section--dark', feelolab_home_section_class( 'servicios' ) );
		$this->assertSame( 'grid grid--4', feelolab_home_grid_class( 'servicios' ) );
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_bg']      = 'violeta';
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_columns'] = '9';
		$this->assertSame( 'section', feelolab_home_section_class( 'servicios' ) );
		$this->assertSame( 'grid grid--3', feelolab_home_grid_class( 'servicios' ) );
	}

	public function test_body_font_can_change_without_touching_headings(): void {
		$GLOBALS['feelo_test_mods']['feelolab_font_pair'] = 'fraunces';
		$this->assertStringContainsString( 'Fraunces', feelolab_font_for( 'heading' )['stack'] );
		$this->assertStringContainsString( 'Inter', feelolab_font_for( 'body' )['stack'] );
		$this->assertSame( array( 'fraunces', 'inter' ), feelolab_active_web_fonts() );

		$GLOBALS['feelo_test_mods']['feelolab_font_body'] = 'source-serif-4';
		$this->assertStringContainsString( 'Fraunces', feelolab_font_for( 'heading' )['stack'] );
		$this->assertStringContainsString( 'Source Serif 4', feelolab_font_for( 'body' )['stack'] );
		$this->assertSame( array( 'fraunces', 'source-serif-4' ), feelolab_active_web_fonts(), 'Inter ya no se descarga' );

		$GLOBALS['feelo_test_mods']['feelolab_font_heading'] = 'sistema';
		$this->assertSame( array( 'source-serif-4' ), feelolab_active_web_fonts() );
	}

	public function test_unknown_font_choice_falls_back_to_the_pair(): void {
		$GLOBALS['feelo_test_mods']['feelolab_font_pair'] = 'editorial';
		$GLOBALS['feelo_test_mods']['feelolab_font_body'] = 'comic-sans';
		$this->assertSame( feelolab_font_stacks()['editorial']['body'], feelolab_font_for( 'body' )['stack'] );
		$this->assertSame( array(), feelolab_active_web_fonts() );
	}

	public function test_brand_css_uses_the_separate_fonts(): void {
		$GLOBALS['feelo_test_mods']['feelolab_font_heading'] = 'figtree';
		$GLOBALS['feelo_test_mods']['feelolab_font_body']    = 'serif';
		$css = feelolab_brand_css();
		$this->assertStringContainsString( '--f-heading:"Figtree"', $css );
		$this->assertStringContainsString( '--f-body:Charter', $css );
		$this->assertStringContainsString( '@font-face{font-family:"Figtree"', $css );
	}
}
