<?php
header("Content-Type: application/json");
include "Config.php";

/*
  Ambil semua jadwal ronda + data warga + rute + jam
*/

$sql = "
SELECT 
    a.nik,
    i.name,
    i.email,

    ELT(WEEKDAY(s.date)+1,
        'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'
    ) AS day,

    DATE_FORMAT(s.date, '%d %M %Y') AS date,

    CONCAT(
        DATE_FORMAT(req.timeStart,'%H:%i'),
        ' - ',
        DATE_FORMAT(req.timeEnd,'%H:%i')
    ) AS time,

    r.routeName
FROM attendances a
JOIN inhabitants i ON a.nik = i.nik
JOIN schedule s ON a.scheduleId = s.scheduleId
JOIN route r ON a.routeId = r.routeId
JOIN requirements req
WHERE a.attendanceStatus = 'Absen'
ORDER BY s.date ASC 
";



$result = mysqli_query($conn, $sql);

// Kalau query error → tampilkan penyebabnya
if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($conn),
        "query" => $sql
    ]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {

    $data[] = [
        "nik"        => $row['nik'],
        "full_name" => $row['name'],
        "email"     => $row['email'],
        "day"       => $row['day'],
        "date"      => $row['date'],
        "time"      => $row['time'],
        "route_name"=> $row['routeName']
    ];
}

echo json_encode($data);