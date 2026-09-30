<?php
/**
 * Arranque del plugin.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Fields\FieldGroups;
use Feelo\Core\Forms\ContactForm;
use Feelo\Core\Forms\Newsletter;
use Feelo\Core\Modules\Registry;
use Feelo\Core\Schema\Schema;
use Feelo\Core\Settings\ModulesPage;
use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public static function boot(): void {
		// Traducciones incluidas en /languages (el plugin se distribuye fuera de wordpress.org).
		load_plugin_textdomain( 'feelolab-core', false, dirname( plugin_basename( FEELO_CORE_FILE ) ) . '/languages' ); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound

		add_action( 'init', array( Registry::class, 'register_all' ) );
		add_action( 'acf/init', array( FieldGroups::class, 'register' ) );

		SiteSettings::init();
		ModulesPage::init();
		ContactForm::init();
		Newsletter::init();
		Schema::init();
		Frontend::init();
		Gallery::init();
		LlmsTxt::init();
		Consent::init();
		Tracking::init();
		Privacy::init();
		Media::init();
		Performance::init();
		// El actualizador y Versiones no van en la versión de wordpress.org (ahí actualiza WordPress).
		if ( class_exists( Updater::class ) ) {
			Updater::init();
		}

		if ( is_admin() ) {
			Admin::init();
			Branding::init();
			Launch::init();
			Importer::init();
			if ( class_exists( Versions::class ) ) {
				Versions::init();
			}
			Wizard::init();
		}
	}
}
