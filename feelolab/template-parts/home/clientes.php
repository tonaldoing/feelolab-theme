<?php
/**
 * Logos de clientes. El nombre del cliente es el alt del logo (el logo ES información).
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_q = new WP_Query(
	array(
		'post_type'      => feelo_module_post_type( 'clientes' ),
		'posts_per_page' => 24,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'no_found_rows'  => true,
	)
);
if ( ! $feelolab_q->have_posts() ) {
	return;
}
?>
<section class="section" id="clientes" aria-labelledby="clientes-title">
	<div class="container">
		<?php feelolab_section_header( (string) feelolab_home( 'clientes', 'title' ), '', 'clientes-title' ); ?>
		<ul class="logos">
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
