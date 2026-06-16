<?php
session_start();
require 'koneksi.php';

// Jika sudah login, langsung arahkan ke admin
if (isset($_SESSION['login_admin'])) { header("Location: admin.php"); exit; }

$error = "";
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM admin WHERE username = '$username'");
    
    // Cek ketersediaan Username
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek Password dengan sistem Verifikasi Hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['login_admin'] = true;
            header("Location: admin.php");
            exit;
        }
    }
    $error = "Username atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Kertas Calistung</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #FAFBFF; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 400px; text-align: center; box-sizing: border-box; }
        .brand-logo img { height: 50px; margin-bottom: 20px; }
        h2 { color: #1E73FF; margin: 0 0 10px 0; font-size: 24px; }
        p { color: #64748B; font-size: 14px; margin-bottom: 30px; }
        input { width: 100%; padding: 14px; margin-bottom: 20px; border: 1px solid #E2E8F0; border-radius: 12px; box-sizing: border-box; background: #F8FAFC; font-family: 'Poppins'; }
        input:focus { outline: none; border-color: #1E73FF; background: white; }
        .btn-login { width: 100%; padding: 14px; background: #1E73FF; color: white; border: none; border-radius: 12px; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; font-family: 'Poppins'; }
        .btn-login:hover { background: #1656cc; transform: translateY(-2px); }
        .error-msg { color: #EF476F; background: #FFE5EB; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-logo">
            <img src="assets/logo.png" alt="Logo Kertas Calistung">
        </div>
        <h2>Panel Admin</h2>
        <p>Silakan masuk untuk mengelola website.</p>
        
        <?php if($error != "") : ?> <div class="error-msg"><?php echo $error; ?></div> <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username Anda" required autofocus>
            <input type="password" name="password" placeholder="Password Anda" required>
            <button type="submit" name="login" class="btn-login">Masuk ke Dashboard</button>
        </form>
    </div>
</body>
</html>