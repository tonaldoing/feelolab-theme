## Guía de desarrollo: Feelolab

Tema clásico (`feelolab/`) + plugin compañero (`feelolab-core/`). WordPress 6.8+ (probado en 7.0), PHP 8.1+, ACF **free**.

### Reglas de arquitectura
- **Contenido en el plugin, diseño en el tema.** CPTs, taxonomías, campos, ajustes del negocio, schema y formulario van en `feelolab-core`. El tema llama al plugin SIEMPRE detrás de `function_exists()` / `class_exists()` (o con los wrappers `feelolab_setting()` y `feelolab_field()`): sin el plugin, el sitio no se rompe.
- **Solo ACF free.** Nada de repeater, gallery, flexible content, options page ni ACF Blocks. Lo que sería un repeater es una relación entre CPTs. Los campos se definen en PHP (`src/Fields/FieldGroups.php`), nunca desde la UI. Se leen con `feelo_field()`, que cae a `get_post_meta()` sin ACF.
- CPTs y taxonomías se declaran como datos en `src/Modules/Registry.php`. Un módulo nuevo es una entrada ahí, más su tarjeta (`template-parts/cards/card-{tipo}.php`) y su ficha (`template-parts/single/content-{tipo}.php`). `{tipo}` es el post type sin `feelo_`.
- Home = secciones fijas del Personalizador (`inc/home.php` + `template-parts/home/{clave}.php`), no un constructor libre.

### Performance (no negociable)
- Sin jQuery, sin frameworks CSS/JS, sin fuentes externas. Presupuesto: CSS < 30 KB y JS < 15 KB sin comprimir.
- La imagen LCP de cada plantilla lleva `loading="eager"` + `fetchpriority="high"`. El resto queda lazy (por defecto de core).
- Consultas secundarias con `no_found_rows => true`.
- Antes de sumar un asset, que cargue solo donde se usa.

### Accesibilidad (WCAG 2.2 AA)
- Un solo h1 por página. Títulos de tarjeta: h2 en archivos o búsqueda y h3 dentro de secciones (`feelolab_card_heading( $args )`).
- Colores siempre desde las variables de `inc/colors.php`, que ya llegan con contraste resuelto. Nunca hardcodear un color de texto sobre `--c-primary`: usar `--c-on-primary`. Links sobre fondo: `--c-primary-text`.
- Íconos SVG con `aria-hidden`. El texto accesible va en el elemento contenedor. Links externos: `feelolab_link_attrs()` + `feelolab_external_hint()`.
- Tarjetas: UN solo link (el del título, estirado con `::after`), nunca links duplicados.
- Nada que dependa de hover. Acordeones con `<details>`. Sin carruseles automáticos.

### SEO
- El schema lo arma `feelolab-core/src/Schema/Schema.php`. La entidad principal, los meta y el OG se apagan si hay un plugin SEO (`feelo_seo_plugin_active()`). El schema por CPT sale siempre.
- Nunca inventar datos en el schema: un campo vacío se omite.

### Verificar antes de commitear
- `composer lint` (WPCS) tiene que pasar limpio.
- `composer test` (PHPUnit): contraste de colores, orden de secciones, importador, ajustes, asistente, newsletter, actualizaciones y changelog. Son tests sin WordPress (`tests/bootstrap.php` imita lo mínimo); si una función nueva es lógica pura, sumale su test.
- `npm run test:e2e` (con `npm run dev` levantado): formularios y antispam, banner de cookies y Consent Mode, eventos, importador, asistente, Personalizar en vivo y Versiones, en un navegador real. Usa el Chrome del sistema o el que diga `CHROME_PATH`. Si un cambio toca algo que se ve o se guarda, sumale su test en `tests/e2e/`.
- Nada de `<script>` escritos a mano: JS en archivos encolados, y lo mínimo que tiene que correr antes de pintar con `wp_print_inline_script_tag()`.
- `npm run dev` + `npm run a11y` (pa11y, 0 errores) + `npm run lighthouse` (performance ≥ 85 en CI, accesibilidad y SEO 100, CLS ≤ 0.1). El CI corre los tres en cada push.
- Textos de UI en español rioplatense, con text domain `feelolab` (tema) o `feelolab-core` (plugin).

### Traducciones
- El sitio viene en español y trae la traducción al inglés (`en_US`): con WordPress en inglés, el tema, el plugin, el panel y las direcciones (`/services/`, `/products/`) salen en inglés.
- Archivos: `feelolab/languages/` (`feelolab.pot`, `en_US.po/.mo/.l10n.php`) y `feelolab-core/languages/` (`feelolab-core.pot`, `feelolab-core-en_US.*`). Para otro idioma, copiar el `.pot`, traducir (Poedit o Loco Translate) y compilar.
- Si agregás o cambiás textos, regenerá antes de publicar con `npm run i18n` (necesita WP-CLI; o `WP="php wp-cli.phar" npm run i18n`): arma los `.pot`, actualiza los `.po`, y compila `.mo` y `.l10n.php`. Traducí en los `.po` lo que quedó con `msgstr ""` y volvé a correrlo.

### Publicar una versión (llega a todos los sitios)
1. Anotar los cambios en `CHANGELOG.md` bajo `## X.Y.Z — fecha`, en texto plano: una línea por cambio empezando con "- ", sin negritas ni comillas invertidas. Ese texto es el que ven los clientes en "Ver detalles".
2. Subir la versión en los cuatro lugares: `feelolab/style.css` (Version), `FEELOLAB_VERSION` en `feelolab/functions.php`, y el header Version y `FEELO_CORE_VERSION` en `feelolab-core/feelolab-core.php`. Tema y plugin van siempre con la misma versión.
3. Commit y push a `main`. No hace falta crear el tag a mano.
4. `.github/workflows/release.yml` detecta que esa versión no tiene release en `tonaldoing/feelolab-releases` (público), verifica que las cuatro versiones coincidan, arma los zips, publica el release allá y deja el tag `vX.Y.Z` acá. Necesita el secret `RELEASES_TOKEN`. También se puede correr a mano desde Actions → Release → Run workflow. El workflow también publica `update.json`, que es lo que leen los sitios. Lo ven en hasta 12 horas (el chequeo automático de WordPress), o al instante con "Buscar de nuevo" en Actualizaciones.
- Versión de prueba: usar un número con guion, por ejemplo `0.9.0-beta.1` (en los cuatro lugares). Se publica como "prerelease": solo la reciben los sitios con "Recibir versiones de prueba" (Ajustes del sitio → Versiones), que conviene tener en un sitio de pruebas. Cuando está bien, se publica la estable (`0.9.0`) y llega a todos, incluidos los de prueba.
- Si una versión trae un problema, cada sitio puede volver a la anterior desde Ajustes del sitio → Versiones (reinstala tema y plugin de la versión elegida; no toca ajustes ni contenido). Arreglar y publicar una versión nueva: nunca borrar un release publicado, es a donde vuelven los sitios.
- Nunca renombrar la carpeta `feelolab`: los theme_mods de cada sitio están guardados con ese nombre.
- Cambios que rompen datos guardados (claves de opciones, metas, slugs de CPT) necesitan una migración, nunca un rename silencioso.
