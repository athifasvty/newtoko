<?php
session_start();
include("koneksi.php"); // Tambahkan ini untuk mendefinisikan $koneksi
include("crudBarang.php");
$tanggal = date("d/m/Y");
$username = $_SESSION['Username'];

// Tombol cari di klik, maka mencari barang berdasarkan kode barang
if (array_key_exists('btnCari', $_POST)) {
    $kodeBarang = trim($_POST['kodeBarang']);
    $_SESSION['kodeBarang'] = $kodeBarang;

    // Simpan hasil pencarian
    $hasil = cariBarang($kodeBarang);

    if ($hasil != null) {
        $_SESSION['kodeBarang'] = $kodeBarang;
        $_SESSION['hasil'] = $hasil;
    } else {
        $_SESSION['kodeBarang'] = '';
        unset($_SESSION['hasil']);
    }
    header("location:penjualan.php");
    exit; // Tambahkan exit setelah redirect
}

// Jika tombol tambah di klik..
if (array_key_exists('btnTambah', $_POST)) {
    $kodeBarang = $_SESSION['kodeBarang'];
    if (!empty($kodeBarang)) {
        $hasil = $_SESSION['hasil'];
        $namaBarang = $hasil['namaBarang'];
        $hargaBarang = $hasil['hargaBarang'];
        $jumlahBarang = $_POST['jumlahBarang'];
        tambahDetail($username, $kodeBarang, $namaBarang, $hargaBarang, $jumlahBarang);
    }

    $_SESSION['kodeBarang'] = '';
    unset($_SESSION['hasil']);
    header("location:penjualan.php");
    exit; // Tambahkan exit setelah redirect
}

// Jika tombol simpan di klik..
if (array_key_exists('btnSimpan', $_POST)) {
    $nomorPenjualan = getNomorPenjualan() + 1;
    $total = getTotalPenjualan($username);
    if ($total != 0) {
        // Simpan data ke tabel penjualan
        tambahPenjualan($nomorPenjualan, $tanggal, $total, $username);
        // Simpan data ke tabel detailPenjualan
        tambahDetailPenjualan($nomorPenjualan, $username);

        // Bersihkan tabel sementara (detailbarang) setelah penjualan selesai menggunakan prepared statement
        $queryClear = "DELETE FROM detailbarang WHERE Username = ?";
        $stmt = mysqli_prepare($koneksi, $queryClear);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Tambahkan pesan sukses
        $_SESSION['success'] = "Transaksi dengan nomor $nomorPenjualan berhasil disimpan.";
    } else {
        $_SESSION['error'] = "Tidak ada barang untuk disimpan.";
    }
    header("location:penjualan.php");
    exit; // Tambahkan exit setelah redirect
}

mysqli_close($koneksi);
?>