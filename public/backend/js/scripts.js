/*!
    * Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2023 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Center the active sidebar link vertically so a full page reload
    // does not drop the user back to the top of the menu.
    const sidenavMenu = document.querySelector('.sb-sidenav-menu');
    const activeLink  = sidenavMenu ? sidenavMenu.querySelector('.nav-link.active') : null;

    if (sidenavMenu && activeLink) {
        const centerActiveLink = () => {
            const menuRect = sidenavMenu.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();
            const target   = sidenavMenu.scrollTop
                           + (linkRect.top - menuRect.top)
                           - (menuRect.height / 2)
                           + (linkRect.height / 2);

            sidenavMenu.scrollTo({ top: Math.max(0, target), behavior: 'auto' });
        };

        // Run after layout (and once more when fonts/images settle).
        requestAnimationFrame(() => requestAnimationFrame(centerActiveLink));
        window.addEventListener('load', centerActiveLink);
    }

});
