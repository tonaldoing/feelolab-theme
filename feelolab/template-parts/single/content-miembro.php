<?php
/**
 * Ficha de integrante del equipo.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_role  = (string) feelolab_field( 'cargo' );
$feelolab_links = array_filter(
	array(
		'linkedin'  => (string) feelolab_field( 'linkedin' ),
		'instagram' => (string) feelolab_field( 'instagram' ),
	)
);
$feelolab_email = (string) feelolab_field( 'email' );
?>
<article <?php post_class( 'entry' ); ?>>
	<div class="container section">
		<?php feelolab_breadcrumbs(); ?>
		<div class="person">
			<div class="person__media">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'feelolab-square', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) );
				}
				?>
			</div>
			<div>
				<h1 class="person__name"><?php the_title(); ?></h1>
				<?php if ( $feelolab_role ) : ?>
					<p class="person__role"><?php echo esc_html( $feelolab_role ); ?></p>
				<?php endif; ?>
				<div class="entry-content prose"><?php the_content(); ?></div>
				<?php if ( $feelolab_links || $feelolab_email ) : ?>
					<ul class="socials">
						<?php if ( $feelolab_email ) : ?>
							<li><a href="<?php echo esc_url( 'mailto:' . antispambot( $feelolab_email ) ); ?>"><?php echo feelolab_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php esc_html_e( 'Email', 'feelolab' ); ?></span></a></li>
						<?php endif; ?>
						<?php foreach ( $feelolab_links as $feelolab_net => $feelolab_link ) : ?>
							<li><a href="<?php echo esc_url( $feelolab_link ); ?>" target="_blank" rel="noopener"><?php echo feelolab_icon( $feelolab_net ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="screen-reader-text"><?php echo esc_html( ucfirst( $feelolab_net ) . ' ' . __( '(se abre en otra pestaña)', 'feelolab' ) ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</article>
