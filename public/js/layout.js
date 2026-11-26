// Layout behaviour: Lucide icons and closing the mobile sidebar drawer.
if (window.lucide) {
    window.lucide.createIcons();
}

(function () {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    if (!sidebar || !backdrop) return;

    // Below 992px the "collapsed" class means the drawer is open.
    const isDrawerOpen = () => window.innerWidth < 992 && sidebar.classList.contains('collapsed');
    const close = () => sidebar.classList.remove('collapsed');

    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isDrawerOpen()) close();
    });
})();
