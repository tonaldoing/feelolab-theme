<?php
/**
 * Orden de las secciones de la home: lo que se arrastra en el Personalizador y los sitios viejos.
 *
 * @package Feelolab
 */

use PHPUnit\Framework\TestCase;

final class HomeTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['feelo_test_mods']    = array();
		$GLOBALS['feelo_test_modules'] = array( 'servicios', 'testimonios', 'faq' );
	}

	public function test_without_saved_order_uses_numbers(): void {
		$keys = feelolab_home_ordered_keys();
		$this->assertSame( 'hero', $keys[0] );
		$this->assertLessThan( array_search( 'contacto', $keys, true ), array_search( 'cta', $keys, true ) );
		$this->assertNotContains( 'proyectos', $keys, 'sin el módulo, la sección no se ofrece' );
	}

	public function test_old_numeric_order_is_respected(): void {
		$GLOBALS['feelo_test_mods']['feelolab_home_contacto_order'] = 1;
		$this->assertSame( 'contacto', feelolab_home_ordered_keys()[0] );
	}

	public function test_saved_order_wins_and_new_sections_are_placed_after_their_neighbour(): void {
		// Orden guardado antes de que existieran newsletter y las secciones repetidas.
		$GLOBALS['feelo_test_mods']['feelolab_home_order'] = 'contacto,hero,servicios,nosotros,cifras,testimonios,faq,blog,cta';
		$keys = feelolab_home_ordered_keys();
		$this->assertSame( array( 'contacto', 'hero', 'servicios' ), array_slice( $keys, 0, 3 ) );
		$this->assertSame( array_search( 'cta', $keys, true ) + 1, array_search( 'newsletter', $keys, true ), 'newsletter va después de la CTA, como en su número' );
		$this->assertCount( count( array_unique( $keys ) ), $keys, 'sin repetidas' );
	}

	public function test_unknown_keys_in_saved_order_are_ignored(): void {
		$GLOBALS['feelo_test_mods']['feelolab_home_order'] = 'hero,inventada,faq';
		$this->assertNotContains( 'inventada', feelolab_home_ordered_keys() );
	}

	public function test_active_sections_skip_hidden_ones(): void {
		$GLOBALS['feelo_test_mods']['feelolab_home_servicios_show'] = false;
		$active = feelolab_home_active_sections();
		$this->assertNotContains( 'servicios', $active );
		$this->assertNotContains( 'cifras', $active, 'cifras viene apagada' );
		$this->assertContains( 'hero', $active );
	}

	public function test_hero_background_without_image_falls_back_to_centered(): void {
		$GLOBALS['feelo_test_mods']['feelolab_home_hero_layout'] = 'fondo';
		$this->assertSame( 'centrada', feelolab_hero_layout() );
		$GLOBALS['feelo_test_mods']['feelolab_home_hero_image'] = 12;
		$this->assertSame( 'fondo', feelolab_hero_layout() );
		$GLOBALS['feelo_test_mods']['feelolab_home_hero_layout'] = 'rara';
		$this->assertSame( 'dividida', feelolab_hero_layout() );
	}

	public function test_repeated_sections_use_the_original_template(): void {
		$this->assertSame( 'cta', feelolab_home_template( 'cta_2' ) );
		$this->assertSame( 'nosotros', feelolab_home_template( 'nosotros_2' ) );
		$this->assertSame( 'faq', feelolab_home_template( 'faq' ) );
	}

	public function test_reading_time(): void {
		$GLOBALS['feelo_test_post_content'] = '<p>' . str_repeat( 'palabra ', 401 ) . '</p>';
		$this->assertSame( 3, feelolab_reading_time() );
		$GLOBALS['feelo_test_post_content'] = '';
		$this->assertSame( 1, feelolab_reading_time() );
	}
}
