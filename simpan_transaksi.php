<?php
session_start();
include('crudBarang.php');

// Cek apakah user sudah login
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['Username'];
$tanggal = date("Y-m-d"); // Format tanggal YYYY-MM-DD

// Ambil nomor penjualan terakhir dan buat nomor baru
$nomorPenjualan = getNomorPenjualan() + 1;

// Hitung total penjualan user ini
$total = getTotalPenjualan($username);

// Pastikan ada total transaksi
if ($total > 0) {
    $koneksi = koneksiToko();

    // Mulai transaksi database (otomatis rollback kalau error)
    mysqli_begin_transaction($koneksi);

    try {
        // Simpan ke tabel penjualan
        $sqlPenjualan = "INSERT INTO penjualan (noPenjualan, tanggalPenjualan, Total, Username) 
                         VALUES ('$nomorPenjualan', '$tanggal', '$total', '$username')";
        $queryPenjualan = mysqli_query($koneksi, $sqlPenjualan);

        if (!$queryPenjualan) {
            throw new Exception("Gagal simpan penjualan utama.");
        }

        // Ambil semua data detail sementara user
        $sqlDetail = "SELECT * FROM detailbarang WHERE Username = '$username'";
        $hasilDetail = mysqli_query($koneksi, $sqlDetail);

        while ($row = mysqli_fetch_assoc($hasilDetail)) {
            $kodeBarang = $row['kodeBarang'];
            $namaBarang = $row['namaBarang'];
            $hargaBarang = $row['hargaBarang'];
            $jumlahBarang = $row['jumlahBarang'];

            // Simpan ke tabel detailpenjualan
            $sqlDetailPenjualan = "INSERT INTO detailpenjualan (nomorPenjualan, kodeBarang, harga, jumlah) 
                       VALUES ('$nomorPenjualan', '$kodeBarang', '$hargaBarang', '$jumlahBarang')";
            $queryDetail = mysqli_query($koneksi, $sqlDetailPenjualan);

            if (!$queryDetail) {
                throw new Exception("Gagal simpan detail barang: $kodeBarang");
            }
        }

        // Jika semua sukses
        mysqli_commit($koneksi);

        // Bersihkan keranjang detailbarang user
        $hapusDetail = "DELETE FROM detailbarang WHERE Username = '$username'";
        mysqli_query($koneksi, $hapusDetail);

        echo "<script>
                alert('Transaksi berhasil disimpan!');
                window.location.href='penjualan.php';
              </script>";
        exit;
    } catch (Exception $e) {
        mysqli_rollback($koneksi);

        echo "<script>
                alert('Gagal menyimpan transaksi: " . $e->getMessage() . "');
                window.location.href='penjualan.php';
              </script>";
        exit;
    }

} else {
    echo "<script>
            alert('Tidak ada barang yang ditransaksikan!');
            window.location.href='penjualan.php';
          </script>";
    exit;
}
?>