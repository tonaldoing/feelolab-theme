<?php
/**
 * Contacto en la home: formulario + datos. Ancla #contacto para los botones.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'feelo_contact_form' ) ) {
	return;
}
?>
<section class="section" id="contacto" aria-labelledby="contacto-title">
	<div class="container split split--contact">
		<div>
			<?php feelolab_section_header( (string) feelolab_home( 'contacto', 'title' ), (string) feelolab_home( 'contacto', 'text' ), 'contacto-title' ); ?>
			<?php feelolab_contact_list(); ?>
			<?php feelolab_socials(); ?>
		</div>
		<div><?php echo feelo_contact_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- el plugin escapa. ?></div>
	</div>
</section>
