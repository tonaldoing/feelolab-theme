# Changelog

Cada versión publicada acá llega a los sitios como actualización (Escritorio → Actualizaciones).

## 0.8.1 — 2026-09-30

- Importar productos reconoce la exportación de WooCommerce tal como sale (Productos → Exportar): ahora también toma las columnas de stock y marcas.

## 0.8.0 — 2026-09-30

- Asistente de primeros pasos: al instalar, cinco pasos (negocio, contacto, marca, contenidos y páginas) dejan el sitio armado. Crea las páginas Inicio, Contacto y Blog, arma el menú, activa las direcciones amigables, elige la zona horaria y borra el contenido de ejemplo. Los sitios que ya estaban configurados no lo ven. Está en Ajustes del sitio → Primeros pasos.
- Nueva pantalla Versiones (Ajustes del sitio → Versiones): muestra la versión instalada y qué trae la última, y permite volver a una versión anterior si una actualización trae un problema. Tema y plugin vuelven juntos; los ajustes y el contenido no se tocan.
- Canal beta opcional: un sitio de pruebas puede recibir las versiones nuevas antes que los clientes.
- WooCommerce: si está instalado, la tienda, la ficha de producto, el carrito, el checkout y Mi cuenta toman el diseño y los colores del sitio, y el encabezado muestra el carrito con la cantidad.
- Google: los listados (servicios, sedes, tienda) tienen una descripción con el nombre del negocio. Antes, en servicios y otros listados se mostraba el texto interno del panel.
- Arreglo: los cambios hechos desde el asistente y desde Versiones no se guardaban en Ajustes del sitio.

## 0.7.0 — 2026-09-30

- Importador de productos desde una planilla (Ajustes del sitio → Importar productos): se sube un CSV de Excel o Google Sheets, se revisa cómo se leyó y se importa con barra de progreso. Trae precio, oferta, código, disponibilidad, ficha técnica, opciones, categorías (con subcategorías), marca y fotos (por link o por nombre de archivo de la Biblioteca). Si el producto ya existe, se actualiza. Hay una plantilla para descargar.
- Fotos en WebP: las imágenes que se suben se guardan en WebP, que pesa entre 25 y 35 % menos. El original queda guardado aparte. Se apaga en Ajustes del sitio → Integraciones.
- Datos personales: los mensajes y suscripciones se pueden exportar o borrar desde Herramientas → Exportar / Borrar datos personales, y se pueden borrar solos a los 6, 12 o 24 meses (Ajustes del sitio → Formulario).
- Texto sugerido para la política de privacidad, armado con lo que el sitio usa (Ajustes → Privacidad → guía).
- Desinstalación limpia: al borrar el plugin se borran sus ajustes; los mensajes, solo si se eligió; el contenido nunca.
- Traducción al inglés del tema y del plugin: con WordPress en inglés, el sitio y el panel salen en inglés.
- El tema recibe actualizaciones aunque el plugin esté desactivado.
- Arreglo de contraste: con botones de tono medio (por ejemplo gris), el texto del botón ahora siempre alcanza el mínimo de legibilidad.
- Tests automáticos y control de Lighthouse en cada cambio del código, para detectar errores antes de publicar.

## 0.6.0 — 2026-09-30

- Patrones de bloques para armar páginas interiores en minutos: introducción con botones, texto con imagen, misión, visión y valores, tabla de precios, cómo trabajamos, equipo, cifras, preguntas frecuentes, llamada a la acción, newsletter y una landing completa. Están en el editor, en Patrones → FeeloLab.
- Estilos nuevos para los bloques: tarjeta, tarjeta destacada, fondo suave, franja de marca, volanta, dato grande, bajada y lista con tildes. Siempre con los colores de la marca y el contraste asegurado.
- Newsletter: formulario de suscripción conectado a Brevo o Mailchimp (Ajustes del sitio → Newsletter), con confirmación por email opcional. Se suma como sección de la home o con el patrón. Sin servicio conectado, las suscripciones quedan en Mensajes, y si el servicio falla también: no se pierde ninguna.
- Mapa en la página de contacto y en cada sede. No hace lenta la página: el mapa de Google se carga recién cuando la persona toca "Ver mapa".
- Opciones de producto para elegir antes de consultar (por ejemplo, plataforma y altura). Lo elegido va en el mensaje de WhatsApp o del formulario.
- Fuente propia de la marca: se suben los archivos .woff2 en Personalizar → Marca → Tipografía y forma.
- Las suscripciones al newsletter se miden como evento sign_up en Google Analytics.
- En una instalación nueva, los widgets de ejemplo de WordPress (en inglés) ya no aparecen en el pie.
- Las landings con la plantilla Ancho completo quedan alineadas con el encabezado y con aire entre secciones.

