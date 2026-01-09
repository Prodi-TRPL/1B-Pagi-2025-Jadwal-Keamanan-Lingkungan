<?php
session_start();

// Cek apakah session sudah ada
if (!isset($_SESSION['rw']) || !isset($_SESSION['ward'])) {
  echo "<script>alert('Session tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit();
}

$pengelolaRW = $_SESSION['rw'];
$pengelolaWard = $_SESSION['ward'];
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">
  <link rel="stylesheet" href="../../CSS/Dashboard-Pengelola_Data-Warga.css">
  <link rel="stylesheet" href="../../CSS/Main.css" />
  <style>
    .edit-btn,
    .delete-btn {
      padding: 6px 14px;
      font-size: 14px;
      white-space: nowrap;
      /* Buttons won't stretch cells */
    }

    /* Popup Layout */
    .popup-form {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      justify-content: center;
      align-items: center;
      background: rgba(0, 0, 0, 0.6);
    }

    .popup-form.active {
      display: flex;
    }

    .form-content {
      background: #ffffff;
      padding: 25px;
      border-radius: 12px;
      width: 450px;
      max-width: 100%;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .close-btn {
      position: absolute;
      font-size: 24px;
      cursor: pointer;
      color: var(--Text4);
      right: 485px;
      top: 50px;
    }

    .close-btn-edit {
      position: absolute;
      font-size: 24px;
      cursor: pointer;
      color: var(--Text4);
      right: 485px;
      top: 10px;
    }

    .save-btn {
      background-color: var(--Text4);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      cursor: pointer;
      width: 100%;
      transition: 0.3s;
      font-weight: 600;
    }

    .save-btn:hover {
      background-color: var(--Text3);
    }

    /* Search + Filter */
    .filter-select,
    .search-input {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid var(--Text1);
      background: white;
      font-size: 14px;
    }

    /* Pagination Fix */
    .pagination {
      margin-top: 15px;
      display: flex;
      justify-content: center;
      gap: 8px;
    }

    .page-btn {
      padding: 8px 14px;
      border: none;
      background: #064d2e;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .page-btn.active {
      background: #4CAF50;
    }

    .dots {
      color: white;
      opacity: 0.8;
      padding: 8px 6px;
    }

    section {
      display: flex;
      gap: 25px;
      padding: 30px;
      height: calc(100vh - 180px);
      /* Make both columns same height */
    }

    .left-container,
    .right-container {
      flex: 1;
      /* Equal width columns */
      display: flex;
      flex-direction: column;
      background-color: var(--Text2);
      padding: 25px;
      border-radius: 10px;
    }

    .left-container {
      height: auto;
      /* let the content decide the height */
      align-self: flex-start;
      /* prevent flex forcing full height */
      gap: 20px;
    }

    .right-container {
      height: auto;
      align-self: flex-start;
    }


    .scroll-container {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }


    .Pemberitahuan-list {
      background-color: var(--Text4);
      color: var(--Text2);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      border-radius: 8px;
      width: 100%;
      font-weight: 800;
      height: 60px;
      transition: .1s ease;
      cursor: pointer;
      border: none;
      font-family: 'inter', sans-serif;
      font-size: 16px;
    }

    .Pemberitahuan-list:hover {
      scale: 1.05;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      background-color: #11311e;
    }

    .details {
      text-align: left;
    }

    .details p {
      font-weight: 100;
      color: var(--Text3);
    }

    .right-container h2,
    .right-container p,
    .right-container strong {
      color: var(--Text4);
      /* or any color you want */
    }

    /* Right container UI improvement */
    .right-container {
      background-color: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    /* Title */
    .right-container h2 {
      font-size: 26px;
      font-weight: 700;
      color: #064d2e;
      margin-bottom: 10px;
    }

    /* Detail row layout */
    .detail-row {
      display: flex;
      gap: 10px;
      font-size: 16px;
      padding: 8px 0;
    }

    .detail-label {
      width: 130px;
      font-weight: 700;
      color: #064d2e;
    }

    .detail-value {
      flex: 1;
      color: #333;
    }

    /* Button group */
    .action-buttons {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .edit-btn,
    .delete-btn {
      flex: 1;
      padding: 12px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 700;
      border: none;
      transition: 0.2s;
      font-size: 16px;
    }

    .edit-btn {
      background: #07663b;
      color: #fff;
    }

    .edit-btn:hover {
      background: #054f2d;
    }

    .delete-btn {
      background: #ca3d3d;
      color: #fff;
    }

    .delete-btn:hover {
      background: #a42a2a;
    }

    .swal2-container {
      z-index: 99999 !important;
      /* keep popup on top */
    }

    html.swal2-shown body {
      padding-right: 0 !important;
      /* stops layout jump */
    }

    #addForm,
    #editForm{
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    #addForm label,
    #editForm label {
      color: var(--Text1);
    }

    #addForm select,
    #editForm select {
      padding: 12px;
      border-radius: 10px;
      background: var(--Text2);
      font-size: 14px;
      width: 100%;
      outline: none;
      color: var(--Text1);
      transition: .3s;
    }

    #addForm input,
    #editForm input {
      padding: 12px;
      border-radius: 10px;
      background: var(--Text2);
      font-size: 14px;
      width: 100%;
      outline: none;
      color: var(--Text1);
      transition: .3s;
    }

    #addForm select:focus,
    #editForm select:focus {
      border-color: var(--Text4);
    }
  </style>
  <script>
    const userRW = "<?= $pengelolaRW ?>";
    const userWard = "<?= $pengelolaWard ?>";
  </script>

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
      <a href="Beranda.php" class="unselected">Beranda</a>
      <a href="Jadwal.php" class="unselected">Jadwal</a>
      <a href="#" class="selected">Data Warga</a>
      <a href="Laporan.php" class="unselected">Laporan</a>
      <a href="../Profil.php" class="unselected">Profil</a>
    </div>
  </nav>

  <!-- Data Warga -->
  <section class="data-warga">
    <div class="left-container">
      <div style="display: flex; gap: 10px;">
        <input type="text" id="searchInput" class="search-input" placeholder="Cari">

        <select id="filterStatus" class="filter-select">
          <option value="">Semua</option>
          <option value="Warga">Warga</option>
          <option value="Pengelola">Pengelola</option>
        </select>

        <button class="add-btn" onclick="openForm()">+ Tambah</button>
      </div>


      <div class="scroll-container">

        <?php
        include "../../PHP/Config.php";

        $stmt = $conn->prepare("SELECT *, 
  TIMESTAMPDIFF(YEAR, dateBirth, CURDATE()) AS umur 
  FROM inhabitants WHERE RW = ? AND Ward = ? ORDER BY Name ASC");

        $stmt->bind_param("ss", $pengelolaRW, $pengelolaWard);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "
<button class='Pemberitahuan-list next-btn'
  data-status='{$row['status']}'
  onclick=\"openResidentDetails(
    '{$row['inhabitantId']}',
    '{$row['nik']}',
    '{$row['name']}',
    '{$row['address']}',
    '{$row['gender']}',
    '{$row['phone']}',
    '{$row['umur']}',
    '{$row['status']}',
    '{$row['dateBirth']}'
  )\">


        <div class='details'>
          <span>{$row['name']}</span>
          <p>{$row['nik']}</p>
        </div>
        <span>&gt;</span>
      </button>
    ";
          }
        } else {
          echo "<p style='color:var(--Text4);'>Tidak ada data warga</p>";
        }
        ?>


      </div>
      <div id="pagination" class="pagination"></div>

    </div>
    <div class="right-container" id="detailContainer">
      <div class="placeholder">
        <h2 style="color:var(--Text4);">Detail Warga</h2>
        <p style="color:var(--Text1);">Tidak ada data dipilih</p>
      </div>
    </div>





  </section>

  <!-- POPUP TAMBAH -->
  <div id="popupForm" class="popup-form">
    <div class="form-content">
      <span class="close-btn" onclick="closeForm()">&times;</span>
      <h2>Tambah Data Warga</h2>

      <form id="addForm" onsubmit="addData(event)">
        <div>
          <label>NIK:</label>
          <input type="text" name="nik" required minlength="16" maxlength="16" oninput="validateNIK(this)" oninvalid="this.setCustomValidity('NIK harus terdiri dari 16 digit angka')">
        </div>

        <div>
          <label>Nama:</label>
          <input type="text" name="nama" required>
        </div>

        <div>
          <label>Alamat:</label>
          <input type="text" name="alamat" required>
        </div>

        <div>
          <label>Jenis Kelamin:</label>
          <select name="jenis_kelamin" required>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <div>
          <label>No Telp:</label>
          <input type="text" name="no_telp" required>
        </div>

        <div>
          <label>Tanggal Lahir:</label>
          <input type="date" name="tanggal_lahir" required>
        </div>


        <input type="hidden" name="rw" id="addRW">
        <input type="hidden" name="ward" id="addWard">

        <button type="submit" class="save-btn">Simpan</button>
      </form>
    </div>
  </div>

  <!-- POPUP EDIT -->
  <div id="editPopup" class="popup-form">
    <div class="form-content">
      <span class="close-btn-edit" onclick="closeEdit()">&times;</span>
      <h2>Edit Data Warga</h2>

      <form id="editForm" onsubmit="updateData(event)">
        <input type="hidden" id="editID">

        <label>NIK:</label>
        <input type="text" id="editNIK" required minlength="16" maxlength="16" oninput="validateNIK(this)" oninvalid="this.setCustomValidity('NIK harus terdiri dari 16 digit angka')">

        <label>Nama:</label>
        <input type="text" id="editNama" required>

        <label>Alamat:</label>
        <input type="text" id="editAlamat" required>

        <label>Jenis Kelamin:</label>
        <select id="editJK" required>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>

        <label>No. Telp:</label>
        <input type="text" id="editTelp" required>

        <label>Tanggal Lahir:</label>
        <input type="date" id="editTanggalLahir" required>

        <input type="hidden" value="Warga" id="editStatus" required>

        <button type="submit" class="save-btn">Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>

  function validateNIK(input) {
    if (!/^\d{16}$/.test(input.value)) {
      input.setCustomValidity("NIK harus terdiri dari 16 digit angka");
    } else {
      input.setCustomValidity("");
    }
  }

    let deleteID = null;

    function openResidentDetails(id, nik, nama, alamat, jk, telp, umur, status, tanggal_lahir) {
      deleteID = id;

      const canModify = (status === "Warga"); // Only "Warga" can be edited/deleted

      const right = document.getElementById("detailContainer");
      right.innerHTML = `
    <h2>Detail Warga</h2>
    <div class="detail-row"><div class="detail-label">NIK</div><div class="detail-value">${nik}</div></div>
    <div class="detail-row"><div class="detail-label">Nama</div><div class="detail-value">${nama}</div></div>
    <div class="detail-row"><div class="detail-label">Alamat</div><div class="detail-value">${alamat}</div></div>
    <div class="detail-row"><div class="detail-label">Jenis Kelamin</div><div class="detail-value">${jk}</div></div>
    <div class="detail-row"><div class="detail-label">No. Telp</div><div class="detail-value">${telp}</div></div>
    <div class="detail-row"><div class="detail-label">Status</div><div class="detail-value">${status}</div></div>
    <div class="detail-row"><div class="detail-label">Umur</div><div class="detail-value">${umur} Tahun</div></div>


    ${canModify ? `
      <div class="action-buttons">
        <button class="edit-btn" onclick="openEdit('${id}', '${nik}', '${nama}', '${alamat}', '${jk}', '${telp}', '${tanggal_lahir}', '${status}')">Edit</button>
        <button class="delete-btn" onclick="openDelete('${id}')">Hapus</button>
      </div>
      `
          : `<p style="color:red; font-weight:bold; margin-top:10px;">⚠ Tidak bisa mengubah data ini.</p>`
        }
  `;
    }




    function openForm() {
      document.getElementById("popupForm").classList.add("active");
      document.body.classList.add("popup-open");

      document.getElementById("addRW").value = userRW;
      document.getElementById("addWard").value = userWard;

    }

    function closeForm() {
      document.getElementById("popupForm").classList.remove("active");
      document.body.classList.remove("popup-open");
    }


    function addData(e) {
      e.preventDefault();

      let form = document.getElementById("addForm");
      let formData = new FormData(form);
      formData.append("action", "insert"); // 🟢 VERY IMPORTANT
      formData.append("status", "Warga");

      fetch("../../PHP/Proses.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.text())
        .then(data => {
          closeForm(); // close manual popup
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Data warga telah ditambahkan!",
            timer: 2000,
            showConfirmButton: false,
            heightAuto: false
          }).then(() => {
            location.reload();
          });
        })
        .catch(() => {
          Swal.fire({
            icon: "error",
            title: "Gagal!",
            text: "Terjadi kesalahan. Silakan coba lagi.",
            timer: 2000,
            showConfirmButton: false
          });
        });
    }



    function openEdit(id, nik, nama, alamat, jk, telp, tanggal_lahir, status) {
      document.getElementById("editPopup").style.display = "flex";
      editID.value = id;
      editNIK.value = nik;
      editNama.value = nama;
      editAlamat.value = alamat;
      editJK.value = jk;
      editTelp.value = telp;
      editTanggalLahir.value = tanggal_lahir;
      editStatus.value = status;
    }


    function closeEdit() {
      document.getElementById("editPopup").style.display = "none";
    }

    function updateData(e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append("action", "update");
      formData.append("id", editID.value);
      formData.append("nik", editNIK.value);
      formData.append("nama", editNama.value);
      formData.append("alamat", editAlamat.value);
      formData.append("jenis_kelamin", editJK.value);
      formData.append("tanggal_lahir", editTanggalLahir.value);
      formData.append("no_telp", editTelp.value);
      formData.append("status", document.getElementById("editStatus").value);




      fetch("../../PHP/Proses.php", { method: "POST", body: formData })
        .then(res => res.text())
        .then(data => {
          closeEdit();
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Data berhasil diperbarui!",
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        });
    }

    function openDelete(id) {
      deleteID = id;

      Swal.fire({
        title: "Hapus Data Ini?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal"
      }).then((result) => {
        if (result.isConfirmed) {
          deleteData();
        }
      });
    }


    function closeDelete() {
      document.getElementById("hapusPopup").style.display = "none";
    }

    function deleteData() {
      let formData = new FormData();
      formData.append("action", "delete");
      formData.append("id", deleteID);

      fetch("../../PHP/Proses.php", { method: "POST", body: formData })
        .then(res => res.text())
        .then(data => {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Data telah dihapus!",
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            location.reload();
          });
        });
    }


    let currentPage = 1;
    const rowsPerPage = 5;
    let sortDirection = true;
    let sortColumnIndex = null;

    function displayTableRows() {
      const rows = Array.from(document.querySelectorAll('.scroll-container .Pemberitahuan-list'));
      const keyword = document.getElementById("searchInput").value.toLowerCase();

      const filterStatus = document.getElementById("filterStatus").value.toLowerCase();

      let filteredRows = rows.filter(row => {
        const text = row.textContent.toLowerCase();
        const statusText = row.querySelector("p").textContent.toLowerCase(); // NIK but we need status

        // get status from button onclick argument dynamically
        const onclickAttr = row.getAttribute("onclick");
        const extractedStatus = row.dataset.status.toLowerCase();


        const matchesSearch = text.includes(keyword);
        const matchesStatus = filterStatus === "" || extractedStatus === filterStatus;

        return matchesSearch && matchesStatus;
      });




      if (sortColumnIndex !== null) {
        filteredRows.sort((a, b) => {
          const aText = a.children[sortColumnIndex].textContent.trim();
          const bText = b.children[sortColumnIndex].textContent.trim();
          return sortDirection ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });
      }

      const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

      rows.forEach(row => row.style.display = "none");

      const start = (currentPage - 1) * rowsPerPage;
      const end = start + rowsPerPage;

      filteredRows.slice(start, end).forEach(row => row.style.display = "");

      renderPagination(totalPages);
    }

    function changePage(page, total) {
      if (page < 1) page = 1;
      if (page > total) page = total;
      currentPage = page;
      displayTableRows();
    }

    function renderPagination(totalPages) {
      const pagination = document.getElementById("pagination");
      pagination.innerHTML = "";

      if (totalPages <= 1) return;

      const maxVisible = 1; // jumlah nomor kiri & kanan current

      const addButton = (label, page, disabled = false, active = false) => {
        const btn = document.createElement("button");
        btn.textContent = label;
        btn.disabled = disabled;
        btn.className = "page-btn " + (active ? "active" : "");
        btn.onclick = () => changePage(page);
        pagination.appendChild(btn);
      };

      // First + Prev
      addButton("<<", 1, currentPage === 1);
      addButton("<", currentPage - 1, currentPage === 1);

      // Show first page + ellipsis
      if (currentPage > maxVisible + 1) {
        addButton(1, 1, false, currentPage === 1);
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "dots";
        pagination.appendChild(dots);
      }

      // Middle pages (dinamis)
      const start = Math.max(1, currentPage - maxVisible);
      const end = Math.min(totalPages, currentPage + maxVisible);

      for (let i = start; i <= end; i++) {
        addButton(i, i, false, i === currentPage);
      }

      // Last page + ellipsis
      if (currentPage < totalPages - maxVisible) {
        const dots = document.createElement("span");
        dots.textContent = "...";
        dots.className = "dots";
        pagination.appendChild(dots);

        addButton(totalPages, totalPages, false, currentPage === totalPages);
      }

      // Next + Last
      addButton(">", currentPage + 1, currentPage === totalPages);
      addButton(">>", totalPages, currentPage === totalPages);
    }


    function createPageBtn(text, onclick) {
      const btn = document.createElement("button");
      btn.textContent = text;
      btn.className = "page-btn";
      btn.onclick = onclick;
      return btn;
    }

    document.getElementById("searchInput").addEventListener("keyup", () => {
      currentPage = 1;
      displayTableRows();
    });

    document.getElementById("filterStatus").addEventListener("change", () => {
      currentPage = 1;
      displayTableRows();
    });





    window.onload = displayTableRows;



  </script>
</body>

</html>