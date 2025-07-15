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
include("peringatan.php");


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
    <a href="dummyserver.php" class="active">Display</a>
    <a href="login.php">Login</a>
    <a href="data.php">Data</a>
  </nav>

</body>




<main class ="contend">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-10">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
          <h1 style= "margin: 0 auto;">Persebaran Peralatan Otomatis Curah Hujan di Provinsi Jawa Timur</h1>
        </div>
        <div id="peta"></div>
      </div>
      <div class="col-md-2">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
      <h5 style= "margin: 0 auto; font-weight: bold;"><small>Event Hujan</small></h5>
        </div>
        <!-- isi konten Carousel OFFLINE -->
        <div class="owl-carousel owl-carousel-2" style="border: 2px solid">


<!-- DATA EVENT HUJAN -->
<?php
    $filteredData = array();
    foreach ($dataonline as $row) {
        if ($row['rr'] >= 0.2) {
            $filteredData[] = $row;
        }
    }

    if (empty($filteredData)) {
        echo '<div class="item centered-item"><h3><br><br><br><b>Tidak Ada Hujan</b></h3><br><br><br></div>';
    } else {
        // Memetakan kolom-kolom yang akan diurutkan
        $curah = array_column($filteredData, 'rr');
        $tanggal = array_column($filteredData, 'timestamp');
        
        // Mengurutkan array berdasarkan curah hujan secara descending
        array_multisort($curah, SORT_DESC, $tanggal, $filteredData);

        // Menampilkan urutan data hujan di console
        foreach ($filteredData as $index => $row) {
            $urutan = $index + 1;
            $tanggal = str_replace('+00', '', $row['timestamp']);
            echo '<div class="item centered-item"><h3>' . $row['name_station'] . '</h3><span style="font-size: 3em;"><b>'.$row['rr'].'</b></span><span style="font-size: 0.8em;"> mm</span><p>Last Update: '.$tanggal.'</p></div>';
    
    // echo "Urutan ke-$urutan: {$row['name_station']} - Curah Hujan: {$row['curah']} mm - Last Update: {$tanggal} UTC\n";
        }

        // Menampilkan pesan bahwa data telah diurutkan
        // echo "Data hujan telah diurutkan berdasarkan curah hujan secara descending.\n";
    }
?>

<!-- LEGENDA PETA -->
</div>
<div class="button-container">
    <h4>Pilih Mode</h4>
    <button onclick="toggleMode('toolStatus')">Status Alat</button>
    <button onclick="toggleMode('rainCategory')">Rainfall Category Mode</button>

    <h4>Kategori Hujan</h4>
    <button onclick="filterMarkers('all')">Semua</button><br>
    <span class="legend-color" style="background-color: grey;"></span>
    <button onclick="filterMarkers('grey')">TTU/Tidak ada hujan</button><br>
    <span class="legend-color" style="background-color: green;"></span>
    <button onclick="filterMarkers('green')">Hujan ringan (0.5-20 mm/hari)</button><br>
    <span class="legend-color" style="background-color: yellow;"></span>
    <button onclick="filterMarkers('yellow')">Hujan sedang (20-50 mm/hari)</button><br>
    <span class="legend-color" style="background-color: orange;"></span>
    <button onclick="filterMarkers('orange')">Hujan lebat (50-100 mm/hari)</button><br>
    <span class="legend-color" style="background-color: red;"></span>
    <button onclick="filterMarkers('red')">Hujan sangat lebat (100-150 mm/hari)</button><br>
    <span class="legend-color" style="background-color: violet;"></span>
    <button onclick="filterMarkers('violet')">Hujan ekstrim (>150 mm/hari)</button>

    <h4>Filter Jenis Site</h4>
    <button onclick="filterByType('all')">Semua</button>
    <button onclick="filterByType('ARG')">ARG</button>
    <button onclick="filterByType('AWS')">AWS</button>
    <button onclick="filterByType('AAWS')">AAWS</button>
</div>


