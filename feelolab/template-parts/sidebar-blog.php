<?php
/**
 * Barra lateral del blog (Apariencia → Widgets → "Barra lateral del blog"). Solo si tiene widgets.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'blog' ) ) {
	return;
}
?>
<aside class="blog-sidebar" aria-label="<?php esc_attr_e( 'Barra lateral del blog', 'feelolab' ); ?>">
	<?php dynamic_sidebar( 'blog' ); ?>
</aside>