## 0.5.0 — 2026-09-30

- Portada con tres diseños a elegir: texto e imagen lado a lado, imagen de fondo a todo el ancho o solo texto centrado. Con imagen de fondo, un velo oscuro parejo asegura que el texto se lea con cualquier foto.
- Encabezado con más opciones: logo centrado con el menú debajo, barra superior con teléfono, WhatsApp, email y redes, buscador, y encabezado transparente sobre la portada con imagen de fondo (se puede cargar un logo claro para ese caso).
- Pie de página claro, además del que usa el color de la marca, y columnas extra con widgets (Apariencia → Widgets → Pie de página: columnas).
- Secciones de la home: ahora se ordenan arrastrándolas (o con flechas) en Personalizar → Secciones de la home → Orden de las secciones. El orden que ya tenías se respeta.
- Dos secciones nuevas para repetir: una segunda llamada a la acción y un segundo bloque de texto con imagen, que puede llevar la imagen a la izquierda.
- Vista previa al instante: los cambios de textos, imágenes, colores y tipografía se ven sin recargar la página.
- Blog: tiempo de lectura, botones para compartir (WhatsApp, Facebook, LinkedIn, X, email y copiar link) y recuadro del autor con su biografía. Cada opción se apaga en Personalizar → Marca → Blog.
- Los clics en compartir se miden como evento share en Google Analytics.

## 0.4.0 — 2026-09-30

- Nueva pantalla Lanzamiento (Ajustes del sitio → Lanzamiento): revisa 19 puntos antes de publicar (sitio visible para Google, direcciones amigables, logo, política de privacidad, datos de contacto, menú, portada, zona horaria, envío de emails, contenido de ejemplo borrado y más). Cada punto dice por qué importa y trae un botón Arreglar que lleva a la pantalla indicada.
- Resumen del lanzamiento en el Escritorio, con barra de progreso y lo más urgente primero.
- Banner de cookies opcional con el modo de consentimiento de Google (Ajustes del sitio → Integraciones). Aceptar y Rechazar pesan lo mismo, la elección se recuerda 180 días y se vuelve a abrir con Preferencias de cookies en el pie. Sin aceptar, Google mide sin guardar cookies.
- Medición de conversiones sin configurar nada: con Google Analytics o Tag Manager cargados se registran los clics en WhatsApp, teléfono y email, y cada formulario enviado (generate_lead), con la parte del sitio donde ocurrió.

## 0.3.1 — 2026-09-30

- La barra lateral del blog ahora se muestra en la portada del blog, en las categorías y etiquetas y en cada nota, cuando tiene widgets cargados (Apariencia → Widgets).
- Las notas del blog muestran notas relacionadas al final, como los servicios y los productos.
- Los resultados de búsqueda y los archivos con menos de dos contenidos le piden a Google que no los indexe (noindex, follow). Si hay un plugin de SEO instalado, decide él.
- Íconos de X y TikTok con los logos oficiales.
- El link de contacto se calcula una sola vez por página: menos consultas a la base.
- El botón "Publicar comentario" y el tilde de cookies de los comentarios usan el diseño del tema.

## 0.3.0 — 2026-09-30

