<?php
/**
 * Ajustes del sitio → Módulos: cada cliente prende solo lo que usa y el admin queda limpio.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core\Settings;

use Feelo\Core\Modules\Registry;

defined( 'ABSPATH' ) || exit;

final class ModulesPage {

	private const FLUSH_FLAG = 'feelo_flush_rewrites';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 11 );
		add_action( 'admin_init', array( self::class, 'register' ) );
		// Los CPT nuevos necesitan reglas de reescritura: se regeneran una vez, en el request siguiente.
		add_action( 'update_option_' . Registry::OPTION, array( self::class, 'schedule_flush' ) );
		add_action( 'add_option_' . Registry::OPTION, array( self::class, 'schedule_flush' ) );
		add_action( 'init', array( self::class, 'maybe_flush' ), 99 );
	}

	public static function menu(): void {
		add_submenu_page(
			SiteSettings::PAGE,
			__( 'Módulos', 'feelolab-core' ),
			__( 'Módulos', 'feelolab-core' ),
			'manage_options',
			'feelo-modulos',
			array( self::class, 'render' )
		);
	}

	public static function register(): void {
		register_setting(
			'feelo_modules_group',
			Registry::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => static function ( $input ): array {
					$input = is_array( $input ) ? array_map( 'sanitize_key', $input ) : array();
					return array_values( array_intersect( $input, array_keys( Registry::definitions() ) ) );
				},
			)
		);
	}

	public static function schedule_flush(): void {
		update_option( self::FLUSH_FLAG, 1, false );
	}

	public static function maybe_flush(): void {
		if ( get_option( self::FLUSH_FLAG ) ) {
			delete_option( self::FLUSH_FLAG );
			flush_rewrite_rules();
		}
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$enabled = Registry::enabled();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Módulos de contenido', 'feelolab-core' ); ?></h1>
			<p><?php esc_html_e( 'Apagar un módulo lo oculta del admin y del sitio, pero no borra su contenido: al volver a prenderlo aparece tal cual.', 'feelolab-core' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'feelo_modules_group' ); ?>
				<input type="hidden" name="<?php echo esc_attr( Registry::OPTION ); ?>[]" value="">
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Módulos activos', 'feelolab-core' ); ?></legend>
					<table class="widefat striped" role="presentation">
						<tbody>
						<?php foreach ( Registry::definitions() as $key => $def ) : ?>
							<tr>
								<td style="width:2.5em">
									<input type="checkbox" id="feelo-mod-<?php echo esc_attr( $key ); ?>"
										name="<?php echo esc_attr( Registry::OPTION ); ?>[]"
										value="<?php echo esc_attr( $key ); ?>"
										aria-describedby="feelo-mod-<?php echo esc_attr( $key ); ?>-desc"
										<?php checked( in_array( $key, $enabled, true ) ); ?>>
								</td>
								<td>
									<label for="feelo-mod-<?php echo esc_attr( $key ); ?>"><strong><?php echo esc_html( $def['plural'] ); ?></strong></label>
									<p class="description" id="feelo-mod-<?php echo esc_attr( $key ); ?>-desc">
										<?php echo esc_html( $def['description'] ); ?>
										<?php if ( ! empty( $def['public'] ) ) : ?>
											<br><code>/<?php echo esc_html( $def['slug'] ); ?>/</code>
										<?php endif; ?>
									</p>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</fieldset>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
