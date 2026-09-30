# Feelolab: starter WordPress de Feelo

Base para armar rápido el sitio de un cliente: se instala, se cargan los datos del negocio y la marca, y queda listo. Tiene dos piezas:

| Carpeta | Qué es | Se copia a |
|---|---|---|
| `feelolab/` | **Tema** clásico: diseño, plantillas PHP, Personalizador (marca y home por secciones) | `wp-content/themes/feelolab` |
| `feelolab-core/` | **Plugin compañero**: tipos de contenido, taxonomías, campos, Ajustes del sitio, schema, formulario de contacto | `wp-content/plugins/feelolab-core` |

El contenido vive en el plugin y no en el tema. Si mañana el cliente cambia de diseño, no pierde sus servicios ni sus productos.

**Requisitos:** WordPress 6.8 o superior (probado en 7.1), PHP 8.1 o superior y [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) **free** (o Secure Custom Fields). Sin ACF el sitio funciona igual, pero no se pueden cargar los campos extra (precio, cargo, horarios…).

## Puesta en marcha de un cliente nuevo

1. Bajar `feelolab.zip` y `feelolab-core.zip` del [último release](https://github.com/tonaldoing/feelolab-releases/releases/latest) y subirlos desde *Temas → Añadir nuevo → Subir* y *Plugins → Añadir nuevo → Subir*. Instalar también ACF free.
2. Activar el plugin y el tema.
3. **Ajustes del sitio → Módulos**: prender solo lo que el cliente usa.
4. **Ajustes del sitio**: completar negocio, contacto, redes, legal y formulario.
5. **Apariencia → Personalizar → Marca**: logo (en Identidad del sitio), colores, tipografía y esquinas.
6. **Personalizar → Secciones de la home**: prender, ordenar y completar las secciones.
7. Crear la página "Contacto" con la plantilla **Contacto**, la de privacidad, y armar los menús (principal, pie y legal).

## Actualizaciones

El tema y el plugin se actualizan solos, **sin configurar nada en cada cliente**. Aparecen en *Escritorio → Actualizaciones* como cualquier plugin de wordpress.org: botón "Actualizar", changelog ("Ver detalles") y la opción de activar actualizaciones automáticas. El estado se ve arriba en *Ajustes del sitio*.

Cómo está armado:
- **Este repo (privado)** tiene el código fuente.
- **[`tonaldoing/feelolab-releases`](https://github.com/tonaldoing/feelolab-releases) (público)** tiene solo los releases con los zips. Los sitios consultan ahí, sin credenciales.
- Al subir la versión y pushear a `main`, `.github/workflows/release.yml` publica el release en el repo público. Usa el secret `RELEASES_TOKEN`: un token fine-grained con *Contents: Read and write* solo sobre `feelolab-releases`. Ese token vive en GitHub, nunca en los sitios.

Reglas para que las actualizaciones no rompan nada:
- **Nunca editar el tema ni el plugin en el sitio del cliente**: la próxima actualización pisa los cambios. Lo propio de un cliente va en un **tema hijo** o en un plugin aparte.
- La carpeta del tema se llama `feelolab` siempre: los ajustes del Personalizador se guardan con ese nombre.
- El plugin tiene que estar activo para que el tema reciba actualizaciones (el actualizador vive ahí).
- Los zips publicados son GPL como el tema: lo que se vende es el servicio (armado, soporte, mantenimiento).

## Qué trae

**Módulos de contenido** (se activan uno por uno): Servicios, Productos (catálogo con consulta), Proyectos, Equipo, Testimonios, Preguntas frecuentes, Sedes y Clientes. Cada uno tiene sus taxonomías, sus campos de ACF free (definidos en PHP y versionados), su tarjeta, su ficha y su schema.

**Galería** para productos, proyectos y sedes: meta box con la biblioteca de medios y orden por arrastre (reemplaza el campo Gallery de ACF Pro). En productos se muestra como visor con miniaturas, que funciona sin JS; en proyectos y sedes, como grilla.

**/llms.txt**: resumen en markdown del negocio y su contenido para asistentes con IA (ChatGPT, Perplexity, Gemini). Se arma solo con lo cargado y se desactiva en *Ajustes del sitio → Integraciones*.

**Patrones de bloques** (en el editor, pestaña Patrones → FeeloLab): introducción con botones, texto con imagen, misión/visión/valores, tabla de precios, pasos, equipo, cifras, preguntas frecuentes, llamada a la acción, newsletter y una landing completa. Usan estilos de bloque propios (Tarjeta, Tarjeta destacada, Fondo suave, Franja de marca, Volanta, Dato grande, Bajada, Lista con tildes) cuyo CSS se carga solo en las páginas que los usan.

**Newsletter**: formulario inline (`[feelo_newsletter]`, sección de la home o patrón) conectado a Brevo o Mailchimp, con doble opt-in opcional. Sin servicio, o si el servicio falla, la suscripción queda en Mensajes. Mismo antispam que el formulario de contacto y evento `sign_up` en GA4.

**Mapa con fachada** en la página de contacto y en cada sede: no carga nada de Google hasta que la persona toca "Ver mapa". Sin clave de API.

**Opciones de producto**: listas desplegables ("Plataforma: Acrílico | Metálica") cuya elección viaja en el mensaje de WhatsApp o del formulario.

**Fuente propia de la marca**: se suben los .woff2 desde Personalizar (títulos, textos y negrita) y se sirven desde el propio sitio con precarga.

**Primeros pasos**: al activar el plugin se abre un asistente de cinco pasos (negocio, contacto, marca, contenidos y páginas) que crea Inicio, Contacto y Blog, arma el menú, activa las direcciones amigables y borra el contenido de ejemplo. Los sitios ya configurados no lo ven.

**Versiones**: pantalla con la versión instalada y el changelog, canal beta opcional para un sitio de pruebas y botón para volver a una versión anterior (tema y plugin juntos).

**WooCommerce**: si está activo, la tienda toma el diseño del tema (grilla, ficha, carrito, checkout de bloques y mi cuenta con los colores de la marca), un solo `<main>` y carrito con cantidad en el encabezado. El CSS extra carga solo en las páginas de la tienda.

**Importador de productos**: CSV de Excel o Google Sheets con vista previa, importación por tandas (sin tiempos de espera) y actualización por código/SKU. Fotos por link (se bajan una sola vez) o por nombre de archivo de la Biblioteca; categorías jerárquicas con "Padre > Hijo".

**Privacidad**: exportación y borrado de datos personales integrados con WordPress, borrado automático de mensajes por antigüedad, texto sugerido para la política de privacidad y desinstalación limpia.

**Traducciones**: `.pot` de tema y plugin, y traducción completa al inglés.

**Lanzamiento**: checklist de 19 puntos con barra de progreso (Ajustes del sitio → Lanzamiento) y resumen en el Escritorio. Cada punto explica por qué importa y lleva a la pantalla donde se arregla. Se pueden sumar puntos con el filtro `feelo_launch_checks`.

**Cookies y medición**: banner opcional con Google Consent Mode v2 (todo denegado por defecto, Aceptar y Rechazar con el mismo peso, reabrible desde el pie). Con GA4 o GTM cargados se envían solos los eventos `click_whatsapp`, `click_phone`, `click_email` y `generate_lead`, cada uno con `feelo_location`.

**Ajustes del sitio**: una página con la Settings API nativa que reemplaza la Options Page de ACF Pro. Los datos del negocio se cargan una vez y aparecen en el header, el footer, la página de contacto, el botón de WhatsApp y el schema.

**Personalizador**:
- **Colores con contraste AA garantizado.** El tema calcula solo el color de texto sobre los botones y oscurece el tono de los links si hace falta. Mientras elegís un color, aparece un aviso en vivo si el contraste no alcanza.
- Cinco combinaciones tipográficas con fuentes del sistema, que no descargan nada, y cuatro con **fuentes web servidas desde el propio sitio** (Inter, Figtree, Fraunces + Inter, Source Serif + Figtree). Las fuentes web vienen con fallback de métricas ajustadas: el texto no salta al cargar (CLS 0). Licencias OFL en `feelolab/assets/fonts/`.
- Home con 13 secciones que se prenden y se ordenan arrastrando (con flechas para teclado): hero, servicios, nosotros, cifras, proyectos, testimonios, logos, FAQ, blog, CTA, contacto y dos repetidas (segunda CTA y segundo bloque de texto con imagen). Los cambios de texto, imágenes, colores y tipografía se ven al instante, sin recargar la vista previa.
- Portada en tres diseños: texto e imagen lado a lado, imagen de fondo a todo el ancho (velo parejo que garantiza el contraste del texto con cualquier foto) o solo texto centrado.
- Encabezado: logo a la izquierda o centrado, barra superior con contacto y redes, buscador, y transparente sobre la portada de imagen de fondo (con logo claro opcional). Sin salto de contenido al cargar.
- Pie del color de la marca o claro, con columnas de widgets opcionales.
- Blog: tiempo de lectura, botones para compartir sin scripts de terceros (y el menú nativo del teléfono) y recuadro del autor cuando tiene biografía. Compartir se mide como evento `share` de GA4.

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
