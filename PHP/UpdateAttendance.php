<?php
header("Content-Type: application/json");
include "Config.php";

if (!isset($_POST["id"])) {
    echo json_encode(["status"=>"error","msg"=>"ID tidak dikirim"]);
    exit;
}

$id = intval($_POST["id"]);

/* ===== AMBIL JAM RONDA ===== */
$req = $conn->query("SELECT timeStart,timeEnd FROM requirements LIMIT 1");
if(!$req){
    echo json_encode(["status"=>"error","msg"=>"Gagal ambil jam ronda"]);
    exit;
}
$req = $req->fetch_assoc();

$today = date("Y-m-d");
$start = strtotime("$today {$req['timeStart']}");
$end   = strtotime("$today {$req['timeEnd']}");
$now   = time();

/* shift jika lewat tengah malam */
if($end < $start){
    $end = strtotime("+1 day", $end);
    if($now < $start) $now = strtotime("+1 day", $now);
}

if($now < $start || $now > $end){
    echo json_encode(["status"=>"closed"]);
    exit;
}

/* ===== CEK STATUS ===== */
$check = $conn->prepare("SELECT attendanceStatus FROM attendances WHERE attendanceId=?");
if(!$check){
    echo json_encode(["status"=>"error","msg"=>"Prepare cek gagal"]);
    exit;
}
$check->bind_param("i",$id);
$check->execute();
$st = $check->get_result()->fetch_assoc();

if($st['attendanceStatus']==="Hadir"){
    echo json_encode(["status"=>"already"]);
    exit;
}

/* ===== UPDATE ===== */
$upd = $conn->prepare("UPDATE attendances SET attendanceStatus='Hadir' WHERE attendanceId=?");
if(!$upd){
    echo json_encode(["status"=>"error","msg"=>"Prepare update gagal"]);
    exit;
}
$upd->bind_param("i",$id);
$upd->execute();

echo json_encode(["status"=>"success"]);