<!-- Additional Legend for Markers -->
<div id="legend">
<div class="legend-item">
        <div class="legend-box"><span class="legend-color blue"></span></div>
        <span class="legend-label">Online</span>
    </div>
    <div class="legend-item">
        <div class="legend-box"><span class="legend-color black"></span></div>
        <span class="legend-label">Offline</span>
    </div>

    <div class="legend-item arg">
        <span class="legend-marker">
            <img src="https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png" 
                 alt="ARG Marker" width="16" height="28">
        </span>
        <span class="legend-label">ARG (Automatic Rain Gauge) (Marker: Peta)</span>
    </div>

    <div class="legend-item aws">
        <span class="legend-marker">
            <img src="data:image/svg+xml;charset=UTF-8,
                %3Csvg xmlns='http://www.w3.org/2000/svg' width='30' height='50' viewBox='0 0 30 50'%3E
                    %3Crect x='0' y='0' width='2' height='50' fill='black'/%3E
                    %3Cpath d='M2 2 Q10 10 20 5 Q25 2 28 10 Q23 18 28 26 Q25 35 20 30 Q10 25 2 35 Z' fill='red'/%3E
                %3C/svg%3E" 
                alt="AWS Marker" width="16" height="28">
        </span>
        <span class="legend-label">AWS (Automatic Weather Station) (Marker: Bendera)</span>
    </div>

    <div class="legend-item aaws">
        <span class="legend-marker">
            <img src="data:image/svg+xml;charset=UTF-8,
                %3Csvg xmlns='http://www.w3.org/2000/svg' width='30' height='60' viewBox='0 0 30 60'%3E
                    %3Ccircle cx='15' cy='20' r='12' fill='green'/%3E
                    %3Crect x='14' y='35' width='2' height='25' fill='black'/%3E
                %3C/svg%3E" 
                alt="AAWS Marker" width="16" height="28">
        </span>
        <span class="legend-label">AAWS (Agroclimate Automatic Weather Station) (Marker: Lolipop)</span>
    </div>
    <style>
    
    .legend-color {
        width: 15px;
        height: 15px;
        display: inline-block;
        border-radius: 3px;
    }
    .blue { background: blue; }
    .black { background: black; }
</style>
</div>




</div>
      </div>
    </div>
  </div>

<div class = "container-fluid">
  <div class = "row">
  <div class ="col-md-6" style="border-right: 2px solid">
    <!-- Isi Konten Carousel Data > 1 Jam -->
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h5 style= "margin: 0 auto;"> <small>Lokasi Online < 1 jam </small> </h5>
  </div>
  <div class="owl-carousel owl-carousel-3" >
<?php
$counton = count($dataonn);

if (empty($dataonn)) {
    echo '<div class="item centered-item"><h3></h3><span style="font-size: 3em;"><b></b></span><span style="font-size: 0.8em;"></span> <p></p></div>';
} else {
    // Fungsi untuk mendapatkan timestamp dari string waktu
    function getTimestamp($datetime) {
        return strtotime(str_replace('+00', '', $datetime));
    }

    // Fungsi untuk membandingkan dua data berdasarkan waktu terkini
    function compareTimestamp($a, $b) {
        return getTimestamp($b['timestamp']) - getTimestamp($a['timestamp']);
    }

    // Mengurutkan array menggunakan fungsi pembanding
    usort($dataonn, 'compareTimestamp');

    // Menampilkan urutan data hujan di console
    foreach ($dataonn as $row) {
        $tanggal = str_replace('+00', '', $row['timestamp']);
        echo '<div class="item centered-item"><h3>' . $row['name_station'] . '</h3><span style="font-size: 3em;"><b>' . $row['rr'] . '</b></span><span style="font-size: 0.8em;"> mm</span> <p>Last Update : ' . $tanggal . '</p></div>';
    }
}
?>

  </div>
  </div>
  <div class ="col-md-4" style="border-left: 2px solid">
    <!-- Isi Konten Carousel Data > 1 Jam -->
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
  <h5 style= "margin: 0 auto;"><b><small>Lokasi Online > 1 Jam - 24 jam </b></small></h5>
  </div>
  <div class="owl-carousel owl-carousel-1">
  <?php
