/**
 * DigiFusion Customizer Preview
 *
 * Live preview script for the Customizer.
 */

(function() {
    'use strict';

    // Text Setting
    wp.customize('digifusion_text_setting', function(value) {
        value.bind(function(newval) {
            // Replace with actual selectors and CSS properties
            const siteTitle = document.querySelector('.site-title');
            if (siteTitle) {
                siteTitle.textContent = newval;
            }
        });
    });

    // Rich Text Setting
    wp.customize('digifusion_rich_text_setting', function(value) {
        value.bind(function(newval) {
            // Replace with actual selectors
            const siteDescription = document.querySelector('.site-description');
            if (siteDescription) {
                siteDescription.innerHTML = newval;
            }
        });
    });

    // Image Upload
    wp.customize('digifusion_image_upload', function(value) {
        value.bind(function(newval) {
            const siteLogo = document.querySelector('.site-logo');
            const siteLogoWrapper = document.querySelector('.site-logo-wrapper');
            
            if (newval && siteLogo) {
                siteLogo.setAttribute('src', newval);
                
                if (siteLogoWrapper) {
                    siteLogoWrapper.style.display = '';
                }
            } else if (siteLogoWrapper) {
                siteLogoWrapper.style.display = 'none';
            }
        });
    });

    // Helper function to parse dimensions values
    function parseDimensionsValue(value, device) {
        try {
            const values = JSON.parse(value);
            if (values && values[device]) {
                return values[device];
            }
        } catch (e) {
            console.error('Error parsing dimensions value:', e);
        }
        return { top: '', right: '', bottom: '', left: '', unit: 'px' };
    }

    // Helper function to parse range values
    function parseRangeValue(value, device) {
        try {
            const values = JSON.parse(value);
            if (values && values[device]) {
                return values[device];
            }
        } catch (e) {
            console.error('Error parsing range value:', e);
        }
        return { value: '', unit: 'px' };
    }

    // Helper function to parse box shadow values
    function parseBoxShadowValue(value, state) {
        try {
            const values = JSON.parse(value);
            if (values && values[state]) {
                return values[state];
            }
        } catch (e) {
            console.error('Error parsing box shadow value:', e);
        }
        
        return {
            enable: false,
            color: 'rgba(0, 0, 0, 0.2)',
            horizontal: 0,
            vertical: 0,
            blur: 0,
            spread: 0,
            position: 'outset'
        };
    }

    // Helper function to generate box shadow CSS
    function getBoxShadowCSS(shadow) {
        if (!shadow.enable) return 'none';
        
        const inset = shadow.position === 'inset' ? 'inset ' : '';
        return `${inset}${shadow.horizontal}px ${shadow.vertical}px ${shadow.blur}px ${shadow.spread}px ${shadow.color}`;
    }

    // Dimensions Setting
    wp.customize('digifusion_dimensions_setting', function(value) {
        value.bind(function(newval) {
            // Apply for each device
            ['desktop', 'tablet', 'mobile'].forEach(function(device) {
                const dimensions = parseDimensionsValue(newval, device);
                const selector = device === 'desktop' ? '.site-content' : 
                               device === 'tablet' ? '.tablet .site-content' : 
                               '.mobile .site-content';
                
                // Only apply if dimensions are set
                if (dimensions.top !== '' || dimensions.right !== '' || dimensions.bottom !== '' || dimensions.left !== '') {
                    const top = dimensions.top !== '' ? `${dimensions.top}${dimensions.unit}` : '0';
                    const right = dimensions.right !== '' ? `${dimensions.right}${dimensions.unit}` : '0';
                    const bottom = dimensions.bottom !== '' ? `${dimensions.bottom}${dimensions.unit}` : '0';
                    const left = dimensions.left !== '' ? `${dimensions.left}${dimensions.unit}` : '0';
                    
                    const targetElements = document.querySelectorAll(selector);
                    targetElements.forEach(function(element) {
                        element.style.padding = `${top} ${right} ${bottom} ${left}`;
                    });
                }
            });
        });
    });

    // Range Setting
    wp.customize('digifusion_range_setting', function(value) {
        value.bind(function(newval) {
            // Apply for each device
            ['desktop', 'tablet', 'mobile'].forEach(function(device) {
                const range = parseRangeValue(newval, device);
                const selector = device === 'desktop' ? 'body' : 
                               device === 'tablet' ? '.tablet body' : 
                               '.mobile body';
                
                // Only apply if value is set
                if (range.value !== '') {
                    const targetElements = document.querySelectorAll(selector);
                    targetElements.forEach(function(element) {
                        element.style.fontSize = `${range.value}${range.unit}`;
                    });
                }
            });
        });
    });

    // Color Setting
    wp.customize('digifusion_color_setting', function(value) {
        value.bind(function(newval) {
            const bodyElement = document.querySelector('body');
            if (bodyElement) {
                bodyElement.style.color = newval;
            }
        });
    });

    // Box Shadow Setting
    wp.customize('digifusion_box_shadow_setting', function(value) {
        value.bind(function(newval) {
            const normalShadow = parseBoxShadowValue(newval, 'normal');
            const hoverShadow = parseBoxShadowValue(newval, 'hover');
            
            // Apply normal shadow
            const siteHeader = document.querySelector('.site-header');
            if (siteHeader) {
                siteHeader.style.boxShadow = getBoxShadowCSS(normalShadow);
            }
            
            // Apply hover shadow via dynamic style tag
            let styleTag = document.getElementById('digifusion-box-shadow-hover-style');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'digifusion-box-shadow-hover-style';
                document.head.appendChild(styleTag);
            }
            
            styleTag.textContent = `
                .site-header:hover {
                    box-shadow: ${getBoxShadowCSS(hoverShadow)};
                }
            `;
        });
    });

})();