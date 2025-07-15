<?php
// Tampilkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi koneksi
$host = 'localhost';
$dbname = 'jatg4813_rainfall_data_db';
$username = 'jatg4813_fenura1303';
$password = 'Fenura1302&';
$port = 3306; // default port MySQL

// Koneksi ke database
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Daftar tabel
$table_names = [
    "aaws_banyuwangi", "aaws_bojonegoro", "aaws_igg_glenmore", "aaws_jember",
    "aaws_tuban", "aaws_tulungagung", "aaws_yosowilangun",
    "arg_bajul_mati", "arg_bangunsari", "arg_bojonegoro", "arg_bp3k_balongpanggang",
    "arg_bpp_ambulu", "arg_bpp_banyuputih", "arg_bpp_binangun", "arg_bpp_bluri",
    "arg_bpp_dongko", "arg_bpp_donorojo", "arg_bpp_galis", "arg_bpp_karangpenang",
    "arg_bpp_kenduruan", "arg_bpp_ngantang", "arg_bpp_ngluyu", "arg_bpp_prambon",
    "arg_bpp_pulung", "arg_bpp_sepulu", "arg_bpp_socah", "arg_bpp_tegalombo",
    "arg_bpp_wonosalam", "arg_caruban", "arg_dampit", "arg_dasuk", "arg_gondang",
    "arg_grajagan", "arg_jatibanteng", "arg_jokarto", "arg_kademangan", "arg_kalibaru",
    "arg_kangean", "arg_karangsuko", "arg_kartoharjo", "arg_kediri", "arg_kencong",
    "arg_kepatihan", "arg_lamongan", "arg_lanud_pacitan", "arg_lidjen_jambu",
    "arg_magetan", "arg_mojokerto", "arg_mojowarno", "arg_ngajum", "arg_nganjuk",
    "arg_ngariboyo", "arg_ngawi", "arg_p3gi", "arg_pacet", "arg_pajarakan_kulon",
    "arg_pakong", "arg_panti", "arg_pasrujambe", "arg_pinang_pahit", "arg_pronojiwo",
    "arg_purwosari", "arg_rekayasa_panti", "arg_rekayasa_sine", "arg_rekayasa_sukowono",
    "arg_samiran", "arg_sampang", "arg_sidoarjo", "arg_siman", "arg_sitiarjo",
    "arg_smpk_sebayi_gemarang", "arg_srengat", "arg_sruni_gedangan",
    "arg_stamet_sangkapura_gresik", "arg_sudimoro", "arg_sumber", "arg_tajinan",
    "arg_tambak_ombo_manyar", "arg_tinap", "arg_trenggalek", "arg_triwong_kidul",
    "arg_tuban", "arg_tumpak_mergo", "arg_tutur", "arg_widang", "arg_wirolegi",
    "arg_wonokromo", "aws_batu", "aws_bondowoso", "aws_bromo", "aws_kandat",
    "aws_kanigoro", "aws_karangan", "aws_kediri", "aws_lamongan", "aws_mayang",
    "aws_panarukan", "aws_paron", "aws_sampang", "aws_situbondo", "aws_smpk_jombang",
    "aws_smpk_mojokerto", "aws_smpk_nganjuk", "aws_smpk_sebayi",
    "aws_stageof_karangkates", "aws_stageof_pasuruan", "aws_stageof_sawahan",
    "aws_tanggul", "aws_tiris", "aws_unida_gontor"
];

// Mulai sesi
session_start();
$loggedIn = $_SESSION['loggedIn'] ?? true;

$data_rows = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'], $_POST['bulan'], $_POST['tahun'])) {
    $bulan = str_pad((int)$_POST['bulan'], 2, '0', STR_PAD_LEFT);
    $tahun = $_POST['tahun'];

    if (!preg_match('/^\d{2}$/', $bulan) || !preg_match('/^\d{4}$/', $tahun)) {
        die("Format bulan/tahun tidak valid.");
    }

    $queries = [];
    foreach ($table_names as $table) {
        $queries[] = "
            SELECT 
                station_id, 
                name_station,
                timestamp, 
                rr, 
                CASE
                    WHEN rr >= 50 AND rr < 100 THEN 'Hujan Lebat'
                    WHEN rr >= 100 AND rr < 150 THEN 'Hujan Sangat Lebat'
                    WHEN rr >= 150 THEN 'Hujan Ekstrim'
                END AS intensitas,
                id_kabkota
            FROM $table
            WHERE 
                TIME(timestamp) = '06:50:00'
                AND rr >= 50
                AND MONTH(timestamp) = '$bulan'
                AND YEAR(timestamp) = '$tahun'
        ";
    }

    $sql = implode(" UNION ALL ", $queries) . " ORDER BY timestamp ASC";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data_rows[] = $row;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="peringatan_hujan_' . $tahun . '_' . $bulan . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'station_id', 'name_station', 'timestamp', 'rr', 'intensitas', 'id_kabkota']);

        $no = 1;
        foreach ($data_rows as $row) {
            fputcsv($output, [
                $no++,
                $row['station_id'],
                $row['name_station'],
                $row['timestamp'],
                $row['rr'],
                $row['intensitas'] ?? '',
                $row['id_kabkota'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    } else {
        die("Tidak ada data yang cocok untuk bulan dan tahun tersebut.");
    }
}
?>
