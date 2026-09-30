<?php
/**
 * Plugin: lógica pura de los ajustes, el asistente, el newsletter, las actualizaciones, Medios y
 * Performance. Lo que necesita WordPress de verdad (guardar, redirigir, subir archivos) va en la
 * suite e2e (tests/e2e) contra Playground.
 *
 * @package Feelolab
 */

use Feelo\Core\Forms\Newsletter;
use Feelo\Core\Importer;
use Feelo\Core\Launch;
use Feelo\Core\Media;
use Feelo\Core\Performance;
use Feelo\Core\Settings\SiteSettings;
use Feelo\Core\Updater;
use Feelo\Core\Wizard;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase {

	protected function setUp(): void {
		$GLOBALS['feelo_test_options'] = array();
		$GLOBALS['feelo_test_can']     = true;
		unset( $_POST['feelo_tab'] );
	}

	protected function tearDown(): void {
		unset( $_POST['feelo_tab'] );
	}

	// Ajustes del sitio.

	public function test_settings_saved_by_code_are_kept_as_is(): void {
		// El asistente y Versiones guardan con update_option(): sin pestaña, no se toca nada.
		$input = array(
			'nombre_comercial' => 'Clínica',
			'canal_beta'       => 1,
		);
		$this->assertSame( $input, SiteSettings::sanitize( $input ) );
	}

	public function test_settings_tab_sanitizes_only_its_fields_and_keeps_the_rest(): void {
		$GLOBALS['feelo_test_options'][ SiteSettings::OPTION ] = array(
			'telefono'         => '+54 11 4000-0000',
			'turnstile_secret' => 'secreto-viejo',
		);
		$_POST['feelo_tab'] = 'formulario';
		$saved              = SiteSettings::sanitize(
			array(
				'form_email'            => 'no es un email',
				'turnstile_site'        => " <b>clave</b>\n ",
				'turnstile_secret'      => '',
				'mensajes_retencion'    => '99',
				'borrar_al_desinstalar' => 'on',
			)
		);
		$this->assertSame( '+54 11 4000-0000', $saved['telefono'], 'otra pestaña no se pisa' );
		$this->assertSame( '', $saved['form_email'] );
		$this->assertSame( 'clave', $saved['turnstile_site'] );
		$this->assertSame( 'secreto-viejo', $saved['turnstile_secret'], 'contraseña vacía = no cambiar' );
		$this->assertSame( '', $saved['mensajes_retencion'], 'opción fuera de la lista' );
		$this->assertSame( 1, $saved['borrar_al_desinstalar'] );
	}

	public function test_settings_unknown_tab_changes_nothing(): void {
		$GLOBALS['feelo_test_options'][ SiteSettings::OPTION ] = array( 'telefono' => '1' );
		$_POST['feelo_tab'] = 'inventada';
		$this->assertSame( array( 'telefono' => '1' ), SiteSettings::sanitize( array( 'telefono' => '2' ) ) );
	}

	public function test_every_select_default_is_a_valid_option(): void {
		foreach ( SiteSettings::tabs() as $tab => $data ) {
			foreach ( $data['fields'] as $key => $field ) {
				$this->assertArrayHasKey( 'type', $field, "$tab.$key sin tipo" );
				if ( 'select' === $field['type'] ) {
					$this->assertNotEmpty( $field['options'], "$tab.$key sin opciones" );
				}
			}
		}
	}

	// Asistente.

	public function test_wizard_steps_advance_and_skip_missing_ones(): void {
		$this->assertSame( 2, Wizard::following( 1, array( 1, 2, 3, 4, 5, 6 ) ) );
		$this->assertSame( 4, Wizard::following( 2, array( 1, 2, 4, 5, 6 ) ), 'sin el tema, se saltea Marca' );
		$this->assertSame( 6, Wizard::following( 6, array( 1, 2, 4, 5, 6 ) ), 'el último se queda' );
		$this->assertSame( 1, Wizard::following( 9, array( 1, 2, 4, 5, 6 ) ), 'paso desconocido vuelve al primero' );
	}

	public function test_wizard_is_not_offered_to_sites_with_contact_data(): void {
		$this->assertFalse( Wizard::already_configured( array() ) );
		$this->assertFalse( Wizard::already_configured( array( 'nombre_comercial' => 'X' ) ) );
		$this->assertTrue( Wizard::already_configured( array( 'whatsapp' => '+54 9 11' ) ) );
		$this->assertTrue( Wizard::already_configured( array( 'email' => 'a@b.co' ) ) );
	}

	// Newsletter.

	public function test_mailchimp_endpoint_uses_the_key_datacenter_and_email_hash(): void {
		$this->assertSame(
			'https://us21.api.mailchimp.com/3.0/lists/abc123/members/' . md5( 'ana@example.com' ),
			Newsletter::mailchimp_url( 'f00ba4-us21', 'abc123', ' Ana@Example.com ' )
		);
		$this->assertSame( '', Newsletter::mailchimp_url( 'sin-centro-de-datos', 'abc123', 'a@b.co' ) );
		$this->assertSame( '', Newsletter::mailchimp_url( 'f00ba4-us21', '', 'a@b.co' ), 'sin audiencia' );
		$this->assertStringNotContainsString( '/../', Newsletter::mailchimp_url( 'k-us1', '../x', 'a@b.co' ), 'la audiencia se sanea' );
	}

	// Actualizaciones.

	public function test_manifest_is_normalized_and_keeps_only_stable_versions(): void {
		$release = Updater::parse_manifest(
			array(
				'version'  => 'v0.9.0',
				'theme'    => 'https://github.com/o/r/releases/download/v0.9.0/feelolab.zip',
				'plugin'   => 'javascript:alert(1)',
				'versions' => array( 'v0.9.0', '0.9.0-beta.1', '0.8.1', 'basura', '0.8.0' ),
			)
		);
		$this->assertSame( '0.9.0', $release['version'] );
		$this->assertSame( '', $release['plugin'], 'URL no http descartada' );
		$this->assertSame( array( '0.9.0', '0.8.1', '0.8.0' ), $release['versions'] );
		$this->assertNull( Updater::parse_manifest( array( 'notes' => 'sin versión' ) ) );
		$this->assertNull( Updater::parse_manifest( array( 'version' => array( '1' ) ) ) );
	}

	public function test_api_release_picks_the_right_download_for_each_zip(): void {
		$data   = array(
			'tag_name' => 'v1.2.3',
			'assets'   => array(
				array(
					'name'                 => 'feelolab.zip',
					'url'                  => 'https://api.github.com/assets/1',
					'browser_download_url' => 'https://github.com/d/feelolab.zip',
				),
				array(
					'name'                 => 'feelolab-core.zip',
					'url'                  => 'https://api.github.com/assets/2',
					'browser_download_url' => 'https://github.com/d/feelolab-core.zip',
				),
				array( 'name' => 'otro.zip' ),
			),
		);
		$public = Updater::parse_api_release( $data, false );
		$this->assertSame( '1.2.3', $public['version'] );
		$this->assertSame( 'https://github.com/d/feelolab.zip', $public['theme'] );
		$this->assertSame( 'https://github.com/d/feelolab-core.zip', $public['plugin'] );
		$private = Updater::parse_api_release( $data, true );
		$this->assertSame( 'https://api.github.com/assets/2', $private['plugin'] );
		$this->assertSame( '', Updater::parse_api_release( array(), false )['version'] );
	}

	// Importador.

	public function test_csv_from_excel_windows_is_converted_to_utf8(): void {
		$this->assertSame( 'Título;Categoría', Importer::to_utf8( "T\xEDtulo;Categor\xEDa" ) );
		$this->assertSame( 'Título', Importer::to_utf8( "\xEF\xBB\xBFTítulo" ), 'sin BOM' );
		$this->assertSame( 'ya en UTF-8 ñ', Importer::to_utf8( 'ya en UTF-8 ñ' ) );
	}

	// Lanzamiento.

	public function test_launch_summary_counts_done_and_critical(): void {
		$summary = Launch::summary(
			array(
				array(
					'ok'    => true,
					'level' => 'critico',
				),
				array(
					'ok'    => false,
					'level' => 'critico',
				),
				array(
					'ok'    => false,
					'level' => 'recomendado',
				),
			)
		);
		$this->assertSame(
			array(
				'done'     => 1,
				'total'    => 3,
				'critical' => 1,
			),
			$summary
		);
	}

	// Medios: fuentes propias.

	public function test_fonts_can_be_uploaded_only_by_who_edits_the_design(): void {
		$this->assertSame( 'font/woff2', Media::font_mimes( array() )['woff2'] );
		$GLOBALS['feelo_test_can'] = false;
		$this->assertArrayNotHasKey( 'woff2', Media::font_mimes( array() ) );
	}

	public function test_font_type_is_confirmed_by_the_file_signature(): void {
		$this->assertTrue( Media::is_font( 'woff2', 'wOF2' ) );
		$this->assertTrue( Media::is_font( 'woff', 'wOFF' ) );
		$this->assertFalse( Media::is_font( 'woff2', 'wOFF' ) );
		$this->assertFalse( Media::is_font( 'woff2', '<?ph' ), 'un PHP renombrado no pasa' );

		$file = tempnam( sys_get_temp_dir(), 'font' );
		file_put_contents( $file, 'wOF2' . str_repeat( "\0", 20 ) );
		$wp = array(
			'ext'             => false,
			'type'            => false,
			'proper_filename' => false,
		);
		$this->assertSame( 'font/woff2', Media::font_filetype( $wp, $file, 'marca.woff2' )['type'] );
		file_put_contents( $file, '<?php echo 1;' );
		$this->assertFalse( Media::font_filetype( $wp, $file, 'marca.woff2' )['type'] );
		$this->assertSame( $wp, Media::font_filetype( $wp, $file, 'foto.jpg' ), 'otros archivos no se tocan' );
		unlink( $file );
	}

	// Performance.

	public function test_revisions_are_capped_at_ten(): void {
		$this->assertSame( 10, Performance::revisions( -1 ), 'ilimitado pasa a 10' );
		$this->assertSame( 10, Performance::revisions( 50 ) );
		$this->assertSame( 3, Performance::revisions( 3 ) );
		$this->assertSame( 0, Performance::revisions( 0 ), 'sin revisiones se respeta' );
	}

	public function test_cleanup_options_default_to_on(): void {
		$this->assertSame(
			array(
				'emoji'     => true,
				'embeds'    => true,
				'head_junk' => true,
			),
			Performance::options()
		);
	}
}
