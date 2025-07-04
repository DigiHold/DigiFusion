<?php
/**
 * Template part for displaying posts in archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
/**
 * DigiFusion before archive post.
 */
do_action( 'digifusion_before_archive_post' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'digi-post-card' ); ?> <?php echo wp_kses_post( digifusion_get_schema_markup('blog-post') ); ?>>
	<?php
	/**
	 * DigiFusion before archive post inner.
	 */
	do_action( 'digifusion_before_archive_post_inner' );
	?>
	
	<?php
	// Display the featured image if available
	if ( has_post_thumbnail() ) {
		?>
		<?php
		/**
		 * DigiFusion before archive post image.
		 */
		do_action( 'digifusion_before_archive_post_image' );
		?>
		<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="digi-post-image">
			<?php
			the_post_thumbnail( 'medium_large', array(
				'class' => 'digi-featured-image',
				'itemprop' => 'image'
			) );
			?>
			<div class="digi-image-overlay">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100" height="100" fill="#ffffffa8"><path class="fill-blue" d="M16 256C16 123.5 123.5 16 256 16c4.4 0 8-3.6 8-8s-3.6-8-8-8C114.6 0 0 114.6 0 256S114.6 512 256 512c4.4 0 8-3.6 8-8s-3.6-8-8-8C123.5 496 16 388.5 16 256zM357.2 121.9c-3.4-2.8-8.4-2.4-11.3 1s-2.4 8.4 1 11.3L482.1 248 168 248c-4.4 0-8 3.6-8 8s3.6 8 8 8l314.1 0L346.8 377.9c-3.4 2.8-3.8 7.9-1 11.3s7.9 3.8 11.3 1l152-128c1.8-1.5 2.8-3.8 2.8-6.1s-1-4.6-2.8-6.1l-152-128z"/></svg>
			</div>
		</a>
		<?php
		/**
		 * DigiFusion after archive post image.
		 */
		do_action( 'digifusion_after_archive_post_image' );
		?>
		<?php
	}
	?>

	<div class="digi-post-content">
		<?php
		/**
		 * DigiFusion before archive post content inner.
		 */
		do_action( 'digifusion_before_archive_post_content_inner' );
		?>
		
		<div class="digi-post-meta">
			<div class="digi-post-date">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="digi-meta-icon">
					<path d="M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
				</svg>
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" <?php echo wp_kses_post( digifusion_get_schema_property('datePublished') ); ?>>
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</div>
			<div class="digi-post-comments">
				<a href="<?php echo esc_url( get_permalink() ); ?>#comments" class="digi-comments-link">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="digi-meta-icon">
						<path d="M512 240c0 114.9-114.6 208-256 208c-37.1 0-72.3-6.4-104.1-17.9c-11.9 8.7-31.3 20.6-54.3 30.6C73.6 471.1 44.7 480 16 480c-6.5 0-12.3-3.9-14.8-9.9c-2.5-6-1.1-12.8 3.4-17.4c0 0 0 0 0 0s0 0 0 0s0 0 0 0c0 0 0 0 0 0l.3-.3c.3-.3 .7-.7 1.3-1.4c1.1-1.2 2.8-3.1 4.9-5.7c4.1-5 9.6-12.4 15.2-21.6c10-16.6 19.5-38.4 21.4-62.9C17.7 326.8 0 285.1 0 240C0 125.1 114.6 32 256 32s256 93.1 256 208z"/>
					</svg>
					<?php comments_number( '0 Comments', '1 Comment', '% Comments' ); ?>
				</a>
			</div>
		</div>
		
		<?php
		/**
		 * DigiFusion after archive post meta.
		 */
		do_action( 'digifusion_after_archive_post_meta' );
		?>
		
		<h2 class="digi-post-title" <?php echo wp_kses_post( digifusion_get_schema_property('headline') ); ?>>
			<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="digi-title-link"><?php echo wp_kses_post( get_the_title() ); ?></a>
		</h2>
		
		<?php
		/**
		 * DigiFusion after archive post title.
		 */
		do_action( 'digifusion_after_archive_post_title' );
		?>
		
		<div class="digi-post-excerpt" <?php echo wp_kses_post( digifusion_get_schema_property('description') ); ?>>
			<?php the_excerpt(); ?>
		</div>
		
		<?php if ( is_tax() || is_category() || is_tag() ) : ?>
		<?php
		/**
		 * DigiFusion before archive post taxonomy.
		 */
		do_action( 'digifusion_before_archive_post_taxonomy' );
		?>
		<div class="digi-taxonomy-meta">
			<?php
			// Show categories if not in a category archive
			if ( ! is_category() ) {
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					echo '<div class="digi-term-list digi-categories">';
					echo '<span class="digi-term-label">' . esc_html__( 'Categories:', 'digifusion' ) . '</span> ';
					$cat_links = array();
					foreach ( $categories as $category ) {
						$cat_links[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" class="digi-term-link digi-category-link">' . esc_html( $category->name ) . '</a>';
					}
					echo implode( ', ', $cat_links );
					echo '</div>';
				}
			}
			
			// Show tags if not in a tag archive
			if ( ! is_tag() ) {
				$tags = get_the_tags();
				if ( ! empty( $tags ) ) {
					echo '<div class="digi-term-list digi-tags">';
					echo '<span class="digi-term-label">' . esc_html__( 'Tags:', 'digifusion' ) . '</span> ';
					$tag_links = array();
					foreach ( $tags as $tag ) {
						$tag_links[] = '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="digi-term-link digi-tag-link">' . esc_html( $tag->name ) . '</a>';
					}
					echo implode( ', ', $tag_links );
					echo '</div>';
				}
			}
			?>
		</div>
		<?php
		/**
		 * DigiFusion after archive post taxonomy.
		 */
		do_action( 'digifusion_after_archive_post_taxonomy' );
		?>
		<?php endif; ?>
		
		<div class="digi-post-action">
			<a href="<?php echo esc_url( get_the_permalink() ); ?>" class="digi-button" <?php echo wp_kses_post( digifusion_get_schema_property('url') ); ?>>
				<span class="text"><?php esc_html_e( 'Read More', 'digifusion' ); ?></span>
			</a>
		</div>
		
		<?php
		/**
		 * DigiFusion after archive post content inner.
		 */
		do_action( 'digifusion_after_archive_post_content_inner' );
		?>
	</div>
	
	<?php
	/**
	 * DigiFusion after archive post inner.
	 */
	do_action( 'digifusion_after_archive_post_inner' );
	?>
</article>

<?php
/**
 * DigiFusion after archive post.
 */
do_action( 'digifusion_after_archive_post' );
?>