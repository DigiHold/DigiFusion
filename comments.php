<?php
/**
 * The template for displaying comments.
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

/**
 * DigiFusion before comments.
 */
do_action( 'digifusion_before_comments' );
?>

<div id="comments" class="digi-comments-area" <?php echo wp_kses_post( digifusion_get_schema_markup('comment-section') ); ?>>
	<?php
	/**
	 * DigiFusion before comments inner.
	 */
	do_action( 'digifusion_before_comments_inner' );
	
	if ( have_comments() ) :
		/**
		 * DigiFusion before comments title.
		 */
		do_action( 'digifusion_before_comments_title' );
		?>
		<h2 class="digi-comments-title" <?php echo wp_kses_post( digifusion_get_schema_property('name') ); ?>>
			<?php
				printf(
					_nx( 'One Comment', '%1$s Comments', get_comments_number(), 'comments title', 'digifusion' ),
					number_format_i18n( get_comments_number() ),
					wp_kses_post( get_the_title() )
				);
			?>
		</h2>
		<?php
		/**
		 * DigiFusion after comments title.
		 */
		do_action( 'digifusion_after_comments_title' );
		?>

		<ol class="digi-comment-list" <?php echo wp_kses_post( digifusion_get_schema_property('comment') ); ?>>
			<?php
				wp_list_comments(
					array(
						'style'       => 'ol',
						'short_ping'  => true,
						'avatar_size' => 200,
					)
				);
			?>
		</ol>

	<?php endif;
	
	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>

		<nav class="digi-comment-navigation" id="comment-nav-above">

			<h1 class="screen-reader-text"><?php esc_html_e( 'Comments navigation', 'digifusion' ); ?></h1>

			<?php if ( get_previous_comments_link() ) { ?>
					<div class="nav-previous">
						<?php previous_comments_link( esc_html__( '&larr; Older comments', 'digifusion' ) ); ?>
					</div>
			<?php }
			
			if ( get_next_comments_link() ) { ?>
				<div class="nav-next">
					<?php next_comments_link( esc_html__( 'More recent comments &rarr;', 'digifusion' ) ); ?>
				</div>
			<?php } ?>

		</nav><!-- #comment-nav-above -->

	<?php endif;
	
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="digi-no-comments"><?php esc_html_e( 'Comments are closed.', 'digifusion' ); ?></p>
	<?php endif;
	
	/**
	 * DigiFusion before comment form.
	 */
	do_action( 'digifusion_before_comment_form' );
	
    $comments_args = array(
        'title_reply' => esc_html__( 'We need your opinion !', 'digifusion' ),
        'comment_notes_before' => '',
        'comment_notes_after' => '',
        'comment_field' =>
        '<p class="digi-comment-textarea">
            <label for="comment">' . esc_html__( 'Your comment', 'digifusion' ) . '</label>
            <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
        </p>',
        'fields' => apply_filters( 'comment_form_default_fields', array(
            'author' => 
                '<div class="digi-comment-fields"><p class="digi-comment-input">
                    <label for="author">' . esc_html__( 'Your name', 'digifusion' ) . ' <span class="digi-required">*</span></label>
                    <input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" required />
                </p>',

            'email' => 
                '<p class="digi-comment-input">
                    <label for="email">' . esc_html__( 'Your email', 'digifusion' ) . ' <span class="digi-required">*</span></label>
                    <input id="email" name="email" type="email" size="30" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" required />
                </p>',

            'url' => 
                '<p class="digi-comment-input">
                    <label for="url">' . esc_html__( 'Your website', 'digifusion' ) . '</label>
                    <input id="url" name="url" type="url" size="30" value="' . esc_attr( $commenter['comment_author_url'] ) . '" />
                </p></div>'
        ) ),
        'label_submit' => esc_html__( 'Send comment', 'digifusion' ),
    );

    comment_form( $comments_args );

	/**
	 * DigiFusion after comments inner.
	 */
	do_action( 'digifusion_after_comments_inner' );
	?>
</div>

<?php
/**
 * DigiFusion after comments.
 */
do_action( 'digifusion_after_comments' );
?>