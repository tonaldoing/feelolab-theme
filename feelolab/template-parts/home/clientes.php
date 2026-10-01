<?php
/**
 * Logos de clientes. El nombre del cliente es el alt del logo (el logo ES información).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_q = new WP_Query( feelolab_home_query_args( 'clientes', (string) feelo_module_post_type( 'clientes' ) ) );
if ( ! $feelolab_q->have_posts() ) {
	feelolab_home_placeholder( 'clientes' );
	return;
}
?>
<section class="<?php echo esc_attr( feelolab_home_section_class( 'clientes' ) ); ?>" id="clientes" aria-labelledby="clientes-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'clientes', 'title' ), (string) feelolab_home( 'clientes', 'text' ), 'clientes-title' ); ?>
		<ul class="<?php echo esc_attr( feelolab_home( 'clientes', 'color' ) ? 'logos logos--color' : 'logos' ); ?>">
			<?php
			while ( $feelolab_q->have_posts() ) :
				$feelolab_q->the_post();
				if ( ! has_post_thumbnail() ) {
					continue;
				}
				$feelolab_url = (string) feelolab_field( 'url' );
				$feelolab_img = get_the_post_thumbnail( null, 'medium', array( 'alt' => get_the_title() ) );
				echo '<li>';
				echo $feelolab_url
					? '<a href="' . esc_url( $feelolab_url ) . '" target="_blank" rel="noopener">' . $feelolab_img . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					: $feelolab_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</li>';
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
