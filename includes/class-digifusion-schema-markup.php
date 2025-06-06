<?php
/**
 * Schema Markup Helper for DigiFusion
 *
 * @package DigiFusion
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class DigiFusion_Schema_Markup
 * 
 * Handles schema markup generation for theme blocks with filters to disable
 */
class DigiFusion_Schema_Markup {
    
    /**
     * Get schema markup attributes for different content types
     *
     * @param string $type Schema type (navigation, logo, title, breadcrumb, etc.)
     * @param array  $args Additional arguments
     * @return string Schema markup attributes or empty string if disabled
     */
    public static function get_schema_markup( $type, $args = array() ) {
        // Check if enabled first
        if ( ! get_theme_mod( 'enable_schema_markup', true ) ) {
            return '';
        }

        // Global filter to disable all schema markup
        if ( ! apply_filters( 'digifusion_enable_schema_markup', true ) ) {
            return '';
        }
        
        // Specific filter for this schema type
        if ( ! apply_filters( "digifusion_enable_schema_markup_{$type}", true, $args ) ) {
            return '';
        }
        
        $schema_markup = '';
        
        switch ( $type ) {
            case 'header':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WPHeader"';
                break;
                
            case 'footer':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WPFooter"';
                break;
                
            case 'sidebar':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WPSideBar"';
                break;
                
            case 'navigation':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/SiteNavigationElement"';
                break;
                
            case 'site-title':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WebSite"';
                break;
                
            case 'breadcrumb':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/BreadcrumbList"';
                break;
                
            case 'list-item':
                $schema_markup = 'itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"';
                break;
                
            case 'article':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/Article"';
                break;
                
            case 'blog-post':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/BlogPosting"';
                break;
                
            case 'person':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/Person"';
                break;
                
            case 'organization':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/Organization"';
                break;
                
            case 'webpage':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WebPage"';
                break;
                
            case 'blog':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/Blog"';
                break;
                
            case 'search-box':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WebSite"';
                break;
                
            case 'main-content':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/WebPageElement"';
                break;
                
            case 'comment-section':
                $schema_markup = 'itemscope="itemscope" itemtype="https://schema.org/Comment"';
                break;
                
            default:
                // Allow custom schema types via filter
                $custom_markup = apply_filters( "digifusion_custom_schema_markup_{$type}", '', $args );
                $schema_markup = is_string( $custom_markup ) ? $custom_markup : '';
                break;
        }
        
        // Final filter to modify the markup - ensure we always return a string
        $final_markup = apply_filters( 'digifusion_schema_markup_output', $schema_markup, $type, $args );
        
        // Always return a string, never null
        return is_string( $final_markup ) ? $final_markup : '';
    }
    
    /**
     * Get schema property attribute
     *
     * @param string $property Schema property name
     * @param string $type     Parent schema type
     * @param array  $args     Additional arguments
     * @return string Schema property attribute or empty string if disabled
     */
    public static function get_schema_property( $property, $type = '', $args = array() ) {
        // Check if enabled first
        if ( ! get_theme_mod( 'enable_schema_markup', true ) ) {
            return '';
        }

        // Global filter to disable all schema markup
        if ( ! apply_filters( 'digifusion_enable_schema_markup', true ) ) {
            return '';
        }
        
        // Specific filter for this property
        if ( ! apply_filters( "digifusion_enable_schema_property_{$property}", true, $type, $args ) ) {
            return '';
        }
        
        return 'itemprop="' . esc_attr( $property ) . '"';
    }
}

/**
 * Helper functions for easy access
 */

/**
 * Get schema markup attributes
 *
 * @param string $type Schema type
 * @param array  $args Additional arguments
 * @return string Schema markup attributes (never null)
 */
function digifusion_get_schema_markup( $type, $args = array() ) {
    $result = DigiFusion_Schema_Markup::get_schema_markup( $type, $args );
    
    // Guarantee we never return null - always return a string
    return is_string( $result ) ? $result : '';
}

/**
 * Get schema property attribute
 *
 * @param string $property Schema property name
 * @param string $type     Parent schema type
 * @param array  $args     Additional arguments
 * @return string Schema property attribute (never null)
 */
function digifusion_get_schema_property( $property, $type = '', $args = array() ) {
    $result = DigiFusion_Schema_Markup::get_schema_property( $property, $type, $args );
    
    // Guarantee we never return null - always return a string
    return is_string( $result ) ? $result : '';
}