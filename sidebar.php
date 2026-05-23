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
    const isCollapsed = sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

function showLockWarning(e) {
    e.preventDefault();
    var modal = document.getElementById('lockWarningModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

(function() {
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) sidebar.classList.add('collapsed');
    }
})();
</script>