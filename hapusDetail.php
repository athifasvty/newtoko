<?php
session_start();
include 'koneksi.php';
include 'crudBarang.php';

// Cek login
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['Username'];
$kodeBarang = $_GET['kode'] ?? '';

if ($kodeBarang) {
    $koneksi = koneksiToko();

    // Ambil jumlah sebelum dihapus
    $sqlSelect = "SELECT jumlahBarang FROM detailbarang WHERE kodeBarang = ? AND Username = ?";
    $stmtSelect = mysqli_prepare($koneksi, $sqlSelect);
    mysqli_stmt_bind_param($stmtSelect, "ss", $kodeBarang, $username);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);
    $data = mysqli_fetch_assoc($result);
    $jumlah = $data['jumlahBarang'] ?? 0;
    mysqli_stmt_close($stmtSelect);

    // Debug: Log perubahan stok sebelum update
    $sqlStokBefore = "SELECT Stok FROM barang WHERE kodeBarang = ?";
    $stmtStokBefore = mysqli_prepare($koneksi, $sqlStokBefore);
    mysqli_stmt_bind_param($stmtStokBefore, "s", $kodeBarang);
    mysqli_stmt_execute($stmtStokBefore);
    $resultStokBefore = mysqli_stmt_get_result($stmtStokBefore);
    $rowStokBefore = mysqli_fetch_assoc($resultStokBefore);
    $stokBefore = $rowStokBefore['Stok'] ?? 0;
    mysqli_stmt_close($stmtStokBefore);

    // Hapus dari detailbarang
    $sqlDelete = "DELETE FROM detailbarang WHERE kodeBarang = ? AND Username = ?";
    $stmtDelete = mysqli_prepare($koneksi, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, "ss", $kodeBarang, $username);
    $success = mysqli_stmt_execute($stmtDelete);
    mysqli_stmt_close($stmtDelete);

    if ($success && $jumlah > 0) {
        // Kembalikan stok
        $sqlUpdate = "UPDATE barang SET Stok = Stok + ? WHERE kodeBarang = ?";
        $stmtUpdate = mysqli_prepare($koneksi, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "is", $jumlah, $kodeBarang);
        $successUpdate = mysqli_stmt_execute($stmtUpdate);
        mysqli_stmt_close($stmtUpdate);

        // Debug: Ambil stok setelah update
        $sqlStokAfter = "SELECT Stok FROM barang WHERE kodeBarang = ?";
        $stmtStokAfter = mysqli_prepare($koneksi, $sqlStokAfter);
        mysqli_stmt_bind_param($stmtStokAfter, "s", $kodeBarang);
        mysqli_stmt_execute($stmtStokAfter);
        $resultStokAfter = mysqli_stmt_get_result($stmtStokAfter);
        $rowStokAfter = mysqli_fetch_assoc($resultStokAfter);
        $stokAfter = $rowStokAfter['Stok'] ?? 0;
        mysqli_stmt_close($stmtStokAfter);

        // Debug: Log perubahan stok
        error_log("Hapus detail - Kode Barang: $kodeBarang, Jumlah: $jumlah, Stok Sebelum: $stokBefore, Stok Sesudah: $stokAfter");

        if (!$successUpdate) {
            $_SESSION['error'] = "Gagal mengembalikan stok untuk kode barang $kodeBarang.";
        }
    }

    if ($success) {
        $_SESSION['success'] = "Barang dengan kode $kodeBarang berhasil dihapus dari transaksi.";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang dengan kode $kodeBarang.";
    }

    mysqli_close($koneksi);
}

header("Location: penjualan.php");
exit;
?>