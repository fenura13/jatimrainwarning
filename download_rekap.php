<?php
// Konfigurasi koneksi
                    $host = 'localhost';
                    $dbname = 'jatg4813_rainfall_data_db';
                    $username = 'jatg4813_fenura1303';
                    $password = 'Fenura1302&';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil parameter GET
$tanggal_awal = $_GET['tanggal_awal'] ?? null;
$tanggal_akhir = $_GET['tanggal_akhir'] ?? null;

if (!$tanggal_awal || !$tanggal_akhir) {
    die("Parameter tanggal tidak lengkap.");
}

if ($tanggal_awal > $tanggal_akhir) {
    die("Tanggal awal tidak boleh lebih besar dari tanggal akhir.");
}

// Hitung jumlah hari dalam rentang tanggal (inklusif)
$start = new DateTime($tanggal_awal);
$end = new DateTime($tanggal_akhir);
$interval = $start->diff($end)->days + 1;
$total_diharapkan = $interval * 143;

// Daftar tabel yang digunakan
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

// Siapkan CSV
$filename = "rekap_scraping_{$tanggal_awal}_sd_{$tanggal_akhir}.csv";
$csv = fopen($filename, "w");

// Header CSV
fputcsv($csv, ['Nama Stasiun', 'Tanggal Awal', 'Tanggal Akhir', 'Total Entri Tersimpan', 'Total Data Diharapkan', 'Persentase Keberhasilan']);

foreach ($table_names as $table) {
    // Pastikan ada kolom timestamp
    $checkCol = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'timestamp'");
    if ($checkCol->num_rows == 0) continue;

    // Hitung entri selama rentang waktu
$stmt = $conn->prepare("SELECT COUNT(*) FROM `$table` WHERE DATE(CONVERT_TZ(`timestamp`, '+07:00', '+00:00')) BETWEEN ? AND ?");
    $stmt->bind_param("ss", $tanggal_awal, $tanggal_akhir);
    $stmt->execute();
    $stmt->bind_result($jumlah);
    $stmt->fetch();
    $stmt->close();

    $persen = $jumlah > 0 ? round($jumlah / $total_diharapkan * 100, 2) : 0;
    fputcsv($csv, [$table, $tanggal_awal, $tanggal_akhir, $jumlah, $total_diharapkan, $persen . '%']);
}

fclose($csv);
$conn->close();

// Unduh file
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
readfile($filename);
unlink($filename);
exit;
?>
