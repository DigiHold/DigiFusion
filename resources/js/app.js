document.addEventListener('DOMContentLoaded', function() {
    // --------
    // Mobile
    // --------
    const mobile = document.querySelector('.digi-menu-toggle');
    const body = document.body;
    const nav = document.querySelector('.digi-header-nav');

    if (mobile && nav) {
        mobile.addEventListener('click', function () {
            if (body.classList.contains('mopen')) {
				body.classList.remove('mopen');

                // Closing - slide up then remove class
                slideUp(nav, 300, () => {
                    nav.classList.remove('nav-open');
                });
            } else {
                body.classList.add('mopen');

                // Opening - add class then slide down
                nav.classList.add('nav-open');
                slideDown(nav, 300);
            }
        });
    }

    // Slide down function
    function slideDown(element, duration) {
        element.style.height = '0px';
        element.style.overflow = 'hidden';
        element.style.transition = `height ${duration}ms ease-in-out`;
        
        // Get the natural height
        const naturalHeight = element.scrollHeight;
        
        // Trigger animation
        requestAnimationFrame(() => {
            element.style.height = naturalHeight + 'px';
        });
        
        // Clean up after animation
        setTimeout(() => {
            element.style.height = '';
            element.style.overflow = '';
            element.style.transition = '';
        }, duration);
    }

    // Slide up function
    function slideUp(element, duration, callback) {
        const currentHeight = element.scrollHeight;
        element.style.height = currentHeight + 'px';
        element.style.overflow = 'hidden';
        element.style.transition = `height ${duration}ms ease-in-out`;
        
        // Trigger animation
        requestAnimationFrame(() => {
            element.style.height = '0px';
        });
        
        // Clean up after animation
        setTimeout(() => {
            element.style.height = '';
            element.style.overflow = '';
            element.style.transition = '';
            if (callback) callback();
        }, duration);
    }

    // --------
    // Mobile sub menu
    // --------
    const navArrows = document.querySelectorAll('.nav-arrow');

    if (navArrows) {
        navArrows.forEach(function (arrow) {
            arrow.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const parentLi = this.closest('li');
                const subMenu = parentLi.querySelector('.sub-menu');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';

                if (!subMenu) return;

                // Close other submenus at the same level first
                const parentUl = parentLi.parentElement;
                const siblingItems = parentUl.querySelectorAll(':scope > li.submenu-open');
                
                siblingItems.forEach(function(sibling) {
                    if (sibling !== parentLi) {
                        const siblingSubMenu = sibling.querySelector('.sub-menu');
                        const siblingArrow = sibling.querySelector('.nav-arrow');
                        
                        if (siblingSubMenu && siblingArrow) {
                            sibling.classList.remove('submenu-open');
                            siblingArrow.setAttribute('aria-expanded', 'false');
                            slideUp(siblingSubMenu, 300);
                        }
                    }
                });

                // Toggle current submenu
                if (isExpanded) {
                    // Closing - slide up then remove class
                    slideUp(subMenu, 300, () => {
                        parentLi.classList.remove('submenu-open');
                    });
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    // Opening - add class then slide down
                    parentLi.classList.add('submenu-open');
                    this.setAttribute('aria-expanded', 'true');
                    slideDown(subMenu, 300);
                }
            });

            // Handle keyboard navigation
            arrow.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });
    }

    // --------
    // Smooth Scroll for Anchor Links
    // --------
    const adminBar = document.getElementById('wpadminbar');
    const anchorLinks = document.querySelectorAll('a[href^="#"]');

    if (anchorLinks) {
        anchorLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                // Get ID
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const extraOffset = targetId === 'top' ? 20 : 0;

					let adminBarHeight = 0;
					if (adminBar) {
                    	adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
					}

                    // Get target position
                    const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY;

                    window.scrollTo({
                        top: targetPosition - adminBarHeight - extraOffset,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // --------
    // Share
    // --------
    const share = document.querySelectorAll( '.share__btn' );
    if ( share ) {
        document.querySelectorAll( '.share__btn' ).forEach( function( e ) {
            e.addEventListener( 'click', function(t) {
                t.preventDefault();
                const dim = { width: 700, height: 300 };
                const href = void 0 !== this.href ? this.href : this.getAttribute( 'href' );
                window.open( href, 'targetWindow', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=yes,width=' + dim.width + ',height=' + dim.height + ',top=200,left=' + ( window.innerWidth - dim.width ) / 2 );
            } );
        } );
    }
});