$count = count($datafilter);
if ($count == 0) {
    echo '<div class="item centered-item"><h3></h3><span style="font-size: 3em;"><b></b></span><span style="font-size: 0.8em;"></span> <p></p></div>';
} elseif ($count == 1) {
    $row = $datafilter[0];
    $tanggal = str_replace('+00', '', $row['timestamp']);
    echo '<div class="item centered-item"><h3>' . $row['name_station'] . '</h3><span style="font-size: 3em;"><b>' . $row['rr'] . '</b></span><span style="font-size: 0.8em;"> mm</span> <p>Last Update : ' . $tanggal . '</p></div>';
} else {
    // Fungsi untuk mendapatkan timestamp dari string waktu
    function getTimestamp1($datetime) {
        return strtotime(str_replace('+00', '', $datetime));
    }
    // Fungsi untuk membandingkan dua data berdasarkan waktu terkini
    function compareTimestamp1($a, $b) {
        return getTimestamp1($b['timestamp']) - getTimestamp1($a['timestamp']);
    }
    // Menyimpan fungsi-fungsi di dalam ruang lingkup global
    $getTimestamp1 = 'getTimestamp1';
    $compareTimestamp1 = 'compareTimestamp1';
    // Mengurutkan array menggunakan fungsi pembanding
    usort($datafilter, $compareTimestamp1);
    // Menampilkan urutan data hujan di console
    foreach ($datafilter as $row) {
        $tanggal = str_replace('+00', '', $row['timestamp']);
        echo '<div class="item centered-item"><h3>' . $row['name_station'] . '</h3><span style="font-size: 3em;"><b>' . $row['rr'] . '</b></span><span style="font-size: 0.8em;"> mm</span> <p>Last Update : ' . $tanggal . '</p></div>';
    }
}
?>
</div>
</div>
  <div class = "box col-md-2" style="border: 2px solid" >
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom ">
  <h5 style = "margin: 0 auto"> <b>OFFLINE </b> </h5>
  </div>
  <!-- isi konten Carousel OFFLINE-->
  <div class="owl-carousel owl-carousel-2" >
 <?php
if (empty($dataoff)) {
    echo '<div class="item centered-item"><h3><br><br><b>Tidak Ada Data</b></h3><br><br><br><br></div>';
} else {
    foreach ($dataoff as $row) {
		$tanggal = str_replace('+00', '', $row['timestamp']);
        echo '<div class="item centered-item" ><h3>' . $row['name_station'] . '</h3><span style="font-size: 3em;"><b>' . $row['rr'] . '</b></span><span style="font-size: 0.8em;"> mm</span> <p>Last Update : ' . $tanggal . ' </p></div>';
    }
}
?>
</div>
  </div>
    </div>
      </div>
    

 
</main>

<footer>
   <div id="last-refresh">
       <p id="footer-clock">Waktu UTC: </p>
       <p id="footer-clock-wib">Waktu WIB: </p>
   </div>
   <div class="footer-wrapper">
       <p>&copy; STMKG 2025 - 41.21.0011
</p>
   </div>
</footer>


<script>

var map = L.map('peta').setView([-7.2504, 112.7688], 9);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

var markers = [];

fetch('metadata.json')
  .then(response => response.json())
  .then(metadata => {
    fetch('data_hujan_terkini2.json?' + new Date().getTime())
      .then(response => response.json())
      .then(dataHujan => {
        dataHujan.forEach(station => {
          var stationId = station.id;
          var stationMetadata = metadata[stationId] || {};
          var stationType = stationMetadata.type || 'default';
          var rrValue = station.rr;
          var timestamp = station.timestamp;
          var isOnline = checkOnlineStatus(timestamp);
          
          var markerColor = isOnline ? 'blue' : 'black';
          var categoryColor = getColor(rrValue);
          
          var markerIcon = getMarkerIcon(stationType, markerColor);
          
          var marker = L.marker([station.latitude, station.longitude], { icon: markerIcon })
            .addTo(map)
            .bindPopup(
              `Stasiun ID: ${stationId}<br>
              Nama Stasiun: ${stationMetadata.name_station || 'Unknown'}<br>
              Kota/Kab: ${stationMetadata.nama_kota || 'Unknown'}<br>
              Last Update: ${timestamp}<br>
              Curah: ${rrValue} mm<br>
              Status: ${isOnline ? "<span style='color:green;'>Online</span>" : "<span style='color:red;'>Offline</span>"}`
            );
          
          markers.push({ id: stationId, marker: marker, status: isOnline ? 'active' : 'inactive', category: categoryColor, type: stationType });
        });
      })
    .catch(error => console.error('Error loading data_hujan_terkini2.json:', error));
  })
  .catch(error => console.error('Error loading metadata.json:', error));

