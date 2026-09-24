<?php
/**
 * Cifras destacadas. Lista de definiciones: el número y su significado quedan asociados.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

$feelolab_stats = array();
for ( $feelolab_n = 1; $feelolab_n <= 4; $feelolab_n++ ) {
	$feelolab_value = (string) feelolab_home( 'cifras', 'value_' . $feelolab_n );
	if ( '' !== $feelolab_value ) {
		$feelolab_stats[] = array( $feelolab_value, (string) feelolab_home( 'cifras', 'label_' . $feelolab_n ) );
	}
}
if ( ! $feelolab_stats ) {
	return;
}
$feelolab_title = (string) feelolab_home( 'cifras', 'title' );
?>
<section class="section section--dark" <?php echo $feelolab_title ? 'aria-labelledby="cifras-title"' : 'aria-label="' . esc_attr__( 'Cifras', 'feelolab' ) . '"'; ?>>
	<div class="container">
		<?php feelolab_section_header( $feelolab_title, '', 'cifras-title' ); ?>
		<dl class="stats">
			<?php foreach ( $feelolab_stats as $feelolab_stat ) : ?>
				<div class="stats__item">
					<dt class="stats__label"><?php echo esc_html( $feelolab_stat[1] ); ?></dt>
					<dd class="stats__value"><?php echo esc_html( $feelolab_stat[0] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>
