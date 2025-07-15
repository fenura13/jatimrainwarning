<?php
session_start();
require 'db.php';

$error = '';

// Proses login ketika tombol ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $recaptcha_secret = '6LcKsS8rAAAAADOv7WjA8pvIw1F_yEz8VTmbXaMq';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Verifikasi captcha ke Google
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($verify_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_data = json_decode($response);

    if (!$response_data->success) {
        $error = 'Captcha verification failed. Please try again.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            $error = 'Username atau password salah.';
        }
    }



    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Simpan informasi pengguna dalam sesi
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        $error = 'Username atau password salah.';
    }
}

// Proses logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: dummyserver.php'); // Redirect ke halaman utama
    exit;
}


// Cek apakah pengguna sudah login
$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);



?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>MONITORING HUJAN JAWA TIMUR</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<?php 
include("include/header.php");
include("include/footer.php");
include("APIdummy.php");


?>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<style>
    html, body {
      height: 100%;
    }
</style>


<head>
  <style>
    /* Style untuk navigasi */
    .navbar {
        background-color: #003366;
        overflow: hidden;
        display: flex;
        justify-content: center;
        padding: 10px 0;
    }

    .navbar a {
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        font-size: 16px;
        transition: 0.3s;
    }

    .navbar a:hover, .navbar a.active {
        background-color: #005bb5;
        border-radius: 5px;
    }
  </style>
</head>

<body>

<header>
    <div class="header-wrapper">
        <img src="images/logo-bmkg-white.png" class="logo">
        <h1 class="header-title">MONITORING HUJAN TERKINI PROVINSI JAWA TIMUR</h1>
        <div class="header-time">
            <span>WAKTU : </span>
            <span id="clock"></span><span> UTC / </span>
            <span id="clock2"></span><span> WIB</span>
        </div>
    </div>
</header>


  <!-- Submenu Navigation -->
<nav class="navbar">
    <a href="dummyserver.php">Display</a>
    <a href="login.php" class="active">Login</a>
    <a href="data.php">Data</a>
</nav>

</body>



<title>Login Page</title>
    <style>
        .login-container, .welcome-container {
            padding: 20px;
            max-width: 400px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.6em;
        }

        p {
            color: #555;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        form input[type="text"], form input[type="password"] {
            padding: 12px;
            margin-bottom: 15px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .btn-login {
            background-color: #28a745;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #218838;
        }

        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .logout-link {
            display: inline-block;
            background-color: #dc3545;
            color: #fff;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            font-size: 1em;
        }

        .logout-link:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Welcome Container -->
            <div class="welcome-container">
                <h2>Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p>Senang melihat Anda kembali. Jelajahi dan nikmati pengalaman Anda di platform kami!</p>
                <a href="?logout=true" class="logout-link">Logout</a>
            </div>
        <?php else: ?>
            <!-- Login Form -->
            <div class="login-container">
                <h2>Login</h2>
                <form method="POST" action="">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <!-- Tambahkan reCAPTCHA -->
                    <div class="g-recaptcha" data-sitekey="6LcKsS8rAAAAANK-0g-Jlc-PkpZTZd9QFLCbJI2n"></div>

                    <button type="submit" name="login" class="btn-login">Login</button>
                </form>

                <!-- Script reCAPTCHA -->
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>

                <?php if (!empty($error)): ?>
                    <p class="error-message"><?= htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>



</main>

<footer>
   <div id="last-refresh">
       <p id="footer-clock">Waktu UTC: </p>
       <p id="footer-clock-wib">Waktu WIB: </p>
   </div>
   <div class="footer-wrapper">
       <p>&copy; STMKG 2025 - 41.21.0011</p>
   </div>
</footer>


<script>


 //Setting Waktu
 function updateClock() {
  var now = new Date(); // Mendapatkan waktu saat ini
  var offsetWIB = 7;
  
  // Calculate WIB time
  var hoursWIB = ((now.getUTCHours() + offsetWIB) % 24).toString().padStart(2, '0');
  var hoursUTC = (now.getUTCHours()).toString().padStart(2, '0');
  var minutes = now.getUTCMinutes().toString().padStart(2, '0');
  var seconds = now.getUTCSeconds().toString().padStart(2, '0');
  
  // Format times
  var timeWIB = hoursWIB + ':' + minutes + ':' + seconds;
  var timeUTC = hoursUTC + ':' + minutes + ':' + seconds;
  
  // Display times
  document.getElementById('clock').innerHTML = timeUTC;
  document.getElementById('clock2').innerHTML = timeWIB;
  
  // Update every second
  setTimeout(updateClock, 1000);
}

// Memanggil fungsi updateClock saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
  updateClock();
});
  window.setTimeout( function() {
        window.location.reload();
        }   , 600000);
        function refreshPage() {
            var currentTime = new Date();
            var hours = currentTime.getHours().toString().padStart(2, '0');
            var minutes = currentTime.getMinutes().toString().padStart(2, '0');
            var seconds = currentTime.getSeconds().toString().padStart(2, '0');
            var timeString = hours + ':' + minutes + ':' + seconds;
            document.getElementById('last-refresh').innerHTML = 'Terakhir diperbarui pada: ' + timeString +'  WIB';
            setTimeout(refreshPage, 600000);
        }
        window.onload = function() {
            refreshPage();
        };

</script>


<?php
include ('include/script2.php'); 
?>
</body>
</html>