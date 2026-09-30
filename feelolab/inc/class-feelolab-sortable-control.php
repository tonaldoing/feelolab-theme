<?php
/**
 * Control del Personalizador para ordenar las secciones de la home arrastrando.
 *
 * Cada fila tiene flechas para subir y bajar (teclado y lectores de pantalla: arrastrar no es
 * accesible por sí solo, WCAG 2.5.7) y un botón que abre la sección para editarla.
 * El valor es la lista de claves separadas por coma (theme_mod feelolab_home_order).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista ordenable.
 */
class Feelolab_Sortable_Control extends WP_Customize_Control {

	/** @var string */
	public $type = 'feelolab-sortable';

	/** @var array<string, string> Clave => etiqueta, en el orden actual. */
	public $items = array();

	public function render_content(): void {
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php if ( $this->description ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<input type="hidden" class="feelolab-sortable__value" <?php $this->link(); ?> value="<?php echo esc_attr( implode( ',', array_keys( $this->items ) ) ); ?>">
		<ol class="feelolab-sortable">
			<?php foreach ( $this->items as $key => $label ) : ?>
				<li class="feelolab-sortable__item" draggable="true" data-key="<?php echo esc_attr( $key ); ?>">
					<span class="feelolab-sortable__handle dashicons dashicons-menu" aria-hidden="true"></span>
					<span class="feelolab-sortable__label"><?php echo esc_html( $label ); ?></span>
					<span class="feelolab-sortable__state" data-show-setting="<?php echo esc_attr( "feelolab_home_{$key}_show" ); ?>"></span>
					<button type="button" class="feelolab-sortable__btn feelolab-sortable__move" data-dir="-1">
						<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: nombre de la sección */
							echo esc_html( sprintf( __( 'Subir %s', 'feelolab' ), $label ) );
							?>
						</span>
					</button>
					<button type="button" class="feelolab-sortable__btn feelolab-sortable__move" data-dir="1">
						<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: nombre de la sección */
							echo esc_html( sprintf( __( 'Bajar %s', 'feelolab' ), $label ) );
							?>
						</span>
					</button>
					<button type="button" class="feelolab-sortable__btn feelolab-sortable__edit" data-section="<?php echo esc_attr( 'feelolab_home_' . $key ); ?>">
						<span class="dashicons dashicons-edit" aria-hidden="true"></span>
						<span class="screen-reader-text">
							<?php
							/* translators: %s: nombre de la sección */
							echo esc_html( sprintf( __( 'Editar %s', 'feelolab' ), $label ) );
							?>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}
}
