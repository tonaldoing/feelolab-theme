<?php
/**
 * Plugin Name:       Feelolab Core
 * Description:       Contenido y datos del sitio para el tema Feelolab: tipos de contenido, taxonomías, ajustes de marca, schema y formulario de contacto. El contenido vive acá y no en el tema, así sobrevive a un cambio de diseño.
 * Version:           0.2.0
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author:            Feelo
 * Text Domain:       feelolab-core
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 *
 * @package Feelo\Core
 */

defined( 'ABSPATH' ) || exit;

define( 'FEELO_CORE_VERSION', '0.2.0' );
define( 'FEELO_CORE_FILE', __FILE__ );
define( 'FEELO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FEELO_CORE_URL', plugin_dir_url( __FILE__ ) );

/*
 * Autoload PSR-4 mínimo: Feelo\Core\Settings\SiteSettings → src/Settings/SiteSettings.php.
 * Sin Composer en runtime: el plugin se instala copiando la carpeta.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = 'Feelo\\Core\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}
		$file = FEELO_CORE_DIR . 'src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require FEELO_CORE_DIR . 'src/functions.php';

add_action( 'plugins_loaded', array( Feelo\Core\Plugin::class, 'boot' ) );

register_activation_hook(
	__FILE__,
	static function (): void {
		Feelo\Core\Modules\Registry::register_all();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
