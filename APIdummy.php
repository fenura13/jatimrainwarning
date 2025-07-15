<?php
// Baca isi file data_hujan_terkini2.json
$json_data = file_get_contents('data_hujan_terkini2.json');

// Decode JSON menjadi array
$data = json_decode($json_data, true);

// Set timezone WIB (GMT+7) sebagai acuan 'today'
$today = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

$dataoff    = [];
$dataonline = [];

// Baca metadata
$metadata_json = file_get_contents('metadata.json');
$metadata      = json_decode($metadata_json, true);

// Mapping metadata dengan id_station sebagai key
$data_metadata = [];
foreach ($metadata as $item) {
    $clean_id = trim($item['id']);
    $data_metadata[$clean_id] = [
        'name_station' => $item['name_station'],
        'nama_kota'    => $item['nama_kota']
    ];
}

// Loop melalui data dan tambahkan informasi + konversi waktu
foreach ($data as &$item) {
    $id_station = trim($item['id']);

    // Tambahkan name_station & nama_kota
    if (isset($data_metadata[$id_station])) {
        $item['name_station'] = $data_metadata[$id_station]['name_station'];
        $item['nama_kota']    = $data_metadata[$id_station]['nama_kota'];
    } else {
        $item['name_station'] = 'Unknown Station';
        $item['nama_kota']    = 'Unknown City';
    }

    // Format nilai hujan
    if (is_numeric($item['rr'])) {
        $item['rr'] = number_format((float)$item['rr'], 1);
    }

    // Parse timestamp UTC, lalu clone + ubah ke WIB
    $dtUtc = new DateTime($item['timestamp'], new DateTimeZone('UTC'));
    $dtWib = clone $dtUtc;
    $dtWib->setTimezone(new DateTimeZone('Asia/Jakarta'));

    // Simpan string timestamp dalam WIB
    $item['timestamp_wib'] = $dtWib->format('Y-m-d H:i:s');

    // Hitung selisih hari berdasarkan WIB
    $item['diffday'] = $today->diff($dtWib)->days;

    // Klasifikasi online/offline
    if ($item['diffday'] >= 1) {
        $dataoff[] = $item;
    } else {
        $dataonline[] = $item;
    }
}
unset($item);

// Filter Data Online yang lebih dari 1 jam (berdasarkan WIB)
$datafilter = [];
$dataonn   = [];
foreach ($dataonline as $item) {
    $dtWib      = new DateTime($item['timestamp_wib'], new DateTimeZone('Asia/Jakarta'));
    $interval   = $today->diff($dtWib);
    $diff_hours = $interval->h + ($interval->days * 24);

    if ($diff_hours >= 1) {
        $datafilter[] = $item;
    } else {
        $dataonn[]    = $item;
    }
}

// Debug (opsional)
// print_r($datafilter);
// print_r($dataonn);
// print_r($dataonline);
?>
