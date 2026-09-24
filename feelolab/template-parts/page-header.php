<?php
/**
 * Cabecera de página interna: migas, un solo h1 y bajada opcional.
 *
 * @package Feelolab
 *
 * @var array{title?: string, lead?: string} $args
 */

defined( 'ABSPATH' ) || exit;

$feelolab_title = $args['title'] ?? '';
$feelolab_lead  = $args['lead'] ?? '';
?>
<header class="page-header">
	<div class="container">
		<?php feelolab_breadcrumbs(); ?>
		<h1 class="page-header__title"><?php echo wp_kses_post( $feelolab_title ); ?></h1>
		<?php if ( $feelolab_lead ) : ?>
			<div class="page-header__lead"><?php echo wp_kses_post( $feelolab_lead ); ?></div>
		<?php endif; ?>
	</div>
</header>
