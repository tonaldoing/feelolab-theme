<?php
/**
 * Solo desarrollo: productos de ejemplo de WooCommerce (blueprint-woo.json).
 */
require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
if ( ! class_exists( 'WC_Product_Simple' ) ) {
	echo "sin WooCommerce\n";
	return;
}
$feelo_colors = array( array( 36, 71, 216 ), array( 214, 222, 255 ), array( 255, 228, 214 ), array( 200, 230, 210 ) );
$feelo_items  = array(
	array( 'Implante cónico 3,5 mm', '45000', '39900', 'Implante de titanio grado 4.' ),
	array( 'Pilar recto', '18500', '', 'Pilar para prótesis cementada.' ),
	array( 'Tornillo de cicatrización', '3200', '', 'Tornillo de titanio.' ),
	array( 'Kit quirúrgico', '120000', '', 'Kit completo con fresas.' ),
);
foreach ( $feelo_items as $feelo_i => $feelo_item ) {
	$feelo_product = new WC_Product_Simple();
	$feelo_product->set_name( $feelo_item[0] );
	$feelo_product->set_regular_price( $feelo_item[1] );
	if ( $feelo_item[2] ) {
		$feelo_product->set_sale_price( $feelo_item[2] );
	}
	$feelo_product->set_description( '<p>' . $feelo_item[3] . '</p>' );
	$feelo_product->set_short_description( $feelo_item[3] );
	$feelo_upload = wp_upload_dir();
	$feelo_file   = trailingslashit( $feelo_upload['path'] ) . 'woo-' . $feelo_i . '.png';
	$feelo_img    = imagecreatetruecolor( 800, 800 );
	imagefill( $feelo_img, 0, 0, imagecolorallocate( $feelo_img, ...$feelo_colors[ $feelo_i ] ) );
	imagepng( $feelo_img, $feelo_file );
	$feelo_att = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => $feelo_item[0], 'post_status' => 'inherit' ), $feelo_file );
	wp_update_attachment_metadata( $feelo_att, wp_generate_attachment_metadata( $feelo_att, $feelo_file ) );
	update_post_meta( $feelo_att, '_wp_attachment_image_alt', $feelo_item[0] );
	$feelo_product->set_image_id( $feelo_att );
	$feelo_product->save();
}
echo "ok productos woo\n";
