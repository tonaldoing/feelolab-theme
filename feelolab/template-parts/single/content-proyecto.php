<?php
/**
 * Ficha de proyecto: datos clave (cliente, año, resultado) y contenido.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_facts = array_filter(
	array(
		__( 'Cliente', 'feelolab' )   => (string) feelolab_field( 'cliente' ),
		__( 'Año', 'feelolab' )       => (string) feelolab_field( 'anio' ),
		__( 'Resultado', 'feelolab' ) => (string) feelolab_field( 'resultado' ),
	)
);
$feelolab_url   = (string) feelolab_field( 'url' );

get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html( get_the_title() ) ) );
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="entry__hero"><?php the_post_thumbnail( 'feelolab-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></figure>
		<?php endif; ?>
		<div class="split split--aside">
			<div class="entry-content prose"><?php the_content(); ?></div>
			<?php if ( $feelolab_facts || $feelolab_url ) : ?>
				<aside class="sticky-box" aria-label="<?php esc_attr_e( 'Datos del proyecto', 'feelolab' ); ?>">
					<dl class="facts">
						<?php foreach ( $feelolab_facts as $feelolab_label => $feelolab_value ) : ?>
							<dt><?php echo esc_html( $feelolab_label ); ?></dt>
							<dd><?php echo esc_html( $feelolab_value ); ?></dd>
						<?php endforeach; ?>
					</dl>
					<?php echo feelolab_button( __( 'Ver el proyecto', 'feelolab' ), $feelolab_url, 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</article>
<?php
get_template_part( 'template-parts/related' );
