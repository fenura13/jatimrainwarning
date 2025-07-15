<?php
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: dummyserver.php');
    exit;
}

// Konfigurasi database
$host = 'localhost';
$dbname = 'jatg4813_rainfall_data_db';
$username = 'jatg4813_fenura1303';
$password = 'Fenura1302&';

// Periksa apakah data dikirim melalui POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil data dari form
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $tableName = isset($_POST['table_name']) ? $_POST['table_name'] : '';

    try {
        // Validasi nama tabel agar aman (hanya huruf, angka, dan underscore)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception('Nama tabel tidak valid.');
        }

        // Koneksi ke database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Query untuk mengambil data berdasarkan rentang tanggal dan tabel
        if (!empty($startDate) && !empty($endDate)) {
            // Format tanggal untuk MySQL
// Konversi input UTC ke WIB (UTC+7)
$startUtc = new DateTime($startDate . ' 00:00:00', new DateTimeZone('UTC'));
$startUtc->setTimezone(new DateTimeZone('Asia/Jakarta'));
$startDateFormatted = $startUtc->format('Y-m-d H:i:s');

$endUtc = new DateTime($endDate . ' 23:59:59', new DateTimeZone('UTC'));
$endUtc->setTimezone(new DateTimeZone('Asia/Jakarta'));
$endDateFormatted = $endUtc->format('Y-m-d H:i:s');

            // Query dengan rentang tanggal + JOIN ke `kabupaten_kota`
            $stmt = $pdo->prepare("
                SELECT 
                    t.station_id, 
                    t.name_station, 
                    k.nama_kabkota AS nama_kota_kab, 
                    t.latitude, 
                    t.longitude, 
                    t.timestamp, 
                    t.rr, 
                    t.intensitas
                FROM `$tableName` AS t
                LEFT JOIN kabupaten_kota AS k ON t.id_kabkota = k.id_kabkota
                WHERE t.timestamp BETWEEN :start_date AND :end_date
            ");
            $stmt->bindParam(':start_date', $startDateFormatted);
            $stmt->bindParam(':end_date', $endDateFormatted);
            $stmt->execute();
        } else {
            // Query tanpa filter tanggal
            $stmt = $pdo->prepare("
                SELECT 
                    t.station_id, 
                    t.name_station, 
                    k.nama_kabkota AS nama_kota_kab, 
                    t.latitude, 
                    t.longitude, 
                    t.timestamp, 
                    t.rr, 
                    t.intensitas
                FROM `$tableName` AS t
                LEFT JOIN kabupaten_kota AS k ON t.id_kabkota = k.id_kabkota
            ");
            $stmt->execute();
        }

        // Set header untuk download CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $tableName . '_rainfall_data.csv"');

        // Buka output buffer untuk menulis data CSV
        $output = fopen('php://output', 'w');

        // Ambil contoh baris pertama untuk mengetahui nama kolom
        $columns = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($columns) {
            // Tulis header CSV
            fputcsv($output, array_keys($columns));

            // Reset cursor dan ambil semua data untuk ditulis ke file CSV
            $stmt->execute();

            // Tulis data ke CSV
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Format timestamp
                $formattedTimestamp = date("Y-m-d H:i:s", strtotime($row['timestamp']));
                
                // Tulis data ke CSV
                fputcsv($output, [
                    $row['station_id'],
                    $row['name_station'],
                    $row['nama_kota_kab'], // Dari hasil JOIN dengan kabupaten_kota
                    $row['latitude'],
                    $row['longitude'],
                    $formattedTimestamp,
                    $row['rr'],
                    $row['intensitas']
                ]);
            }
        } else {
            echo "Tidak ada data pada tabel: {$tableName}";
        }

        // Tutup output buffer
        fclose($output);
        exit;

    } catch (PDOException $e) {
        echo "Koneksi ke database gagal: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Formulir tidak disubmit dengan benar.";
}
?>
