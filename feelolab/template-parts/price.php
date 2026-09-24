<?php
/**
 * Precio con oferta: el tachado se anuncia con texto ("Antes:"), no solo con el estilo.
 *
 * @package Feelolab
 *
 * @var array{price: string, offer: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_price = $args['price'] ?? '';
$feelolab_offer = $args['offer'] ?? '';
if ( ! $feelolab_price && ! $feelolab_offer ) {
	return;
}
?>
<p class="price">
	<?php if ( $feelolab_offer && $feelolab_price ) : ?>
		<del><span class="screen-reader-text"><?php esc_html_e( 'Antes:', 'feelolab' ); ?> </span><?php echo esc_html( $feelolab_price ); ?></del>
		<ins><span class="screen-reader-text"><?php esc_html_e( 'Ahora:', 'feelolab' ); ?> </span><?php echo esc_html( $feelolab_offer ); ?></ins>
	<?php else : ?>
		<span><?php echo esc_html( $feelolab_offer ? $feelolab_offer : $feelolab_price ); ?></span>
	<?php endif; ?>
</p>
