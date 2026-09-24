<?php
/**
 * Arranque del plugin.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Fields\FieldGroups;
use Feelo\Core\Forms\ContactForm;
use Feelo\Core\Modules\Registry;
use Feelo\Core\Schema\Schema;
use Feelo\Core\Settings\ModulesPage;
use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	public static function boot(): void {
		load_plugin_textdomain( 'feelolab-core', false, dirname( plugin_basename( FEELO_CORE_FILE ) ) . '/languages' );

		add_action( 'init', array( Registry::class, 'register_all' ) );
		add_action( 'acf/init', array( FieldGroups::class, 'register' ) );

		SiteSettings::init();
		ModulesPage::init();
		ContactForm::init();
		Schema::init();
		Frontend::init();

		if ( is_admin() ) {
			Admin::init();
		}
	}
}
