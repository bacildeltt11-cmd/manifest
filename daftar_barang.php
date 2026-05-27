<?php
session_start();

// Pengecekan login
if (!isset($_SESSION['login_rifqy'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu.';
    header("Location: login.php");
    exit;
}

include 'koneksi_mongodb.php';
include 'functions.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$is_boss = isset($_SESSION['login_rifqy']) && $_SESSION['login_rifqy'] === 'Boss';
$is_admin = !$is_boss;

// Handle tambah barang (hanya admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_barang'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }

    $nama = sanitize_string($_POST['nama_barang'] ?? '');
    $pcs = intval($_POST['pcs'] ?? 0);
    if (!empty($nama)) {
        $exist = findOneDocument("master_barang", ['nama' => $nama]);
        if (!$exist) {
            insertDocument("master_barang", ['nama' => $nama, 'pcs' => $pcs]);
            $_SESSION['success_msg'] = 'Daftar barang berhasil ditambahkan';
        } else {
            $_SESSION['error'] = 'Barang sudah terdaftar.';
        }
    } else {
        $_SESSION['error'] = 'Nama barang tidak boleh kosong.';
    }
    header("Location: daftar_barang.php");
    exit;
}

// Handle hapus barang (hanya admin)
if ($is_admin && isset($_GET['hapus'])) {
    $nama = sanitize_string($_GET['hapus']);
    if (!empty($nama)) {
        deleteDocument("master_barang", ['nama' => $nama]);
        $_SESSION['success_msg'] = 'Barang berhasil dihapus dari daftar.';
    }
    header("Location: daftar_barang.php");
    exit;
}

// Ambil data master barang
$docs = findDocuments("master_barang", [], ['sort' => ['nama' => 1]]);
$barang_list = [];
foreach ($docs as $d) {
    $arr = (array)$d;
    if (!empty($arr['nama'])) {
        $barang_list[] = ['nama' => $arr['nama'], 'pcs' => $arr['pcs'] ?? 0];
    }
}

// Jika kosong, pakai default (sama seperti api)
if (empty($barang_list)) {
    $default_names = ["Alpukat","Apel","Bawang Putih","Bawang Goreng","Bombay","Brambang","Cabe","Emping","Garam","Gula","Jipan","Kacang Hijau","Kacang Tanah","Kemiri","Kentang","Kertas","Ketan","Kol","Krupuk","Kunir","Mangga","Plastik","Rempah","Salak","Sawi","Telur","Tomat","Terong","Wortel","Trasi","Kurma","Keluak","Kacang kupas"];
    foreach ($default_names as $name) {
        $barang_list[] = ['nama' => $name, 'pcs' => 0];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang - CV. MANUNGGAL</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .barang-tag {
            background: linear-gradient(135deg, #e0f0ff, #c3d9f0);
            color: #0a4dbf;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .barang-tag:hover {
            transform: translateY(-1px);
        }
        .barang-tag .del {
            color: #e53935;
            font-weight: 900;
            font-size: 16px;
            text-decoration: none;
            line-height: 1;
            margin-left: 4px;
        }
        .barang-tag .del:hover {
            color: #c62828;
        }
        .container-barang {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            margin-top: 20px;
        }
        .success-popup {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: #d4edda; color: #155724; padding: 22px 35px;
            border-radius: 14px; box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            z-index: 9999; text-align: center; font-size: 17px; font-weight: 700;
            border: 3px solid #c3e6cb; min-width: 280px;
        }
        .success-popup .check { font-size: 28px; display: block; margin-bottom: 6px; }
        .del { color: #e53935; font-weight: 900; font-size: 16px; text-decoration: none; }
    </style>
</head>
<body>
<div class="layout-wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'top_nav.php'; ?>
        <div style="padding: 40px;">

            <h3 class="header-title">📦 Daftar Barang Master</h3>

            <?php
            // Error alert (tetap pakai div untuk error)
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger" style="margin: 15px 0; padding:12px 20px; background:#f8d7da; color:#721c24; border-radius:8px; border:1px solid #f5c6cb;">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            // Success popup trigger
            if (isset($_SESSION['success_msg'])) {
                echo '<script>window.__barangSuccess = ' . json_encode($_SESSION['success_msg']) . ';</script>';
                unset($_SESSION['success_msg']);
            }
            ?>

            <?php if ($is_admin): ?>
            <!-- Form Tambah untuk Admin -->
            <div style="background: #fff8e1; border-left: 5px solid #ff9800; padding: 18px 22px; border-radius: 12px; margin-bottom: 25px;">
                <strong style="color:#e65100;">Tambah Barang Baru</strong>
                <form method="POST" style="margin-top: 10px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                    <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Durian, Jeruk, dll" required style="max-width: 320px; width: 100%;" autocomplete="off">
                    <input type="number" name="pcs" class="form-control" placeholder="PCS (angka)" min="0" required style="max-width: 120px; width: 100%;">
                    <button type="submit" name="add_barang" class="btn btn-primary" style="padding: 10px 22px;">+ Tambahkan ke Daftar</button>
                </form>
                <small style="color:#856404; display:block; margin-top:8px;">Admin dapat menambahkan nama barang baru yang akan tersedia untuk dipilih saat input muatan.</small>
            </div>
            <?php else: ?>
            <div style="background:#f0f4f8; padding:12px 18px; border-radius:10px; margin-bottom:20px; font-size:14px; color:#555;">
                <strong>Mode Monitoring (Boss)</strong> — Daftar ini hanya untuk dilihat. Penambahan barang baru hanya dapat dilakukan oleh Admin.
            </div>
            <?php endif; ?>

            <div class="container-barang">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 15px;">
                    <h4 style="margin: 0; color: #0a4dbf;">Daftar Barang yang Tersedia (<?php echo count($barang_list); ?>)</h4>
                    <div style="position: relative; max-width: 320px; width: 100%;">
                        <input type="text" id="search-barang" class="form-control" placeholder="Cari nama barang..." style="padding-left: 35px; width: 100%; box-sizing: border-box; border-radius: 20px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #888; font-size: 14px;">🔍</span>
                    </div>
                </div>

                <?php if (!empty($barang_list)): ?>
                    <table id="barang-table" class="barang-table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background:#f0f4f8;">
                                <th style="padding:8px; text-align:left;">Nama Barang</th>
                                <th style="padding:8px; text-align:left;">PCS</th>
                                <?php if ($is_admin): ?><th style="padding:8px;">Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barang_list as $item): ?>
                                <tr data-name="<?php echo htmlspecialchars(strtolower($item['nama'])); ?>">
                                    <td style="padding:8px; border-bottom: 1px solid #eee;"><?php echo e($item['nama']); ?></td>
                                    <td style="padding:8px; border-bottom: 1px solid #eee;"><?php echo e($item['pcs']); ?></td>
                                    <?php if ($is_admin): ?>
                                        <td style="padding:8px; border-bottom: 1px solid #eee;"><a href="?hapus=<?php echo urlencode($item['nama']); ?>" class="del" onclick="return confirm('Hapus \"<?php echo e($item['nama']); ?>\" dari daftar?')">×</a></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color:#888;">Belum ada daftar barang.</p>
                <?php endif; ?>

                <div style="margin-top: 25px; font-size: 13px; color: #777;">
                    <?php if ($is_admin): ?>
                        Barang yang ditambahkan di sini akan muncul sebagai pilihan saat Admin menginput muatan di halaman Input Muatan.
                    <?php else: ?>
                        Daftar ini digunakan sebagai referensi untuk manifest. Perubahan hanya oleh Admin.
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function showSuccessPopup(message) {
    var popup = document.createElement('div');
    popup.className = 'success-popup';
    popup.innerHTML = '<span class="check">✅</span>' + message;
    document.body.appendChild(popup);

    setTimeout(function() {
        if (popup && popup.parentNode) {
            popup.parentNode.removeChild(popup);
        }
    }, 2000);
}

// Script untuk Search Barang
document.getElementById('search-barang')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#barang-table tbody tr');
    let hasVisible = false;

    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        if (name.includes(searchTerm)) {
            row.style.display = '';// show row
            hasVisible = true;
        } else {
            row.style.display = 'none';
        }
    });

    let emptyMsg = document.getElementById('empty-search-msg');
    if (!hasVisible && searchTerm !== '') {
        if (!emptyMsg) {
            emptyMsg = document.createElement('p');
            emptyMsg.id = 'empty-search-msg';
            emptyMsg.style.color = '#888';
            emptyMsg.style.width = '100%';
            emptyMsg.style.textAlign = 'center';
            emptyMsg.style.marginTop = '20px';
            emptyMsg.textContent = 'Barang "' + e.target.value + '" tidak ditemukan.';
            document.getElementById('barang-table').parentNode.appendChild(emptyMsg);
        } else {
            emptyMsg.textContent = 'Barang "' + e.target.value + '" tidak ditemukan.';
            emptyMsg.style.display = 'block';
        }
    } else if (emptyMsg) {
        emptyMsg.style.display = 'none';
    }
});

// Tampilkan popup jika ada pesan sukses dari penambahan daftar barang
if (window.__barangSuccess) {
    setTimeout(function() {
        showSuccessPopup(window.__barangSuccess);
    }, 350);
}
</script>

</body>
</html>
