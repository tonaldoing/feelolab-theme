<?php
/**
 * Newsletter en la home: franja con el formulario inline (se configura en Ajustes del sitio → Newsletter).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'feelo_newsletter_form' ) ) {
	return;
}
?>
<section class="section section--surface" id="newsletter" aria-labelledby="newsletter-title">
	<div class="container newsletter-band">
		<div>
			<h2 class="section__title" id="newsletter-title"><?php echo esc_html( feelolab_home( 'newsletter', 'title' ) ); ?></h2>
			<?php if ( feelolab_home( 'newsletter', 'text' ) ) : ?>
				<p class="newsletter-band__text"><?php echo esc_html( feelolab_home( 'newsletter', 'text' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php echo feelo_newsletter_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- el plugin escapa. ?>
	</div>
</section>
