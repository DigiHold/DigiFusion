<?php
/**
 * The template for displaying search form
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<form role="search" method="get" class="digi-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="digi-search-label">
		<span class="screen-reader-text"><?php echo esc_attr_x( 'Search for:', 'label', 'digifusion' ); ?></span>
		<input type="search" class="digi-search-field" placeholder="<?php echo esc_attr__( 'Search', 'digifusion' ); ?>" value="<?php echo get_search_query(); ?>" name="s" title="<?php echo esc_attr__( 'Search for:', 'digifusion' ); ?>" />
	</label>
	<input type="submit" class="digi-search-submit" value="<?php echo esc_attr__( 'Search', 'digifusion' ); ?>" />
</form>