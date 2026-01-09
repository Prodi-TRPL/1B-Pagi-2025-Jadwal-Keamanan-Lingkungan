<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['inhabitants_id']) || !isset($_SESSION['rw'])) {
  echo "<script>alert('Session pengelola tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit();
}

include "../../PHP/Config.php";

$rw = $_SESSION['rw'];

// Ambil status terbaru
$sql = "SELECT status FROM envistatus WHERE rw = ? ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $rw);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$currentStatus = $row ? strtoupper($row['status']) : "AMAN"; // default
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">
  <link rel="stylesheet" href="../../CSS/Beranda-Pengelola.css" />
  <link rel="stylesheet" href="../../CSS/Main.css">

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <style>
    :root {
      --pulseColor: #5ff397;
      /* default */
    }

    /* Global Layout */
    /* Main content layout */
    .main-content {
      display: flex;
      flex-direction: row;
      gap: 25px;
      justify-content: space-between;
      align-items: center;
    }

    .left-section {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    /* Cards */
    .card {
      background: none;
      border-radius: 10px;
      color: var(--Text2);
      transition: .25s;
      width: 900px;
      padding: 20px;
    }

    .jadwal-ronda {
      height: auto;
    }

    .laporan-warga {
      height: 525px;
    }

    .card h3 {
      margin-bottom: 15px;
      font-size: 40px;
      color: white;
      font-weight: 600;
    }

    /* Hover for cards */
    .card:not(.status-keamanan):hover {
      transform: translateY(-5px);
    }

    /* Laporan Cards */
    /* Laporan ke tengah */
    .laporan-container {
      justify-content: center;
      height: 490px;
      overflow: hidden;
    }

    .laporan-list {
      overflow: hidden;
    }

    /* Card laporan update */
    .laporan-card {
      width: 400px;
      height: 460px;
      text-align: left;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      margin: 10px;
      background: white;
      box-shadow: 0px 6px 12px rgba(0, 0, 0, .15);
      padding: 20px;
      gap: 20px;
    }

    .laporan-card h4 {
      font-size: 30px;
      color: var(--Text4);
    }

    .desc-text {
      font-size: 18px;
      color: var(--Text1);
      text-align: justify;
      margin-bottom: 10px;
    }

    /* Tombol aksi */
    .btn-group {
      display: flex;
      justify-content: space-between;
      margin-top: auto;
      gap: 30px;
    }

    .btn-detail {
      padding: 7px 10px;
      border-radius: 5px;
      font-size: 14px;
      cursor: pointer;
      border: none;
      font-weight: 700;
      transition: .1s ease;
      width: 200px;
      height: 40px;
      background-color: var(--Text4);
      color: var(--Text2);
    }

    .btn-detail:hover {
      scale: 1.12;
    }

    /*Tombol Aksi Pop Up*/
    .btn-group-pop {
      display: flex;
      flex-direction: row;
      justify-content: end;
      margin-top: auto;
      gap: 30px;
    }

    .btn-approve-pop,
    .btn-reject-pop {
      padding: 7px 10px;
      border-radius: 5px;
      font-size: 14px;
      cursor: pointer;
      border: none;
      font-weight: 700;
      transition: .1s ease;
      width: 120px;
      height: 40px;
      float: right;
    }

    .btn-approve-pop {
      background: var(--Text4);
      border: 2px solid var(--Text4);
      color: white;
    }

    .btn-approve-pop:hover {
      background-color: #0b3d2e;
      scale: 1.1;
    }

    .btn-reject-pop {
      background: #ff0000ff;
      color: white;
    }

    .btn-reject-pop:hover {
      background-color: #cf0000ff;
      scale: 1.1;
    }

    .empty {
      font-size: 20px;
      color: #e2e2e2;
      text-align: center;
      margin: 0 20px;
    }


    .laporan-card:hover {
      transform: scale(1.05);
    }

    .prev-btn,
    .next-btn {
      border: none;
      background: var(--Text4);
      font-size: 24px;
      width: 35px;
      height: 35px;
      border-radius: 8px;
      cursor: pointer;
      color: #fff;
      transition: .1s ease;
      margin: 10px;
    }

    .prev-btn {
      margin-right: 20px;
    }

    .next-btn {
      margin-left: 20px;
    }

    .prev-btn:hover,
    .next-btn:hover {
      background: #0b3d2e;
      scale: 1.2;
    }

    /* Status Keamanan */
    .status-keamanan {
      text-align: center;
      background: var(--Text2);
      border-radius: 10px;
      padding: 20px;
      height: 460px;
    }

    .status-keamanan h2 {
      color: var(--Text4);
      margin-bottom: 25px;
      font-size: 30px;
    }

    .status-circle {
      position: relative;
      width: 250px;
      height: 250px;
      border: 60px solid #5ff397;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
    }

    .status-text {
      color: rgba(0, 0, 0, .3);
      font-size: 20px;
      font-weight: 800;
    }

    .ubah-btn {
      margin-top: 30px;
      background: var(--Text4);
      color: var(--Text2);
      font-weight: bold;
      padding: 12px 25px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      transition: .1s;
    }

    .ubah-btn:hover {
      scale: 1.1;
    }

    /* Popups */
    .popup-overlay {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
    }

    .popup-card {
      background: #fff;
      padding: 25px;
      width: 350px;
      border-radius: 10px;
      box-shadow: 0px 6px 18px rgba(0, 0, 0, .25);
      text-align: center;
    }

    .close-btn {
      float: right;
      cursor: pointer;
      font-size: 24px;
      color: #0b3d2e;
    }

    /* Status selection popup */
    .status-options {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 15px;
    }

    .status-option {
      padding: 12px 18px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 700;
      transition: .1s ease;
      border: 2px solid transparent;
    }

    .status-option:hover {
      scale: 1.05;
    }

    /* PULSE GLOW */
    /* PULSE 1 */
    .status-circle::before,
    .status-circle::after {
      content: "";
      position: absolute;
      width: 230px;
      height: 230px;
      border: 10px solid var(--pulseColor);
      border-radius: 50%;
      animation: pulseGlow 5s infinite ease-out;
    }

    /* PULSE 2 */
    .status-circle::after {
      animation-delay: 1.25s;
    }

    /* PULSE 3 */
    .status-circle span {
      position: absolute;
      width: 230px;
      height: 230px;
      border: 5px solid var(--pulseColor);
      border-radius: 50%;
      animation: pulseGlow 5s infinite ease-out;
      animation-delay: 1.5s;
    }

    .ImageIcon {
      height: 160px;
    }


    @keyframes pulseGlow {
      0% {
        transform: scale(1);
        opacity: 0.4;
      }

      70% {
        transform: scale(1.2);
        opacity: 0;
      }

      100% {
        opacity: 0;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-left">
      <div class="profile-icon">
        <img src="../../Asset/Image/Logo.png">
        <p>TREE</p>
      </div>
    </div>
    <div class="navbar-right">
      <a href="#" class="selected">Beranda</a>
      <a href="Jadwal.php" class="unselected">Jadwal</a>
      <a href="Data-Warga.php" class="unselected">Data Warga</a>
      <a href="Laporan.php" class="unselected">Laporan</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </nav>

  <!-- Konten Utama -->
  <main class="main-content">
    <section class="left-section">
      <!-- Laporan Warga -->
      <div class="card laporan-warga">
        <div class="laporan-container">
          <button class="prev-btn">&lt;</button>
          <div class="laporan-list">
            <?php
            $query = $conn->prepare("
        SELECT *
        FROM reports
        WHERE rw = ?
        AND status = 'Pending'
        AND time >= NOW() - INTERVAL 7 DAY
        ORDER BY time DESC
      ");
            $query->bind_param("i", $rw);
            $query->execute();
            $result = $query->get_result();

            $iconMap = [
              "kebakaran" => "Kebakaran.png",
              "pembunuhan" => "Pembunuhan.png",
              "pencurian" => "Pencurian.png",
              "kecelakaan" => "Kecelakaan.png",
              "lainnya" => "Lainnya.png"
            ];

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {

                $type = $row['type'];
                $title = $row['title'];

                $desc = strip_tags($row['description']);
                $desc = strlen($desc) > 120 ? substr($desc, 0, 120) . '…' : $desc;

                // ambil icon sesuai type (jika tidak ada pakai default)
                $icon = $iconMap[$type] ?? "Lainnya.png";

                echo '<div class="laporan-card"
  data-reportid="' . $row['reportId'] . '"
  data-judul="' . htmlspecialchars($row['title']) . '"
  data-type="' . htmlspecialchars(ucwords(strtolower($row['type']))) . '"
  data-desc="' . htmlspecialchars($row['description']) . '"
  data-file="../../Uploads/' . $row['file'] . '"
  data-datetime="' . $row['time'] . '"
  data-lat="' . $row['latitude'] . '"
  data-lng="' . $row['longitude'] . '"
>
      <h4>' . htmlspecialchars(ucwords(strtolower($title))) . '</h4>

      <img class="ImageIcon" src="../../Asset/Image/' . $icon . '" class="laporan-icon">

      <p class="desc-text">' . htmlspecialchars($desc) . '</p>

      <div class="btn-group">
        <button class="btn-detail"">Detail</button>
      </div>
    </div>';
              }
            } else {
              echo '<p class="empty">Belum ada laporan baru</p>';
            }

            ?>
          </div>
          <button class="next-btn">&gt;</button>
        </div>
      </div>
    </section>

    <?php
    $color = "#00ff40";
    if ($currentStatus == "WASPADA")
      $color = "#fffb00";
    if ($currentStatus == "BAHAYA")
      $color = "#ff0000";
    ?>

    <!-- Status Keamanan -->
    <aside class="status-keamanan">
      <h2>Status Keamanan Terkini</h2>
      <div class="status-circle" id="statusCircle" style="border-color: <?= $color ?>;">
        <span></span>
        <div class="status-text" id="statusText"><?php echo $currentStatus; ?></div>
      </div>
      <button class="ubah-btn" id="ubahStatusBtn">Ubah</button>
    </aside>
  </main>

  <!-- Popup Laporan -->
  <div id="popup-laporan" class="popup">
    <div class="popup-content" data-reportid="">
      <span class="close-btn">&times;</span>
      <h1>Lala Lepas</h1>
      <div class="date-time">
        <p>22:00</p>
        <p>|</p>
        <p>20-12-25</p>
      </div>

      <div class="popup-container">
        <div class="popup-leftcontainer">
          <img id="image" alt="Image">
          <video id="video" controls
            style="display:none; width:100%; height:350px; border-radius:12px; object-fit:cover;"></video>
          <div id="map"></div>
          <div class="buttons">
            <button id="gambar" class="active" onclick="changetoimg()">Gambar</button>
            <button id="lokasi" onclick="changetoloc()">Lokasi</button>
          </div>
        </div>
        <div class="popup-rightcontainer">
          <p class="Title"><strong>Kategori:</strong> Lainnya</p>
          <p>Lala adalah seorang manusia yang dilaporkan lepas dari pengawasan pada waktu yang belum ditentukan
            secara pasti. Berdasarkan informasi yang diterima, Lala terakhir kali terlihat berada di area sekitar
            tempat tinggalnya sebelum dinyatakan tidak diketahui keberadaannya.</p>

          <div class="btn-group-pop">
            <input type="hidden" id="popupReportId">
            <button class="btn-approve-pop" onclick="quickAction(this,'Diterima')">Setujui</button>
            <button class="btn-reject-pop" onclick="quickAction(this,'Ditolak')">Tolak</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Popup Ubah Status -->
  <div class="popup-overlay" id="popupStatus">
    <div class="popup-card">
      <span class="close-btn">&times;</span>
      <h2>Ubah Status Keamanan</h2>
      <div class="status-options">
        <div class="status-option aman" data-status="AMAN" data-color="#00ff40">AMAN</div>
        <div class="status-option waspada" data-status="WASPADA" data-color="#fffb00">WASPADA</div>
        <div class="status-option bahaya" data-status="BAHAYA" data-color="#ff0000">BAHAYA</div>
      </div>
    </div>
  </div>

  <script>
    let map = null;
    let currentLat = null;
    let currentLng = null;

    const image = document.getElementById("image");
    const video = document.getElementById("video");
    const leafletmap = document.getElementById("map");

    const gambarBtn = document.getElementById("gambar");
    const lokasiBtn = document.getElementById("lokasi");
    const mapDiv = document.getElementById("map");
    let marker = null;


    // Carousel
    const laporanList = document.querySelector(".laporan-list");
    const nextBtn = document.querySelector(".next-btn");
    const prevBtn = document.querySelector(".prev-btn");

    nextBtn.onclick = () => laporanList.scrollBy({ left: 250, behavior: "smooth" });
    prevBtn.onclick = () => laporanList.scrollBy({ left: -250, behavior: "smooth" });

    // Popup Jadwal
    const hariCards = document.querySelectorAll(".hari");
    const popupJadwal = document.getElementById("popupJadwal");
    const popupHari = document.getElementById("popupHari");
    const popupPetugas = document.getElementById("popupPetugas");

    // Popup Laporan
    const laporanCards = document.querySelectorAll(".laporan-card");
    const popupLaporan = document.getElementById("popup-laporan");
    const laporanJudul = document.getElementById("laporanJudul");
    const laporanDeskripsi = document.getElementById("laporanDeskripsi");

    function changetoloc() {
      if (gambarBtn.classList.contains("active")) {
        lokasiBtn.classList.add('active')
        gambarBtn.classList.remove('active')

        image.style.display = "none";
        video.style.display = "none";
        leafletmap.style.display = "block";
        setTimeout(() => {
          map.invalidateSize(); // render ulang saat map SUDAH kelihatan
        }, 200);
      }
    }


    function changetoimg() {
      if (lokasiBtn.classList.contains("active")) {
        gambarBtn.classList.add('active');
        lokasiBtn.classList.remove('active');

        leafletmap.style.display = "none";
        image.style.display = "block";
        if (video.src) video.style.display = "block";
      }
    }

    laporanCards.forEach(card => {
      card.addEventListener("click", () => {

        const popupBox = document.querySelector("#popup-laporan .popup-content");
        popupBox.dataset.reportid = card.dataset.reportid;

        document.querySelector("#popup-laporan h1").innerText = card.dataset.judul;
        document.querySelector(".popup-rightcontainer .Title").innerHTML =
          `<strong>Kategori:</strong> ${card.dataset.type}`;
        document.querySelector(".popup-rightcontainer p:nth-child(2)").innerText =
          card.dataset.desc;

        const dt = card.dataset.datetime;
        const parts = dt.split(" ");

        const date = parts[0];
        const time = parts[1].slice(0, 5);

        document.querySelector(".date-time p:nth-child(1)").innerText = time;
        document.querySelector(".date-time p:nth-child(3)").innerText = date;


        const file = card.dataset.file;
        currentLat = parseFloat(card.dataset.lat);
        currentLng = parseFloat(card.dataset.lng);

        // reset view
        image.style.display = "none";
        video.style.display = "none";
        mapDiv.style.display = "none";

        const ext = file.split('.').pop().toLowerCase();
        if (["mp4", "webm", "ogg"].includes(ext)) {
          video.src = file;
          video.style.display = "block";
        } else {
          image.src = file;
          image.style.display = "block";
        }

        // init map
        setTimeout(() => {
          if (map) map.remove();

          leafletmap.style.display = "block";   // WAJIB tampil dulu
          map = L.map("map", {
            zoomControl: false,
            attributionControl: false
          }).setView([currentLat, currentLng], 16);

          L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(map);
          marker = L.marker([currentLat, currentLng]).addTo(map);

          leafletmap.style.display = "none";
        }, 200);


        popupLaporan.style.display = "flex";
      });
    });





    // Popup Ubah Status
    const ubahStatusBtn = document.getElementById("ubahStatusBtn");
    const popupStatus = document.getElementById("popupStatus");
    const statusCircle = document.getElementById("statusCircle");
    const statusText = document.getElementById("statusText");

    ubahStatusBtn.addEventListener("click", () => {
      popupStatus.style.display = "flex";
    });

    document.querySelectorAll(".status-option").forEach(opt => {
      opt.addEventListener("click", () => {

        let status = opt.dataset.status;

        fetch("../../PHP/UbahStatus.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "status=" + status
        })
          .then(res => res.text())
          .then(response => {
            if (response === "success") {

              statusText.textContent = status;
              statusCircle.style.borderColor = opt.dataset.color;
              document.documentElement.style.setProperty("--pulseColor", opt.dataset.color);

              Swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Status sekarang: " + status,
                color: "var(--Text1)",
                timer: 1500,
                showConfirmButton: false
              });

              popupStatus.style.display = "none";
            } else {
              Swal.fire("Gagal", "Tidak bisa mengubah status", "error");
            }
          });
      });
    });

    function quickAction(btn, action) {
      const popupBox = document.querySelector("#popup-laporan .popup-content");
      const id = popupBox.dataset.reportid;

      if (!id) {
        Swal.fire("Error", "ID laporan tidak ditemukan", "error");
        return;
      }

      Swal.fire({
        title: "Laporan " + action + "?",
        icon: "question",
        iconColor: "var(--Text4)",
        color: "var(--Text1)",
        showCancelButton: true,
        confirmButtonText: "Ya",
        confirmButtonColor: "var(--Text4)"
      }).then(result => {

        if (!result.isConfirmed) return;

        fetch("../../PHP/UpdateLaporan.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "id=" + id + "&action=" + action
        })
          .then(res => res.text())
          .then(r => {
            if (r === "success") {

              Swal.fire({
                icon: "success",
                iconColor: "var(--Text4)",
                title: "Berhasil",
                text: "Laporan berhasil " + action,
                color: "var(--Text1)",
                timer: 1400,
                showConfirmButton: false
              });

              popupLaporan.style.display = "none";

              // hapus card visualnya
              document.querySelector(`.laporan-card[data-reportid="${id}"]`)?.remove();

            } else {
              Swal.fire({
                title: "Gagal", 
                text: "Laporan Gagal" + action, 
                icon: "error",
                iconColor: "red",
                color: "var(--Text1)"
              });
            }   
          });
      });
    }



    // Tutup semua popup
    document.querySelectorAll(".close-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        popupJadwal.style.display = "none";
        popupLaporan.style.display = "none";
        popupStatus.style.display = "none";
      });
    });

    // Tutup popup jika klik di luar card
    window.addEventListener("click", (e) => {
      if (e.target === popupLaporan) popupLaporan.style.display = "none";
      if (e.target === popupStatus) popupStatus.style.display = "none";
    });



  </script>
</body>

</html>