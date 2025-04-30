<?php
session_start();
include 'crudBarang.php';

// Cek login
if (!isset($_SESSION['Username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['Username'];
$tanggal = date("d F Y");

// Ambil data semua barang
$semuaBarang = bacaSemuaBarang();

// Proses tambah barang
if (isset($_POST['btnTambah'])) {
    $kodeBarang = $_POST['kodeBarang'];
    $namaBarang = $_POST['namaBarang'];
    $hargaBarang = $_POST['hargaBarang'];
    $stok = $_POST['stok'];
    if (tambahBarang($kodeBarang, $namaBarang, $hargaBarang, $stok)) {
        header("Location: master_daftar.php");
        exit;
    } else {
        $error = "Gagal menambah barang.";
    }
}

// Proses edit barang
if (isset($_POST['btnEdit'])) {
    $kodeBarang = $_POST['kodeBarang'];
    $namaBarang = $_POST['namaBarang'];
    $hargaBarang = $_POST['hargaBarang'];
    $stok = $_POST['stok'];
    if (editBarang($kodeBarang, $namaBarang, $hargaBarang, $stok)) {
        header("Location: master_daftar.php");
        exit;
    } else {
        $error = "Gagal mengedit barang.";
    }
}

// Proses hapus barang
if (isset($_GET['hapus'])) {
    $kodeBarang = $_GET['hapus'];
    if (hapusBarang($kodeBarang)) {
        header("Location: master_daftar.php");
        exit;
    } else {
        $error = "Gagal menghapus barang.";
    }
}

// Ambil data untuk edit
$editData = null;
if (isset($_GET['edit'])) {
    $kodeBarang = $_GET['edit'];
    $editData = cariBarang($kodeBarang);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Daftar Barang</title>
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
        .date {
            opacity: 0.8;
            font-size: 13px;
            font-weight: 300;
            letter-spacing: 0.5px;
            margin: 8px 0;
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
            margin-top: 8px;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .card-title {
            margin-top: 0;
            color: #2E7D32;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #E8F5E9;
        }
        .form-table {
            width: 100%;
        }
        .form-table td {
            padding: 10px 5px;
        }
        .form-table td:first-child {
            width: 120px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #66BB6A;
            box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.2);
            outline: none;
        }
        .btn-add, .btn {
            background-color: #66BB6A;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-weight: 500;
            font-size: 14px;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            margin-top: 10px;
        }
        .btn-add:hover, .btn:hover {
            background-color: #43A047;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn {
            background-color: #90A4AE;
        }
        .btn:hover {
            background-color: #78909C;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        .item-table th,
        .item-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .item-table th {
            background: #A5D6A7;
            color: #1B5E20;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .item-table tr:last-child td {
            border-bottom: none;
        }
        .item-table tr:hover {
            background-color: #F5F9F5;
        }
        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .edit-btn:hover {
            background-color: #43A047;
            transform: translateY(-2px);
        }
        .delete-btn {
            background-color: #F44336;
            color: white;
        }
        .delete-btn:hover {
            background-color: #E53935;
            transform: translateY(-2px);
        }
        .error-message {
            color: #C62828;
            background: #FFEBEE;
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            border-left: 4px solid #F44336;
            font-size: 14px;
        }
        .empty-text {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
            }
            .header-title {
                width: 100%;
                text-align: center;
            }
            .user-info {
                width: 100%;
                padding: 15px;
            }
            .user-info:before {
                display: none;
            }
            .item-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <h1><i class="fas fa-boxes"></i> Master Daftar Barang</h1>
            </div>
            <div class="user-info">
                <strong><i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?></strong>
                <span class="date"><i class="far fa-calendar-alt"></i> <?php echo $tanggal; ?></span>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Log out</a>
            </div>
        </header>

        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="card-title"><?php echo $editData ? 'Edit Barang' : 'Tambah Barang Baru'; ?></h2>
            <form action="master_daftar.php" method="post" class="form-table">
                <table>
                    <tr>
                        <td>Kode Barang</td>
                        <td>
                            <input type="text" name="kodeBarang" value="<?php echo $editData ? htmlspecialchars($editData['kodeBarang']) : ''; ?>" <?php echo $editData ? 'readonly' : ''; ?> required>
                        </td>
                    </tr>
                    <tr>
                        <td>Nama Barang</td>
                        <td>
                            <input type="text" name="namaBarang" value="<?php echo $editData ? htmlspecialchars($editData['namaBarang']) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Harga Barang</td>
                        <td>
                            <input type="number" name="hargaBarang" value="<?php echo $editData ? htmlspecialchars($editData['hargaBarang']) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td>Stok</td>
                        <td>
                            <input type="number" name="stok" value="<?php echo $editData ? htmlspecialchars($editData['Stok']) : ''; ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <button type="submit" name="<?php echo $editData ? 'btnEdit' : 'btnTambah'; ?>" class="btn-add">
                                <i class="fas fa-<?php echo $editData ? 'save' : 'plus'; ?>"></i> <?php echo $editData ? 'Simpan Perubahan' : 'Tambah Barang'; ?>
                            </button>
                            <?php if ($editData): ?>
                                <a href="master_daftar.php" class="btn">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Daftar Barang</h2>
            <?php if (empty($semuaBarang)): ?>
                <p class="empty-text"><i class="fas fa-info-circle"></i> Belum ada barang yang tersedia.</p>
            <?php else: ?>
                <table class="item-table">
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga Barang</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                    <?php foreach ($semuaBarang as $barang): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($barang['kodeBarang']); ?></td>
                            <td><?php echo htmlspecialchars($barang['namaBarang']); ?></td>
                            <td><?php echo formatRupiah($barang['hargaBarang']); ?></td>
                            <td><?php echo htmlspecialchars($barang['Stok']); ?></td>
                            <td>
                                <a href="master_daftar.php?edit=<?php echo htmlspecialchars($barang['kodeBarang']); ?>" class="edit-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="master_daftar.php?hapus=<?php echo htmlspecialchars($barang['kodeBarang']); ?>" class="delete-btn" onclick="return confirm('Yakin ingin menghapus barang ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>