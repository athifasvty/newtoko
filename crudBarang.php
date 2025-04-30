<?php
require_once('koneksi.php');

function bacaSemuaBarang() {
    $data = array();
    $sql = 'select * from barang';
    $koneksi = koneksiToko();
    $hasil = mysqli_query($koneksi, $sql);
    $i = 0;
    while ($baris = mysqli_fetch_assoc($hasil)) {
        $data[$i]['kodeBarang'] = $baris['kodeBarang'];
        $data[$i]['namaBarang'] = $baris['namaBarang'];
        $data[$i]['hargaBarang'] = $baris['hargaBarang'];
        $data[$i]['Stok'] = $baris['Stok'];
        $i++;
    }
    mysqli_close($koneksi);
    return $data;
}

function cariBarang($kodeBarang) {
    $koneksi = koneksiToko();
    $sql = "select * from barang where kodeBarang = '$kodeBarang'";
    $hasil = mysqli_query($koneksi, $sql);
    $jumlah = mysqli_num_rows($hasil);
    if ($jumlah > 0) {
        $baris = mysqli_fetch_assoc($hasil);
        $data['kodeBarang'] = $baris['kodeBarang'];
        $data['namaBarang'] = $baris['namaBarang'];
        $data['hargaBarang'] = $baris['hargaBarang'];
        $data['Stok'] = $baris['Stok'];
        mysqli_close($koneksi);
    } else {
        $data = null;
    }
    return $data;
}

function dataDetailBarang($username) {
    $data = array();
    $sql = "Select * from detailbarang where Username = '$username'";
    $koneksi = koneksiToko();
    $hasil = mysqli_query($koneksi, $sql);
    $i = 0;
    while ($baris = mysqli_fetch_assoc($hasil)) {
        $data[$i]['Username'] = $baris['Username'];
        $data[$i]['kodeBarang'] = $baris['kodeBarang'];
        $data[$i]['namaBarang'] = $baris['namaBarang'];
        $data[$i]['hargaBarang'] = $baris['hargaBarang'];
        $data[$i]['jumlahBarang'] = $baris['jumlahBarang'];
        $data[$i]['subtotal'] = $baris['subtotal'];
        $i++;
    }
    mysqli_close($koneksi);
    return $data;
}

function bacaSemuaDetailBarang() {
    $data = array();
    $sql = "Select * from detailbarang";
    $koneksi = koneksiToko();
    $hasil = mysqli_query($koneksi, $sql);
    $i = 0;
    while ($baris = mysqli_fetch_assoc($hasil)) {
        $data[$i]['Username'] = $baris['Username'];
        $data[$i]['kodeBarang'] = $baris['kodeBarang'];
        $data[$i]['namaBarang'] = $baris['namaBarang'];
        $data[$i]['hargaBarang'] = $baris['hargaBarang'];
        $data[$i]['jumlahBarang'] = $baris['jumlahBarang'];
        $data[$i]['subtotal'] = $baris['subtotal'];
        $i++;
    }
    mysqli_close($koneksi);
    return $data;
}

function tambahDetail($username, $kodeBarang, $namaBarang, $hargaBarang, $jumlahBarang){
    $koneksi = koneksiToko();
    $subTotal = $hargaBarang * $jumlahBarang;

    // Validasi stok sebelum menambah detail
    $sqlStok = "SELECT Stok FROM barang WHERE kodeBarang = '$kodeBarang'";
    $resultStok = mysqli_query($koneksi, $sqlStok) or die(mysqli_error($koneksi));
    $dataStok = mysqli_fetch_assoc($resultStok);

    if ($dataStok['Stok'] >= $jumlahBarang) {
        // Kurangi stok
        $sqlUpdate = "UPDATE barang SET Stok = Stok - $jumlahBarang WHERE kodeBarang = '$kodeBarang'";
        mysqli_query($koneksi, $sqlUpdate) or die(mysqli_error($koneksi));

        // Simpan ke detailbarang
        $sql = "INSERT INTO detailbarang 
                (Username, kodeBarang, namaBarang, hargaBarang, jumlahBarang, subtotal) 
                VALUES 
                ('$username', '$kodeBarang', '$namaBarang', '$hargaBarang', '$jumlahBarang', '$subTotal')";
        
        $hasil = 0;
        if(mysqli_query($koneksi, $sql)){
            $hasil = 1;
        }
    } else {
        // Jika stok tidak cukup, simpan pesan error ke session
        $_SESSION['error'] = "Stok barang dengan kode $kodeBarang tidak cukup! Stok tersedia: " . $dataStok['Stok'];
        $hasil = 0;
    }
    
    mysqli_close($koneksi);
    return $hasil;
}

function formatRupiah($angka) {
    $angka = floatval($angka); // mastiin selalu angka
    return "Rp " . number_format($angka,0,',','.');
}

function hapusDetailBarang($username, $kodeBarang) {
    $koneksi = koneksiToko();
    
    try {
        // Query untuk menghapus barang dari detail transaksi
        $sql = "DELETE FROM detailbarang WHERE Username = ? AND kodeBarang = ?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("ss", $username, $kodeBarang);
        $hasil = $stmt->execute();
        
        $stmt->close();
        $koneksi->close();
        
        return $hasil;
    } catch (Exception $e) {
        // Jika terjadi error
        $koneksi->close();
        return false;
    }
}

