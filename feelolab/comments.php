<?php
/**
 * Comentarios.
 *
 * @package Feelolab
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section class="comments" id="comentarios" aria-labelledby="comentarios-title">
	<?php if ( have_comments() ) : ?>
		<h2 id="comentarios-title" class="comments__title">
			<?php
			/* translators: %d: cantidad de comentarios */
			echo esc_html( sprintf( _n( '%d comentario', '%d comentarios', get_comments_number(), 'feelolab' ), get_comments_number() ) );
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php else : ?>
		<h2 id="comentarios-title" class="screen-reader-text"><?php esc_html_e( 'Comentarios', 'feelolab' ); ?></h2>
	<?php endif; ?>
	<?php comment_form( array( 'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">', 'title_reply_after' => '</h2>' ) ); ?>
</section>
