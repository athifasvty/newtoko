<?php
session_start();
include("crudBarang.php");

if (array_key_exists('btnHapusTransaksi', $_POST)) {
    $nomorPenjualan = $_POST['nomorPenjualan'];
    $hasil = hapusDetailPenjualan($nomorPenjualan);
    if ($hasil) {
        $_SESSION['success'] = "Transaksi dengan nomor $nomorPenjualan berhasil dihapus dan stok telah dikembalikan.";
    } else {
        $_SESSION['error'] = "Gagal menghapus transaksi dengan nomor $nomorPenjualan.";
    }
    header("location:daftar_transaksi.php");
}

// Ambil semua transaksi dari tabel penjualan
$koneksi = koneksiToko();
$sql = "SELECT * FROM penjualan";
$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Transaksi</title>
</head>
<body>
    <h1>Daftar Transaksi</h1>
    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    ?>
    <table border="1">
        <tr>
            <th>Nomor Penjualan</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Username</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
            <tr>
                <td><?php echo $row['noPenjualan']; ?></td>
                <td><?php echo $row['tanggalPenjualan']; ?></td>
                <td><?php echo formatRupiah($row['Total']); ?></td>
                <td><?php echo $row['Username']; ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="nomorPenjualan" value="<?php echo $row['noPenjualan']; ?>">
                        <button type="submit" name="btnHapusTransaksi" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
<?php mysqli_close($koneksi); ?>