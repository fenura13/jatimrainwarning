<?php
// Koneksi database
$host = 'localhost';
$dbname = 'jatg4813_login';
$username = 'jatg4813_fenura1303';
$password = 'Fenura1302&';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Inisialisasi variabel
$error = '';

// Handle registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Cek reCAPTCHA
    $recaptcha_secret = '6LcKsS8rAAAAADOv7WjA8pvIw1F_yEz8VTmbXaMq';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $error = 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.';
    } elseif (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Semua kolom harus diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Simpan ke database
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            // Redirect ke halaman login
            header("Location: dummyserver.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Username sudah digunakan.';
            } else {
                $error = 'Terjadi kesalahan, coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Registrasi</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .register-container {
        padding: 20px;
        max-width: 400px;
        margin: 50px auto;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .register-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    h2 {
        color: #333333;
        margin-bottom: 20px;
        font-size: 1.8em;
        text-align: center;
    }

    form input[type="text"], 
    form input[type="password"] {
        padding: 14px;
        margin-bottom: 15px;
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1em;
        box-sizing: border-box;
        outline: none;
        transition: border-color 0.3s ease;
    }

    form input[type="text"]:focus, 
    form input[type="password"]:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    .btn-register {
        background-color: #007bff;
        color: #ffffff;
        padding: 14px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.1em;
        width: 100%;
        margin-top: 10px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-register:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }

    .btn-register:active {
        background-color: #003d80;
    }

    .error-message {
        font-size: 0.9em;
        margin-top: 10px;
        color: #ff4d4d;
        text-align: center;
    }

    .login-link {
        display: block;
        margin-top: 20px;
        text-align: center;
    }

    .login-link a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    @media (max-width: 600px) {
        .register-container {
            padding: 15px;
            margin: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 1.5em;
        }

        .btn-register {
            font-size: 1em;
        }
    }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="register-container">
        <h2>Registrasi</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>

            <div class="g-recaptcha" data-sitekey="6LcKsS8rAAAAANK-0g-Jlc-PkpZTZd9QFLCbJI2n"></div>

            <button type="submit" name="register" class="btn-register">Daftar</button>
        </form>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <div class="login-link">
            <p>Sudah punya akun? <a href="dummyserver.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