function getNomorPenjualan(){
    //memperoleh nomor penjualan terakhir dari tabel penjualan
    $sql = "select noPenjualan from penjualan order by noPenjualan desc limit 1";
    $koneksi = koneksiToko();
    $hasil = mysqli_query($koneksi, $sql);
    $jumlah = mysqli_num_rows($hasil);
    if($jumlah > 0){
        $baris = mysqli_fetch_assoc($hasil);
        $nomorPenjualan = $baris['noPenjualan'];
    }else{
        $nomorPenjualan = 0;
    }
    mysqli_close($koneksi);
    return $nomorPenjualan;
}

function getTotalPenjualan($username){
    $total = 0;
    $sql = "select subtotal from detailbarang where Username = '$username' ";
    //memperoleh total dari username yang diberikan
    // lengkapi perintah sql nya
    $koneksi = koneksiToko();
    $hasil = mysqli_query($koneksi,$sql);
    $baris = mysqli_fetch_assoc($hasil);
    $subTotalDetail = $baris['subtotal'];
    $total = $subTotalDetail + $total;
    if($total==NULL){
        $total=0;
    }
    mysqli_close($koneksi);
    return $total;
}

function tambahPenjualan($nomorPenjualan,$tanggal,$total,$username){
    $koneksi = koneksiToko();
    $tanggal = date("Y/m/d");
    $sql = "INSERT INTO penjualan (noPenjualan, tanggalPenjualan, Total, Username)
            VALUES ('$nomorPenjualan', '$tanggal', '$total', '$username')";
    $hasil = 0;
    if(mysqli_query($koneksi,$sql)){
        $hasil=1;
        mysqli_close($koneksi);
    }
    return $hasil;
}

function tambahDetailPenjualan($nomorPenjualan, $username) {
    $koneksi = koneksiToko();

    // Ambil data dari detailbarang
    $sqlSelect = "SELECT kodeBarang, namaBarang, hargaBarang, jumlahBarang, subtotal 
                  FROM detailbarang 
                  WHERE Username = ?";
    $stmtSelect = mysqli_prepare($koneksi, $sqlSelect);
    if (!$stmtSelect) {
        die("Prepare failed: " . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmtSelect, "s", $username);
    mysqli_stmt_execute($stmtSelect);
    $result = mysqli_stmt_get_result($stmtSelect);

    $berhasilCopy = true;
    while ($row = mysqli_fetch_assoc($result)) {
        $kodeBarang = $row['kodeBarang'];
        $namaBarang = $row['namaBarang'];
        $hargaBarang = $row['hargaBarang'];
        $jumlahBarang = $row['jumlahBarang'];
        $subtotal = $row['subtotal'];

        // Insert ke detailpenjualan
        $sqlInsert = "INSERT INTO detailpenjualan (nomorPenjualan, kodeBarang, namaBarang, hargaBarang, jumlahBarang, subtotal) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($koneksi, $sqlInsert);
        if (!$stmtInsert) {
            die("Prepare failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmtInsert, "issdid", $nomorPenjualan, $kodeBarang, $namaBarang, $hargaBarang, $jumlahBarang, $subtotal);
        if (!mysqli_stmt_execute($stmtInsert)) {
            $berhasilCopy = false;
        }
        mysqli_stmt_close($stmtInsert);
    }
    
    mysqli_stmt_close($stmtSelect);

    // Jika copy berhasil, hapus data di detailbarang
    if ($berhasilCopy && mysqli_num_rows($result) > 0) {
        $sqlDelete = "DELETE FROM detailbarang WHERE Username = ?";
        $stmtDelete = mysqli_prepare($koneksi, $sqlDelete);
        if (!$stmtDelete) {
            die("Prepare failed: " . mysqli_error($koneksi));
        }
        mysqli_stmt_bind_param($stmtDelete, "s", $username);
        mysqli_stmt_execute($stmtDelete);
        mysqli_stmt_close($stmtDelete);
    }

    mysqli_close($koneksi);
    return $berhasilCopy;
}

// Fungsi untuk menambah barang baru
function tambahBarang($kodeBarang, $namaBarang, $hargaBarang, $stok) {
    $koneksi = koneksiToko();
    $sql = "INSERT INTO barang (kodeBarang, namaBarang, hargaBarang, Stok) 
            VALUES ('$kodeBarang', '$namaBarang', '$hargaBarang', '$stok')";
    $hasil = 0;
    if (mysqli_query($koneksi, $sql)) {
        $hasil = 1;
    }
    mysqli_close($koneksi);
    return $hasil;
}

// Fungsi untuk menghapus barang
function hapusBarang($kodeBarang) {
    $koneksi = koneksiToko();
    $sql = "DELETE FROM barang WHERE kodeBarang = '$kodeBarang'";
    $hasil = 0;
    if (mysqli_query($koneksi, $sql)) {
        $hasil = 1;
    }
    mysqli_close($koneksi);
    return $hasil;
}

// Fungsi untuk mengedit barang
function editBarang($kodeBarang, $namaBarang, $hargaBarang, $stok) {
    $koneksi = koneksiToko();
    $sql = "UPDATE barang SET namaBarang = '$namaBarang', hargaBarang = '$hargaBarang', Stok = '$stok' 
            WHERE kodeBarang = '$kodeBarang'";
    $hasil = 0;
    if (mysqli_query($koneksi, $sql)) {
        $hasil = 1;
    }
    mysqli_close($koneksi);
    return $hasil;
}
?>