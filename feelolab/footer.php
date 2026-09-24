<?php
/**
 * Pie: marca, contacto, menú, redes y línea legal.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_name  = function_exists( 'feelo_business_name' ) ? feelo_business_name() : get_bloginfo( 'name' );
$feelolab_text  = (string) get_theme_mod( 'feelolab_footer_text', '' );
$feelolab_legal = trim( implode( ' · ', array_filter( array( feelolab_setting( 'razon_social' ), feelolab_setting( 'id_fiscal' ) ? 'CUIT ' . feelolab_setting( 'id_fiscal' ) : '' ) ) ) );
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<p class="site-footer__name"><?php echo esc_html( $feelolab_name ); ?></p>
			<?php if ( $feelolab_text ) : ?>
				<p><?php echo esc_html( $feelolab_text ); ?></p>
			<?php endif; ?>
			<?php feelolab_socials(); ?>
		</div>

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Pie de página', 'feelolab' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="site-footer__contact">
			<?php feelolab_contact_list(); ?>
		</div>
	</div>

	<div class="container site-footer__bottom">
		<p>
			&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $feelolab_name ); ?>
			<?php if ( $feelolab_legal ) : ?>
				· <?php echo esc_html( $feelolab_legal ); ?>
			<?php endif; ?>
		</p>
		<?php
		if ( has_nav_menu( 'legal' ) ) {
			wp_nav_menu(
				array(
					'theme_location'       => 'legal',
					'container'            => 'nav',
					'container_aria_label' => __( 'Legal', 'feelolab' ),
					'menu_class'           => 'legal-menu',
					'depth'                => 1,
				)
			);
		} elseif ( get_privacy_policy_url() ) {
			the_privacy_policy_link( '<p>', '</p>' );
		}
		?>
		<?php if ( get_theme_mod( 'feelolab_footer_credit', true ) ) : ?>
			<p class="site-footer__credit">
				<?php esc_html_e( 'Sitio hecho por', 'feelolab' ); ?>
				<a href="https://www.feelolab.com" target="_blank" rel="noopener">FeeloLab<span class="screen-reader-text"> <?php esc_html_e( '(se abre en otra pestaña)', 'feelolab' ); ?></span></a>
			</p>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
