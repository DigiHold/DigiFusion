(()=>{(function(){"use strict";let d={};function r(n,i){let e=document.getElementById(n);e||(e=document.createElement("style"),e.id=n,document.head.appendChild(e)),e.textContent=i}function l(n){try{return JSON.parse(n)||{}}catch(i){return console.error("Error parsing color group value:",i),{}}}function f(n){try{return JSON.parse(n)||{}}catch(i){return console.error("Error parsing typography value:",i),{}}}function a(n,i){let e="",o=[];if(i.fontFamily&&(o.push(`font-family: '${i.fontFamily}'`),!i.fontFamily.includes(",")&&i.fontFamily!=="system-ui"&&g(i.fontFamily,i.fontWeight||"400")),i.fontWeight&&o.push(`font-weight: ${i.fontWeight}`),i.fontStyle&&i.fontStyle!=="normal"&&o.push(`font-style: ${i.fontStyle}`),i.textTransform&&o.push(`text-transform: ${i.textTransform}`),i.textDecoration&&o.push(`text-decoration: ${i.textDecoration}`),i.fontSize&&i.fontSize.desktop!==void 0&&i.fontSize.desktop!==""){let t=i.fontSizeUnit||"px";o.push(`font-size: ${i.fontSize.desktop}${t}`)}if(i.lineHeight&&i.lineHeight.desktop!==void 0&&i.lineHeight.desktop!==""){let t=i.lineHeightUnit||"em";o.push(`line-height: ${i.lineHeight.desktop}${t}`)}if(i.letterSpacing&&i.letterSpacing.desktop!==void 0&&i.letterSpacing.desktop!==""){let t=i.letterSpacingUnit||"px";o.push(`letter-spacing: ${i.letterSpacing.desktop}${t}`)}o.length>0&&(e+=`${n} { ${o.join("; ")}; }`);let s=[];if(i.fontSize&&i.fontSize.tablet!==void 0&&i.fontSize.tablet!==""){let t=i.fontSizeUnit||"px";s.push(`font-size: ${i.fontSize.tablet}${t}`)}if(i.lineHeight&&i.lineHeight.tablet!==void 0&&i.lineHeight.tablet!==""){let t=i.lineHeightUnit||"em";s.push(`line-height: ${i.lineHeight.tablet}${t}`)}if(i.letterSpacing&&i.letterSpacing.tablet!==void 0&&i.letterSpacing.tablet!==""){let t=i.letterSpacingUnit||"px";s.push(`letter-spacing: ${i.letterSpacing.tablet}${t}`)}s.length>0&&(e+=`@media (max-width: 991px) { ${n} { ${s.join("; ")}; } }`);let c=[];if(i.fontSize&&i.fontSize.mobile!==void 0&&i.fontSize.mobile!==""){let t=i.fontSizeUnit||"px";c.push(`font-size: ${i.fontSize.mobile}${t}`)}if(i.lineHeight&&i.lineHeight.mobile!==void 0&&i.lineHeight.mobile!==""){let t=i.lineHeightUnit||"em";c.push(`line-height: ${i.lineHeight.mobile}${t}`)}if(i.letterSpacing&&i.letterSpacing.mobile!==void 0&&i.letterSpacing.mobile!==""){let t=i.letterSpacingUnit||"px";c.push(`letter-spacing: ${i.letterSpacing.mobile}${t}`)}return c.length>0&&(e+=`@media (max-width: 719px) { ${n} { ${c.join("; ")}; } }`),e}function g(n,i="400"){if(!n||n.includes(",")||n==="system-ui")return;(i===""||!i)&&(i="400"),d[n]||(d[n]=new Set(["400"])),d[n].add(i);let e=document.querySelector(`link[data-font-family="${n}"]`);e&&e.remove();let o=Array.from(d[n]).sort((t,m)=>parseInt(t)-parseInt(m)),s=`https://fonts.googleapis.com/css?family=${n.replace(/ /g,"+")}:${o.join(",")}&display=swap`,c=document.createElement("link");c.rel="stylesheet",c.href=s,c.setAttribute("data-font-family",n),document.head.appendChild(c)}wp.customize("digifusion_global_colors",function(n){n.bind(function(i){let e=l(i),o=":root {";Object.keys(e).forEach(s=>{e[s]&&(o+=`--digi-${s}: ${e[s]};`)}),o+="}",r("digifusion-global-colors-style",o)})}),wp.customize("digifusion_body_colors",function(n){n.bind(function(i){let e=l(i),o="";e.background&&(o+=`
                    body {
                        background-color: ${e.background};
                    }
                `),e.text&&(o+=`
                    body {
                        color: ${e.text};
                    }
                `),e.headings&&(o+=`
                    h1, h2, h3, h4, h5, h6,
                    .digi-post-title,
                    .digi-post-title-single,
                    .digi-page-title,
					.digi-page-description p,
                    .digi-related-title,
                    .digi-related-post-title,
                    .digi-author-name {
                        color: ${e.headings};
                    }
                `),r("digifusion-body-colors-style",o)})}),wp.customize("digifusion_button_colors",function(n){n.bind(function(i){let e=l(i),o="";(e.background||e.text)&&(o+=`
                    button.digi,
					.digi-button,
                    input[type="submit"],
                    .digi-share-btn,
                    .digi-author-social-link,
                    .digi-search-submit,
                    .woocommerce ul.products li.product .button {
                `,e.background&&(o+=`background-color: ${e.background};`),e.text&&(o+=`color: ${e.text};`),o+="}"),(e.background_hover||e.text_hover)&&(o+=`
                    button.digi:hover,
					.digi-button:hover,
                    input[type="submit"]:hover,
                    .digi-share-btn:hover,
                    .digi-author-social-link:hover,
                    .digi-search-submit:hover,
                    .woocommerce ul.products li.product .button:hover {
                `,e.background_hover&&(o+=`background-color: ${e.background_hover};`),e.text_hover&&(o+=`color: ${e.text_hover};`),o+="}"),r("digifusion-button-colors-style",o)})}),wp.customize("digifusion_link_colors",function(n){n.bind(function(i){let e=l(i),o="";e.normal&&(o+=`
                    a,
                    .digi-title-link,
                    .digi-comments-link,
                    .digi-author-name {
                        color: ${e.normal};
                    }
                `),e.hover&&(o+=`
                    a:hover,
                    .digi-title-link:hover,
                    .digi-comments-link:hover,
                    .digi-author-name:hover {
                        color: ${e.hover};
                    }
                `),r("digifusion-link-colors-style",o)})}),wp.customize("digifusion_header_colors",function(n){n.bind(function(i){let e=l(i),o="";e.background&&(o+=`
                    .site-header {
                        background-color: ${e.background};
                    }
                `),r("digifusion-header-colors-style",o)})}),wp.customize("digifusion_menu_colors",function(n){n.bind(function(i){let e=l(i),o="";e.normal&&(o+=`
                    .digi-header-nav a,
                    .digi-nav-menu a,
                    .digi-site-name {
                        color: ${e.normal};
                    }
                `),e.hover&&(o+=`
                    .digi-header-nav a:hover,
                    .digi-nav-menu a:hover {
                        color: ${e.hover};
                    }
                `),e.current&&(o+=`
                    .digi-header-nav .current-menu-item > a,
                    .digi-header-nav .current-menu-ancestor > a,
                    .digi-nav-menu .current-menu-item > a,
                    .digi-nav-menu .current-menu-ancestor > a {
                        color: ${e.current};
                    }
                `),r("digifusion-menu-colors-style",o)})}),wp.customize("digifusion_mobile_icon_colors",function(n){n.bind(function(i){let e=l(i),o="";e.normal&&(o+=`
                    .digi-menu-bars span {
                        background-color: ${e.normal};
                    }
                `),e.hover&&(o+=`
                    .digi-menu-toggle:hover .digi-menu-bars span {
                        background-color: ${e.hover};
                    }
                `),e.active&&(o+=`
                    body.mopen .digi-menu-bars span {
                        background-color: ${e.active};
                    }
                `),r("digifusion-mobile-icon-colors-style",o)})}),wp.customize("digifusion_mobile_submenu_colors",function(n){n.bind(function(i){let e=l(i),o="";e.background&&(o+=`
                    @media (max-width: 991px) {
                        .digi-header-nav,
                        .digi-header-nav .sub-menu {
                            background-color: ${e.background};
                        }
                    }
                `),e.normal&&(o+=`
                    @media (max-width: 991px) {
                        .digi-header-nav a,
                        .digi-nav-menu a {
                            color: ${e.normal};
                        }
                    }
                `),e.hover&&(o+=`
                    @media (max-width: 991px) {
                        .digi-header-nav a:hover,
                        .digi-nav-menu a:hover {
                            color: ${e.hover};
                        }
                    }
                `),e.active&&(o+=`
                    @media (max-width: 991px) {
                        .digi-header-nav .current-menu-item > a,
                        .digi-header-nav .current-menu-ancestor > a,
                        .digi-nav-menu .current-menu-item > a,
                        .digi-nav-menu .current-menu-ancestor > a {
                            color: ${e.active};
                        }
                    }
                `),r("digifusion-mobile-submenu-colors-style",o)})}),wp.customize("digifusion_footer_colors",function(n){n.bind(function(i){let e=l(i),o="";e.background&&(o+=`
                    .site-footer {
                        background-color: ${e.background};
                    }
                `),e.heading&&(o+=`
                    .site-footer h1,
                    .site-footer h2,
                    .site-footer h3,
                    .site-footer h4,
                    .site-footer h5,
                    .site-footer h6,
                    .site-footer .widget-title {
                        color: ${e.heading};
                    }
                `),e.text&&(o+=`
                    .site-footer,
                    .site-footer p,
                    .site-footer .widget {
                        color: ${e.text};
                    }
                `),e.link&&(o+=`
                    .site-footer a,
                    .site-footer-nav a {
                        color: ${e.link};
                    }
                `),e.link_hover&&(o+=`
                    .site-footer a:hover,
                    .site-footer-nav a:hover {
                        color: ${e.link_hover};
                    }
                `),r("digifusion-footer-colors-style",o)})}),wp.customize("digifusion_woocommerce_cart_colors",function(n){n.bind(function(i){let e=l(i),o="";e.icon&&(o+=`
					.digifusion-cart-icon-link .digifusion-cart-icon-icon svg {
						fill: ${e.icon};
					}
				`),e.counter&&(o+=`
					.digifusion-cart-count {
						background-color: ${e.counter};
					}
				`),e.counter_text&&(o+=`
					.digifusion-cart-count {
						color: ${e.counter_text};
					}
				`),e.price&&(o+=`
					.digifusion-cart-total {
						color: ${e.price};
					}
				`),r("digifusion-woocommerce-cart-colors-style",o)})});let u={digifusion_body_typo:"body",digifusion_headings1_typo:"h1, .digi-page-title",digifusion_headings2_typo:"h2",digifusion_headings3_typo:"h3",digifusion_headings4_typo:"h4",digifusion_headings5_typo:"h5",digifusion_headings6_typo:"h6",digifusion_menu_typo:".digi-header-nav a, .digi-nav-menu a",digifusion_footer_typo:".site-footer"};Object.keys(u).forEach(function(n){let i=u[n],e=n.replace("digifusion_","digifusion-")+"-style";wp.customize(n,function(o){o.bind(function(s){let c=f(s),t=a(i,c);r(e,t)})})}),wp.customize.bind("ready",function(){let n=wp.customize("digifusion_global_colors");n&&n.callbacks.fireWith(n,[n.get()])})})();})();
