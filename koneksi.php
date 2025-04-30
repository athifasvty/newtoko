<?php
function koneksiToko() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "newtoko";

    $koneksi = mysqli_connect($servername, $username, $password, $database);
    if (!$koneksi) {
        die("koneksi gagal.. " . mysqli_connect_error());
    }
    return $koneksi;
}
?>