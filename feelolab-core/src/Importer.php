<?php
/**
 * Importador de productos desde un CSV (Excel o Google Sheets → Guardar como CSV).
 *
 * 1. Se sube el archivo y se muestra una vista previa: qué columna va a qué campo y las
 *    primeras filas, antes de tocar nada.
 * 2. Se importa de a tandas chicas (las fotos se bajan de internet y tardan), con barra de
 *    progreso: no hay tiempo de espera del servidor aunque sean cientos de productos.
 * 3. Si un producto ya existe (mismo código/SKU, o mismo slug o título) se actualiza en lugar
 *    de duplicarse: se puede corregir el CSV y volver a importar.
 * 4. Al final, un resumen con lo creado, lo actualizado y los errores fila por fila.
 *
 * Columnas reconocidas (el orden no importa, mayúsculas y acentos tampoco): titulo, descripcion,
 * resumen, precio, precio_oferta, sku, disponible, ficha_tecnica, opciones, categorias, marca,
 * imagenes, slug, orden. Ver la plantilla descargable.
 *
 * @package Feelo\Core
 */

namespace Feelo\Core;

use Feelo\Core\Modules\Registry;
use Feelo\Core\Settings\SiteSettings;

defined( 'ABSPATH' ) || exit;

final class Importer {

	public const PAGE = 'feelo-importar';

	private const JOB   = 'feelo_import_job';
	private const BATCH = 3;

