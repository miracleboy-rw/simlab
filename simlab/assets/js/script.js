document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebarNav');
    var overlay = document.getElementById('sidebarOverlay');
    var toggle = document.getElementById('sidebarToggle');

    function openNav() {
        document.body.classList.add('nav-open');
    }

    function closeNav() {
        document.body.classList.remove('nav-open');
    }

    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (document.body.classList.contains('nav-open')) {
                closeNav();
            } else {
                openNav();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeNav);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNav();
    });

    document.querySelectorAll('.sidebar-nav a, .sidebar-footer a').forEach(function(link) {
        link.addEventListener('click', closeNav);
    });

    function labelMobileTables() {
        document.querySelectorAll('.table-wrapper table').forEach(function(table) {
            var headers = [];
            table.querySelectorAll('thead th').forEach(function(th) {
                headers.push(th.textContent.trim());
            });
            if (headers.length === 0) return;
            table.querySelectorAll('tbody tr').forEach(function(tr) {
                tr.querySelectorAll('td').forEach(function(td, i) {
                    if (headers[i]) {
                        td.setAttribute('data-label', headers[i]);
                    }
                });
            });
        });
    }
    labelMobileTables();

    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(el) {
            setTimeout(function() {
                el.style.transition = 'all 0.5s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(function() { el.remove(); }, 500);
            }, 5000);
        });
    }, 100);
});
