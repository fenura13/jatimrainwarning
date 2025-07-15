<?php
session_start();
require 'db.php';

$error = '';

// Proses login ketika tombol ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

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
    <a href="login.php" >Login</a>
    <a href="data.php" class="active">Data</a>
</nav>



<div class="button-kontener">
<title>Download Data</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
    .kontener {
        width: 100%;
        max-width: 600px;
        margin: 50px auto;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 30px;
        text-align: center;
    }

    h1 {
        font-size: 2rem;
        color: #333;
        margin-bottom: 20px;
        font-weight: 500;
    }

    label {
        font-size: 1rem;
        color: #555;
        margin-bottom: 10px;
    }

    select {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: border-color 0.3s;
    }

    select:focus {
        border-color: #007bff;
        outline: none;
    }

    button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 1rem;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }

    .select-kontener {
        width: 100%;
    }

    .btn-kontener {
        display: flex;
        justify-content: center;
    }
</style>




<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unduh Histori Scraping Curah Hujan</title>
</head>
<body>
    <div class="kontener">
        <h1>Fitur Unduh Histori Scraping Curah Hujan</h1>
        <form id="downloadForm" action="download_csv.php" method="post" onsubmit="return checkLogin();">
            <div class="select-container">
                <label for="table-select">Pilih Tabel:</label>
                <select id="table-select" name="table_name" required>
                    <?php
                    // Konfigurasi database
                    $host = 'localhost';
                    $dbname = 'jatg4813_rainfall_data_db';
                    $username = 'jatg4813_fenura1303';
                    $password = 'Fenura1302&';

                    try {
                        // Koneksi ke database
                        $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Query untuk mengambil nama tabel
                        $stmt = $pdo->query("SHOW TABLES");

                        // Cek jika ada tabel
                        if ($stmt) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $tableName = array_values($row)[0];
                                echo '<option value="' . htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') . '">' 
                                     . htmlspecialchars($tableName, ENT_QUOTES, 'UTF-8') . '</option>';
                            }
                        } else {
                            echo '<option value="">Tidak ada tabel ditemukan</option>';
                        }
                    } catch (PDOException $e) {
                        echo '<option value="">Gagal memuat daftar tabel: ' 
                             . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="select-container">
                <label for="start-date">Tanggal Mulai:</label>
                <input type="date" id="start-date" name="start_date" required>
            </div>

            <div class="select-container">
                <label for="end-date">Tanggal Selesai:</label>
                <input type="date" id="end-date" name="end_date" required>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-primary">Download Data</button>
            </div>
        </form>
    </div>

    <script type="text/javascript">
    function checkLogin() {
        var loggedIn = <?php echo json_encode($loggedIn ?? false); ?>;
        var startDate = document.getElementById('start-date').value;
        var endDate = document.getElementById('end-date').value;

        console.log("Checking login status:", loggedIn); // Debugging line to check value

        if (!loggedIn) {
            alert('Anda harus login terlebih dahulu untuk mengunduh CSV.');
            window.location.href = 'dummyserver.php'; // Redirect to login page
            return false; // Prevent form submission
        }

        // Validasi tanggal
        if (!startDate || !endDate) {
            alert('Harap pilih rentang tanggal.');
            return false;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
            return false;
        }

        return true; // Allow form submission
    }
    </script>
</body>
</html>











     
<title>Aplikasi Download Data AWS Center</title>
<style>

    #downloadSection {
        margin-top: 20px;
        display: none;
        text-align: center;
    }

    #downloadLink {
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 1.1em;
    }

    #downloadLink:hover {
        background-color: #218838;
    }
</style>



