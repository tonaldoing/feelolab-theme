<?php
/**
 * Tarjeta de sede: dirección, teléfono y horarios. Los datos se pueden usar sin entrar a la ficha.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_address = trim( implode( ', ', array_filter( array( feelolab_field( 'direccion' ), feelolab_field( 'ciudad' ) ) ) ) );
$feelolab_phone   = (string) feelolab_field( 'telefono' );
$feelolab_map     = (string) feelolab_field( 'mapa_url' );
$feelolab_tag     = ( $args['heading'] ?? 'h3' ) === 'h2' ? 'h2' : 'h3';
$feelolab_hours   = function_exists( 'feelo_opening_hours' ) ? feelo_opening_hours( (string) feelolab_field( 'horarios' ) ) : array();
?>
<article class="card card--location">
	<div class="card__body">
		<<?php echo esc_attr( $feelolab_tag ); ?> class="card__title">
			<?php if ( is_singular() && get_queried_object_id() === get_the_ID() ) : ?>
				<?php esc_html_e( 'Datos de contacto', 'feelolab' ); ?>
			<?php else : ?>
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<?php endif; ?>
		</<?php echo esc_attr( $feelolab_tag ); ?>>
		<ul class="contact-list">
			<?php if ( $feelolab_address ) : ?>
				<li><?php echo feelolab_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><address><?php echo esc_html( $feelolab_address ); ?></address></li>
			<?php endif; ?>
			<?php if ( $feelolab_phone && function_exists( 'feelo_tel_href' ) ) : ?>
				<li><?php echo feelolab_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><a href="<?php echo esc_url( feelo_tel_href( $feelolab_phone ) ); ?>"><?php echo esc_html( $feelolab_phone ); ?></a></li>
			<?php endif; ?>
			<?php if ( $feelolab_hours ) : ?>
				<li><?php echo feelolab_icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo implode( '<br>', array_map( static fn( $h ) => esc_html( $h['text'] ), $feelolab_hours ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
			<?php endif; ?>
		</ul>
		<?php if ( $feelolab_map ) : ?>
			<a class="btn-link" href="<?php echo esc_url( $feelolab_map ); ?>" target="_blank" rel="noopener">
				<?php
				/* translators: %s: nombre de la sede */
				echo esc_html( sprintf( __( 'Cómo llegar a %s', 'feelolab' ), get_the_title() ) );
				?>
				<span class="screen-reader-text"><?php esc_html_e( '(se abre en otra pestaña)', 'feelolab' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</article>
