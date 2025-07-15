<?php
 $conn = pg_connect("host=localhost dbname=AloptamaNTB user=postgres password=teknisintb");
 function getAll($table) {
    global $conn;
    $query = "SELECT * FROM  $table";
    $result = pg_query($conn, $query);
    return $result;
}
function diff_days(&$data_diff) {
    global $conn;
    $alldata = getAll('log_api2');
    $today = new DateTime('now', new DateTimeZone('UTC'));
    $datafilter = array(); 
    if (pg_num_rows($alldata) > 0) {
        while ($row = pg_fetch_assoc($alldata)) {
               $idsta = $row['id_sta'];
               $tanggal = date('Y-m-d', strtotime($row['waktu']));
               $datafilter[] = array('idsta' => $idsta, 'tanggal' => $tanggal);}
        }
        foreach ($datafilter as $data) {
               $idsta = $data['idsta'];
               $tanggal = $data['tanggal'];
               $sel_tanggal = $today->diff(new DateTime($tanggal))->days;
               $data_diff[] = array('idsta' => $idsta, 'diffdays'=>$sel_tanggal) ;
       }
    }
function fillDataOnline(&$dataonlen){
    global $conn;
    $data_diff = array();
    diff_days($data_diff);
    $alldata = getAll('log_api2');
    $dataonlen= array();
    while ($row = pg_fetch_assoc($alldata)) {
    $idsta = $row['id_sta'];
        foreach ($data_diff as $diffdata) {
            if ($diffdata['idsta'] == $idsta && $diffdata['diffdays'] == 0) {
            $dataonlen[] = $row;
        }
    }
}
}


?>