</head>
<body>
    <div class="kontener">
        <h1>Aplikasi Download Data AWS Center</h1>
        <form id="downloadForm">
            <div class="form-group">
                <label for="kodesta">Pilih Data</label>
                <select id="kodesta" name="kodesta">
                    <!-- All your options go here -->
                        <option value="STA0183" data-tipesta="arg">ARG Bangunsari</option>
                        <option value="150036" data-tipesta="arg">ARG Bajul Mati</option>
                        <option value="STA0265" data-tipesta="arg">ARG Dampit</option>
                        <option value="STA0246" data-tipesta="arg">ARG Rekayasa Sine</option>
                        <option value="STA0247" data-tipesta="arg">ARG Ngariboyo</option>
                        <option value="STA0248" data-tipesta="arg">ARG Rekayasa Panti</option>
                        <option value="STA0249" data-tipesta="arg">ARG Rekayasa Sukowono</option>
                        <option value="STA0071" data-tipesta="arg">ARG Bojonegoro</option>
                        <option value="150042" data-tipesta="arg">ARG Caruban</option>
                        <option value="150031" data-tipesta="arg">ARG Cerme</option>
                        <option value="STA0192" data-tipesta="arg">ARG Dasuk</option>
                        <option value="150037" data-tipesta="arg">ARG Gondang</option>
                        <option value="150311" data-tipesta="arg">ARG Grajagan</option>
                        <option value="150033" data-tipesta="arg">ARG Jatibanteng</option>
                        <option value="STA0198" data-tipesta="arg">ARG Jokarto</option>
                        <option value="150046" data-tipesta="arg">ARG Kademangan</option>
                        <option value="STA0065" data-tipesta="arg">ARG Kalibaru</option>
                        <option value="STA0116" data-tipesta="arg">ARG Kangean</option>
                        <option value="150314" data-tipesta="arg">ARG Karangsuko</option>
                        <option value="STA0185" data-tipesta="arg">ARG Kartoharjo</option>
                        <option value="STA0074" data-tipesta="arg">ARG Kediri</option>
                        <option value="150039" data-tipesta="arg">ARG Kencong</option>
                        <option value="STA0184" data-tipesta="arg">ARG Kepatihan</option>
                        <option value="14032807" data-tipesta="arg">ARG Lamongan</option>
                        <option value="STA0196" data-tipesta="arg">ARG Lanud Pacitan</option>
                        <option value="150035" data-tipesta="arg">ARG Lidjen Jambu</option>
                        <option value="STA0061" data-tipesta="arg">ARG Magetan</option>
                        <option value="STA0069" data-tipesta="arg">ARG Mojokerto</option>
                        <option value="150041" data-tipesta="arg">ARG Mojowarno</option>
                        <option value="STA0063" data-tipesta="arg">ARG Ngajum</option>
                        <option value="STA0187" data-tipesta="arg">ARG Nganjuk</option>
                        <option value="STA0060" data-tipesta="arg">ARG Ngawi</option>
                        <option value="STA0189" data-tipesta="arg">ARG P3GI</option>
                        <option value="STA0188" data-tipesta="arg">ARG Pajarakan Kulon</option>
                        <option value="STA0191" data-tipesta="arg">ARG Pakong</option>
                        <option value="STA0064" data-tipesta="arg">ARG Panti</option>
                        <option value="STA0163" data-tipesta="arg">ARG Pasirian</option>
                        <option value="150315" data-tipesta="arg">ARG Pasrujambe</option>
                        <option value="150312" data-tipesta="arg">ARG Pinang Pahit</option>
                        <option value="14032806" data-tipesta="arg">ARG Pronojiwo</option>
                        <option value="STA0070" data-tipesta="arg">ARG Sampang</option>
                        <option value="150043" data-tipesta="arg">ARG Siman</option>
                        <option value="150038" data-tipesta="arg">ARG Sitiarjo</option>
                        <option value="STG1006" data-tipesta="arg">ARG Pacet</option>
                        <option value="STA3219" data-tipesta="arg">ARG SMPK Sebayi Gemarang</option>
                        <option value="STA0062" data-tipesta="arg">ARG Srengat</option>
                        <option value="STA0194" data-tipesta="arg">ARG Sruni Gedangan</option>
                        <option value="150047" data-tipesta="arg">ARG Sudimoro</option>
                        <option value="STA0162" data-tipesta="arg">ARG Sumber</option>
                        <option value="150316" data-tipesta="arg">ARG Tajinan</option>
                        <option value="STA0193" data-tipesta="arg">ARG Tambak Ombo Manyar</option>
                        <option value="150044" data-tipesta="arg">ARG Tinap</option>
                        <option value="14032808" data-tipesta="arg">ARG Trenggalek</option>
                        <option value="150034" data-tipesta="arg">ARG Triwung Kidul</option>
                        <option value="STA0072" data-tipesta="arg">ARG Tuban</option>
                        <option value="150313" data-tipesta="arg">ARG Tumpak Mergo</option>
                        <option value="150045" data-tipesta="arg">ARG Tutur</option>
                        <option value="150032" data-tipesta="arg">ARG Widang</option>
                        <option value="150040" data-tipesta="arg">ARG Wirolegi</option>
                        <option value="STG1070" data-tipesta="arg">ARG BPP Socah</option>
                        <option value="STG1071" data-tipesta="arg">ARG Wonokromo</option>
                        <option value="STG1072" data-tipesta="arg">ARG Sidoarjo</option>
                        <option value="STG1073" data-tipesta="arg">ARG BPP Prambon</option>
                        <option value="STG1074" data-tipesta="arg">ARG BPP Wonosalam</option>
                        <option value="STA2293" data-tipesta="aws">AWS Bromo</option>
                        <option value="STG2058" data-tipesta="aws">AWS Bondowoso</option>
                        <option value="STA2243" data-tipesta="aws">AWS Situbondo</option>
                        <option value="STA4006" data-tipesta="aws">AWS Kandat</option>
                        <option value="STG2109" data-tipesta="aws">AWS Kanigoro</option>
                        <option value="STA3221" data-tipesta="aws">AWS Kediri</option>
                        <option value="STA2170" data-tipesta="aws">AWS Lamongan</option>
                        <option value="STA2072" data-tipesta="aws">AWS Mayang</option>
                        <option value="STW1016" data-tipesta="aws">AWS SMPK Mojokerto</option>
                        <option value="STA4007" data-tipesta="aws">AWS Panarukan</option>
                        <option value="STA4005" data-tipesta="aws">AWS Paron</option>
                        <option value="STA2113" data-tipesta="aws">AWS Sampang</option>
                        <option value="STA2283" data-tipesta="aws">AWS Sawahan</option>
                        <option value="STA2141" data-tipesta="aws">AWS Stageof Karangkates</option>
                        <option value="STA2103" data-tipesta="aws">AWS Tanggul</option>
                        <option value="STA2104" data-tipesta="aws">AWS Tiris</option>
                        <option value="STA2156" data-tipesta="aws">AWS SMPK Nganjuk</option>
                        <option value="STA2057" data-tipesta="aws">AWS SMPK Jombang</option>
                        <option value="160050" data-tipesta="aws">AWS UNIDA Gontor</option>
                        <option value="STA5103" data-tipesta="aws">AWS Digi Stamet Kalianget</option>
                        <option value="STW1017" data-tipesta="aws">AWS Stageof Pasuruan</option>
                        <option value="STA5093" data-tipesta="aws">AWS Digi Stamet Banyuwangi</option>
                        <option value="STA5003" data-tipesta="aws">AWS Digi Stamet Juanda Surabaya</option>
                        <option value="STA2092" data-tipesta="aws">AWS Maritim Ketapang</option>
                        <option value="STA2091" data-tipesta="aws">AWS Maritim Perak II</option>
                        <option value="STA5113" data-tipesta="aws">AWS Digi Stamet Tuban</option>
                        <option value="STW1029" data-tipesta="aws">AWS Batu</option>
                        <option value="STW1059" data-tipesta="aws">AWS Karangan</option>
                        <option value="STW1070" data-tipesta="aws">AWS SMPK Sebayi</option>
                        <option value="STA3057" data-tipesta="aaws">AAWS Banyuwangi</option>
                        <option value="STA3051" data-tipesta="aaws">AAWS Bojonegoro</option>
                        <option value="STA3054" data-tipesta="aaws">AAWS Jember</option>
                        <option value="STA3052" data-tipesta="aaws">AAWS IGG Glenmore</option>
                        <option value="AAWS0359" data-tipesta="aaws">AAWS Tuban</option>
                        <option value="STA3053" data-tipesta="aaws">AAWS Tulungagung</option>
                        <option value="AAWS0336" data-tipesta="aaws">AAWS Yosowilangun</option>       
                </select>
            </div>
            <div class="form-group">
                <label for="tipesta">Kategori</label>
                <input type="text" id="tipesta" name="tipesta" readonly>
            </div>
            <div class="form-group">
                <label for="startDate">Tanggal Mulai</label>
                <input type="date" id="startDate" name="startDate" required>
            </div>
            <div class="form-group">
                <label for="endDate">Tanggal Berakhir</label>
                <input type="date" id="endDate" name="endDate" required>
            </div>
            <button type="button" id="generateLink" class="btn btn-primary">Generate Link</button>
        </form>

        <div id="downloadSection">
            <h3>Link Download</h3>
            <a id="downloadLink" href="#" target="_blank">Download Data</a>
        </div>
    </div>

        <script>
    // Fungsi untuk memeriksa login sebelum mengakses form download
    function checkLogin() {
        var loggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;

        // Jika belum login, tampilkan popup dan arahkan ke halaman login
        if (!loggedIn) {
            alert('Anda harus login terlebih dahulu untuk mengunduh CSV.');
            window.location.href = 'dummyserver.php'; // Mengarahkan ke halaman login
        } else {
            // Aktifkan tombol Generate Link jika sudah login
            document.getElementById('generateLink').disabled = false;
        }
    }

    // Pastikan fungsi checkLogin dijalankan saat halaman dimuat
    window.onload = checkLogin;

    // Menambahkan event listener untuk perubahan pilihan kodesta
    document.getElementById('kodesta').addEventListener('change', function () {
        const kodestaElement = document.getElementById('kodesta');
        const tipestaElement = document.getElementById('tipesta');
        const selectedOption = kodestaElement.options[kodestaElement.selectedIndex];
        const tipesta = selectedOption.getAttribute('data-tipesta');
        tipestaElement.value = tipesta;
    });

    // Trigger the change event on page load to set the initial value of tipesta
    window.onload = function() {
        checkLogin();  // Call the checkLogin function to check login status

        // Trigger the kodesta change event to set initial tipesta value
        document.getElementById('kodesta').dispatchEvent(new Event('change'));
    };

    // Event listener for Generate Link button
    document.getElementById('generateLink').addEventListener('click', function () {
        var loggedIn = <?php echo $loggedIn ? 'true' : 'false'; ?>;

        // Jika belum login, tampilkan popup dan jangan lanjutkan
        if (!loggedIn) {
            alert('Anda harus login terlebih dahulu untuk mengunduh CSV.');
            window.location.href = 'dummyserver.php'; 
            return;  // Stop execution if not logged in
        }

        // Ambil data yang diperlukan untuk membuat URL unduhan
        const kodesta = document.getElementById('kodesta').value;
        const tipesta = document.getElementById('tipesta').value;
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Validasi tanggal
        if (startDate && endDate) {
            const baseUrl = "https://apiaws.bmkg.go.id/rawdata/downloadaccesdata/";
            const downloadUrl = `${baseUrl}${tipesta}/${kodesta}/${startDate}/${endDate}`;
            
            // Set link unduhan dan tampilkan bagian download
            document.getElementById('downloadLink').href = downloadUrl;
            document.getElementById('downloadSection').style.display = 'block';
        } else {
            alert('Harap mengisi tanggal mulai dan tanggal berakhir.');
        }
    });
