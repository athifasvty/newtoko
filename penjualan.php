<?php
session_start();
include 'crudBarang.php';

if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['Username'];
$tanggal = date("d F Y");

$hasil = $_SESSION['hasil'] ?? [
    'kodeBarang' => '',
    'namaBarang' => '',
    'hargaBarang' => ''
];

$detailBarang = dataDetailBarang($username);

// Hapus transaksi
if (array_key_exists('btnHapusTransaksi', $_POST)) {
    $nomorPenjualan = $_POST['nomorPenjualan'];
    $koneksi = koneksiToko();
    if (!$koneksi) {
        $_SESSION['error'] = "Koneksi database gagal: " . mysqli_connect_error();
        header("Location: penjualan.php");
        exit;
    }

    $sqlSelect = "SELECT kodeBarang, jumlah FROM detailpenjualan WHERE nomorPenjualan = ?";
    $stmtSelect = mysqli_prepare($koneksi, $sqlSelect);
    if ($stmtSelect === false) {
        $_SESSION['error'] = "Prepare statement error: " . mysqli_error($koneksi);
        header("Location: penjualan.php");
        exit;
    }
    mysqli_stmt_bind_param($stmtSelect, "i", $nomorPenjualan);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);

    $items = [];
    while ($data = mysqli_fetch_assoc($result)) {
        $items[] = $data;
    }
    mysqli_stmt_close($stmtSelect);

    if (count($items) > 0) {
        foreach ($items as $data) {
            $kodeBarang = $data['kodeBarang'];
            $jumlah = $data['jumlah'];

            if (empty($jumlah) || !is_numeric($jumlah)) {
                $_SESSION['error'] = "Jumlah untuk kode barang $kodeBarang tidak valid.";
                header("Location: penjualan.php");
                exit;
            }

            $sqlUpdate = "UPDATE barang SET Stok = Stok + ? WHERE kodeBarang = ?";
            $stmtUpdate = mysqli_prepare($koneksi, $sqlUpdate);
            if ($stmtUpdate === false) {
                $_SESSION['error'] = "Prepare statement error (update stok): " . mysqli_error($koneksi);
                header("Location: penjualan.php");
                exit;
            }
            mysqli_stmt_bind_param($stmtUpdate, "is", $jumlah, $kodeBarang);
            $success = mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);

            if (!$success) {
                $_SESSION['error'] = "Gagal mengembalikan stok untuk kode barang $kodeBarang: " . mysqli_error($koneksi);
                header("Location: penjualan.php");
                exit;
            }
        }

        $sqlDelete = "DELETE FROM detailpenjualan WHERE nomorPenjualan = ?";
        $stmtDelete = mysqli_prepare($koneksi, $sqlDelete);
        if ($stmtDelete === false) {
            $_SESSION['error'] = "Prepare statement error (delete detailpenjualan): " . mysqli_error($koneksi);
            header("Location: penjualan.php");
            exit;
        }
        mysqli_stmt_bind_param($stmtDelete, "i", $nomorPenjualan);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);

        $sqlDeletePenjualan = "DELETE FROM penjualan WHERE noPenjualan = ?";
        $stmtDeletePenjualan = mysqli_prepare($koneksi, $sqlDeletePenjualan);
        if ($stmtDeletePenjualan === false) {
            $_SESSION['error'] = "Prepare statement error (delete penjualan): " . mysqli_error($koneksi);
            header("Location: penjualan.php");
            exit;
        }
        mysqli_stmt_bind_param($stmtDeletePenjualan, "i", $nomorPenjualan);
        mysqli_stmt_execute($stmtDeletePenjualan);
        mysqli_stmt_close($stmtDeletePenjualan);

        $_SESSION['success'] = "Transaksi dengan nomor $nomorPenjualan berhasil dihapus dan stok dikembalikan.";
    } else {
        $_SESSION['error'] = "Tidak ada detail transaksi ditemukan untuk nomor $nomorPenjualan.";
    }

    header("Location: penjualan.php");
    exit;
}