function checkOnlineStatus(rawTs) {
  if (!rawTs) return false;

  // 1. Hilangkan “WIB” jika ada
  const noWib   = rawTs.replace(/\s*WIB$/, "");
  // 2. Ubah ke ISO 8601 dengan +07:00
  //    - jika format "YYYY-MM-DD HH:mm:ss" → tambahkan “T” dan “+07:00”
  //    - jika sudah ada offset (+07:00) biarkan
  const iso = /\+\d{2}:\d{2}$/.test(noWib)
    ? noWib
    : noWib.replace(" ", "T") + "+07:00";

  const lastUpdate = new Date(iso);
  if (isNaN(lastUpdate)) return false;

  return (Date.now() - lastUpdate.getTime()) <= 24 * 60 * 60 * 1000;
}


function getColor(curah) {
        if (curah >= 0.5 && curah <= 20) { return 'green'; }
        else if (curah > 20 && curah <= 50) { return 'yellow'; }
        else if (curah > 50 && curah <= 100) { return 'orange'; }
        else if (curah > 100 && curah <= 150){ return 'red'; }
        else if (curah > 150) { return 'violet'; }
        else { return 'grey'; }
    }

function getMarkerIcon(type, color) {
  let iconUrl;
  if (type === 'ARG') {
        // Marker ARG seperti marker peta
        iconUrl = `https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`;
    } else if (type === 'AWS') {
        // Marker AWS seperti pin kertas
        iconUrl = `data:image/svg+xml;charset=UTF-8,
    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="50" viewBox="0 0 30 50">
        <!-- Flagpole -->
        <rect x="0" y="0" width="2" height="50" fill="black" />
        
        <!-- Waving flag -->
        <path d="M2 2 Q10 10 20 5 Q25 2 28 10 Q23 18 28 26 Q25 35 20 30 Q10 25 2 35 Z" 
              fill="${color}" />
    </svg>`;
;
    } else if (type === 'AAWS') {
        // Marker AAWS seperti lolipop
        iconUrl = `data:image/svg+xml;charset=UTF-8,
    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="60" viewBox="0 0 30 60">
        <circle cx="15" cy="20" r="15" fill="${color}" />
        <rect x="14" y="35" width="2" height="25" fill="black" />
    </svg>`;

    } else {
        // Marker default abu-abu
        iconUrl = `data:image/svg+xml;charset=UTF-8,
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="50" viewBox="0 0 30 50">
                <circle cx="15" cy="15" r="12" fill="grey" />
            </svg>`;
    }
  return L.icon({
    iconUrl: iconUrl,
    iconSize: [30, 50],
    iconAnchor: [15, 50],
    popupAnchor: [0, -50],
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png'
  });
}

function toggleMode(mode) {
  markers.forEach(function(item) {
    var newColor = mode === 'toolStatus'
      ? (item.status === 'active' ? 'blue' : 'black')
      : item.category;
    
    var newIcon = getMarkerIcon(item.type, newColor);
    item.marker.setIcon(newIcon);
  });
}
function filterByType(type) {
  markers.forEach(function(item) {
    if (type === 'all' || item.type === type) {
      map.addLayer(item.marker);
    } else {
      map.removeLayer(item.marker);
    }
  });
}

function filterMarkers(category) {
  markers.forEach(function(item) {
    if (category === 'all' || item.category === category) {
      map.addLayer(item.marker);
    } else {
      map.removeLayer(item.marker);
    }
  });
}

// Event listener for legend clicks
document.getElementById('legend').addEventListener('click', function(event) {
  let target = event.target.closest('.legend-item');
  if (target) {
    let category = target.classList.contains('arg') ? 'ARG' :
                   target.classList.contains('aws') ? 'AWS' :
                   target.classList.contains('aaws') ? 'AAWS' : 'all';
    filterMarkers(category);
  }
});













    // Automatically adjust the map size on window resize
    window.addEventListener('resize', function() {
        map.invalidateSize();
    });

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