</script>


    <div class="kontener">
        <h1>Fitur Unduh Data Peringatan Hujan</h1>
        <form id="downloadForm" action="unduh_peringatan_csv.php" method="post" onsubmit="return checkLogin();">
            <div class="select-container">
                <label for="tahun">Pilih Tahun:</label>
                <select name="tahun" id="tahun" required>
                    <?php
                    // Menampilkan opsi tahun dari 2025 sampai tahun sekarang (mundur)
                    $currentYear = date("Y");
                    for ($year = $currentYear; $year >= 2025; $year--) {
                        echo "<option value=\"$year\">$year</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="select-container">
                <label for="bulan">Pilih Bulan:</label>
                <select name="bulan" id="bulan" required>
                    <?php
                    $bulan = [
                        "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April",
                        "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus",
                        "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
                    ];

                    foreach ($bulan as $num => $nama) {
                        echo "<option value=\"$num\">$nama</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn" name="export_csv">Download Data</button>
            </div>
        </form>
    </div>

    <script>
        // Fungsi pengecekan login
        function checkLogin() {
            const loggedIn = <?= json_encode($loggedIn ?? true); ?>;
            if (!loggedIn) {
                alert('Anda harus login terlebih dahulu!');
                window.location.href = 'dummyserver.php';
                return false;
            }
            return true;
        }
    </script>
    <div class="kontener">
    <h1>Rekap Scraping</h1>
    <form id="rekapForm" action="download_rekap.php" method="get" onsubmit="return checkLogin();">

        <div class="select-container">
            <label for="tanggal_awal">Tanggal Awal:</label>
            <input type="date" id="tanggal_awal" name="tanggal_awal" required value="<?= date('Y-m-d'); ?>">
        </div>

        <div class="select-container">
            <label for="tanggal_akhir">Tanggal Akhir:</label>
            <input type="date" id="tanggal_akhir" name="tanggal_akhir" required value="<?= date('Y-m-d'); ?>">
        </div>

        <div class="btn-container">
            <button type="submit" class="btn" name="export_rekap">Download CSV</button>
        </div>
    </form>
</div>

<script>
    // Fungsi pengecekan login
    function checkLogin() {
        const loggedIn = <?= json_encode($loggedIn ?? true); ?>;
        if (!loggedIn) {
            alert('Anda harus login terlebih dahulu!');
            window.location.href = 'dummyserver.php';
            return false;
        }

        // Validasi rentang tanggal
        const tglAwal = document.getElementById('tanggal_awal').value;
        const tglAkhir = document.getElementById('tanggal_akhir').value;
        if (tglAwal > tglAkhir) {
            alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
            return false;
        }

        return true;
    }
</script>

</body>

    <script type="text/javascript">
    function checkLogin() {
        var loggedIn = <?php echo json_encode($loggedIn ?? false); ?>;

        console.log("Checking login status:", loggedIn); // Debugging line to check value

        if (!loggedIn) {
            alert('Anda harus login terlebih dahulu untuk mengunduh CSV.');
            window.location.href = 'dummyserver.php'; // Redirect to login page
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
    </script>
</body>
</html>




<footer>
   <div id="last-refresh">
       <p id="footer-clock">Waktu UTC: </p>
       <p id="footer-clock-wib">Waktu WIB: </p>
   </div>
   <div class="footer-wrapper">
       <p>&copy; STMKG 2025 - 41.21.0011 </p>
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