<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

// cek session admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_ward'])) {
    echo json_encode(['success' => false, 'message' => 'Session admin tidak ditemukan']);
    exit();
}

$adminWard = $_SESSION['admin_ward'];

include "Config.php"; // sesuaikan path

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'insert') {

    // =========================
    // AMBIL DATA POST
    // =========================
    $nik = trim($_POST['nik'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dateBirth = trim($_POST['dateBirth'] ?? '');
    $RW = trim($_POST['rw'] ?? '');
    $Ward = trim($_POST['ward'] ?? $adminWard);

    if (!$nik || !$name || !$address || !$gender || !$phone || !$dateBirth || !$RW) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit();
    }

    // =========================
    // CEK DUPLIKAT NIK
    // =========================
    $check = $conn->prepare("SELECT inhabitantId FROM inhabitants WHERE nik = ? LIMIT 1");
    $check->bind_param("s", $nik);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'NIK sudah terdaftar']);
        exit();
    }

    // =========================
    // CEK ENVISTATUS (PARENT)
    // =========================
    $checkEnv = $conn->prepare("SELECT rw FROM envistatus WHERE rw = ? LIMIT 1");
    $checkEnv->bind_param("s", $RW);
    $checkEnv->execute();
    $envRes = $checkEnv->get_result();

    // 🔑 JIKA BELUM ADA → BUAT DULU
    if ($envRes->num_rows === 0) {
        $createEnv = $conn->prepare("
            INSERT INTO envistatus (rw, status, date)
            VALUES (?, 'Aman', NOW())
        ");
        $createEnv->bind_param("s", $RW);
        $createEnv->execute();
    }


    // =========================
    // CEK RADIUS (PARENT)
    // =========================
    $checkRas = $conn->prepare("SELECT rw FROM radius WHERE rw = ? LIMIT 1");
    $checkRas->bind_param("s", $RW);
    $checkRas->execute();
    $RasRes = $checkRas->get_result();

    // 🔑 JIKA BELUM ADA → BUAT DULU
    if ($RasRes->num_rows === 0) {
        $createRas = $conn->prepare("
            INSERT INTO radius (rw)
            VALUES (?)
        ");
        $createRas->bind_param("s", $RW);
        $createRas->execute();
    }

    // =========================
    // CEK REQUIREMENTS (PARENT)
    // =========================
    $checkReq = $conn->prepare("SELECT rw FROM requirements WHERE rw = ? LIMIT 1");
    $checkReq->bind_param("s", $RW);
    $checkReq->execute();
    $ReqRes = $checkReq->get_result();

    // 🔑 JIKA BELUM ADA → BUAT DULU
    if ($ReqRes->num_rows === 0) {
        $createReq = $conn->prepare("
            INSERT INTO requirements (rw)
            VALUES (?)
        ");
        $createReq->bind_param("s", $RW);
        $createReq->execute();
    }

    // =========================
    // BARU INSERT PENGELOLA
    // =========================
    $stmt = $conn->prepare("
        INSERT INTO inhabitants
        (nik, name, address, gender, phone, dateBirth, rw, ward, status, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pengelola', ?)
    ");
    $stmt->bind_param(
        "sssssssss",
        $nik,
        $name,
        $address,
        $gender,
        $phone,
        $dateBirth,
        $RW,
        $Ward,
        $nik
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Pengelola berhasil ditambahkan']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan: ' . $conn->error]);
    }

    exit();
}



if ($action === 'update') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $dateBirth = isset($_POST['dateBirth']) ? trim($_POST['dateBirth']) : '';
    $RW = isset($_POST['rw']) ? trim($_POST['rw']) : '';
    $Ward = isset($_POST['ward']) ? trim($_POST['ward']) : $adminWard;

    if (!$id || !$nik || !$name || !$address || !$gender || !$phone || !$dateBirth || !$RW) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit();
    }

    // Pastikan record yang akan diupdate adalah status Pengelola dan Ward sama dengan admin (security)
    $check = $conn->prepare("SELECT inhabitantId FROM inhabitants WHERE inhabitantId = ? AND status = 'Pengelola' AND ward = ? LIMIT 1");
    $check->bind_param("is", $id, $adminWard);
    $check->execute();
    $cr = $check->get_result();
    if ($cr->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau Anda tidak berhak mengubahnya']);
        exit();
    }

    // Update (cek unique nik kecuali jika milik record ini)
    $checknik = $conn->prepare("SELECT inhabitantId FROM inhabitants WHERE nik = ? AND inhabitantId != ? LIMIT 1");
    $checknik->bind_param("si", $nik, $id);
    $checknik->execute();
    $rnik = $checknik->get_result();
    if ($rnik && $rnik->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'NIK sudah dipakai oleh data lain']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE inhabitants SET nik = ?, name = ?, address = ?, gender = ?, phone = ?, dateBirth = ?, rw = ?, ward = ? WHERE inhabitantId = ?");
    $stmt->bind_param("ssssssssi", $nik, $name, $address, $gender, $phone, $dateBirth, $RW, $Ward, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui: ' . $conn->error]);
    }
    exit();
}

