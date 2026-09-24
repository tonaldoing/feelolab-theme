<?php
/**
 * Página.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-header', null, array( 'title' => esc_html( get_the_title() ) ) );
	?>
	<div class="container section">
		<div class="entry-content prose">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
