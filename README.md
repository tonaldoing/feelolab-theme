# Feelolab: starter WordPress de Feelo

Base para armar rápido el sitio de un cliente: se instala, se cargan los datos del negocio y la marca, y queda listo. Tiene dos piezas:

| Carpeta | Qué es | Se copia a |
|---|---|---|
| `feelolab/` | **Tema** clásico: diseño, plantillas PHP, Personalizador (marca y home por secciones) | `wp-content/themes/feelolab` |
| `feelolab-core/` | **Plugin compañero**: tipos de contenido, taxonomías, campos, Ajustes del sitio, schema, formulario de contacto | `wp-content/plugins/feelolab-core` |

El contenido vive en el plugin y no en el tema. Si mañana el cliente cambia de diseño, no pierde sus servicios ni sus productos.

**Requisitos:** WordPress 6.8 o superior (probado en 7.1), PHP 8.1 o superior y [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) **free** (o Secure Custom Fields). Sin ACF el sitio funciona igual, pero no se pueden cargar los campos extra (precio, cargo, horarios…).

## Puesta en marcha de un cliente nuevo

1. Bajar `feelolab.zip` y `feelolab-core.zip` del [último release](https://github.com/tonaldoing/feelolab-theme/releases/latest) y subirlos desde *Temas → Añadir nuevo → Subir* y *Plugins → Añadir nuevo → Subir*. Instalar también ACF free.
2. Agregar el token de actualizaciones en `wp-config.php` (ver abajo).
3. Activar el plugin y el tema.
4. **Ajustes del sitio → Módulos**: prender solo lo que el cliente usa.
5. **Ajustes del sitio**: completar negocio, contacto, redes, legal y formulario.
6. **Apariencia → Personalizar → Marca**: logo (en Identidad del sitio), colores, tipografía y esquinas.
7. **Personalizar → Secciones de la home**: prender, ordenar y completar las secciones.
8. Crear la página "Contacto" con la plantilla **Contacto**, la de privacidad, y armar los menús (principal, pie y legal).

## Actualizaciones

El tema y el plugin se actualizan desde los **Releases de GitHub**. Aparecen en *Escritorio → Actualizaciones* como cualquier plugin de wordpress.org: botón "Actualizar", changelog ("Ver detalles") y la opción de activar actualizaciones automáticas. El estado se ve arriba en *Ajustes del sitio*.

Como el repo es privado, cada sitio necesita un token de solo lectura en `wp-config.php`, antes de la línea `/* That's all, stop editing! */`:

```php
define( 'FEELO_GITHUB_TOKEN', 'github_pat_…' );
```

Para crearlo: GitHub → *Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token*.
- *Repository access*: **Only select repositories** → `feelolab-theme`.
- *Permissions → Repository permissions → Contents*: **Read-only**. Nada más.
- Poné una fecha de vencimiento y agendá renovarlo. Un solo token sirve para todos los clientes; si se filtra, solo da lectura a este repo y se revoca en un clic.

Si algún día el repo pasa a ser público, el token deja de hacer falta.

Reglas para que las actualizaciones no rompan nada:
- **Nunca editar el tema ni el plugin en el sitio del cliente**: la próxima actualización pisa los cambios. Lo propio de un cliente va en un **tema hijo** o en un plugin aparte.
- La carpeta del tema se llama `feelolab` siempre: los ajustes del Personalizador se guardan con ese nombre.
- El plugin tiene que estar activo para que el tema reciba actualizaciones (el actualizador vive ahí).

## Qué trae

**Módulos de contenido** (se activan uno por uno): Servicios, Productos (catálogo con consulta), Proyectos, Equipo, Testimonios, Preguntas frecuentes, Sedes y Clientes. Cada uno tiene sus taxonomías, sus campos de ACF free (definidos en PHP y versionados), su tarjeta, su ficha y su schema.

**Galería** para productos, proyectos y sedes: meta box con la biblioteca de medios y orden por arrastre (reemplaza el campo Gallery de ACF Pro). En productos se muestra como visor con miniaturas, que funciona sin JS; en proyectos y sedes, como grilla.

**/llms.txt**: resumen en markdown del negocio y su contenido para asistentes con IA (ChatGPT, Perplexity, Gemini). Se arma solo con lo cargado y se desactiva en *Ajustes del sitio → Integraciones*.

**Ajustes del sitio**: una página con la Settings API nativa que reemplaza la Options Page de ACF Pro. Los datos del negocio se cargan una vez y aparecen en el header, el footer, la página de contacto, el botón de WhatsApp y el schema.

**Personalizador**:
- **Colores con contraste AA garantizado.** El tema calcula solo el color de texto sobre los botones y oscurece el tono de los links si hace falta. Mientras elegís un color, aparece un aviso en vivo si el contraste no alcanza.
- Cinco combinaciones tipográficas con fuentes del sistema, que no descargan nada, y cuatro con **fuentes web servidas desde el propio sitio** (Inter, Figtree, Fraunces + Inter, Source Serif + Figtree). Las fuentes web vienen con fallback de métricas ajustadas: el texto no salta al cargar (CLS 0). Licencias OFL en `feelolab/assets/fonts/`.
- Home con 11 secciones que se prenden y se ordenan: hero, servicios, nosotros, cifras, proyectos, testimonios, logos, FAQ, blog, CTA y contacto.

**Formulario de contacto propio** (`[feelo_formulario]` o `feelo_contact_form()`):
- Sin plugin y sin costo. Antispam con honeypot, tiempo mínimo de llenado firmado y límite por IP. Opcionalmente, Cloudflare Turnstile, que es gratis.
- No usa nonce, a propósito: con caché de página los nonces vencen y el formulario falla en silencio.
- Cada mensaje se guarda en **Mensajes** antes de mandar el email: si el correo falla, no se pierde.
- Errores accesibles: resumen con foco, `aria-invalid` y links a cada campo.
- Para que los emails no caigan en spam, instalar un plugin SMTP (FluentSMTP o WP Mail SMTP, ambos gratis) con el correo del cliente o con Brevo (plan gratuito).

**SEO**: JSON-LD de Organization o LocalBusiness con horarios y dirección, WebSite, Service, Product, FAQPage, Person, BlogPosting y BreadcrumbList. También meta description, Open Graph, migas de pan, un solo h1 por página y slugs en español. Si detecta Yoast, Rank Math, AIOSEO, SEOPress o The SEO Framework, apaga sus propios meta y su schema principal para no duplicar.

## Performance, accesibilidad y SEO: cómo se mide

Resultados medidos sobre la demo en WordPress 7 (Playground):

- **Lighthouse mobile: 100 / 100 / 100 / 100** (performance, accesibilidad, buenas prácticas, SEO). La home pesa 79 KB en total, con LCP de 1,3 s, CLS 0 y TBT 0 ms.
- **axe-core (WCAG 2.2 AA + best practices): 0 violaciones** en 10 plantillas, en 390 px y en 1280 px.
- **pa11y-ci: 9/9.**
- Probado con teclado: skip link, menú, submenús (se cierran con Escape y el foco vuelve) y formulario.

Cómo lo logra:
- Sin jQuery, sin frameworks. Un CSS (27 KB, 6 KB comprimido) y un JS de ~1 KB diferido.
- Fuentes del sistema: cero requests de tipografía y cero saltos de layout.
- Se sacan emojis, oEmbed, dashicons para visitantes y jquery-migrate. El CSS de bloques se carga solo si el bloque aparece en la página.
- La imagen principal (LCP) va con `fetchpriority="high"` y tamaños de imagen a medida con `srcset`.
- FAQ con `<details>` nativo y testimonios en grilla: nada de carruseles.

## Desarrollo

```bash
composer install     # PHPCS + WordPress Coding Standards
npm install          # WordPress Playground + pa11y

npm run dev          # WordPress en http://127.0.0.1:9400 con ACF y contenido demo (sin Docker)
composer lint        # WPCS
npm run a11y         # pa11y-ci contra el Playground levantado
```

`dev/demo-content.php` carga el contenido de ejemplo. No forma parte del tema ni del plugin.

Para probar en una app local (Local, MAMP…): copiá o enlazá con un symlink `feelolab/` en `themes/` y `feelolab-core/` en `plugins/`.

El CI (`.github/workflows/ci.yml`) corre la sintaxis en PHP 8.1, 8.3 y 8.4, WPCS y pa11y contra WordPress real en cada push.

## Extender sin tocar el core

| Filtro | Para qué |
|---|---|
| `feelo_modules` | Agregar o modificar tipos de contenido |
| `feelo_module_slug` | Cambiar el slug de URL de un módulo (`servicios` → `que-hacemos`) |
| `feelo_schema_graph` | Ajustar el JSON-LD antes de imprimirlo |
| `feelo_seo_plugin_active` | Forzar o desactivar la detección de plugin SEO |
| `feelolab_home_sections` | Agregar secciones a la home (más su `template-parts/home/{clave}.php`) |
| `feelolab_font_stacks` / `feelolab_web_fonts` | Sumar combinaciones tipográficas o fuentes web propias |
| `feelo_gallery_modules` | Qué módulos tienen galería |
| `feelo_llms_txt` / `feelo_llms_txt_enabled` | Ajustar o apagar /llms.txt |
| `feelolab_cleanup` | Reactivar emojis, embeds, etc. |
| `feelolab_breadcrumb_trail` | Modificar las migas |

Para un cliente con necesidades propias, lo recomendado es un **tema hijo**, no editar el tema.

## Próximas fases

- **Fase 3:** asistente de configuración inicial e importador de contenido demo desde el admin.
- **Fase 4:** plantillas compatibles con WooCommerce.
