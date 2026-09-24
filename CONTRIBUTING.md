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
- `npm run dev` + `npm run a11y` (pa11y, 0 errores). Si tocás plantillas, correr también Lighthouse mobile.
- Textos de UI en español rioplatense, con text domain `feelolab` (tema) o `feelolab-core` (plugin).

### Publicar una versión (llega a todos los sitios)
1. Anotar los cambios en `CHANGELOG.md` bajo `## X.Y.Z — fecha`.
2. Subir la versión en los cuatro lugares: `feelolab/style.css` (Version), `FEELOLAB_VERSION` en `feelolab/functions.php`, y el header Version y `FEELO_CORE_VERSION` en `feelolab-core/feelolab-core.php`. Tema y plugin van siempre con la misma versión.
3. Commit y push a `main`. No hace falta crear el tag a mano.
4. `.github/workflows/release.yml` detecta que esa versión no tiene release en `tonaldoing/feelolab-releases` (público), verifica que las cuatro versiones coincidan, arma los zips, publica el release allá y deja el tag `vX.Y.Z` acá. Necesita el secret `RELEASES_TOKEN`. También se puede correr a mano desde Actions → Release → Run workflow. El workflow también publica `update.json`, que es lo que leen los sitios. Lo ven en hasta 12 horas (el chequeo automático de WordPress), o al instante con "Buscar de nuevo" en Actualizaciones.
- Nunca renombrar la carpeta `feelolab`: los theme_mods de cada sitio están guardados con ese nombre.
- Cambios que rompen datos guardados (claves de opciones, metas, slugs de CPT) necesitan una migración, nunca un rename silencioso.