- Nueva identidad de FeeloLab en las pantallas propias del panel (Ajustes del sitio, Módulos y Mensajes): cabecera con el degradado de la marca, el logotipo, la ardilla y accesos rápidos a Personalizar marca y Ver sitio.
- Estado de las actualizaciones siempre visible en la cabecera. Si algo falla, un botón lleva a Actualizaciones y el detalle queda debajo.
- Navegación entre Ajustes, Módulos y Mensajes con botones grandes.
- Ajustes del sitio: pestañas con íconos, el formulario en una tarjeta y la barra de guardado fija abajo, siempre a mano.
- Módulos: tarjetas con interruptores en lugar de una tabla.
- Mensajes: cuando todavía no llegó ninguno, lo avisa la ardilla del megáfono.
- Ícono propio de FeeloLab en el menú del panel y pie "Tu sitio, hecho por FeeloLab" en esas pantallas.
- Personalizar: los paneles Marca y Secciones de la home llevan la marca de FeeloLab.

## 0.2.7 — 2026-09-24

- El crédito del pie dice "Sitio hecho por FeeloLab" y enlaza a feelolab.com. Se sigue pudiendo ocultar desde Personalizar → Marca → Pie de página.

## 0.2.6 — 2026-09-24

- Colores separados en tres: fondo de los botones, texto de los botones y links. El texto del botón se puede elegir; vacío es automático (blanco o negro, el que mejor se lea).
- Si el texto elegido no se lee sobre el botón, el sitio usa el automático y el panel avisa.
- Un botón muy claro (por ejemplo, blanco sobre fondo blanco) lleva un borde para que se vea como botón.
- Si el color de los botones es muy claro para links, los links usan el color Secundario o el del texto, en lugar de un gris.
- Avisos más claros en Personalizar, cada uno en el color que lo causa.
- El crédito del pie, las cifras y la franja de llamada a la acción ya no usan transparencia en el texto: sobre algunos colores de fondo no se leían bien.

## 0.2.5 — 2026-09-24

- La página principal del tema y del plugin ahora es feelolab.com.
- El informe de cambios se ve con formato (listas y negritas) en lugar de mostrar los símbolos.

## 0.2.4 — 2026-09-24

- "Buscar de nuevo" ahora encuentra las versiones nuevas al instante. Antes el caché se borraba después de que WordPress consultaba, y una versión nueva podía tardar hasta un día en aparecer.
- El actualizador lee update.json desde la CDN de GitHub, que no tiene límite de consultas; la API de GitHub queda de respaldo. En un hosting compartido, la API podía cortar por límite de la IP.
- Caché de 1 hora en lugar de 12.

## 0.2.3 — 2026-09-24

- Miniatura del tema (screenshot.png) con la ardilla de FeeloLab.
- Nombre y autor con la marca: "FeeloLab" y "FeeloLab Core", con link a feelolab.com. La carpeta sigue siendo feelolab, así que no se pierde ningún ajuste.

## 0.2.2 — 2026-09-24

- Compatibilidad declarada con WordPress 7.1 (probado en 7.1.2).
- Primera versión que llega por el actualizador: si la ves en Escritorio → Actualizaciones, el circuito funciona.

## 0.2.1 — 2026-09-24

- Actualizaciones sin token: los zips se publican en el repo público tonaldoing/feelolab-releases y los sitios se actualizan desde ahí sin configurar nada. FEELO_GITHUB_TOKEN ya no hace falta.
- Si instalaste la 0.2.0, esta actualización hay que subirla a mano una única vez (la 0.2.0 buscaba en el repo privado).

## 0.2.0 — 2026-09-24

- Actualizaciones desde GitHub: el tema y el plugin aparecen en Escritorio → Actualizaciones, con changelog y actualización automática opcional.
- Galería para productos, proyectos y sedes (meta box con la biblioteca de medios, orden por arrastre). En productos, visor con miniaturas que funciona sin JS; en proyectos y sedes, grilla. Las imágenes se suman al schema del producto.
- Fuentes web opcionales servidas desde el propio sitio: Inter, Figtree, Fraunces + Inter y Source Serif + Figtree. Con fallback de métricas ajustadas: el texto no salta al cargar.
- /llms.txt: resumen del negocio y su contenido para asistentes con IA. Se desactiva en Ajustes del sitio → Integraciones.
- Meta description y resúmenes ya no pegan los párrafos ("fiscal.Ganancias").

## 0.1.0 — 2026-09-24

- Primera versión: tema clásico, plugin con módulos de contenido, Ajustes del sitio, schema, formulario de contacto propio.
