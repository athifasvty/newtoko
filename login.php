<?php
session_start();
include 'koneksi.php';

$conn = koneksiToko();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['Username'] = $username;
        header("Location: penjualan.php");
    } else {
        $error = "Login gagal. Cek username dan password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background: #e9f5ee;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #2f855a;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            color: #2f855a;
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #c6f6d5;
            border-radius: 6px;
            background: #f0fff4;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #38a169;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        input[type="submit"]:hover {
            background: #2f855a;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Penjualan</h2>
        <?php if (isset($error)) { echo "<div class='error-message'>$error</div>"; } ?>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
