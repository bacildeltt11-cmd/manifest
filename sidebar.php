<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current_page = basename($_SERVER['PHP_SELF']);

// Tentukan role
$is_boss = isset($_SESSION['login_rifqy']) && $_SESSION['login_rifqy'] === 'Boss';
$is_admin = !$is_boss;
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner">
        <h2>CV. MANUNGGAL</h2>

        <!-- Lock Indicator: tampil HANYA untuk admin -->
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>

        <?php if ($is_boss): ?>
        <a href="input_keberangkatan.php" class="nav-link <?= $current_page == 'input_keberangkatan.php' ? 'active' : '' ?>">➕ Buat Manifest</a>
        <?php else: ?>
        <!-- Non-Boss: TIDAK ADA tombol Input Muatan. Hanya bisa akses via klik event di kalender -->
        <a href="#"
           class="nav-link nav-link-locked"
           onclick="event.preventDefault(); showLockWarning(event);">
            📦 Input Muatan
            <span class="nav-lock-icon">🔒</span>
        </a>
        <?php endif; ?>

        <a href="data.php" class="nav-link <?= $current_page == 'data.php' ? 'active' : '' ?>">📜 Riwayat Arsip</a>

        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin keluar?');">🚪 Keluar</a>
    </div>
</div>

<!-- Sidebar Overlay for Mobile -->
<div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebarMobile()"></div>

<!-- Lock Warning Modal -->
<?php if ($is_admin): ?>
<div id="lockWarningModal" class="modal-overlay-lock" style="display:none;">
    <div class="modal-lock">
        <div class="modal-lock-icon">🔒</div>
        <h4>Menu Terkunci</h4>
        <p>Input Muatan hanya bisa diakses dari Kalender.<br>Klik salah satu event jadwal yang sudah dibuat Boss.</p>
        <button class="btn btn-secondary" onclick="document.getElementById('lockWarningModal').style.display='none'">Tutup</button>
    </div>
</div>
<?php endif; ?>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isMobile = window.innerWidth <= 1024;

    if (isMobile) {
        // Mobile: slide in/out from left
        sidebar.classList.toggle('collapsed');
        if (overlay) overlay.classList.toggle('active');
    } else {
        // Desktop: collapse width
        const isCollapsed = sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }
}

function closeSidebarMobile() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.add('collapsed');
    if (overlay) overlay.classList.remove('active');
}

function showLockWarning(e) {
    e.preventDefault();
    var modal = document.getElementById('lockWarningModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

(function() {
    const isMobile = window.innerWidth <= 1024;
    const sidebar = document.getElementById('sidebar');
    if (isMobile) {
        // Always start collapsed on mobile/tablet so sidebar doesn't obstruct the view
        if (sidebar) sidebar.classList.add('collapsed');
    } else {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            if (sidebar) sidebar.classList.add('collapsed');
        }
    }
})();
</script>