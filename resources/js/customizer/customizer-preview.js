/**
 * DigiFusion Customizer Preview
 *
 * Live preview script for the Customizer.
 */

(function() {
    'use strict';

    // Track loaded fonts and their weights
    const loadedFonts = {};

    // Helper function to add or update CSS
    function updateCSS(id, css) {
        let styleTag = document.getElementById(id);
        if (!styleTag) {
            styleTag = document.createElement('style');
            styleTag.id = id;
            document.head.appendChild(styleTag);
        }
        styleTag.textContent = css;
    }

    // Helper function to parse color group values
    function parseColorGroup(value) {
        try {
            return JSON.parse(value) || {};
        } catch (e) {
            console.error('Error parsing color group value:', e);
            return {};
        }
    }

    // Helper function to parse typography values
    function parseTypography(value) {
        try {
            return JSON.parse(value) || {};
        } catch (e) {
            console.error('Error parsing typography value:', e);
            return {};
        }
    }

    // Helper function to generate typography CSS
    function generateTypographyCSS(selector, typography) {
        let css = '';
        
        // Base properties (apply to all devices) and desktop responsive values
        const baseProperties = [];
        
        if (typography.fontFamily) {
            baseProperties.push(`font-family: '${typography.fontFamily}'`);
            
            // Load Google Font if needed
            if (!typography.fontFamily.includes(',') && typography.fontFamily !== 'system-ui') {
                loadGoogleFont(typography.fontFamily, typography.fontWeight || '400');
            }
        }
        
        if (typography.fontWeight) {
            baseProperties.push(`font-weight: ${typography.fontWeight}`);
        }
        
        if (typography.fontStyle && typography.fontStyle !== 'normal') {
            baseProperties.push(`font-style: ${typography.fontStyle}`);
        }
        
        if (typography.textTransform) {
            baseProperties.push(`text-transform: ${typography.textTransform}`);
        }
        
        if (typography.textDecoration) {
            baseProperties.push(`text-decoration: ${typography.textDecoration}`);
        }
        
        // Desktop responsive properties
        if (typography.fontSize && typography.fontSize.desktop !== undefined && typography.fontSize.desktop !== '') {
            const fontSizeUnit = typography.fontSizeUnit || 'px';
            baseProperties.push(`font-size: ${typography.fontSize.desktop}${fontSizeUnit}`);
        }
        
        if (typography.lineHeight && typography.lineHeight.desktop !== undefined && typography.lineHeight.desktop !== '') {
            const lineHeightUnit = typography.lineHeightUnit || 'em';
            baseProperties.push(`line-height: ${typography.lineHeight.desktop}${lineHeightUnit}`);
        }
        
        if (typography.letterSpacing && typography.letterSpacing.desktop !== undefined && typography.letterSpacing.desktop !== '') {
            const letterSpacingUnit = typography.letterSpacingUnit || 'px';
            baseProperties.push(`letter-spacing: ${typography.letterSpacing.desktop}${letterSpacingUnit}`);
        }
        
        // Add base CSS (desktop/default)
        if (baseProperties.length > 0) {
            css += `${selector} { ${baseProperties.join('; ')}; }`;
        }
        
        // Tablet responsive properties
        const tabletProperties = [];
        
        if (typography.fontSize && typography.fontSize.tablet !== undefined && typography.fontSize.tablet !== '') {
            const fontSizeUnit = typography.fontSizeUnit || 'px';
            tabletProperties.push(`font-size: ${typography.fontSize.tablet}${fontSizeUnit}`);
        }
        
        if (typography.lineHeight && typography.lineHeight.tablet !== undefined && typography.lineHeight.tablet !== '') {
            const lineHeightUnit = typography.lineHeightUnit || 'em';
            tabletProperties.push(`line-height: ${typography.lineHeight.tablet}${lineHeightUnit}`);
        }
        
        if (typography.letterSpacing && typography.letterSpacing.tablet !== undefined && typography.letterSpacing.tablet !== '') {
            const letterSpacingUnit = typography.letterSpacingUnit || 'px';
            tabletProperties.push(`letter-spacing: ${typography.letterSpacing.tablet}${letterSpacingUnit}`);
        }
        
        // Add tablet CSS
        if (tabletProperties.length > 0) {
            css += `@media (max-width: 991px) { ${selector} { ${tabletProperties.join('; ')}; } }`;
        }
        
        // Mobile responsive properties
        const mobileProperties = [];
        
        if (typography.fontSize && typography.fontSize.mobile !== undefined && typography.fontSize.mobile !== '') {
            const fontSizeUnit = typography.fontSizeUnit || 'px';
            mobileProperties.push(`font-size: ${typography.fontSize.mobile}${fontSizeUnit}`);
        }
        
        if (typography.lineHeight && typography.lineHeight.mobile !== undefined && typography.lineHeight.mobile !== '') {
            const lineHeightUnit = typography.lineHeightUnit || 'em';
            mobileProperties.push(`line-height: ${typography.lineHeight.mobile}${lineHeightUnit}`);
        }
        
        if (typography.letterSpacing && typography.letterSpacing.mobile !== undefined && typography.letterSpacing.mobile !== '') {
            const letterSpacingUnit = typography.letterSpacingUnit || 'px';
            mobileProperties.push(`letter-spacing: ${typography.letterSpacing.mobile}${letterSpacingUnit}`);
        }
        
        // Add mobile CSS
        if (mobileProperties.length > 0) {
            css += `@media (max-width: 719px) { ${selector} { ${mobileProperties.join('; ')}; } }`;
        }
        
        return css;
    }

    // Enhanced Google Font loading with weight tracking
    function loadGoogleFont(fontFamily, fontWeight = '400') {
        if (!fontFamily || fontFamily.includes(',') || fontFamily === 'system-ui') {
            return;
        }
        
        // Normalize weight
        if (fontWeight === '' || !fontWeight) {
            fontWeight = '400';
        }
        
        // Initialize font tracking if not exists
        if (!loadedFonts[fontFamily]) {
            loadedFonts[fontFamily] = new Set(['400']); // Always include 400 as default
        }
        
        // Add the new weight
        loadedFonts[fontFamily].add(fontWeight);
        
        // Remove existing font link
        const existingLink = document.querySelector(`link[data-font-family="${fontFamily}"]`);
        if (existingLink) {
            existingLink.remove();
        }
        
        // Create new font URL with all weights
        const weights = Array.from(loadedFonts[fontFamily]).sort((a, b) => parseInt(a) - parseInt(b));
        const fontUrl = `https://fonts.googleapis.com/css?family=${fontFamily.replace(/ /g, '+')}:${weights.join(',')}&display=swap`;
        
        // Create and append new link element
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = fontUrl;
        link.setAttribute('data-font-family', fontFamily);
        document.head.appendChild(link);
    }

    // Global Colors - Dynamic CSS Custom Properties
    wp.customize('digifusion_global_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = ':root {';
            
            // Create CSS custom properties for each global color
            Object.keys(colors).forEach(colorKey => {
                if (colors[colorKey]) {
                    css += `--digi-${colorKey}: ${colors[colorKey]};`;
                }
            });
            
            css += '}';
            
            updateCSS('digifusion-global-colors-style', css);
        });
    });

    // Body Colors
    wp.customize('digifusion_body_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.background) {
                css += `
                    body {
                        background-color: ${colors.background};
                    }
                `;
            }
            
            if (colors.text) {
                css += `
                    body {
                        color: ${colors.text};
                    }
                `;
            }

            if (colors.headings) {
                css += `
                    h1, h2, h3, h4, h5, h6,
                    .digi-post-title,
                    .digi-post-title-single,
                    .digi-page-title,
					.digi-page-description p,
                    .digi-related-title,
                    .digi-related-post-title,
                    .digi-author-name {
                        color: ${colors.headings};
                    }
                `;
            }
            
            updateCSS('digifusion-body-colors-style', css);
        });
    });

    // Button Colors
    wp.customize('digifusion_button_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.background || colors.text) {
                css += `
                    button.digi,
					.digi-button,
                    input[type="submit"],
                    .digi-share-btn,
                    .digi-author-social-link,
                    .digi-search-submit,
                    .woocommerce ul.products li.product .button {
                `;
                
                if (colors.background) {
                    css += `background-color: ${colors.background};`;
                }
                
                if (colors.text) {
                    css += `color: ${colors.text};`;
                }
                
                css += '}';
            }
            
            if (colors.background_hover || colors.text_hover) {
                css += `
                    button.digi:hover,
					.digi-button:hover,
                    input[type="submit"]:hover,
                    .digi-share-btn:hover,
                    .digi-author-social-link:hover,
                    .digi-search-submit:hover,
                    .woocommerce ul.products li.product .button:hover {
                `;
                
                if (colors.background_hover) {
                    css += `background-color: ${colors.background_hover};`;
                }
                
                if (colors.text_hover) {
                    css += `color: ${colors.text_hover};`;
                }
                
                css += '}';
            }
            
            updateCSS('digifusion-button-colors-style', css);
        });
    });

    // Link Colors
    wp.customize('digifusion_link_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.normal) {
                css += `
                    a,
                    .digi-title-link,
                    .digi-comments-link,
                    .digi-author-name {
                        color: ${colors.normal};
                    }
                `;
            }
            
            if (colors.hover) {
                css += `
                    a:hover,
                    .digi-title-link:hover,
                    .digi-comments-link:hover,
                    .digi-author-name:hover {
                        color: ${colors.hover};
                    }
                `;
            }
            
            updateCSS('digifusion-link-colors-style', css);
        });
    });

    // Header Colors
    wp.customize('digifusion_header_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.background) {
                css += `
                    .site-header {
                        background-color: ${colors.background};
                    }
                `;
            }
            
            updateCSS('digifusion-header-colors-style', css);
        });
    });

    // Menu Colors
    wp.customize('digifusion_menu_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.normal) {
                css += `
                    .digi-header-nav a,
                    .digi-nav-menu a,
                    .digi-site-name {
                        color: ${colors.normal};
                    }
                `;
            }
            
            if (colors.hover) {
                css += `
                    .digi-header-nav a:hover,
                    .digi-nav-menu a:hover {
                        color: ${colors.hover};
                    }
                `;
            }
            
            if (colors.current) {
                css += `
                    .digi-header-nav .current-menu-item > a,
                    .digi-header-nav .current-menu-ancestor > a,
                    .digi-nav-menu .current-menu-item > a,
                    .digi-nav-menu .current-menu-ancestor > a {
                        color: ${colors.current};
                    }
                `;
            }
            
            updateCSS('digifusion-menu-colors-style', css);
        });
    });

    // Mobile Menu Icon Colors
    wp.customize('digifusion_mobile_icon_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.normal) {
                css += `
                    .digi-menu-bars span {
                        background-color: ${colors.normal};
                    }
                `;
            }
            
            if (colors.hover) {
                css += `
                    .digi-menu-toggle:hover .digi-menu-bars span {
                        background-color: ${colors.hover};
                    }
                `;
            }
            
            if (colors.active) {
                css += `
                    body.mopen .digi-menu-bars span {
                        background-color: ${colors.active};
                    }
                `;
            }
            
            updateCSS('digifusion-mobile-icon-colors-style', css);
        });
    });

    // Mobile Submenu Colors
    wp.customize('digifusion_mobile_submenu_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.background) {
                css += `
                    @media (max-width: 991px) {
                        .digi-header-nav,
                        .digi-header-nav .sub-menu {
                            background-color: ${colors.background};
                        }
                    }
                `;
            }
            
            if (colors.normal) {
                css += `
                    @media (max-width: 991px) {
                        .digi-header-nav a,
                        .digi-nav-menu a {
                            color: ${colors.normal};
                        }
                    }
                `;
            }
            
            if (colors.hover) {
                css += `
                    @media (max-width: 991px) {
                        .digi-header-nav a:hover,
                        .digi-nav-menu a:hover {
                            color: ${colors.hover};
                        }
                    }
                `;
            }
            
            if (colors.active) {
                css += `
                    @media (max-width: 991px) {
                        .digi-header-nav .current-menu-item > a,
                        .digi-header-nav .current-menu-ancestor > a,
                        .digi-nav-menu .current-menu-item > a,
                        .digi-nav-menu .current-menu-ancestor > a {
                            color: ${colors.active};
                        }
                    }
                `;
            }
            
            updateCSS('digifusion-mobile-submenu-colors-style', css);
        });
    });

    // Footer Colors
    wp.customize('digifusion_footer_colors', function(value) {
        value.bind(function(newval) {
            const colors = parseColorGroup(newval);
            
            let css = '';
            
            if (colors.background) {
                css += `
                    .site-footer {
                        background-color: ${colors.background};
                    }
                `;
            }
            
            if (colors.heading) {
                css += `
                    .site-footer h1,
                    .site-footer h2,
                    .site-footer h3,
                    .site-footer h4,
                    .site-footer h5,
                    .site-footer h6,
                    .site-footer .widget-title {
                        color: ${colors.heading};
                    }
                `;
            }
            
            if (colors.text) {
                css += `
                    .site-footer,
                    .site-footer p,
                    .site-footer .widget {
                        color: ${colors.text};
                    }
                `;
            }
            
            if (colors.link) {
                css += `
                    .site-footer a,
                    .site-footer-nav a {
                        color: ${colors.link};
                    }
                `;
            }
            
            if (colors.link_hover) {
                css += `
                    .site-footer a:hover,
                    .site-footer-nav a:hover {
                        color: ${colors.link_hover};
                    }
                `;
            }
            
            updateCSS('digifusion-footer-colors-style', css);
        });
    });

    // Typography Controls
    const typographyMappings = {
        'digifusion_body_typography': 'body',
        'digifusion_h1_typography': 'h1, .digi-page-title',
        'digifusion_h2_typography': 'h2',
        'digifusion_h3_typography': 'h3',
        'digifusion_h4_typography': 'h4',
        'digifusion_h5_typography': 'h5',
        'digifusion_h6_typography': 'h6',
        'digifusion_menu_typography': '.digi-header-nav a, .digi-nav-menu a',
        'digifusion_footer_typography': '.site-footer'
    };

    // Create typography controls for each mapping
    Object.keys(typographyMappings).forEach(function(settingId) {
        const selector = typographyMappings[settingId];
        const styleId = settingId.replace('digifusion_', 'digifusion-') + '-style';
        
        wp.customize(settingId, function(value) {
            value.bind(function(newval) {
                const typography = parseTypography(newval);
                const css = generateTypographyCSS(selector, typography);
                updateCSS(styleId, css);
            });
        });
    });

    // Initialize global colors on load
    wp.customize.bind('ready', function() {
        // Trigger initial global colors setup
        const globalColorsValue = wp.customize('digifusion_global_colors');
        if (globalColorsValue) {
            globalColorsValue.callbacks.fireWith(globalColorsValue, [globalColorsValue.get()]);
        }
    });

})();