<?php
session_start();
header("Content-Type: application/json; charset=utf-8");
include "../Config.php";

if (!isset($_SESSION['rw'])) {
    http_response_code(401);
    echo json_encode(["error"=>"Session expired"]);
    exit;
}

$rw = mysqli_real_escape_string($conn, $_SESSION['rw']);
$route = mysqli_real_escape_string($conn, $_GET['route'] ?? "");
$date  = mysqli_real_escape_string($conn, $_GET['date'] ?? date("Y-m-d"));

if ($route === "") {
    echo json_encode([]);
    exit;
}

/* ===== Ambil filter umur ===== */
$ageQuery = mysqli_query($conn,"SELECT minAge,maxAge FROM requirements WHERE rw='$rw' LIMIT 1");
if(!$ageQuery){
    http_response_code(500);
    echo json_encode(["error"=>mysqli_error($conn)]);
    exit;
}

$ageData = mysqli_fetch_assoc($ageQuery);
$minAge = $ageData['minAge'] ?? 0;
$maxAge = $ageData['maxAge'] ?? 200;

/* ===== Ambil semua petugas RW ===== */
$petugasRes = mysqli_query($conn,"
    SELECT nik,name 
    FROM inhabitants
    WHERE rw='$rw'
      AND gender='L'
      AND status='Warga'
      AND TIMESTAMPDIFF(YEAR,dateBirth,CURDATE()) BETWEEN $minAge AND $maxAge
    ORDER BY name ASC
");
if(!$petugasRes){
    http_response_code(500);
    echo json_encode(["error"=>mysqli_error($conn)]);
    exit;
}

/* ===== Ambil yang sudah dijadwalkan ===== */
$scheduledRes = mysqli_query($conn,"
    SELECT a.nik
    FROM attendances a
    LEFT JOIN schedule s ON a.scheduleId=s.scheduleId
    WHERE s.route='$route'
      AND s.rw='$rw'
      AND DATE(s.date)='$date'
");
if(!$scheduledRes){
    http_response_code(500);
    echo json_encode(["error"=>mysqli_error($conn)]);
    exit;
}

$scheduledNiks=[];
while($r=mysqli_fetch_assoc($scheduledRes)){
    $scheduledNiks[]=$r['nik'];
}

/* ===== Build JSON ===== */
$out=[];
while($p=mysqli_fetch_assoc($petugasRes)){
    $out[]=[
        "nik"=>$p['nik'],
        "name"=>$p['name'],
        "selected"=>in_array($p['nik'],$scheduledNiks)?1:0
    ];
}

echo json_encode($out);
