<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * DigiFusion before single post.
 */
do_action( 'digifusion_before_single_post' );

if ( ! function_exists( 'digifusion_single_post' ) || ! digifusion_single_post() ) :
	if (have_posts()) :
		while (have_posts()) : the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('digi-single-article'); ?> <?php echo wp_kses_post( digifusion_get_schema_markup('blog-post') ); ?>>
				<?php
				/**
				* DigiFusion before single post title.
				*/
				do_action( 'digifusion_before_single_post_meta' );
				?>

				<h1 class="digi-post-title-single"><?php echo wp_kses_post( get_the_title() ); ?></h1>

				<?php
				/**
				 * DigiFusion after single post title.
				 */
				do_action( 'digifusion_after_single_post_meta' );
				?>
				
				<div class="digi-post-meta-single">
					<div class="digi-post-author">
						<?php 
						printf(
							/* translators: %s: Author link */
							esc_html__('By: %s', 'digifusion'),
							sprintf(
								'<a href="%s" title="%s" %s>%s</a>',
								esc_url(get_author_posts_url(get_the_author_meta('ID'))),
								esc_attr(get_the_author_meta('display_name')),
								digifusion_get_schema_property('author'),
								esc_html(get_the_author_meta('display_name'))
							)
						);
						?>
					</div>
					<span class="digi-meta-separator">/</span>
					<time class="digi-post-date-single" datetime="<?php echo esc_attr(get_the_date('c')); ?>" <?php echo wp_kses_post( digifusion_get_schema_property('datePublished') ); ?>><?php echo esc_html(get_the_date()); ?></time>
				</div>

				<?php
				/**
				 * DigiFusion after single post meta.
				 */
				do_action( 'digifusion_after_single_post_meta' );
				?>

				<div class="digi-post-share">
					<span class="digi-share-text"><?php esc_html_e('Share on', 'digifusion'); ?></span>
					<?php
					$url = get_permalink();
					$title = get_the_title();
					?>
					<div class="digi-share-buttons">
						<a href="https://www.linkedin.com/shareArticle?title=<?php echo urlencode($title); ?>&url=<?php echo urlencode($url); ?>&mini=true" 
							class="digi-share-btn digi-linkedin" 
							title="<?php echo esc_attr($title); ?>" 
							target="_blank" 
							aria-label="<?php esc_attr_e('Share on LinkedIn', 'digifusion'); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" height="16" width="14.25" viewBox="0 0 448 512" class="digi-share-icon"><path d="M100.3 448H7.4V148.9h92.9zM53.8 108.1C24.1 108.1 0 83.5 0 53.8a53.8 53.8 0 0 1 107.6 0c0 29.7-24.1 54.3-53.8 54.3zM447.9 448h-92.7V302.4c0-34.7-.7-79.2-48.3-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.8V148.9h89.1v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z"/></svg>
						</a>

						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>&t=<?php echo urlencode($title); ?>" 
							class="digi-share-btn digi-facebook" 
							title="<?php echo esc_attr($title); ?>" 
							target="_blank" 
							aria-label="<?php esc_attr_e('Share on Facebook', 'digifusion'); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" height="16" width="10.75" viewBox="0 0 320 512" class="digi-share-icon"><path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"/></svg>
						</a>
					</div>
				</div>
				<?php
				/**
				* DigiFusion before single post inner.
				*/
				do_action( 'digifusion_before_single_post_inner' );

				if (has_post_thumbnail()) {
					echo '<div class="digi-featured-image-single">';
					the_post_thumbnail('Large', array(
						'class' => 'digi-post-thumbnail-single',
						'itemprop' => 'image'
					));
					echo '</div>';
				}

				/**
				* DigiFusion after single post featured image.
				*/
				do_action( 'digifusion_after_single_post_featured_image' );
				?>

				<?php
				/**
				 * DigiFusion before single article content.
				 */
				do_action( 'digifusion_before_single_article_content' );
				?>
				<div class="digi-post-content-single" <?php echo wp_kses_post( digifusion_get_schema_property('articleBody') ); ?>>
					<?php
					the_content();

					wp_link_pages(
						array(
							'before'      => '<div class="digi-page-links"><span class="digi-page-links-title">' . esc_html__( 'Pages:', 'digifusion' ) . '</span>',
							'after'       => '</div>',
							'link_before' => '<span class="digi-page-number">',
							'link_after'  => '</span>',
							'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'digifusion' ) . ' </span>%',
							'separator'   => '<span class="screen-reader-text">, </span>',
						)
					);
					?>
				</div>
				<?php
				/**
				 * DigiFusion after single article content.
				 */
				do_action( 'digifusion_after_single_article_content' );
				?>

				<div class="digi-author-box" <?php echo wp_kses_post( digifusion_get_schema_markup('person') ); ?>>
					<?php
					/**
					 * DigiFusion before author box inner.
					 */
					do_action( 'digifusion_before_author_box_inner' );
					?>
					<div class="digi-author-avatar-wrapper">
						<a class="digi-author-avatar-link" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" 
							title="<?php echo esc_attr(get_the_author_meta('display_name')); ?>" <?php echo wp_kses_post( digifusion_get_schema_property('name') ); ?>>
							<?php 
							echo get_avatar(
								get_the_author_meta('ID'), 
								100, 
								'', 
								/* translators: %s: Author name */
								sprintf(esc_attr__('%s\'s avatar', 'digifusion'), get_the_author_meta('display_name')),
								array('class' => 'digi-author-avatar')
							); 
							?>
						</a>
					</div>

					<div class="digi-author-content">
						<div class="digi-author-name-wrapper">
							<a class="digi-author-name" 
								href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" 
								title="<?php echo esc_attr(get_the_author_meta('display_name')); ?>" <?php echo wp_kses_post( digifusion_get_schema_property('name') ); ?>>
								<?php echo esc_html(get_the_author_meta('display_name')); ?>
							</a>
						</div>
						<div class="digi-author-bio" <?php echo wp_kses_post( digifusion_get_schema_property('description') ); ?>><?php echo wp_kses_post(get_the_author_meta('description')); ?></div>
						<?php
						// Get author meta for social links
						$author_linkedin = get_the_author_meta('linkedin');
						$author_facebook = get_the_author_meta('facebook'); 
						$author_youtube = get_the_author_meta('youtube');
						$author_twitter = get_the_author_meta('twitter');
						$author_website = get_the_author_meta('user_url');

						if ($author_linkedin || $author_facebook || $author_youtube || $author_twitter || $author_website) :
							?>
							<ul class="digi-author-social">
								<?php
								if ($author_linkedin) : ?>
									<li>
										<a href="<?php echo esc_url($author_linkedin); ?>" 
											class="digi-author-social-link digi-linkedin" 
											target="_blank" 
											aria-label="<?php esc_attr_e('Author\'s LinkedIn Profile', 'digifusion'); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" height="16" width="14.25" viewBox="0 0 448 512" class="digi-social-icon"><path d="M100.3 448H7.4V148.9h92.9zM53.8 108.1C24.1 108.1 0 83.5 0 53.8a53.8 53.8 0 0 1 107.6 0c0 29.7-24.1 54.3-53.8 54.3zM447.9 448h-92.7V302.4c0-34.7-.7-79.2-48.3-79.2-48.3 0-55.7 37.7-55.7 76.7V448h-92.8V148.9h89.1v40.8h1.3c12.4-23.5 42.7-48.3 87.9-48.3 94 0 111.3 61.9 111.3 142.3V448z"/></svg>
										</a>
									</li>
								<?php endif;
								
								if ($author_facebook) : ?>
									<li>
										<a href="<?php echo esc_url($author_facebook); ?>" 
											class="digi-author-social-link digi-facebook" 
											target="_blank" 
											aria-label="<?php esc_attr_e('Author\'s Facebook Profile', 'digifusion'); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" height="16" width="10.75" viewBox="0 0 320 512" class="digi-social-icon"><path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z"/></svg>
										</a>
									</li>
								<?php endif;
								
								if ($author_youtube) : ?>
									<li>
										<a href="<?php echo esc_url($author_youtube); ?>" 
											class="digi-author-social-link digi-youtube" 
											target="_blank" 
											aria-label="<?php esc_attr_e('Author\'s YouTube Channel', 'digifusion'); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="16" class="digi-social-icon"><path d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z"></path></svg>
										</a>
									</li>
								<?php endif;
								
								if ($author_twitter) : ?>
									<li>
										<a href="<?php echo esc_url($author_twitter); ?>" 
											class="digi-author-social-link digi-twitter" 
											target="_blank" 
											aria-label="<?php esc_attr_e('Author\'s Twitter Profile', 'digifusion'); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" class="digi-social-icon"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
										</a>
									</li>
								<?php endif; ?>
							</ul>
							<?php
						endif; ?>
					</div>
					<?php
					/**
					 * DigiFusion after author box inner.
					 */
					do_action( 'digifusion_after_author_box_inner' );
					?>
				</div>
			</article>

			<?php
			/**
			 * DigiFusion before related posts.
			 */
			do_action( 'digifusion_before_related_posts' );
			
			$categories = get_the_category(get_the_ID());
			$categoryIDs = array();
			foreach ($categories as $category) {
				$categoryIDs[] = $category->term_id;
			}
			
			$related_query = array(
				'post_type' => 'post',
				'posts_per_page' => 2,
				'post__not_in' => array(get_the_ID()),
				'orderby' => 'rand',
				'cat' => implode(',', $categoryIDs),
			);
			
			$custom_query = new WP_Query($related_query);
			
			if ($custom_query->have_posts()) :
				?>
				<div class="digi-related-posts">
					<?php
					/**
					 * DigiFusion before related posts inner.
					 */
					do_action( 'digifusion_before_related_posts_inner' );
					?>
					<h3 class="digi-related-title"><?php esc_html_e('Similar Articles', 'digifusion'); ?></h3>
					<div class="digi-related-grid">
						<?php
						while ($custom_query->have_posts()) :
							$custom_query->the_post();
							?>
							<div class="digi-related-post" <?php echo wp_kses_post( digifusion_get_schema_markup('blog-post') ); ?>>
								<?php if (has_post_thumbnail()) : ?>
									<a href="<?php echo esc_url(get_permalink()); ?>" 
										class="digi-related-image">
										<?php
										the_post_thumbnail('medium_large', array(
											'class' => 'digi-related-thumbnail',
											'itemprop' => 'image'
										));
										?>
										<div class="digi-image-overlay">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100" height="100"><path class="fill-blue" d="M16 256C16 123.5 123.5 16 256 16c4.4 0 8-3.6 8-8s-3.6-8-8-8C114.6 0 0 114.6 0 256S114.6 512 256 512c4.4 0 8-3.6 8-8s-3.6-8-8-8C123.5 496 16 388.5 16 256zM357.2 121.9c-3.4-2.8-8.4-2.4-11.3 1s-2.4 8.4 1 11.3L482.1 248 168 248c-4.4 0-8 3.6-8 8s3.6 8 8 8l314.1 0L346.8 377.9c-3.4 2.8-3.8 7.9-1 11.3s7.9 3.8 11.3 1l152-128c1.8-1.5 2.8-3.8 2.8-6.1s-1-4.6-2.8-6.1l-152-128z"/></svg>
										</div>
									</a>
								<?php endif; ?>
				
								<div class="digi-related-content">
									<div class="digi-post-meta">
										<div class="digi-post-date">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="digi-meta-icon">
												<path d="M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/>
											</svg>
											<time datetime="<?php echo esc_attr(get_the_date('c')); ?>" <?php echo wp_kses_post( digifusion_get_schema_property('datePublished') ); ?>>
												<?php echo esc_html(get_the_date()); ?>
											</time>
										</div>
										<div class="digi-post-comments">
											<a href="<?php echo esc_url(get_permalink()); ?>#comments" class="digi-comments-link">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="digi-meta-icon">
													<path d="M512 240c0 114.9-114.6 208-256 208c-37.1 0-72.3-6.4-104.1-17.9c-11.9 8.7-31.3 20.6-54.3 30.6C73.6 471.1 44.7 480 16 480c-6.5 0-12.3-3.9-14.8-9.9c-2.5-6-1.1-12.8 3.4-17.4c0 0 0 0 0 0s0 0 0 0s0 0 0 0c0 0 0 0 0 0l.3-.3c.3-.3 .7-.7 1.3-1.4c1.1-1.2 2.8-3.1 4.9-5.7c4.1-5 9.6-12.4 15.2-21.6c10-16.6 19.5-38.4 21.4-62.9C17.7 326.8 0 285.1 0 240C0 125.1 114.6 32 256 32s256 93.1 256 208z"/>
												</svg>
												<?php comments_number('0 Comments', '1 Comment', '% Comments'); ?>
											</a>
										</div>
									</div>
									<h2 class="digi-related-post-title" <?php echo wp_kses_post( digifusion_get_schema_property('headline') ); ?>>
										<a href="<?php echo esc_url(get_permalink()); ?>" 
											class="digi-title-link">
											<?php echo wp_kses_post(get_the_title()); ?>
										</a>
									</h2>
									<?php the_excerpt(); ?>
								</div>
							</div>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</div>
					<?php
					/**
					 * DigiFusion after related posts inner.
					 */
					do_action( 'digifusion_after_related_posts_inner' );
					?>
				</div>
				<?php
			endif;
			
			/**
			 * DigiFusion after related posts.
			 */
			do_action( 'digifusion_after_related_posts' );
			?>

			<div class="digi-comments-wrapper">
				<?php
				if (comments_open() || get_comments_number()) :
					comments_template();
				endif;
				?>
			</div>
			
			<?php
			/**
			 * DigiFusion after single post inner.
			 */
			do_action( 'digifusion_after_single_post_inner' );
		endwhile;
	endif;
endif;

/**
 * DigiFusion after single post.
 */
do_action( 'digifusion_after_single_post' );
        
get_footer();