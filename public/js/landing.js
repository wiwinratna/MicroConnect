// Landing page: icons, navbar, reveal animation and hero video sound.
if (window.lucide) {
    window.lucide.createIcons();
}

(function () {
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', function () {
        navbar.classList.toggle('scrolled', window.scrollY > 10);
    });

    const toggle = document.getElementById('navToggle');
    const links = document.getElementById('navLinks');
    const setMenu = function (open) {
        links.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    toggle.addEventListener('click', function () { setMenu(!links.classList.contains('open')); });
    links.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { setMenu(false); });
    });

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(function (el) { observer.observe(el); });

    const video = document.getElementById('heroVideo');
    const mute = document.getElementById('muteToggle');
    if (video && mute) {
        mute.addEventListener('click', function () {
            video.muted = !video.muted;
            document.getElementById('iconMuted').hidden = !video.muted;
            document.getElementById('iconUnmuted').hidden = video.muted;
        });
    }
})();
