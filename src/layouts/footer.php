</main>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
// Logika Notifikasi Toastify (tetap sama)
<?php if (isset($_SESSION['toast'])) { /* ... kode toastify ... */ } ?>

// --- JAVASCRIPT BARU UNTUK STRUKTUR BARU ---
const sidebar = document.getElementById('sidebar');
const contentWrapper = document.querySelector('.content-wrapper');
const sidebarToggleDesktop = document.getElementById('sidebar-toggle-desktop');
const mobileToggleButtons = document.querySelectorAll('#sidebar-toggle-mobile');
const mobileCloseButton = document.getElementById('sidebar-close-mobile');
const sidebarOverlay = document.getElementById('sidebar-overlay');

function setSidebarState(isCollapsed) {
    if (sidebar) {
        sidebar.classList.toggle('is-collapsed', isCollapsed);
    }
    // Sekarang kita mengubah margin dari content-wrapper
    if (contentWrapper) {
        contentWrapper.classList.toggle('md:ml-20', isCollapsed);
        contentWrapper.classList.toggle('md:ml-64', !isCollapsed);
    }
}

// --- LOGIKA DESKTOP ---
if (sidebar && contentWrapper && sidebarToggleDesktop) {
    sidebarToggleDesktop.addEventListener('click', () => {
        const isCollapsed = !sidebar.classList.contains('is-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        setSidebarState(isCollapsed);
    });

    sidebar.addEventListener('mouseenter', () => {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            setSidebarState(false);
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            setSidebarState(true);
        }
    });
}

// --- LOGIKA MOBILE ---
function openMobileSidebar() { sidebar.classList.remove('-translate-x-full'); sidebarOverlay.classList.remove('hidden'); }
function closeMobileSidebar() { sidebar.classList.add('-translate-x-full'); sidebarOverlay.classList.add('hidden'); }

if (mobileToggleButtons.length > 0) mobileToggleButtons.forEach(btn => btn.addEventListener('click', openMobileSidebar));
if (mobileCloseButton) mobileCloseButton.addEventListener('click', closeMobileSidebar);
if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeMobileSidebar);


document.addEventListener('DOMContentLoaded', () => {
    if (window.innerWidth >= 768 && sidebar) {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        setSidebarState(isCollapsed);
    }
});
</script>
</body>
</html>