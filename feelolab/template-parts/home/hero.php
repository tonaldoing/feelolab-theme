<?php
/**
 * Portada. La imagen es el LCP de la home: eager + fetchpriority="high" + srcset a medida.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_image = (int) feelolab_home( 'hero', 'image' );
?>
<section class="hero<?php echo $feelolab_image ? ' hero--image' : ''; ?>">
	<div class="container hero__inner">
		<div class="hero__content">
			<h1 class="hero__title"><?php echo esc_html( feelolab_home( 'hero', 'title' ) ); ?></h1>
			<?php if ( feelolab_home( 'hero', 'text' ) ) : ?>
				<p class="hero__text"><?php echo esc_html( feelolab_home( 'hero', 'text' ) ); ?></p>
			<?php endif; ?>
			<div class="hero__actions">
				<?php
				echo feelolab_button( (string) feelolab_home( 'hero', 'cta1_text' ), (string) feelolab_home( 'hero', 'cta1_url' ), 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo feelolab_button( (string) feelolab_home( 'hero', 'cta2_text' ), (string) feelolab_home( 'hero', 'cta2_url' ), 'secondary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</div>
		<?php if ( $feelolab_image ) : ?>
			<div class="hero__media">
				<?php
				echo wp_get_attachment_image(
					$feelolab_image,
					'feelolab-hero',
					false,
					array(
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(min-width: 960px) 50vw, 100vw',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
