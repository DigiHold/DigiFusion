<?php
/**
 * DigiFusion Breadcrumbs Class
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Breadcrumbs Class
 * 
 * Handles breadcrumb functionality with support for WooCommerce, RankMath, Yoast, and custom breadcrumbs.
 */
class DigiFusion_Breadcrumbs {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// No hooks needed, this is a utility class
	}

	/**
	 * Get instance of this class.
	 *
	 * @return DigiFusion_Breadcrumbs
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Display breadcrumbs with automatic detection of available plugins.
	 *
	 * @param array $args Breadcrumb arguments.
	 */
	public function display_breadcrumbs( $args = array() ) {
		// Default arguments
		$defaults = array(
			'home_text'   => __( 'Home', 'digifusion' ),
			'separator'   => ' <span class="digi-breadcrumb-separator">/</span> ',
			'wrap_before' => '<nav class="digi-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'digifusion' ) . '">',
			'wrap_after'  => '</nav>',
			'before'      => '<span class="digi-breadcrumb-item">',
			'after'       => '</span>',
		);

		$args = wp_parse_args( $args, $defaults );

		// Check for WooCommerce pages first (highest priority)
		if ( class_exists( 'WooCommerce' ) && $this->is_woocommerce_page() ) {
			$this->display_woocommerce_breadcrumbs( $args );
			return;
		}

		// Check for RankMath breadcrumbs
		if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
			echo '<nav class="digi-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'digifusion' ) . '" ' . wp_kses_post( digifusion_get_schema_markup( 'breadcrumb' ) ) . '>';
			rank_math_the_breadcrumbs();
			echo '</nav>';
			return;
		}

		// Check for Yoast breadcrumbs
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			yoast_breadcrumb( 
				'<nav class="digi-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'digifusion' ) . '" ' . wp_kses_post( digifusion_get_schema_markup( 'breadcrumb' ) ) . '>', 
				'</nav>' 
			);
			return;
		}

		// Fallback to custom breadcrumbs
		$this->display_custom_breadcrumbs( $args );
	}

	/**
	 * Check if current page is a WooCommerce page.
	 *
	 * @return bool
	 */
	private function is_woocommerce_page() {
		return is_shop() || is_product_category() || is_product_tag() || is_product() || is_cart() || is_checkout() || is_account_page();
	}

	/**
	 * Display WooCommerce breadcrumbs.
	 *
	 * @param array $args Breadcrumb arguments.
	 */
	private function display_woocommerce_breadcrumbs( $args ) {
		if ( function_exists( 'woocommerce_breadcrumb' ) ) {
			// Build the wrapper with schema markup
			$wrap_before = '<nav class="digi-breadcrumbs woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'digifusion' ) . '" ' . wp_kses_post( digifusion_get_schema_markup( 'breadcrumb' ) ) . '>';
			
			// Build the item wrapper with schema markup
			$before = '<span class="digi-breadcrumb-item" ' . wp_kses_post( digifusion_get_schema_markup( 'list-item' ) ) . '>';
			
			woocommerce_breadcrumb( array(
				'delimiter'   => $args['separator'],
				'wrap_before' => $wrap_before,
				'wrap_after'  => '</nav>',
				'before'      => $before,
				'after'       => '</span>',
				'home'        => $args['home_text'],
			) );
		}
	}

	/**
	 * Display custom breadcrumbs for non-plugin pages.
	 *
	 * @param array $args Breadcrumb arguments.
	 */
	private function display_custom_breadcrumbs( $args ) {
		// Don't show breadcrumbs on front page
		if ( is_front_page() ) {
			return;
		}

		$breadcrumbs = array();

		// Add home link
		$breadcrumbs[] = array(
			'title' => $args['home_text'],
			'url'   => home_url( '/' ),
		);

		// Build breadcrumb trail
		if ( is_home() ) {
			$breadcrumbs[] = array(
				'title' => get_the_title( get_option( 'page_for_posts' ) ),
				'url'   => '',
			);
		} elseif ( is_category() ) {
			$category = get_queried_object();
			if ( $category->parent ) {
				$breadcrumbs = array_merge( $breadcrumbs, $this->get_category_parents( $category->parent ) );
			}
			$breadcrumbs[] = array(
				'title' => $category->name,
				'url'   => '',
			);
		} elseif ( is_tag() ) {
			$breadcrumbs[] = array(
				'title' => single_tag_title( '', false ),
				'url'   => '',
			);
		} elseif ( is_author() ) {
			$breadcrumbs[] = array(
				'title' => get_the_author_meta( 'display_name' ),
				'url'   => '',
			);
		} elseif ( is_search() ) {
			$breadcrumbs[] = array(
				'title' => sprintf( __( 'Search Results for: %s', 'digifusion' ), get_search_query() ),
				'url'   => '',
			);
		} elseif ( is_404() ) {
			$breadcrumbs[] = array(
				'title' => __( '404 Not Found', 'digifusion' ),
				'url'   => '',
			);
		} elseif ( is_singular() ) {
			$post = get_queried_object();
			
			// Add post type archive if it exists
			if ( 'post' !== $post->post_type ) {
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object && $post_type_object->has_archive ) {
					$breadcrumbs[] = array(
						'title' => $post_type_object->labels->name,
						'url'   => get_post_type_archive_link( $post->post_type ),
					);
				}
			} else {
				// For blog posts, add blog page if set
				$blog_page_id = get_option( 'page_for_posts' );
				if ( $blog_page_id ) {
					$breadcrumbs[] = array(
						'title' => get_the_title( $blog_page_id ),
						'url'   => get_permalink( $blog_page_id ),
					);
				}
			}

			// Add categories for posts
			if ( 'post' === $post->post_type ) {
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					$category = $categories[0]; // Use first category
					if ( $category->parent ) {
						$breadcrumbs = array_merge( $breadcrumbs, $this->get_category_parents( $category->parent ) );
					}
					$breadcrumbs[] = array(
						'title' => $category->name,
						'url'   => get_category_link( $category->term_id ),
					);
				}
			}

			// Add current post/page
			$breadcrumbs[] = array(
				'title' => get_the_title(),
				'url'   => '',
			);
		} elseif ( is_archive() ) {
			// Handle different archive types
			if ( is_post_type_archive() ) {
				$post_type = get_query_var( 'post_type' );
				if ( is_array( $post_type ) ) {
					$post_type = reset( $post_type );
				}
				
				$post_type_object = get_post_type_object( $post_type );
				if ( $post_type_object ) {
					$breadcrumbs[] = array(
						'title' => $post_type_object->labels->name,
						'url'   => '',
					);
				}
			} elseif ( is_tax() ) {
				$term = get_queried_object();
				if ( $term ) {
					// Add parent terms if they exist
					if ( $term->parent ) {
						$breadcrumbs = array_merge( $breadcrumbs, $this->get_term_parents( $term->parent, $term->taxonomy ) );
					}
					
					$breadcrumbs[] = array(
						'title' => $term->name,
						'url'   => '',
					);
				}
			} elseif ( is_date() ) {
				if ( is_day() ) {
					$breadcrumbs[] = array(
						'title' => get_the_date(),
						'url'   => '',
					);
				} elseif ( is_month() ) {
					$breadcrumbs[] = array(
						'title' => get_the_date( 'F Y' ),
						'url'   => '',
					);
				} elseif ( is_year() ) {
					$breadcrumbs[] = array(
						'title' => get_the_date( 'Y' ),
						'url'   => '',
					);
				}
			} else {
				// Fallback - strip HTML tags from archive title
				$archive_title = get_the_archive_title();
				$archive_title = wp_strip_all_tags( $archive_title );
				
				$breadcrumbs[] = array(
					'title' => $archive_title,
					'url'   => '',
				);
			}
		}

		// Filter breadcrumbs
		$breadcrumbs = apply_filters( 'digifusion_breadcrumbs', $breadcrumbs );

		// Output breadcrumbs
		if ( ! empty( $breadcrumbs ) ) {
			// Use schema helper for nav wrapper
			echo '<nav class="digi-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'digifusion' ) . '" ' . wp_kses_post( digifusion_get_schema_markup( 'breadcrumb' ) ) . '>';
			
			foreach ( $breadcrumbs as $index => $breadcrumb ) {
				if ( $index > 0 ) {
					echo wp_kses_post( $args['separator'] );
				}
				
				// Use schema helper for list item
				echo '<span class="digi-breadcrumb-item" ' . wp_kses_post( digifusion_get_schema_markup( 'list-item' ) ) . '>';
				
				// Add position meta for schema
				$position_meta = digifusion_get_schema_property( 'position' );
				if ( ! empty( $position_meta ) ) {
					echo '<meta ' . wp_kses_post( $position_meta ) . ' content="' . esc_attr( $index + 1 ) . '">';
				}
				
				if ( ! empty( $breadcrumb['url'] ) ) {
					// Link with schema
					$item_prop = digifusion_get_schema_property( 'item' );
					$name_prop = digifusion_get_schema_property( 'name' );
					
					echo '<a href="' . esc_url( $breadcrumb['url'] ) . '"';
					if ( ! empty( $item_prop ) ) {
						echo ' ' . wp_kses_post( $item_prop );
					}
					echo '>';
					
					if ( ! empty( $name_prop ) ) {
						echo '<span ' . wp_kses_post( $name_prop ) . '>' . esc_html( $breadcrumb['title'] ) . '</span>';
					} else {
						echo esc_html( $breadcrumb['title'] );
					}
					echo '</a>';
				} else {
					// Current page (no link)
					$name_prop = digifusion_get_schema_property( 'name' );
					
					if ( ! empty( $name_prop ) ) {
						echo '<span ' . wp_kses_post( $name_prop ) . '>' . esc_html( $breadcrumb['title'] ) . '</span>';
					} else {
						echo '<span>' . esc_html( $breadcrumb['title'] ) . '</span>';
					}
				}
				
				echo '</span>';
			}
			
			echo '</nav>';
		}
	}

	/**
	 * Get category parents for breadcrumb trail.
	 *
	 * @param int $category_id Category ID.
	 * @return array Category parents.
	 */
	private function get_category_parents( $category_id ) {
		$parents = array();
		$category = get_category( $category_id );
		
		if ( $category && ! is_wp_error( $category ) ) {
			if ( $category->parent ) {
				$parents = array_merge( $parents, $this->get_category_parents( $category->parent ) );
			}
			
			$parents[] = array(
				'title' => $category->name,
				'url'   => get_category_link( $category->term_id ),
			);
		}
		
		return $parents;
	}

	/**
	 * Get term parents for breadcrumb trail.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return array Term parents.
	 */
	private function get_term_parents( $term_id, $taxonomy ) {
		$parents = array();
		$term = get_term( $term_id, $taxonomy );
		
		if ( $term && ! is_wp_error( $term ) ) {
			if ( $term->parent ) {
				$parents = array_merge( $parents, $this->get_term_parents( $term->parent, $taxonomy ) );
			}
			
			$parents[] = array(
				'title' => $term->name,
				'url'   => get_term_link( $term->term_id, $taxonomy ),
			);
		}
		
		return $parents;
	}
}

// Initialize the breadcrumbs class
DigiFusion_Breadcrumbs::get_instance();