if ($action === 'delete') {

    $inhabitantId = $_POST['inhabitantId'] ?? '';

    if (!$inhabitantId) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
        exit();
    }

    try {
        // =========================
        // BEGIN TRANSACTION
        // =========================
        $conn->begin_transaction();

        // 1️⃣ Ambil RW
        $get = $conn->prepare("
            SELECT rw 
            FROM inhabitants 
            WHERE inhabitantId = ? AND status = 'Pengelola'
            LIMIT 1
        ");
        $get->bind_param("i", $inhabitantId);
        $get->execute();
        $res = $get->get_result();

        if ($res->num_rows === 0) {
            throw new Exception('Pengelola tidak ditemukan');
        }

        $rw = $res->fetch_assoc()['rw'];

        // 2️⃣ Hapus pengelola
        $del = $conn->prepare("
            DELETE FROM inhabitants 
            WHERE inhabitantId = ? AND status = 'Pengelola'
        ");
        $del->bind_param("i", $inhabitantId);
        $del->execute();

        // 3️⃣ Cek sisa pengelola
        $checkRW = $conn->prepare("
            SELECT inhabitantId 
            FROM inhabitants 
            WHERE rw = ? AND status = 'Pengelola'
            LIMIT 1
        ");
        $checkRW->bind_param("s", $rw);
        $checkRW->execute();

        // 4️⃣ Jika TIDAK ADA → hapus sesuai urutan
        if ($checkRW->get_result()->num_rows === 0) {

            // Hapus Radius
            $delRad = $conn->prepare(query: "
                DELETE FROM radius
                WHERE rw = ?
            ");
            $delRad->bind_param("s", $rw);
            $delRad->execute();

            // Hapus Reports
            $delRep = $conn->prepare(query: "
                DELETE FROM reports
                WHERE rw = ?
            ");
            $delRep->bind_param("s", $rw);
            $delRep->execute();

            // Hapus Submission
            $delSub = $conn->prepare(query: "
                DELETE FROM submission
                WHERE rw = ?
            ");
            $delSub->bind_param("s", $rw);
            $delSub->execute();

            // Hapus Requirements
            $delReq = $conn->prepare(query: "
                DELETE FROM requirements
                WHERE rw = ?
            ");
            $delReq->bind_param("s", $rw);
            $delReq->execute();

            // Hapus Attendances
            $delAtt = $conn->prepare("
                DELETE a FROM attendances a
                INNER JOIN schedule s ON a.scheduleId = s.scheduleId
                WHERE s.rw = ?
            ");
            $delAtt->bind_param("s", $rw);
            $delAtt->execute();

            // Hapus Schedule
            $delSch = $conn->prepare(query: "
                DELETE FROM schedule
                WHERE rw = ?
            ");
            $delSch->bind_param("s", $rw);
            $delSch->execute();

            // Hapus Route
            $delRou = $conn->prepare(query: "
                DELETE FROM route
                WHERE rw = ?
            ");
            $delRou->bind_param("s", $rw);
            $delRou->execute();

            //Hapus Inhabitants
            $delInh = $conn->prepare(query: "
                DELETE FROM inhabitants
                WHERE rw = ?
            ");
            $delInh->bind_param("s", $rw);
            $delInh->execute();

            // Hapus Envistatus
            $delEnv = $conn->prepare("
                DELETE FROM envistatus 
                WHERE rw = ?
            ");
            $delEnv->bind_param("s", $rw);
            $delEnv->execute();
        }

        // =========================
        // COMMIT
        // =========================
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Pengelola berhasil dihapus'
        ]);

    } catch (Exception $e) {
        // =========================
        // ROLLBACK
        // =========================
        $conn->rollback();

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    exit();
}

