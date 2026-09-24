<?php
/**
 * Galería de imágenes para productos, proyectos y sedes (reemplaza el campo Gallery de ACF Pro).
 *
 * Meta box con el media modal nativo: elegir varias imágenes, ordenar arrastrando, quitar.
 * Se guarda como lista de IDs en la meta _feelo_gallery.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Modules\Registry;

defined( 'ABSPATH' ) || exit;

final class Gallery {

	public const META = '_feelo_gallery';

	public static function init(): void {
		add_action( 'init', array( self::class, 'register_meta' ), 20 );
		add_action( 'add_meta_boxes', array( self::class, 'meta_box' ) );
		add_action( 'save_post', array( self::class, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( self::class, 'assets' ) );
	}

	/** @return string[] Post types con galería (de los módulos activos). */
	public static function post_types(): array {
		$modules = apply_filters( 'feelo_gallery_modules', array( 'productos', 'proyectos', 'sedes' ) );
		return array_values( array_filter( array_map( array( Registry::class, 'post_type' ), $modules ) ) );
	}

	public static function register_meta(): void {
		foreach ( self::post_types() as $post_type ) {
			register_post_meta(
				$post_type,
				self::META,
				array(
					'type'              => 'array',
					'single'            => true,
					'default'           => array(),
					'sanitize_callback' => array( self::class, 'sanitize' ),
					'auth_callback'     => static fn( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id ),
					'show_in_rest'      => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
				)
			);
		}
	}

	/**
	 * @param mixed $value Lista de IDs (array o "1,2,3").
	 * @return int[]
	 */
	public static function sanitize( $value ): array {
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}
		$ids = array_filter( array_map( 'absint', (array) $value ) );
		return array_values( array_unique( array_filter( $ids, static fn( $id ) => wp_attachment_is_image( $id ) ) ) );
	}

	/** @return int[] */
	public static function ids( int $post_id ): array {
		$ids = get_post_meta( $post_id, self::META, true );
		return is_array( $ids ) ? array_map( 'intval', $ids ) : array();
	}

	public static function meta_box(): void {
		foreach ( self::post_types() as $post_type ) {
			add_meta_box( 'feelo-gallery', __( 'Galería', 'feelolab-core' ), array( self::class, 'render' ), $post_type, 'normal', 'default' );
		}
	}

	public static function render( \WP_Post $post ): void {
		$ids = self::ids( $post->ID );
		wp_nonce_field( 'feelo_gallery_save', 'feelo_gallery_nonce' );
		?>
		<div class="feelo-gallery" data-feelo-gallery>
			<p class="description"><?php esc_html_e( 'Imágenes adicionales. La imagen destacada va primero; estas se muestran después, en este orden (arrastrá para reordenar). Completá el texto alternativo de cada imagen en la biblioteca.', 'feelolab-core' ); ?></p>
			<input type="hidden" name="feelo_gallery" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-feelo-gallery-input>
			<ul class="feelo-gallery__list" data-feelo-gallery-list>
				<?php foreach ( $ids as $id ) : ?>
					<?php self::item( $id ); ?>
				<?php endforeach; ?>
			</ul>
			<p>
				<button type="button" class="button" data-feelo-gallery-add><?php esc_html_e( 'Agregar imágenes', 'feelolab-core' ); ?></button>
			</p>
		</div>
		<script type="text/html" id="tmpl-feelo-gallery-item">
			<?php self::item( 0 ); ?>
		</script>
		<?php
	}

	/** Un ítem de la lista. Con $id = 0 imprime la plantilla para JS. */
	private static function item( int $id ): void {
		$thumb = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '{{ data.url }}';
		$alt   = $id ? (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) : '{{ data.alt }}';
		?>
		<li class="feelo-gallery__item" data-id="<?php echo $id ? esc_attr( (string) $id ) : '{{ data.id }}'; ?>">
			<img src="<?php echo $id ? esc_url( (string) $thumb ) : esc_attr( $thumb ); ?>" alt="" width="96" height="96">
			<?php if ( $id && '' === $alt ) : ?>
				<span class="feelo-gallery__warn"><?php esc_html_e( 'Sin texto alt', 'feelolab-core' ); ?></span>
			<?php endif; ?>
			<button type="button" class="feelo-gallery__remove button-link" data-feelo-gallery-remove>
				<span aria-hidden="true">&times;</span><span class="screen-reader-text"><?php esc_html_e( 'Quitar imagen', 'feelolab-core' ); ?></span>
			</button>
		</li>
		<?php
	}

	public static function save( int $post_id, \WP_Post $post ): void {
		if ( ! in_array( $post->post_type, self::post_types(), true ) || wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( ! isset( $_POST['feelo_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['feelo_gallery_nonce'] ) ), 'feelo_gallery_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$raw = isset( $_POST['feelo_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['feelo_gallery'] ) ) : '';
		$ids = self::sanitize( $raw );
		if ( $ids ) {
			update_post_meta( $post_id, self::META, $ids );
		} else {
			delete_post_meta( $post_id, self::META );
		}
	}

	public static function assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::post_types(), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'feelo-gallery-admin', FEELO_CORE_URL . 'assets/gallery-admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-util' ), FEELO_CORE_VERSION, true );
		wp_localize_script(
			'feelo-gallery-admin',
			'feeloGallery',
			array(
				'title'  => __( 'Elegir imágenes de la galería', 'feelolab-core' ),
				'button' => __( 'Agregar a la galería', 'feelolab-core' ),
			)
		);
		wp_add_inline_style(
			'wp-admin',
			'.feelo-gallery__list{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0}.feelo-gallery__item{position:relative;width:96px;cursor:move}.feelo-gallery__item img{display:block;width:96px;height:96px;object-fit:cover;border:1px solid #c3c4c7}.feelo-gallery__remove{position:absolute;top:2px;right:2px;width:24px;height:24px;border-radius:50%;background:#fff!important;color:#b32d2e!important;font-size:18px;line-height:22px;text-align:center;text-decoration:none}.feelo-gallery__warn{position:absolute;left:0;right:0;bottom:0;background:#b32d2e;color:#fff;font-size:10px;text-align:center}.feelo-gallery__placeholder{width:96px;height:96px;border:2px dashed #c3c4c7}'
		);
	}
}
