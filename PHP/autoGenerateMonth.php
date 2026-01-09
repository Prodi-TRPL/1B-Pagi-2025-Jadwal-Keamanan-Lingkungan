
<?php
session_start();
header("Content-Type: application/json");
include "Config.php";

$rw = $_SESSION['rw'];
$year  = date("Y");
$month = date("m");

$start = "$year-$month-01 00:00:00";
$end   = date("Y-m-t 23:59:59", strtotime($start));

/* 🔥 HARD DELETE BULAN INI */
$conn->query("
DELETE FROM attendances 
WHERE scheduleId IN (
    SELECT scheduleId FROM schedule
    WHERE rw='$rw' AND date BETWEEN '$start' AND '$end'
)");
$conn->query("DELETE FROM schedule WHERE rw='$rw' AND date BETWEEN '$start' AND '$end'");

/* ===== requirements ===== */
$req = $conn->query("SELECT minAge,maxAge FROM requirements WHERE rw='$rw'")->fetch_assoc();
$minAge=$req['minAge']; 
$maxAge=$req['maxAge'];

/* ===== kandidat ===== */
$pq = $conn->query("
SELECT nik FROM inhabitants
WHERE gender='L'
AND TIMESTAMPDIFF(YEAR, dateBirth, CURDATE()) BETWEEN $minAge AND $maxAge
");

$people=[];
while($r=$pq->fetch_assoc()) $people[]=$r['nik'];

/* 🔀 SHUFFLE SETIAP BULAN */
shuffle($people);

/* ===== routes ===== */
$rq = $conn->query("SELECT routeId,routeName FROM route WHERE rw='$rw'");
$routes=[];
while($r=$rq->fetch_assoc()) $routes[]=$r;

$day=new DateTime("$year-$month-01");
$endD=new DateTime(date("Y-m-t",strtotime("$year-$month-01")));
$p=0;

/* 🔥 DISTRIBUSI RATA */
while($day <= $endD){
    foreach($routes as $r){

        if($p==0) shuffle($people);   // <-- kunci fairness

        $nik = $people[$p];
        $p   = ($p+1)%count($people);

        $date = $day->format("Y-m-d 19:00:00");

        $conn->query("INSERT INTO schedule (date,route,rw) VALUES ('$date','{$r['routeName']}','$rw')");
        $sid=$conn->insert_id;

        $conn->query("INSERT INTO attendances (scheduleId,routeId,nik) VALUES ($sid,{$r['routeId']},'$nik')");
    }
    $day->modify("+1 day");
}

echo json_encode(["success"=>true]);
