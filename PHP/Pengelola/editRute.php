<?php
include "../../Config/koneksi.php";

$newRoute = $_POST['newRoute'];
$newDate  = $_POST['newDate'];
$oldRoute = $_POST['oldRoute'];
$oldDate  = $_POST['oldDate'];
$rw       = $_POST['rw'];

$conn->begin_transaction();

try {

    $q = $conn->prepare("
        UPDATE schedule 
        SET route=?, date=?
        WHERE route=? AND rw=? AND DATE(date)=DATE(?)
    ");
    $q->bind_param("sssis", $newRoute, $newDate, $oldRoute, $rw, $oldDate);
    $q->execute();

    if($q->affected_rows == 0){
        throw new Exception("Tidak ada data terupdate.");
    }

    $conn->commit();
    echo json_encode(["status"=>"success"]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(["status"=>"error","msg"=>$e->getMessage()]);
}