$koneksi = koneksiToko();
$sql = "SELECT * FROM penjualan";
$transaksi = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penjualan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    body {
        font-family: 'Poppins', sans-serif;
        background: #f4f7f4;
        margin: 0;
        padding: 0;
    }
    .container {
        width: 92%;
        margin: auto;
        padding-top: 20px;
    }
    header {
        background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%);
        color: #fff;
        padding: 0;
        display: flex;
        justify-content: space-between;
        border-radius: 16px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 25px;
    }
    .header-title {
        padding: 24px 30px;
        display: flex;
        align-items: center;
    }
    .header-title h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 600;
        letter-spacing: 0.5px;
        position: relative;
    }
    .header-title h1:after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40px;
        height: 3px;
        background: #A5D6A7;
        border-radius: 3px;
    }
    .user-info {
        background: rgba(0,0,0,0.15);
        padding: 20px 30px;
        text-align: right;
        font-size: 14px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        min-width: 220px;
    }
    .user-info:before {
        content: '';
        position: absolute;
        left: -20px;
        top: 0;
        height: 100%;
        width: 40px;
        background: inherit;
        transform: skewX(-10deg);
    }
    .user-name {
        font-weight: 600;
        font-size: 16px;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .user-date {
        opacity: 0.8;
        font-size: 13px;
        font-weight: 300;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    .logout-btn {
        color: #fff;
        text-decoration: none;
        display: inline-block;
        background: rgba(255,255,255,0.15);
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        letter-spacing: 0.5px;
        align-self: flex-end;
    }
    .logout-btn:hover {
        background: rgba(255,255,255,0.25);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .row {
        display: flex;
        gap: 25px;
        margin-bottom: 80px; /* Increased spacing significantly */
    }
    .column {
        flex: 1;
    }
    .card {
        background: #fff;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .full-width {
        margin-bottom: 25px;
    }
    .card-title {
        margin-top: 0;
        color: #2E7D32;
        font-size: 18px;
        margin-bottom: 15px;
    }
    .search-form input[type="text"],
    .search-form input[type="number"] {
        padding: 10px;
        margin-right: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 180px;
    }
    .btn-search, .btn-add, .btn-save, .btn-delete, .btn-master {
        background-color: #66BB6A;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        color: white;
        font-weight: bold;
        font-size: 13px;
        transition: 0.2s;
        text-decoration: none; /* For btn-master link */
    }
    .btn-search:hover,
    .btn-add:hover,
    .btn-save:hover,
    .btn-master:hover {
        background-color: #43A047;
    }
    .btn-delete {
        background-color: #C62828;
    }
    .btn-delete:hover {
        background-color: #B71C1C;
    }
    .transaction-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        font-size: 14px;
    }
    .transaction-table th,
    .transaction-table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    .transaction-table th {
        background: #A5D6A7;
        color: #1B5E20;
    }
    .total-row {
        font-weight: bold;
        background-color: #E8F5E9;
    }
    .message-container {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 320px;
        z-index: 1000;
    }
    .success-message, .error-message {
        padding: 15px 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: slideIn 0.5s ease-out forwards;
        position: relative;
        overflow: hidden;
    }
    .success-message {
        color: #2E7D32;
        background: #E8F5E9;
        border-left: 5px solid #66BB6A;
    }
    .error-message {
        color: #C62828;
        background: #FFEBEE;
        border-left: 5px solid #F44336;
    }
    .success-message:before, .error-message:before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
    }
    .success-message:before {
        background: #66BB6A;
    }
    .error-message:before {
        background: #F44336;
    }
    .message-icon {
        margin-right: 12px;
        font-size: 20px;
    }
    .message-content {
        flex: 1;
    }
    .message-close {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        font-size: 16px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .message-close:hover {
        opacity: 1;
    }
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    .fadeOut {
        animation: fadeOut 0.5s ease-in forwards;
    }
    .empty-text {
        text-align: center;
        color: #888;
        font-style: italic;
        margin: 10px 0;
    }
    .action-row {
        text-align: right;
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px; /* Space between buttons */
    }
    .result-item {
        font-weight: bold;
        color: #2E7D32;
    }
    </style>
    <script>
        function confirmDelete(kodeBarang) {
            return confirm('Apakah Anda yakin ingin menghapus barang dengan kode: ' + kodeBarang + '?');
        }
        function confirmDeleteTransaksi(nomorPenjualan) {
            return confirm('Apakah Anda yakin ingin menghapus transaksi dengan nomor: ' + nomorPenjualan + '?');
        }
        
        // Function to close message notifications
        function closeMessage(element) {
            element.classList.add('fadeOut');
            setTimeout(() => {
                element.remove();
            }, 500);
        }
        
        // Auto-hide messages after 5 seconds
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const messages = document.querySelectorAll('.success-message, .error-message');
                messages.forEach(message => {
                    closeMessage(message);
                });
            }, 5000);
        });
    </script>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <h1>Transaksi Penjualan</h1>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                <div class="user-date"><?php echo $tanggal; ?></div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>

        <!-- Message notification container -->
        <div class="message-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <div class="message-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="message-content"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <button class="message-close" onclick="closeMessage(this.parentElement)"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <div class="message-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="message-content"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <button class="message-close" onclick="closeMessage(this.parentElement)"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Card Input Transaksi -->
            <div class="column">
                <div class="card">
                    <h2 class="card-title">Input Transaksi</h2>
                    <form action="prosespenjualan.php" method="post" class="search-form">
                        <input type="text" name="kodeBarang" value="<?php echo htmlspecialchars($hasil['kodeBarang']); ?>" placeholder="Masukkan kode barang...">
                        <button type="submit" name="btnCari" class="btn-search">Cari</button>
                        <a href="master_barang.php" class="btn-master"><i class="fas fa-boxes"></i> Master Barang</a><br><br>
                        Nama Barang: <span class="result-item"><?php echo htmlspecialchars($hasil['namaBarang'] ?? '-'); ?></span><br>
                        Harga: <span class="result-item"><?php echo $hasil['hargaBarang'] ? formatRupiah($hasil['hargaBarang']) : '-'; ?></span><br><br>
                        Jumlah: <input type="number" name="jumlahBarang" min="1" placeholder="Qty">
                        <button type="submit" name="btnTambah" class="btn-add">Tambah</button>
                    </form>
                </div>
            </div>

            <!-- Card Daftar Barang dalam Transaksi -->
            <div class="column">
                <div class="card">
                    <h2 class="card-title">Daftar Barang Transaksi</h2>
                    <form action="simpan_transaksi.php" method="post">
                        <?php if (empty($detailBarang)): ?>
                            <p class="empty-text">Belum ada barang dalam transaksi ini</p>
                        <?php else: ?>
                            <table class="transaction-table">
                                <tr>
                                    <th>Kode</th><th>Nama</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th>
                                </tr>
                                <?php $total = 0; foreach ($detailBarang as $barang): 
                                    $subtotal = $barang['hargaBarang'] * $barang['jumlahBarang'];
                                    $total += $subtotal; ?>
                                    <tr>
                                        <td><?= htmlspecialchars($barang['kodeBarang']) ?></td>
                                        <td><?= htmlspecialchars($barang['namaBarang']) ?></td>
                                        <td><?= formatRupiah($barang['hargaBarang']) ?></td>
                                        <td><?= $barang['jumlahBarang'] ?></td>
                                        <td><?= formatRupiah($subtotal) ?></td>
                                        <td><a href="hapusDetail.php?kode=<?= $barang['kodeBarang'] ?>" class="btn-delete" onclick="return confirmDelete('<?= $barang['kodeBarang'] ?>')">Hapus</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="4">Total</td>
                                    <td colspan="2"><?= formatRupiah($total) ?></td>
                                </tr>
                            </table>
                            <div class="action-row">
                                <button type="submit" name="btnSimpan" class="btn-save">Simpan Transaksi</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card Daftar Transaksi (Full Width) -->
        <div class="full-width">
            <div class="card">
                <h2 class="card-title">Daftar Transaksi</h2>
                <?php if (mysqli_num_rows($transaksi) == 0): ?>
                    <p class="empty-text">Belum ada transaksi</p>
                <?php else: ?>
                    <table class="transaction-table">
                        <tr>
                            <th>No.</th><th>Tanggal</th><th>Total</th><th>User</th><th>Aksi</th>
                        </tr>
                        <?php while ($row = mysqli_fetch_assoc($transaksi)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['noPenjualan']) ?></td>
                                <td><?= htmlspecialchars($row['tanggalPenjualan']) ?></td>
                                <td><?= formatRupiah($row['Total']) ?></td>
                                <td><?= htmlspecialchars($row['Username']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="nomorPenjualan" value="<?= $row['noPenjualan'] ?>">
                                        <button type="submit" name="btnHapusTransaksi" class="btn-delete" onclick="return confirmDeleteTransaksi('<?= $row['noPenjualan'] ?>')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($koneksi); ?>