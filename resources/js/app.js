document.addEventListener('DOMContentLoaded', function() {
    // --------
    // Mobile
    // --------
    const mobile = document.querySelector('.menu__toggle');
    const closeButton = document.querySelector('.menu__toggle_close > .menu__toggle');
    const body = document.body;

    if (mobile && closeButton) {
        // Ouvrir le menu mobile
        mobile.addEventListener('click', function () {
            body.classList.add('mopen');
        });

        // Fermer le menu mobile
        closeButton.addEventListener('click', function () {
            body.classList.remove('mopen');
        });
    }

    // --------
    // Mobile sub menu
    // --------
    const submenuToggles = document.querySelectorAll('.submenu-toggle');

    if (submenuToggles) {
        submenuToggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();

                const parentLi = this.closest('li');

                parentLi.classList.toggle('submenu-open');
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

                // Récupérer l'ID cible
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    const extraOffset = targetId === 'top' ? 20 : 0;

					let adminBarHeight = 0;
					if (adminBar) {
                    	adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
					}

                    // Calculer la position de défilement en tenant compte du header et de l'admin bar
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

    // --------
    // Lightbox
    // --------
    // const images = document.querySelectorAll( '.share__btn' );
    // if ( images ) {
	// 	new PhotoSwipeLightbox({
	// 		gallery: '.addon-gallery',
	// 		children: 'a',
	// 		pswpModule: PhotoSwipe 
	// 	}).init();
	// }
});
