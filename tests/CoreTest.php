<?php
/**
 * Plugin: parseo del CSV del importador y changelog del actualizador.
 *
 * @package Feelolab
 */

use Feelo\Core\Importer;
use Feelo\Core\Updater;
use PHPUnit\Framework\TestCase;

final class CoreTest extends TestCase {

	public function test_delimiter_detection(): void {
		$this->assertSame( ';', Importer::detect_delimiter( 'titulo;precio;sku' ) );
		$this->assertSame( ',', Importer::detect_delimiter( 'titulo,precio,sku' ) );
		$this->assertSame( "\t", Importer::detect_delimiter( "titulo\tprecio\tsku" ) );
	}

	public function test_headers_are_matched_with_accents_case_and_aliases(): void {
		$map = Importer::map_columns( array( 'Nombre', 'PRECIO OFERTA', 'Ficha técnica', 'Código', 'Fotos', 'Otra cosa' ) );
		$this->assertSame(
			array(
				'titulo'        => 0,
				'precio_oferta' => 1,
				'ficha_tecnica' => 2,
				'sku'           => 3,
				'imagenes'      => 4,
			),
			$map
		);
	}

	public function test_row_to_fields_and_lists(): void {
		$row = Importer::row_to_fields( array( ' Pilar ', '$ 10', 'Pilares | Accesorios' ), array( 'titulo' => 0, 'precio' => 1, 'categorias' => 2 ) );
		$this->assertSame( 'Pilar', $row['titulo'] );
		$this->assertSame( array( 'Pilares', 'Accesorios' ), Importer::split_list( $row['categorias'] ) );
		$this->assertSame( array( 'https://a.test/1.jpg', 'b.jpg' ), Importer::split_images( "https://a.test/1.jpg|\nb.jpg" ) );
	}

	public function test_truthy_values_for_available(): void {
		foreach ( array( 'Sí', 'si', '1', 'TRUE', 'x', 'En stock' ) as $yes ) {
			$this->assertTrue( Importer::truthy( $yes ), $yes );
		}
		foreach ( array( 'no', '0', '', 'agotado' ) as $no ) {
			$this->assertFalse( Importer::truthy( $no ), $no );
		}
	}

	public function test_release_notes_render_without_markdown_symbols(): void {
		$html = Updater::notes_html( "- **Nuevo** botón\n- Usa `feelo_setting`\n\nCierre <script>" );
		$this->assertSame( '<ul><li><strong>Nuevo</strong> botón</li><li>Usa <code>feelo_setting</code></li></ul><p>Cierre &lt;script&gt;</p>', $html );
		$this->assertStringNotContainsString( '**', $html );
	}
}