	/** Campo => nombres de columna aceptados (ya normalizados). */
	private const COLUMNS = array(
		'titulo'        => array( 'titulo', 'nombre', 'title', 'name', 'producto' ),
		'descripcion'   => array( 'descripcion', 'contenido', 'description', 'content' ),
		'resumen'       => array( 'resumen', 'bajada', 'excerpt', 'descripcion_corta', 'short_description' ),
		'precio'        => array( 'precio', 'price', 'precio_normal', 'regular_price' ),
		'precio_oferta' => array( 'precio_oferta', 'oferta', 'sale_price', 'precio_promocional' ),
		'sku'           => array( 'sku', 'codigo', 'code', 'referencia' ),
		'disponible'    => array( 'disponible', 'stock', 'en_stock', 'available', 'in_stock' ),
		'ficha_tecnica' => array( 'ficha_tecnica', 'ficha', 'caracteristicas', 'specs' ),
		'opciones'      => array( 'opciones', 'variantes', 'options' ),
		'categorias'    => array( 'categorias', 'categoria', 'category', 'categories', 'rubro' ),
		'marca'         => array( 'marca', 'marcas', 'brand', 'brands' ),
		'imagenes'      => array( 'imagenes', 'imagen', 'fotos', 'foto', 'images', 'image' ),
		'slug'          => array( 'slug', 'url' ),
		'orden'         => array( 'orden', 'order', 'menu_order' ),
	);

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'menu' ), 13 );
		add_action( 'admin_post_feelo_import_upload', array( self::class, 'upload' ) );
		add_action( 'admin_post_feelo_import_cancel', array( self::class, 'cancel' ) );
		add_action( 'admin_post_feelo_import_template', array( self::class, 'template' ) );
		add_action( 'wp_ajax_feelo_import_batch', array( self::class, 'batch' ) );
	}

	public static function menu(): void {
		add_submenu_page(
			SiteSettings::PAGE,
			__( 'Importar productos', 'feelolab-core' ),
			__( 'Importar productos', 'feelolab-core' ),
			'manage_options',
			self::PAGE,
			array( self::class, 'render' )
		);
	}

	private static function url( array $args = array() ): string {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . self::PAGE ) );
	}


	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$job = get_option( self::JOB );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo elige qué aviso mostrar.
		$error = isset( $_GET['feelo_error'] ) ? sanitize_key( wp_unslash( $_GET['feelo_error'] ) ) : '';
		?>
		<div class="wrap">
			<?php
			Branding::header(
				__( 'Importar productos', 'feelolab-core' ),
				__( 'Cargá muchos productos de una vez desde una planilla. Si un producto ya existe, se actualiza: podés corregir la planilla y volver a importar.', 'feelolab-core' )
			);
			Branding::nav( 'importar' );

			if ( ! Registry::post_type( 'productos' ) ) {
				echo '<div class="feelo-card"><p>' . esc_html__( 'Primero activá el módulo Productos.', 'feelolab-core' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=feelo-modulos' ) ) . '">' . esc_html__( 'Ir a Módulos', 'feelolab-core' ) . '</a></p></div></div>';
				return;
			}
			if ( $error ) {
				$messages = array(
					'archivo' => __( 'No llegó ningún archivo, o no es un CSV. En Excel o Google Sheets: Archivo → Descargar / Guardar como → CSV.', 'feelolab-core' ),
					'titulo'  => __( 'La planilla no tiene una columna "titulo" (o "nombre"). Es la única obligatoria: fijate que esté en la primera fila.', 'feelolab-core' ),
					'vacio'   => __( 'La planilla no tiene filas con productos debajo de los títulos de columna.', 'feelolab-core' ),
				);
				echo '<div class="notice notice-error"><p>' . esc_html( $messages[ $error ] ?? __( 'No se pudo leer el archivo.', 'feelolab-core' ) ) . '</p></div>';
			}

			if ( is_array( $job ) && ! empty( $job['finished'] ) ) {
				self::render_summary( $job );
			} elseif ( is_array( $job ) ) {
				self::render_preview( $job );
			} else {
				self::render_upload();
			}
			?>
		</div>
		<?php
	}

	private static function render_upload(): void {
		?>
		<section class="feelo-card" aria-labelledby="feelo-import-title">
			<h2 class="feelo-card__title" id="feelo-import-title"><span class="dashicons dashicons-upload" aria-hidden="true"></span><?php esc_html_e( 'Subí la planilla', 'feelolab-core' ); ?></h2>
			<ol class="feelo-import-steps">
				<li>
					<?php esc_html_e( 'Bajá la plantilla y completala en Excel o Google Sheets (una fila por producto).', 'feelolab-core' ); ?>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=feelo_import_template' ), 'feelo_import_template' ) ); ?>"><?php esc_html_e( 'Descargar plantilla', 'feelolab-core' ); ?></a>
				</li>
				<li><?php esc_html_e( 'Guardala como CSV (Archivo → Descargar → CSV).', 'feelolab-core' ); ?></li>
				<li><?php esc_html_e( 'Subila acá. Antes de importar vas a ver cómo se leyó.', 'feelolab-core' ); ?></li>
			</ol>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="feelo_import_upload">
				<?php wp_nonce_field( 'feelo_import_upload' ); ?>
				<p>
					<label for="feelo-import-file"><strong><?php esc_html_e( 'Archivo CSV', 'feelolab-core' ); ?></strong></label><br>
					<input type="file" id="feelo-import-file" name="feelo_csv" accept=".csv,text/csv" required>
				</p>
				<p class="description">
					<?php esc_html_e( 'Las fotos pueden ser links (https://…) o nombres de archivos que ya estén en la Biblioteca de medios. Varias fotos se separan con |; la primera es la principal.', 'feelolab-core' ); ?>
				</p>
				<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Subir y revisar', 'feelolab-core' ); ?></button></p>
			</form>
		</section>
		<?php
	}

	/** @param array<string, mixed> $job */
	private static function render_preview( array $job ): void {
		$labels = self::field_labels();
		$rows   = self::read_rows( $job, 0, 5 );
		?>
		<section class="feelo-card" aria-labelledby="feelo-import-title">
			<h2 class="feelo-card__title" id="feelo-import-title"><span class="dashicons dashicons-visibility" aria-hidden="true"></span>
				<?php
				/* translators: %d: cantidad de productos */
				echo esc_html( sprintf( _n( 'Revisá antes de importar: %d producto', 'Revisá antes de importar: %d productos', (int) $job['total'], 'feelolab-core' ), (int) $job['total'] ) );
				?>
			</h2>

			<h3><?php esc_html_e( 'Columnas', 'feelolab-core' ); ?></h3>
			<ul class="feelo-import-columns">
				<?php foreach ( $job['headers'] as $i => $header ) : ?>
					<?php $field = array_search( $i, $job['map'], true ); ?>
					<li class="<?php echo $field ? 'is-used' : 'is-ignored'; ?>">
						<strong><?php echo esc_html( '' !== $header ? $header : '—' ); ?></strong>
						→ <?php echo esc_html( $field ? $labels[ $field ] : __( 'no se usa', 'feelolab-core' ) ); ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<h3><?php esc_html_e( 'Primeras filas', 'feelolab-core' ); ?></h3>
			<div class="feelo-import-table">
				<table class="widefat striped">
					<thead><tr>
						<th scope="col"><?php esc_html_e( 'Título', 'feelolab-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Precio', 'feelolab-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Código', 'feelolab-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Categorías', 'feelolab-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Fotos', 'feelolab-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Qué va a pasar', 'feelolab-core' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<?php $existing = self::find_existing( $row ); ?>
							<tr>
								<td><?php echo esc_html( $row['titulo'] ?? '' ); ?></td>
								<td><?php echo esc_html( trim( ( $row['precio'] ?? '' ) . ( ! empty( $row['precio_oferta'] ) ? ' → ' . $row['precio_oferta'] : '' ) ) ); ?></td>
								<td><?php echo esc_html( $row['sku'] ?? '' ); ?></td>
								<td><?php echo esc_html( implode( ', ', self::split_list( $row['categorias'] ?? '' ) ) ); ?></td>
								<td><?php echo esc_html( (string) count( self::split_images( $row['imagenes'] ?? '' ) ) ); ?></td>
								<td><?php echo esc_html( '' === trim( $row['titulo'] ?? '' ) ? __( 'Se saltea: sin título', 'feelolab-core' ) : ( $existing ? __( 'Actualiza el existente', 'feelolab-core' ) : __( 'Crea uno nuevo', 'feelolab-core' ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="feelo-import-run" data-feelo-import data-nonce="<?php echo esc_attr( wp_create_nonce( 'feelo_import_batch' ) ); ?>" data-total="<?php echo esc_attr( (string) $job['total'] ); ?>">
				<div class="feelo-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) self::percent( $job ) ); ?>" aria-label="<?php esc_attr_e( 'Progreso de la importación', 'feelolab-core' ); ?>" hidden>
					<span style="width:<?php echo esc_attr( (string) self::percent( $job ) ); ?>%"></span>
				</div>
				<p class="feelo-import-status" role="status"></p>
				<p class="feelo-import-actions">
					<button type="button" class="button button-primary feelo-import-start">
						<?php
						echo esc_html(
							(int) $job['done'] > 0
								/* translators: 1: importados, 2: total */
								? sprintf( __( 'Seguir importando (%1$d de %2$d)', 'feelolab-core' ), (int) $job['done'], (int) $job['total'] )
								/* translators: %d: cantidad */
								: sprintf( _n( 'Importar %d producto', 'Importar %d productos', (int) $job['total'], 'feelolab-core' ), (int) $job['total'] )
						);
						?>
					</button>
					<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=feelo_import_cancel' ), 'feelo_import_cancel' ) ); ?>"><?php esc_html_e( 'Cancelar y subir otro archivo', 'feelolab-core' ); ?></a>
				</p>
				<p class="description"><?php esc_html_e( 'No cierres esta pestaña mientras importa. Si se corta, volvé a esta pantalla y seguí desde donde quedó.', 'feelolab-core' ); ?></p>
			</div>
		</section>
		<?php
		self::script();
	}

	/** @param array<string, mixed> $job */
	private static function render_summary( array $job ): void {
		$type = (string) Registry::post_type( 'productos' );
		?>
		<section class="feelo-card" aria-labelledby="feelo-import-title">
			<h2 class="feelo-card__title" id="feelo-import-title" tabindex="-1"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Importación terminada', 'feelolab-core' ); ?></h2>
			<ul class="feelo-import-totals">
				<?php /* translators: %d: cantidad */ ?>
				<li><?php echo esc_html( sprintf( _n( '%d producto nuevo', '%d productos nuevos', (int) $job['created'], 'feelolab-core' ), (int) $job['created'] ) ); ?></li>
				<?php /* translators: %d: cantidad */ ?>
				<li><?php echo esc_html( sprintf( _n( '%d actualizado', '%d actualizados', (int) $job['updated'], 'feelolab-core' ), (int) $job['updated'] ) ); ?></li>
				<?php /* translators: %d: cantidad */ ?>
				<li><?php echo esc_html( sprintf( _n( '%d aviso', '%d avisos', count( $job['errors'] ), 'feelolab-core' ), count( $job['errors'] ) ) ); ?></li>
			</ul>
			<?php if ( $job['errors'] ) : ?>
				<h3><?php esc_html_e( 'Para revisar', 'feelolab-core' ); ?></h3>
				<ul class="feelo-import-errors">
					<?php foreach ( array_slice( $job['errors'], 0, 200 ) as $message ) : ?>
						<li><?php echo esc_html( $message ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $type ) ); ?>"><?php esc_html_e( 'Ver los productos', 'feelolab-core' ); ?></a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=feelo_import_cancel' ), 'feelo_import_cancel' ) ); ?>"><?php esc_html_e( 'Importar otra planilla', 'feelolab-core' ); ?></a>
			</p>
		</section>
		<?php
	}

	private static function script(): void {
		$i18n = array(
			/* translators: 1: procesados, 2: total */
			'progress' => __( 'Importando: %1$d de %2$d…', 'feelolab-core' ),
			'error'    => __( 'Se cortó la conexión. Tocá "Seguir importando" para continuar desde donde quedó.', 'feelolab-core' ),
			'resume'   => __( 'Seguir importando', 'feelolab-core' ),
		);
		?>
		<script>
		(function () {
			var box = document.querySelector('[data-feelo-import]');
			if (!box) { return; }
			var i18n = <?php echo wp_json_encode( $i18n ); ?>;
			var start = box.querySelector('.feelo-import-start');
			var bar = box.querySelector('.feelo-progress');
			var status = box.querySelector('.feelo-import-status');
			function paint(done, total) {
				var pct = total ? Math.round(done * 100 / total) : 100;
				bar.hidden = false;
				bar.setAttribute('aria-valuenow', pct);
				bar.firstElementChild.style.width = pct + '%';
				status.textContent = i18n.progress.replace('%1$d', done).replace('%2$d', total);
			}
			function step() {
				var body = new FormData();
				body.append('action', 'feelo_import_batch');
				body.append('_ajax_nonce', box.getAttribute('data-nonce'));
				fetch(ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
					.then(function (res) {
						if (!res.success) { throw new Error(res.data || 'error'); }
						paint(res.data.done, res.data.total);
						if (res.data.finished) { location.reload(); } else { step(); }
					})
					.catch(function (err) {
						console.error('feelolab import:', err.message);
						status.textContent = i18n.error;
						start.disabled = false;
						start.textContent = i18n.resume;
					});
			}
			start.addEventListener('click', function () {
				start.disabled = true;
				paint(0, parseInt(box.getAttribute('data-total'), 10));
				step();
			});
		})();
		</script>
		<?php
	}


	public static function upload(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permisos para importar.', 'feelolab-core' ), 403 );
		}
		check_admin_referer( 'feelo_import_upload' );

		$file = isset( $_FILES['feelo_csv'] ) && is_array( $_FILES['feelo_csv'] ) ? $_FILES['feelo_csv'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se valida abajo (extensión, subida real).
		$name = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		$tmp  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';
		if ( ! $tmp || ! is_uploaded_file( $tmp ) || 'csv' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			self::back( 'archivo' );
		}

		// Se guarda con nombre al azar fuera de la vista pública (y sin extensión ejecutable).
		$dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'feelo-import';
		wp_mkdir_p( $dir );
		if ( ! file_exists( $dir . '/index.php' ) ) {
			file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
		$path = $dir . '/' . wp_generate_password( 20, false ) . '.csv';
		if ( ! move_uploaded_file( $tmp, $path ) ) {
			self::back( 'archivo' );
		}
		self::normalize_encoding( $path );

		$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$first  = (string) fgets( $handle );
		$delim  = self::detect_delimiter( $first );
		rewind( $handle );
		$headers = fgetcsv( $handle, 0, $delim, '"', '' );
		$offset  = ftell( $handle );
		$total   = 0;
		while ( false !== ( $line = fgetcsv( $handle, 0, $delim, '"', '' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( array_filter( $line, static fn( $v ) => '' !== trim( (string) $v ) ) ) {
				++$total;
			}
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		$headers = array_map( static fn( $h ) => trim( (string) $h ), (array) $headers );
		$map     = self::map_columns( $headers );
		if ( ! isset( $map['titulo'] ) ) {
			wp_delete_file( $path );
			self::back( 'titulo' );
		}
		if ( ! $total ) {
			wp_delete_file( $path );
			self::back( 'vacio' );
		}

		update_option(
			self::JOB,
			array(
				'file'     => $path,
				'delim'    => $delim,
				'headers'  => $headers,
				'map'      => $map,
				'offset'   => $offset,
				'row'      => 1,
				'total'    => $total,
				'done'     => 0,
				'created'  => 0,
				'updated'  => 0,
				'errors'   => array(),
				'finished' => false,
			),
			false
		);
		wp_safe_redirect( self::url() );
		exit;
	}

	public static function cancel(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permisos para importar.', 'feelolab-core' ), 403 );
		}
		check_admin_referer( 'feelo_import_cancel' );
		$job = get_option( self::JOB );
		if ( is_array( $job ) && ! empty( $job['file'] ) && file_exists( $job['file'] ) ) {
			wp_delete_file( $job['file'] );
		}
		delete_option( self::JOB );
		wp_safe_redirect( self::url() );
		exit;
	}

	/** Plantilla con los títulos de columna y dos productos de ejemplo. UTF-8 con BOM: Excel la abre bien. */
	public static function template(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permisos para importar.', 'feelolab-core' ), 403 );
		}
		check_admin_referer( 'feelo_import_template' );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="plantilla-productos.csv"' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		$rows = array(
			array( 'titulo', 'descripcion', 'resumen', 'precio', 'precio_oferta', 'sku', 'disponible', 'ficha_tecnica', 'opciones', 'categorias', 'marca', 'imagenes' ),
			array( 'Implante cónico 3,5 mm', 'Implante de titanio grado 4 con superficie tratada.', 'Titanio grado 4, conexión cónica.', '$ 45.000', '', 'IMP-35', 'si', "Material: Titanio grado 4\nLargo: 10 mm", 'Plataforma: Estrecha | Regular | Ancha', 'Implantes > Cónicos', 'Clonadent', 'https://ejemplo.com/foto-1.jpg|https://ejemplo.com/foto-2.jpg' ),
			array( 'Pilar recto', 'Pilar para prótesis cementada.', '', '$ 18.500', '$ 15.900', 'PIL-01', 'si', 'Material: Titanio', 'Altura: 2 mm | 3 mm | 4 mm', 'Pilares', 'Clonadent', 'pilar-recto.jpg' ),
		);
		foreach ( $rows as $row ) {
			fputcsv( $out, $row, ',', '"', '' );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/** Procesa una tanda. Responde el avance; el navegador pide la siguiente. */
	public static function batch(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'permisos', 403 );
		}
		check_ajax_referer( 'feelo_import_batch' );
		$job = get_option( self::JOB );
		if ( ! is_array( $job ) || ! file_exists( (string) $job['file'] ) ) {
			wp_send_json_error( 'sin-importacion' );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 120 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- tanda con descargas de fotos.
		}

		$handle = fopen( $job['file'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fseek( $handle, (int) $job['offset'] );
		$processed = 0;
		while ( $processed < self::BATCH && false !== ( $line = fgetcsv( $handle, 0, $job['delim'], '"', '' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			++$job['row'];
			if ( ! array_filter( $line, static fn( $v ) => '' !== trim( (string) $v ) ) ) {
				continue; // Fila vacía: no cuenta.
			}
			++$processed;
			++$job['done'];
			$result = self::import_row( self::row_to_fields( $line, $job['map'] ), (int) $job['row'] );
			if ( 'created' === $result['status'] ) {
				++$job['created'];
			} elseif ( 'updated' === $result['status'] ) {
				++$job['updated'];
			}
			$job['errors'] = array_merge( $job['errors'], $result['errors'] );
		}
		$job['offset']   = ftell( $handle );
		$job['finished'] = feof( $handle ) || $job['done'] >= $job['total'];
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( $job['finished'] ) {
			wp_delete_file( $job['file'] );
		}
		update_option( self::JOB, $job, false );
		wp_send_json_success(
			array(
				'done'     => (int) $job['done'],
				'total'    => (int) $job['total'],
				'finished' => (bool) $job['finished'],
			)
		);
	}


	/**
	 * @param array<string, string> $row  Campos de la fila.
	 * @param int                   $line Número de fila en la planilla (para los avisos).
	 * @return array{status: string, errors: string[]}
	 */
	public static function import_row( array $row, int $line ): array {
		$errors = array();
		/* translators: %d: número de fila */
		$prefix = sprintf( __( 'Fila %d:', 'feelolab-core' ), $line ) . ' ';
		$title  = trim( $row['titulo'] ?? '' );
		if ( '' === $title ) {
			return array(
				'status' => 'skipped',
				'errors' => array( $prefix . __( 'no tiene título, se salteó.', 'feelolab-core' ) ),
			);
		}
		$type     = (string) Registry::post_type( 'productos' );
		$existing = self::find_existing( $row );
		$post     = array(
			'post_type'   => $type,
			'post_title'  => $title,
			'post_status' => 'publish',
		);
		if ( isset( $row['descripcion'] ) ) {
			$post['post_content'] = wp_kses_post( self::paragraphs( $row['descripcion'] ) );
		}
		if ( isset( $row['resumen'] ) ) {
			$post['post_excerpt'] = sanitize_textarea_field( $row['resumen'] );
		}
		if ( ! empty( $row['slug'] ) ) {
			$post['post_name'] = sanitize_title( $row['slug'] );
		}
		if ( isset( $row['orden'] ) && '' !== $row['orden'] ) {
			$post['menu_order'] = (int) $row['orden'];
		}
		if ( $existing ) {
			$post['ID'] = $existing;
			unset( $post['post_status'] ); // No se republica un borrador a propósito.
		}
		$id = $existing ? wp_update_post( $post, true ) : wp_insert_post( $post, true );
		if ( is_wp_error( $id ) ) {
			return array(
				'status' => 'error',
				'errors' => array( $prefix . $id->get_error_message() ),
			);
		}

		foreach ( array( 'precio', 'precio_oferta', 'sku', 'ficha_tecnica', 'opciones' ) as $field ) {
			if ( isset( $row[ $field ] ) ) {
				self::save_field( $id, $field, 'ficha_tecnica' === $field || 'opciones' === $field ? sanitize_textarea_field( $row[ $field ] ) : sanitize_text_field( $row[ $field ] ) );
			}
		}
		if ( isset( $row['disponible'] ) && '' !== trim( $row['disponible'] ) ) {
			self::save_field( $id, 'disponible', self::truthy( $row['disponible'] ) ? 1 : 0 );
		} elseif ( ! $existing ) {
			self::save_field( $id, 'disponible', 1 );
		}

		if ( isset( $row['categorias'] ) ) {
			self::set_terms( $id, 'feelo_producto_cat', $row['categorias'] );
		}
		if ( isset( $row['marca'] ) && taxonomy_exists( 'feelo_marca' ) ) {
			wp_set_object_terms( $id, self::split_list( $row['marca'] ), 'feelo_marca' );
		}

		if ( ! empty( $row['imagenes'] ) ) {
			$ids = array();
			foreach ( self::split_images( $row['imagenes'] ) as $source ) {
				$image = self::image( $source, $id, $title );
				if ( is_wp_error( $image ) ) {
					/* translators: 1: foto, 2: motivo */
					$errors[] = $prefix . sprintf( __( 'no se pudo usar la foto "%1$s" (%2$s).', 'feelolab-core' ), $source, $image->get_error_message() );
					continue;
				}
				$ids[] = $image;
			}
			if ( $ids ) {
				set_post_thumbnail( $id, $ids[0] );
				$gallery = array_slice( $ids, 1 );
				if ( $gallery ) {
					update_post_meta( $id, Gallery::META, $gallery );
				} else {
					delete_post_meta( $id, Gallery::META );
				}
			}
		}

		return array(
			'status' => $existing ? 'updated' : 'created',
			'errors' => $errors,
		);
	}

	/** Mismo producto: por código, si no por slug, si no por título exacto. */
	private static function find_existing( array $row ): int {
		$type = (string) Registry::post_type( 'productos' );
		if ( ! $type ) {
			return 0;
		}
		$base = array(
			'post_type'      => $type,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);
		if ( ! empty( $row['sku'] ) ) {
			$found = get_posts(
				$base + array(
					'meta_key'   => 'sku', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- importación, a pedido.
					'meta_value' => trim( $row['sku'] ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			return $found ? (int) $found[0] : 0;
		}
		if ( ! empty( $row['slug'] ) ) {
			$found = get_posts( $base + array( 'name' => sanitize_title( $row['slug'] ) ) );
			if ( $found ) {
				return (int) $found[0];
			}
		}
		$title = trim( $row['titulo'] ?? '' );
		if ( '' === $title ) {
			return 0;
		}
		$found = get_posts( $base + array( 'title' => $title ) );
		return $found ? (int) $found[0] : 0;
	}

	/** Guarda un campo con ACF si está (así queda asociado al campo) o como meta simple. */
	private static function save_field( int $post_id, string $name, $value ): void {
		if ( function_exists( 'update_field' ) ) {
			update_field( 'field_feelo_productos_' . $name, $value, $post_id );
			return;
		}
		update_post_meta( $post_id, $name, $value );
	}

	/** Categorías: separadas por coma o |; "Padre > Hijo" crea la jerarquía. */
	private static function set_terms( int $post_id, string $taxonomy, string $value ): void {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}
		$ids = array();
		foreach ( self::split_list( $value ) as $path ) {
			$parent = 0;
			foreach ( array_filter( array_map( 'trim', explode( '>', $path ) ) ) as $name ) {
				$term = term_exists( $name, $taxonomy, $parent );
				if ( ! $term ) {
					$term = wp_insert_term( $name, $taxonomy, array( 'parent' => $parent ) );
				}
				if ( is_wp_error( $term ) ) {
					error_log( 'feelolab-core: importación, no se pudo crear la categoría "' . $name . '": ' . $term->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					break;
				}
				$parent = (int) $term['term_id'];
			}
			if ( $parent ) {
				$ids[] = $parent;
			}
		}
		wp_set_object_terms( $post_id, $ids, $taxonomy );
	}

	/**
	 * Foto: un link se baja a la Biblioteca (una sola vez: si ya se bajó antes, se reusa); un nombre
	 * de archivo se busca en la Biblioteca.
	 *
	 * @return int|\WP_Error ID del adjunto.
	 */
	private static function image( string $source, int $post_id, string $title ) {
		$source = trim( $source );
		if ( preg_match( '#^https?://#i', $source ) ) {
			$found = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_key'       => '_feelo_source_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- importación, a pedido.
					'meta_value'     => esc_url_raw( $source ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			if ( $found ) {
				return (int) $found[0];
			}
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$id = media_sideload_image( esc_url_raw( $source ), $post_id, $title, 'id' );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			update_post_meta( (int) $id, '_feelo_source_url', esc_url_raw( $source ) );
			if ( ! get_post_meta( (int) $id, '_wp_attachment_image_alt', true ) ) {
				update_post_meta( (int) $id, '_wp_attachment_image_alt', $title );
			}
			return (int) $id;
		}
		// Por nombre sin extensión: con WebP activo, "foto.jpg" queda guardada como "foto.webp".
		$stem       = strtolower( pathinfo( sanitize_file_name( basename( $source ) ), PATHINFO_FILENAME ) );
		$candidates = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 20,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- importación, a pedido.
					array(
						'key'     => '_wp_attached_file',
						'value'   => $stem,
						'compare' => 'LIKE',
					),
				),
			)
		);
		foreach ( $candidates as $candidate ) {
			$name = strtolower( pathinfo( (string) get_post_meta( $candidate, '_wp_attached_file', true ), PATHINFO_FILENAME ) );
			if ( $name === $stem || $name === $stem . '-scaled' ) {
				return (int) $candidate;
			}
		}
		return new \WP_Error( 'feelo_no_image', __( 'no está en la Biblioteca de medios', 'feelolab-core' ) );
	}


	/** Excel en Windows guarda en Windows-1252: se pasa a UTF-8. También se quita el BOM. */
	private static function normalize_encoding( string $path ): void {
		$content = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- archivo local recién subido.
		$fixed   = str_starts_with( $content, "\xEF\xBB\xBF" ) ? substr( $content, 3 ) : $content;
		if ( ! mb_check_encoding( $fixed, 'UTF-8' ) ) {
			$fixed = mb_convert_encoding( $fixed, 'UTF-8', 'Windows-1252' );
		}
		if ( $fixed !== $content ) {
			file_put_contents( $path, $fixed ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	/** Coma, punto y coma (Excel en español) o tabulación: la que más aparece en la primera fila. */
	public static function detect_delimiter( string $line ): string {
		$counts = array(
			','  => substr_count( $line, ',' ),
			';'  => substr_count( $line, ';' ),
			"\t" => substr_count( $line, "\t" ),
		);
		arsort( $counts );
		return (string) array_key_first( $counts );
	}

	/** "Título ", "PRECIO OFERTA", "Ficha técnica" → titulo, precio_oferta, ficha_tecnica. */
	public static function normalize_header( string $header ): string {
		$header = strtolower( remove_accents( trim( $header ) ) );
		return trim( (string) preg_replace( '/[^a-z0-9]+/', '_', $header ), '_' );
	}

	/**
	 * @param string[] $headers Primera fila.
	 * @return array<string, int> Campo => índice de columna.
	 */
	public static function map_columns( array $headers ): array {
		$map = array();
		foreach ( $headers as $i => $header ) {
			$key = self::normalize_header( $header );
			foreach ( self::COLUMNS as $field => $aliases ) {
				if ( ! isset( $map[ $field ] ) && in_array( $key, $aliases, true ) ) {
					$map[ $field ] = $i;
					break;
				}
			}
		}
		return $map;
	}

	/**
	 * @param string[]           $line Fila.
	 * @param array<string, int> $map  Campo => índice.
	 * @return array<string, string>
	 */
	public static function row_to_fields( array $line, array $map ): array {
		$row = array();
		foreach ( $map as $field => $i ) {
			$row[ $field ] = trim( (string) ( $line[ $i ] ?? '' ) );
		}
		return $row;
	}

	/** @return array<int, array<string, string>> Filas desde el inicio (para la vista previa). */
	private static function read_rows( array $job, int $skip, int $limit ): array {
		$rows   = array();
		$handle = fopen( $job['file'], 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $handle ) {
			return $rows;
		}
		fgetcsv( $handle, 0, $job['delim'], '"', '' );
		$count = 0;
		while ( $count < $limit && false !== ( $line = fgetcsv( $handle, 0, $job['delim'], '"', '' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( ! array_filter( $line, static fn( $v ) => '' !== trim( (string) $v ) ) ) {
				continue;
			}
			if ( $skip > 0 ) {
				--$skip;
				continue;
			}
			$rows[] = self::row_to_fields( $line, $job['map'] );
			++$count;
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $rows;
	}

	/** @return string[] */
	public static function split_list( string $value ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/[|,]/', $value ) ), 'strlen' ) );
	}

	/** @return string[] Fotos separadas por |, coma o salto de línea. */
	public static function split_images( string $value ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/[|,\n]+/', $value ) ), 'strlen' ) );
	}

	public static function truthy( string $value ): bool {
		return in_array( self::normalize_header( $value ), array( '1', 'si', 'yes', 'true', 'verdadero', 'x', 'disponible', 'en_stock', 'hay' ), true );
	}

	/** Texto plano con saltos de línea → párrafos; si ya trae HTML, se deja. */
	private static function paragraphs( string $text ): string {
		return str_contains( $text, '<' ) ? $text : wpautop( $text );
	}

	/** @param array<string, mixed> $job */
	private static function percent( array $job ): int {
		return $job['total'] ? (int) round( 100 * $job['done'] / $job['total'] ) : 0;
	}

	/** @return array<string, string> */
	private static function field_labels(): array {
		return array(
			'titulo'        => __( 'Título', 'feelolab-core' ),
			'descripcion'   => __( 'Descripción', 'feelolab-core' ),
			'resumen'       => __( 'Resumen', 'feelolab-core' ),
			'precio'        => __( 'Precio', 'feelolab-core' ),
			'precio_oferta' => __( 'Precio de oferta', 'feelolab-core' ),
			'sku'           => __( 'Código / SKU', 'feelolab-core' ),
			'disponible'    => __( 'Disponible', 'feelolab-core' ),
			'ficha_tecnica' => __( 'Ficha técnica', 'feelolab-core' ),
			'opciones'      => __( 'Opciones', 'feelolab-core' ),
			'categorias'    => __( 'Categorías', 'feelolab-core' ),
			'marca'         => __( 'Marca', 'feelolab-core' ),
			'imagenes'      => __( 'Fotos', 'feelolab-core' ),
			'slug'          => __( 'Slug (dirección)', 'feelolab-core' ),
			'orden'         => __( 'Orden', 'feelolab-core' ),
		);
	}

	/** @return never */
	private static function back( string $error ): void {
		wp_safe_redirect( self::url( array( 'feelo_error' => $error ) ) );
		exit;
	}
}
