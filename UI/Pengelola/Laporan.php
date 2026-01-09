<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['inhabitants_id']) || !isset($_SESSION['rw'])) {
  echo "<script>alert('Session pengelola tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit();
}

include "../../PHP/Config.php";

$rw = $_SESSION['rw'];
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">

  <link rel="stylesheet" href="../../CSS/Dashboard-Pengelola_Laporan.css">
  <link rel="stylesheet" href="../../CSS/Main.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://kit.fontawesome.com/8eb0a590d4.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<style>
  .card-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 440px;
  }

  .laporan-card,
  .pengajuan-card {
    background: linear-gradient(135deg, #0f3d26, #14532d);
    border-radius: 12px;
    padding: 0 20px;
    color: #fff;
    cursor: pointer;
    transition: .1s ease;
    display: flex;
    align-items: center;
    height: 100px;
    justify-content: space-between;
    width: 440px;
    display: flex;
    flex-direction: row;
  }

  .laporan-card:hover,
  .pengajuan-card:hover {
    scale: 1.1;
  }


  .card-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .laporan-card h4,
  .pengajuan-card h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
  }

  .laporan-card .meta,
  .pengajuan-card .meta {
    font-size: 13px;
    opacity: .75;
  }

  .status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .status.Pending {
    background: #facc15;
    color: #422006
  }

  .status.Diterima {
    background: #4ade80;
    color: #064e3b
  }

  .status.Ditolak {
    background: #f87171;
    color: #7f1d1d
  }

  .btn-detail {
    margin-top: 10px;
    width: 100%;
    padding: 8px;
    border-radius: 10px;
    background: #111;
    color: #fff;
    border: none;
    cursor: pointer;
  }

  .pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    margin-top: 16px;
  }

  .pagination button {
    background: #14532d;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
  }

  .pagination button:hover {
    background: #22c55e;
  }

  #pageInfo {
    font-weight: 600;
  }

  .popup {
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

  .popup-content h1 {
    color: var(--Text4);
  }


  .popup-content {
    background: var(--Text2);
    color: var(--Text1);
    padding: 40px;
    border-radius: 8px;
    width: 70%;
    height: 85%;
    text-align: left;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-in-out;
  }

  .popup-container {
    display: flex;
    flex-direction: row;
    gap: 40px;
    width: 100%;
    height: 400px;
    margin-top: 20px;
  }

  .popup-container p {
    text-align: justify;
    font-weight: 100;
  }

  .date-time {
    display: flex;
    flex-direction: row;
    gap: 10px;
  }

  .popup-leftcontainer img {
    width: 100%;
    height: 350px;
    border-radius: 6px;
  }

  #map {
    width: 100%;
    height: 350px;
    border-radius: 6px;
    display: none;
  }


  .popup-leftcontainer {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .buttons {
    display: flex;
    flex-direction: row;
    gap: 20px;
    justify-content: space-between;
  }

  .buttons button {
    height: 40px;
    width: 180px;
    border-radius: 6px;
    border: 2px solid var(--Text4);
    background-color: var(--Text2);
    color: var(--Text4);
    font-family: 'inter', sans-serif;
    cursor: pointer;
    transition: .2s ease;
  }

  .buttons button:hover {
    background-color: var(--Text4);
    color: var(--Text2);
  }

  .buttons .active {
    background-color: var(--Text4);
    color: var(--Text2);
    cursor: default;
  }

  .popup-rightcontainer {
    display: flex;
    flex-direction: column;
    width: 500px;
    gap: 20px;
  }

  .popup-rightcontainer p {
    font-weight: 10;

  }

  .Title {
    font-size: 20px;
  }

  .close-btn {
    position: fixed;
    top: 50px;
    right: 205px;
    z-index: 999999;
    color: var(--Text4);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  .close-btn:hover {
    scale: 1.5;
  }

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

  #laporanDropdown {
    margin-bottom: 20px;
  }
</style>

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
      <a href="Beranda.php" class="unselected">Beranda</a>
      <a href="Jadwal.php" class="unselected">Jadwal</a>
      <a href="Data-Warga.php" class="unselected">Data Warga</a>
      <a href="#" class="selected">Laporan</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </nav>

  <!-- Combined Laporan Kejadian -->
  <section class="laporan-kejadian">
    <section class="laporan">
      <div class="search">
        <input type="text" placeholder="Cari" class="search-input" id="searchInput">

        <!-- Pop-up Tambah data -->

      </div>

      <div>
        <select id="laporanDropdown" onchange="toggleLaporan()">
          <option value="insiden">Laporan Insiden</option>
          <option value="pengajuan">Pengajuan</option>
        </select>

        <div id="insidenTable" class="card-list">
          <?php
          $qr = $conn->query("SELECT * FROM reports ORDER BY time DESC");
          while ($r = $qr->fetch_assoc()):
            ?>
            <div class="laporan-card" data-kind="report" data-status="<?= $r['status'] ?>" onclick="openDetail(this)"
              data-reportid="<?= $r['reportId'] ?>" data-judul="<?= htmlspecialchars($r['title']) ?>"
              data-type="<?= htmlspecialchars($r['type']) ?>" data-desc="<?= htmlspecialchars($r['description']) ?>"
              data-file="../../Uploads/<?= $r['file'] ?>" data-datetime="<?= $r['time'] ?>"
              data-lat="<?= $r['latitude'] ?>" data-lng="<?= $r['longitude'] ?>">
              <h4><?= htmlspecialchars($r['title']) ?></h4>
              <div class="meta"><?= date("d M Y H:i", strtotime($r['time'])) ?></div>
              <span class="status <?= $r['status'] ?>"><?= $r['status'] ?></span>
            </div>
          <?php endwhile ?>
        </div>

        <div id="pengajuanTable" class="card-list" style="display:none;">
          <?php
          $qs = $conn->query("SELECT * FROM submission ORDER BY date DESC");
          while ($r = $qs->fetch_assoc()):
            ?>
            <div class="pengajuan-card" data-kind="insiden" data-status="<?= $r['submissionStatus'] ?>"
              data-reportid="<?= $r['submissionId'] ?>" data-type="<?= htmlspecialchars($r['type']) ?>"
              data-desc="<?= htmlspecialchars($r['description']) ?>" data-datetime="<?= $r['date'] ?>"
              onclick="openDetail(this)">
              <div class="meta"><?= date("d M Y H:i", strtotime($r['date'])) ?></div>
              <span class="status <?= $r['submissionStatus'] ?>"><?= $r['submissionStatus'] ?></span>
            </div>
          <?php endwhile ?>
        </div>

        <div class="pagination">
          <button id="prevPage">‹</button>
          <span id="pageInfo">1 / 1</span>
          <button id="nextPage">›</button>
        </div>



    </section>



    <section class="grafik-laporan">
      <div>
        <select id="grafikDropdown" onchange="toggleGrafik()">
          <option value="ronda">Absensi Ronda</option>
          <option value="status">Status Lingkungan</option>
          <option value="pengajuan">Pengajuan Masuk</option>
          <option value="laporan">Laporan Insiden</option>
        </select>
        <button class="download-btn" onclick="downloadGraphs()">Unduh Grafik</button>
      </div>

      <div id="rondaChartContainer" class="chart-container">
        <h2>Absensi Ronda</h2>
        <canvas id="patrolChart"></canvas>
      </div>

      <div id="statusChartContainer" class="chart-container" style="display: none;">
        <h2>Status Lingkungan</h2>
        <canvas id="securityChart"></canvas>
      </div>

      <div id="submissionChartContainer" class="chart-container" style="display: none;">
        <h2>Pengajuan Masuk</h2>
        <canvas id="submissionChart"></canvas>
      </div>

      <div id="reportChartContainer" class="chart-container" style="display: none;">
        <h2>Laporan Insiden</h2>
        <canvas id="reportChart"></canvas>
      </div>

    </section>
  </section>

  <div id="popupForm" class="popup-form">
    <div class="form-content">
      <span class="close-btn">&times;</span>
      <h2>Lapor Insiden</h2>

      <form class="Form">
        <label for="kategori">Kategori</label>
        <select id="kategori" required>
          <option value="" disabled selected hidden>Pilih Opsi</option>
          <option value="pencurian">Pencurian</option>
          <option value="pembunuhan">Pembunuhan</option>
          <option value="kecelakaan">Kecelakaan</option>
          <option value="kebakaran">Kebakaran</option>
          <option value="lainnya">Lainnya</option>
        </select>

        <p>*Informasi Tambahan</p>

        <label>Lokasi</label>
        <button type="button" class="lct-btn">Tetapkan Lokasi</button>

        <label>Detail Insiden</label>
        <input type="text" maxlength="50" placeholder="Ceritakan lebih lanjut (min.50 karakter)" required>

        <label>Lampiran (Opsional)</label>
        <p>*Anda dapat mengunggah jenis file: MP4, JPG</p>
        <input type="file" multiple>

        <button type="submit" class="save-btn">Simpan</button>
      </form>
    </div>
  </div>

  <!-- Popup Laporan -->
  <div id="popup-laporan" class="popup">
    <div class="popup-content" data-reportid="" data-mode="">
      <span class="close-btn" id="popupClose">&times;</span>
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







  <script>
    let map = null;
    let currentLat = null;
    let currentLng = null;

    const image = document.getElementById("image");
    const video = document.getElementById("video");

    const gambarBtn = document.getElementById("gambar");
    const lokasiBtn = document.getElementById("lokasi");
    const leafletmap = document.getElementById("map");
    const mapDiv = document.getElementById("map");
    let marker = null;

    const popupLaporan = document.getElementById("popup-laporan");
    const laporanJudul = document.getElementById("laporanJudul");
    const laporanDeskripsi = document.getElementById("laporanDeskripsi");

    const cards = [...document.querySelectorAll(".laporan-card")];
    const perPage = 3;
    let page = 1;
    const total = Math.ceil(cards.length / perPage);

    const pager = document.querySelector(".pagination");

    const whiteChartOptions = {
      responsive: true,
      plugins: {
        legend: {
          labels: {
            color: "#ffffff"
          }
        },
        tooltip: {
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          backgroundColor: "rgba(0,0,0,0.8)"
        }
      },
      scales: {
        x: {
          ticks: { color: "#ffffff" },
          grid: { color: "rgba(255,255,255,0.15)" }
        },
        y: {
          ticks: { color: "#ffffff" },
          grid: { color: "rgba(255,255,255,0.15)" }
        }
      }
    };

    const searchInput = document.getElementById("searchInput");
    const dropdown = document.getElementById("laporanDropdown");

    searchInput.addEventListener("input", doSearch);
    dropdown.addEventListener("change", doSearch);

    function doSearch() {
      const q = searchInput.value.toLowerCase();
      const mode = dropdown.value;

      // Sembunyikan semua dulu
      document.querySelectorAll(".laporan-card").forEach(card => card.style.display = "none");
      document.querySelectorAll(".pengajuan-card").forEach(card => card.style.display = "none");

      if (mode === "insiden") {
        document.querySelectorAll(".laporan-card").forEach(card => {
          const text = card.innerText.toLowerCase();
          card.style.display = text.includes(q) ? "block" : "none";
        });
      } else {
        document.querySelectorAll(".pengajuan-card").forEach(card => {
          const text = card.innerText.toLowerCase();
          card.style.display = text.includes(q) ? "block" : "none";
        });
      }
    }



    if (cards.length === 0) {
      pager.style.display = "none";        // 🔥 SEMBUNYIKAN PAGINATION
    } else {

      function render() {
        cards.forEach((c, i) => {
          c.style.display = (i >= (page - 1) * perPage && i < page * perPage) ? "flex" : "none";
        });
        document.getElementById("pageInfo").innerText = `${page} / ${total}`;
      }

      document.getElementById("prevPage").onclick = () => {
        if (page > 1) { page--; render(); }
      };
      document.getElementById("nextPage").onclick = () => {
        if (page < total) { page++; render(); }
      };

      render();
    }

    function loadMap(lat, lng) {
      // Jika map sudah ada → reset view
      if (map) {
        map.setView([lat, lng], 17);

        if (marker) marker.remove();
        marker = L.marker([lat, lng]).addTo(map);
        return;
      }

      // Map baru
      map = L.map('detailLokasi').setView([lat, lng], 17);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap'
      }).addTo(map);

      marker = L.marker([lat, lng]).addTo(map);
    }


    function openForm() { document.getElementById("popupForm").style.display = "flex"; }
    function closeForm() { document.getElementById("popupForm").style.display = "none"; }

    function initPagination() {
      const pager = document.querySelector(".pagination");
      const perPage = 3;
      let page = 1;

      function getActiveCards() {
        const mode = document.getElementById("laporanDropdown").value;
        const q = document.getElementById("searchInput").value.toLowerCase();

        let list = mode === "insiden"
          ? document.querySelectorAll("#insidenTable .laporan-card")
          : document.querySelectorAll("#pengajuanTable .pengajuan-card");

        return [...list].filter(c => c.innerText.toLowerCase().includes(q));
      }

      function render() {
        const cards = getActiveCards();
        const total = Math.ceil(cards.length / perPage) || 1;

        pager.style.display = cards.length ? "flex" : "none";
        document.getElementById("pageInfo").innerText = `${page} / ${total}`;

        // hide semua
        document.querySelectorAll("#insidenTable .laporan-card, #pengajuanTable .pengajuan-card")
          .forEach(c => c.style.display = "none");

        // show yg aktif
        cards.slice((page - 1) * perPage, page * perPage)
          .forEach(c => c.style.display = "flex");
      }

      document.getElementById("prevPage").onclick = () => { if (page > 1) { page--; render(); } };
      document.getElementById("nextPage").onclick = () => { page++; render(); };

      // refresh kalau dropdown/search berubah
      document.getElementById("laporanDropdown").addEventListener("change", () => { page = 1; render(); });
      document.getElementById("searchInput").addEventListener("input", () => { page = 1; render(); });

      render();
    }

    function toggleLaporan() {
      const val = document.getElementById("laporanDropdown").value;

      const insiden = document.getElementById("insidenTable");
      const pengajuan = document.getElementById("pengajuanTable");

      const pager = document.querySelector(".pagination");

      if (val === "insiden") {
        insiden.style.display = "grid";
        pengajuan.style.display = "none";
        initPagination(insiden);
      } else {
        insiden.style.display = "none";
        pengajuan.style.display = "grid";
        initPagination(pengajuan);
      }
    }



    function toggleGrafik() {
      const grafikDropdown = document.getElementById("grafikDropdown").value;
      const ids = ['rondaChartContainer', 'statusChartContainer', 'submissionChartContainer', 'reportChartContainer'];
      ids.forEach(id => document.getElementById(id).style.display = 'none');
      if (grafikDropdown === "ronda") document.getElementById('rondaChartContainer').style.display = 'block';
      else if (grafikDropdown === "status") document.getElementById('statusChartContainer').style.display = 'block';
      else if (grafikDropdown === "pengajuan") document.getElementById('submissionChartContainer').style.display = 'block';
      else document.getElementById('reportChartContainer').style.display = 'block';
    }

    let treeLogo = null;
    function loadTreeLogo() {
      return new Promise((resolve, reject) => {
        const imgEl = new Image();
        imgEl.crossOrigin = "anonymous";
        imgEl.src = "../../Asset/Image/Logo.png"; // pastikan path benar
        imgEl.onload = () => {
          const c = document.createElement("canvas");
          const ctx = c.getContext("2d");
          c.width = imgEl.naturalWidth;
          c.height = imgEl.naturalHeight;
          ctx.drawImage(imgEl, 0, 0);
          treeLogo = c.toDataURL("image/png");
          resolve(true);
        };
        imgEl.onerror = () => resolve(false); // jangan crash bila image tidak ditemukan
      });
    }

    // buat image transparan dari dataURL (untuk watermark)
    function makeTransparentImage(dataUrl, alpha = 0.08) {
      return new Promise((resolve, reject) => {
        const imgEl = new Image();
        imgEl.crossOrigin = "anonymous";
        imgEl.src = dataUrl;
        imgEl.onload = () => {
          const c = document.createElement("canvas");
          const ctx = c.getContext("2d");
          c.width = imgEl.naturalWidth;
          c.height = imgEl.naturalHeight;
          ctx.clearRect(0, 0, c.width, c.height);
          ctx.globalAlpha = alpha;
          ctx.drawImage(imgEl, 0, 0, c.width, c.height);
          resolve(c.toDataURL("image/png"));
        };
        imgEl.onerror = () => resolve(dataUrl); // fallback
      });
    }

    // load logo awal
    loadTreeLogo();

    async function downloadGraphs() {
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF("p", "mm", "a4");

      if (!treeLogo) await loadTreeLogo();

      const now = new Date();
      const periode = now.toLocaleString("id-ID", { month: "long", year: "numeric" });

      // HEADER (logo + judul)
      if (treeLogo) {
        pdf.addImage(treeLogo, "PNG", 15, 10, 20, 20); // header logo
      }
      pdf.setFont("helvetica", "bold");
      pdf.setFontSize(18);
      pdf.text("TREE - Laporan Grafik", 105, 18, { align: "center" });
      pdf.setFont("helvetica", "normal");
      pdf.setFontSize(13);
      pdf.text(`Periode: ${periode}`, 105, 25, { align: "center" });

      // WATERMARK: bikin versi transparan lalu masukkan
      let watermark = treeLogo;
      if (treeLogo) watermark = await makeTransparentImage(treeLogo, 0.06);
      if (watermark) {
        // posisikan watermark lebih ke tengah A4; ukuran relatif
        pdf.addImage(watermark, "PNG", 40, 80, 130, 130);
      }

      // DAFTAR GRAFIK 2 KOL
      const charts = [
        { id: "patrolChart", title: "Absensi Ronda" },
        { id: "securityChart", title: "Status Lingkungan" },
        { id: "submissionChart", title: "Data Pengajuan Ronda" },
        { id: "reportChart", title: "Laporan Insiden" }
      ];

      let xLeft = 15;
      let xRight = 110;
      let y = 40;
      const boxW = 85;
      const boxH = 60;

      for (let i = 0; i < charts.length; i++) {
        const chart = charts[i];
        const canvas = document.getElementById(chart.id);
        if (!canvas) continue;

        // perbaikan SYNTAX: remove stray quote
        const img = canvas.toDataURL("image/png", 1.0);

        const x = (i % 2 === 0) ? xLeft : xRight;
        if (i % 2 === 0 && i !== 0) y += boxH + 10;

        // box/card
        pdf.setDrawColor(180);
        pdf.setLineWidth(0.3);
        // roundedRect mungkin tidak tersedia di semua build; fallback ke rect
        if (typeof pdf.roundedRect === "function") {
          pdf.roundedRect(x, y, boxW, boxH, 3, 3);
        } else {
          pdf.rect(x, y, boxW, boxH);
        }

        // judul kecil grafik di tengah box
        pdf.setFontSize(11);
        pdf.setFont("helvetica", "bold");
        pdf.text(chart.title, x + boxW / 2, y + 7, { align: "center" });

        // masukkan gambar grafik (fit di dalam card)
        const imgX = x + 3;
        const imgY = y + 10;
        const imgW = boxW - 6;
        const imgH = boxH - 15;
        pdf.addImage(img, "PNG", imgX, imgY, imgW, imgH);
      }

      // FOOTER
      pdf.setFont("helvetica", "italic");
      pdf.setFontSize(10);
      pdf.text("© 2025 TREE | Teknologi Ronda Efektif & Efisien", 105, 292, { align: "center" });
      pdf.text("Halaman 1", 195, 292, { align: "right" });

      pdf.save("Laporan-Grafik-TREE.pdf");
    }

    // ambil data dan render chart (tetap seperti semula)
    fetch("../../PHP/GetGraphData.php")
      .then(r => r.json())
      .then(data => {

        if (data.error) {
          console.warn("GetGraphData.php:", data.error);
          return;
        }

        buatGrafikAbsensi(data.absensi || []);
        buatGrafikLaporan(data.laporan || []);

        // 🔥 STATUS (perlu lastStatus)
        buatGrafikStatus({
          rows: data.status || [],
          lastStatus: data.lastStatus || "AMAN"
        });

        buatGrafikPengajuan(data.pengajuan || []);
      })
      .catch(err => console.error("fetch error:", err));

    function buatGrafikAbsensi(absensi) {
      const labels = absensi.map(r => r.tgl || '');
      const hadir = absensi.map(r => Number(r.hadir || 0));
      const tidak = absensi.map(r => Number(r.tidak_hadir || 0));
      const rata = labels.map((_, i) => (hadir[i] + tidak[i]) / 2);

      new Chart(document.getElementById('patrolChart'), {
        type: 'line',
        data: {
          labels,
          datasets: [
            { label: "Hadir", data: hadir, borderColor: "green", fill: false },
            { label: "Tidak Hadir", data: tidak, borderColor: "red", fill: false },
            { label: "Rata-rata", data: rata, borderColor: "blue", fill: false }
          ]
        },
        options: whiteChartOptions
      });
    }

    function buatGrafikLaporan(rows) {
      const labels = rows.map(r => r.tgl || '');
      new Chart(document.getElementById('reportChart'), {
        type: "line",
        data: {
          labels,
          datasets: [
            { label: "Pencurian", data: rows.map(r => Number(r.pencurian || 0)), borderColor: "#ff4b4b", fill: false },
            { label: "Pembunuhan", data: rows.map(r => Number(r.pembunuhan || 0)), borderColor: "#ffcc00", fill: false },
            { label: "Kecelakaan", data: rows.map(r => Number(r.kecelakaan || 0)), borderColor: "#00c8ff", fill: false },
            { label: "Kebakaran", data: rows.map(r => Number(r.kebakaran || 0)), borderColor: "#ff6600", fill: false },
            { label: "Lainnya", data: rows.map(r => Number(r.lainnya || 0)), borderColor: "#9d4edd", fill: false },
            { label: "Total", data: rows.map(r => Number(r.total || 0)), borderColor: "#00ff88", fill: false }
          ]
        },
        options: whiteChartOptions
      });
    }

    function buatGrafikStatus(res) {

      const rows = res.rows;
      const last = res.lastStatus;

      let warna = "#2e7d32"; // AMAN
      if (last === "WASPADA") warna = "#f9a825";
      if (last === "BAHAYA") warna = "#c62828";

      const labels = rows.map(r => r.tgl || '');
      const angka = rows.map(r => Number(r.status_angka || 0));

      new Chart(document.getElementById('securityChart'), {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: "Status Lingkungan",
            data: angka,
            borderColor: warna,
            backgroundColor: warna + "22",
            fill: true,
            tension: 0.35,
            pointRadius: 5
          }]
        },
        options: {
          ...whiteChartOptions,

          scales: {
            // SALIN TOTAL SCALE PUTIH
            x: {
              ...whiteChartOptions.scales.x,
              grid: { color: "#ffffff22" },
              ticks: { color: "#fff" }
            },

            y: {
              ...whiteChartOptions.scales.y,
              min: 1,
              max: 3,
              grid: { color: "#ffffff22" },
              ticks: {
                color: "#fff",
                stepSize: 1,
                callback(v) {
                  if (v == 3) return "AMAN";
                  if (v == 2) return "WASPADA";
                  if (v == 1) return "BAHAYA";
                  return "";
                }
              }
            }
          }
        }
      });
    }




    function loadInsiden(rows) {
      if (!rows || !rows.length) return;

      // sortir tanggal
      rows.sort((a, b) => new Date(a.tgl) - new Date(b.tgl));

      buatGrafikLaporan(rows);
      buatGrafikStatus(rows);
    }

    function loadPengajuan(rows) {
      if (!rows || !rows.length) return;

      rows.sort((a, b) => new Date(a.tgl) - new Date(b.tgl));

      buatGrafikPengajuan(rows);
    }


    function buatGrafikPengajuan(rows) {
      const labels = rows.map(r => r.tgl || '');
      new Chart(document.getElementById('submissionChart'), {
        type: "line",
        data: {
          labels,
          datasets: [
            { label: "Izin Ikut Ronda", data: rows.map(r => Number(r.ikut || 0)), borderColor: "#00b4ff", fill: false },
            { label: "Izin Tidak Ikut", data: rows.map(r => Number(r.tidak_ikut || 0)), borderColor: "#ff4b4b", fill: false },
            { label: "Total", data: rows.map(r => Number(r.total || 0)), borderColor: "#00ff88", fill: false }
          ]
        },
        options: whiteChartOptions
      });
    }

    fetch("../../PHP/GetLaporanList.php")
      .then(res => res.json())
      .then(data => {
        loadInsiden(data.reports);
        loadPengajuan(data.submissions);
      });

    function openDetail(item) {

      const popupBox = document.querySelector("#popup-laporan .popup-content");
      if (!popupBox) return;
      popupBox.dataset.reportid = item.dataset.reportid || "";

      const isPengajuan = item.closest("#pengajuanTable") !== null;
      const gambarBtn = document.getElementById("gambar");
      const lokasiBtn = document.getElementById("lokasi");

      const btnApprove = document.querySelector(".btn-approve-pop");
      const btnReject = document.querySelector(".btn-reject-pop");

      if (btnApprove && btnReject) {
        btnApprove.style.display = "inline-block";
        btnReject.style.display = "inline-block";

        btnApprove.disabled = false;
        btnReject.disabled = false;

        btnApprove.className = "btn-approve-pop";
        btnReject.className = "btn-reject-pop";
      }

      // ================= TEXT =================
      const judulEl = document.querySelector("#popup-laporan h1");
      const kategoriEl = document.querySelector(".popup-rightcontainer .Title");
      const descEl = document.querySelector(".popup-rightcontainer p:nth-child(2)");

      if (judulEl) judulEl.innerText = item.dataset.judul || (isPengajuan ? "Pengajuan" : "Laporan");
      if (kategoriEl) kategoriEl.innerHTML = `<strong>Kategori:</strong> ${item.dataset.type || "-"}</strong>`;
      if (descEl) descEl.innerText = item.dataset.desc || "-";

      // ================= DATE =================
      const dt = item.dataset.datetime || "";
      const parts = dt.split(" ");

      const timeEl = document.querySelector(".date-time p:nth-child(1)");
      const dateEl = document.querySelector(".date-time p:nth-child(3)");

      if (timeEl) timeEl.innerText = parts[1] ? parts[1].slice(0, 5) : "-";
      if (dateEl) dateEl.innerText = parts[0] || "-";

      // ================= MEDIA / MAP =================
      const mediaBox = document.querySelector(".popup-media");
      const mapBox = document.getElementById("map");

      if (image) image.style.display = "none";
      if (video) video.style.display = "none";
      if (mapBox) mapBox.style.display = "none";

      // ===== MODE PENGAJUAN =====
      if (isPengajuan) {

        if (mediaBox) mediaBox.style.display = "none";
        if (mapBox) mapBox.style.display = "none";

        if (gambarBtn) gambarBtn.style.display = "none";
        if (lokasiBtn) lokasiBtn.style.display = "none";

      } else {

        // ===== MODE LAPORAN =====
        if (mediaBox) mediaBox.style.display = "block";
        if (gambarBtn) gambarBtn.style.display = "block"; // ✅ MUNCUL
        if (lokasiBtn) lokasiBtn.style.display = "block"; // ✅ MUNCUL

        const file = item.dataset.file || "";
        currentLat = parseFloat(item.dataset.lat || 0);
        currentLng = parseFloat(item.dataset.lng || 0);

        if (file) {
          const ext = file.split('.').pop().toLowerCase();
          if (["mp4", "webm", "ogg"].includes(ext)) {
            if (video) { video.src = file; video.style.display = "block"; }
          } else {
            if (image) { image.src = file; image.style.display = "block"; }
          }

          setTimeout(() => {
            if (!mapBox) return;
            if (map) map.remove();

            mapBox.style.display = "block";
            map = L.map("map", { zoomControl: false, attributionControl: false })
              .setView([currentLat, currentLng], 16);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);
            marker = L.marker([currentLat, currentLng]).addTo(map);
            mapBox.style.display = "none";
          }, 200);
        }
      }

      // ================= STATUS =================
      const status = item.dataset.status || "Pending";

      if (btnApprove && btnReject) {

        if (status === "Pending") {
          btnApprove.innerText = "Setujui";
          btnReject.innerText = "Tolak";
        }

        else if (status === "Diterima") {
          btnApprove.innerText = "✓ Diterima";
          btnApprove.classList.add("btn-approved");
          btnReject.style.display = "none";
          btnApprove.disabled = true;
        }

        else if (status === "Ditolak") {
          btnReject.innerText = "✕ Ditolak";
          btnReject.classList.add("btn-rejected");
          btnApprove.style.display = "none";
          btnReject.disabled = true;
        }
      }

      popupLaporan.dataset.mode = isPengajuan ? "submission" : "report";
      popupLaporan.style.display = "flex";
    }




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



    function closeDetail() {
      document.getElementById("detailPopup").style.display = "none";
    }

    function approveItem() {

      const popup = document.getElementById("popup-laporan");
      const popupBox = popup.querySelector(".popup-content");
      const mode = popup.dataset.mode;
      const id = popupBox.dataset.reportid;

      if (!id || !mode) {
        Swal.fire("Error", "Data tidak lengkap", "error");
        return;
      }

      const url =
        mode === "report"
          ? "../../PHP/ApproveReport.php"
          : "../../PHP/ApproveSubmission.php";

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
      })
        .then(r => r.text())
        .then(() => location.reload());
    }


    function rejectItem() {

      const popup = document.getElementById("popup-laporan");
      const popupBox = popup.querySelector(".popup-content");
      const mode = popup.dataset.mode;
      const id = popupBox.dataset.reportid;

      if (!id || !mode) {
        Swal.fire("Error", "Data tidak lengkap", "error");
        return;
      }

      const url =
        mode === "report"
          ? "../../PHP/RejectReport.php"
          : "../../PHP/RejectSubmission.php";

      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + id
      })
        .then(r => r.text())
        .then(() => location.reload());
    }

    document.addEventListener("click", function (e) {

      if (e.target.closest(".btn-approve-pop")) {
        quickAction(e.target, "Diterima");
      }

      if (e.target.closest(".btn-reject-pop")) {
        quickAction(e.target, "Ditolak");
      }

      if (e.target.closest(".close-btn")) {
        popupLaporan.style.display = "none";
      }

    });

    function quickAction(btn, action) {

      const popup = document.getElementById("popup-laporan");
      const popupBox = popup.querySelector(".popup-content");
      const id = popupBox.dataset.reportid;
      const mode = popup.dataset.mode; // report / submission

      if (!id || !mode) {
        Swal.fire("Error", "Data tidak lengkap", "error");
        return;
      }

      const url =
        mode === "report"
          ? "../../PHP/UpdateLaporan.php"
          : "../../PHP/UpdateSubmission.php";

      const label = mode === "report" ? "Laporan" : "Pengajuan";

      Swal.fire({
        title: label + " " + action + "?",
        icon: "question",
        iconColor: "var(--Text4)",
        color: "var(--Text1)",
        showCancelButton: true,
        confirmButtonText: "Ya",
        confirmButtonColor: "var(--Text4)"
      }).then(result => {

        if (!result.isConfirmed) return;

        fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "id=" + id + "&action=" + action
        })
          .then(res => res.text())
          .then(r => {

            if (r === "success") {

              const selector =
                mode === "report"
                  ? `.laporan-card[data-reportid="${id}"]`
                  : `.pengajuan-card[data-reportid="${id}"]`;

              const card = document.querySelector(selector);

              if (card) {
                card.dataset.status = action;
                const badge = card.querySelector(".status");
                badge.innerText = action;
                badge.className = "status " + action;
              }

              Swal.fire({
                icon: "success",
                iconColor: "var(--Text4)",
                title: "Berhasil",
                text: label + " berhasil " + action,
                color: "var(--Text1)",
                timer: 1400,
                showConfirmButton: false
              });

              popup.style.display = "none";

            } else {
              Swal.fire("Gagal", label + " gagal diproses", "error");
            }
          });
      });
    }

    // Tutup popup jika klik di luar card
    document.addEventListener("click", function (e) {

      if (e.target.classList.contains("close-btn")) {
        popupLaporan.style.display = "none";
      }

    });







  </script>


</body>

</html>