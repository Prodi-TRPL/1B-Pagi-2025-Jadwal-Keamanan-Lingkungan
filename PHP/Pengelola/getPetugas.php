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

/* ===== ambil age ===== */
$sqlAge = "SELECT minAge, maxAge FROM requirements WHERE rw='$rw' LIMIT 1";
$resAge = mysqli_query($conn,$sqlAge);

if(!$resAge){
    http_response_code(500);
    echo json_encode(["error"=>mysqli_error($conn)]);
    exit;
}

$ageFilter = mysqli_fetch_assoc($resAge);
$minAge = $ageFilter['minAge'] ?? 0;
$maxAge = $ageFilter['maxAge'] ?? 200;

/* ===== query utama ===== */
$sql = "
SELECT 
    i.nik, 
    i.name,
    CASE WHEN a.nik IS NULL THEN 0 ELSE 1 END AS selected
FROM inhabitants i
LEFT JOIN (
    SELECT a.nik
    FROM attendances a
    LEFT JOIN schedule s ON a.scheduleId = s.scheduleId
    WHERE s.route = '$route'
      AND s.rw = '$rw'
      AND DATE(s.date) = CURDATE()
) a ON a.nik = i.nik
WHERE i.rw = '$rw'
  AND i.gender = 'L'
  AND i.status = 'Warga'
  AND TIMESTAMPDIFF(YEAR, i.dateBirth, CURDATE()) BETWEEN $minAge AND $maxAge
ORDER BY i.name ASC";

$res = mysqli_query($conn,$sql);

if(!$res){
    http_response_code(500);
    echo json_encode(["error"=>mysqli_error($conn)]);
    exit;
}

$out=[];
while($row=mysqli_fetch_assoc($res)){
    $out[]=$row;
}

echo json_encode($out);
