<?php
header("Content-Type: application/json");
include "Config.php";

$date = $_GET["date"] ?? date("Y-m-d");
$route = $_GET["route"] ?? "";
$search = $_GET["search"] ?? "";

$routeLike = "%$route%";
$searchLike = "%$search%";


$sql = "
SELECT 
    i.nik,
    MAX(a.attendanceId) AS attendanceId,
    MAX(a.attendanceStatus) AS attendanceStatus,
    i.name AS full_name,
    i.phone,
    GROUP_CONCAT(DISTINCT r.routeName SEPARATOR ', ') AS route_name,
    DATE(s.date) AS date
FROM inhabitants i
LEFT JOIN attendances a ON a.nik = i.nik
LEFT JOIN schedule s ON a.scheduleId = s.scheduleId
LEFT JOIN route r ON s.route = r.routeName
WHERE DATE(s.date) = ?
  AND r.routeName LIKE ?
  AND i.name LIKE ?
GROUP BY i.nik, i.name, i.phone, DATE(s.date)
ORDER BY i.name ASC
";



$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status"=>"error","message"=>$conn->error]);
    exit;
}


$stmt->bind_param("sss", $date, $routeLike, $searchLike);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
