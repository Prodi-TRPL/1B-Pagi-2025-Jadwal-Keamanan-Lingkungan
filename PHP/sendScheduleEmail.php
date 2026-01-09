<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "DATA JSON TIDAK MASUK"]);
    exit;
}

foreach ($data as $nik => $user) {

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "treepohonhijau@gmail.com";     // ganti
        $mail->Password = "websxiwknrkbdfbv";      // app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";
        $mail->Encoding = "base64";

        $mail->setFrom("treepohonhijau@gmail.com", "TREE");
        $mail->addAddress($user['email'], $user['name']);

        $mail->isHTML(true);
        $mail->Subject = "Jadwal Ronda Malam Anda";

        $body = "
<div style='font-family:Arial;max-width:600px;margin:auto;border:1px solid #ddd;padding:20px'>
<h2 style='text-align:center;color:#2c3e50'>JADWAL RONDA MALAM</h2>

<p>Yth. <b>{$user['name']}</b>,</p>
<p>Berikut adalah jadwal ronda malam Anda:</p>

<p><b>Jam Ronda:</b> {$user['list'][0]['time']}</p>

<table width='100%' cellpadding='8' cellspacing='0' style='border-collapse:collapse'>
<tr style='background:#2c3e50;color:white'>
<th>Hari</th>
<th>Tanggal</th>
<th>Rute</th>
</tr>
";

        foreach ($user['list'] as $j) {
            $body .= "
    <tr>
        <td style='border:1px solid #ccc'>{$j['day']}</td>
        <td style='border:1px solid #ccc'>{$j['date']}</td>
        <td style='border:1px solid #ccc'>{$j['route_name']}</td>
    </tr>
    ";
        }

        $body .= "
</table>

<p style='margin-top:15px'>
Apabila Bapak/Ibu berhalangan hadir, mohon segera mengisi lembar pengajuan tidak ikut ronda malam.
</p>

<p style='margin-top:20px'>
Terima kasih atas perhatian dan kerja samanya.
</p>

<p style='margin-top:30px'>
Hormat kami,<br>
<b>Pengurus Keamanan Lingkungan</b>
</p>
</div>
";



        $mail->Body = $body;
        $mail->send();

    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Gagal kirim ke {$user['email']}"]);
        exit;
    }
}

echo json_encode(["status" => "ok", "message" => "Semua email berhasil dikirim"]);
