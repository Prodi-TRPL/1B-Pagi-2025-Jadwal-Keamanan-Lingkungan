<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_ward'])) {
  echo "<script>alert('Session admin tidak ditemukan! Silakan login ulang'); window.location.href='../Masuk.php';</script>";
  exit();
}

$adminId = $_SESSION['admin_id'];
$adminWard = $_SESSION['admin_ward'];
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';

include "../../PHP/Config.php";
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">
  <link rel="stylesheet" href="../../CSS/Main.css">
  <link rel="stylesheet" href="../../CSS/Dashboard-Pengelola_Data-Warga.css">
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
      top: 10px;
      right: 12px;
      font-size: 24px;
      cursor: pointer;
      color: var(--Text4);
    }

    .save-btn {
      background-color: var(--Text4);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 10px;
      cursor: pointer;
      width: 140px;
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
      border-radius: 5px;
      cursor: pointer;
      font-weight: 700;
      border: none;
      transition: 0.1s;
      font-size: 16px;
    }

    .edit-btn {
      background: var(--Text4);
      color: #fff;
    }

    .edit-btn:hover {
      scale: 1.05;
      background-color: #11311e;
      color: #fff;
    }

    .delete-btn {
      background: red;
      color: #fff;
    }

    .delete-btn:hover {
      scale: 1.05;
      background: rgba(194, 0, 0, 1);
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
    #editForm {
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
      border: 1px solid var(--Text1);
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
      <a href="../ProfilAdmin.php" class="unselected">Profil</a>
    </div>
  </nav>

  <!-- Data Warga -->
  <section class="data-warga">
    <div class="left-container">
      <div style="display: flex; gap: 10px;">
        <input type="text" id="searchInput" class="search-input" placeholder="Cari">

        <select id="filterRW" class="filter-select">
          <option value="">Semua RW</option>
          <!-- bisa diisi dinamis lewat PHP -->
          <?php
          // Ambil distinct RW (sekedar membantu admin memfilter)
          $rws = $conn->prepare("SELECT DISTINCT rw 
                       FROM inhabitants 
                       WHERE ward = ? AND status = 'Pengelola'
                       ORDER BY rw ASC");
          $rws->bind_param("s", $adminWard);
          $rws->execute();
          $result_rw = $rws->get_result();

          while ($r = $result_rw->fetch_assoc()) {
            echo "<option value=\"" . htmlspecialchars($r['rw']) . "\">" . htmlspecialchars($r['rw']) . "</option>";
          }

          ?>
        </select>
        <button class="save-btn" onclick="openAddForm()">+ Pengelola</button>
      </div>


      <div class="scroll-container">
        <?php
        $stmt = $conn->prepare("SELECT *, TIMESTAMPDIFF(YEAR, dateBirth, CURDATE()) AS umur FROM inhabitants WHERE status = 'Pengelola' AND ward = ? ORDER BY name ASC");
        $stmt->bind_param("s", $adminWard);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
          while ($row = $res->fetch_assoc()) {
            $id = $row['inhabitantId'];
            $name = htmlspecialchars($row['name']);
            $nik = htmlspecialchars($row['nik']);
            $rw = htmlspecialchars($row['rw']);
            $gender = htmlspecialchars($row['gender']);
            $phone = htmlspecialchars($row['phone']);
            $umur = htmlspecialchars($row['umur']);
            $address = htmlspecialchars($row['address']);
            $dob = htmlspecialchars($row['dateBirth']);

            echo "
                <button class='Pemberitahuan-list' data-id='{$id}' data-rw='{$rw}' data-status='pengelola' onclick=\"openDetail({$id}, '" . addslashes($nik) . "','" . addslashes($name) . "','" . addslashes($address) . "','{$gender}','{$phone}','{$umur}','{$dob}','{$rw}')\">
                  <div style='text-align:left'>
                    <strong>{$name}</strong><div style='font-size:13px;color:var(--Text3);'>{$nik} • RW {$rw}</div>
                  </div>
                  <div style='align-self:center'>&gt;</div>
                </button>
                ";
          }
        } else {
          echo "<p style='color: var(--Text1)';>Tidak ada Pengelola pada Kelurahan ini.</p>";
        }
        ?>
      </div>
      <div id="pagination" class="pagination"></div>

    </div>
    <div class="right-container" id="detailContainer">
      <div class="placeholder">
        <h2 style="color:var(--Text4);">Detail Pengelola</h2>
        <p style="color:var(--Text1);">Tidak ada data dipilih</p>
      </div>
    </div>
  </section>

  <!-- POPUP TAMBAH -->
  <div id="popupAdd" class="popup-form">
    <div class="form-content">
      <span class="close-btn" onclick="closeAddForm()">&times;</span>
      <h2>Tambah Data Pengelola</h2>

      <form id="addForm" onsubmit="addPengelola(event)">
        <div>
          <label>NIK:</label>
          <input type="text" name="nik" required minlength="16" maxlength="16" oninput="validateNIK(this)" oninvalid="this.setCustomValidity('NIK harus terdiri dari 16 digit angka')">
        </div>

        <div>
          <label>Nama:</label>
          <input type="text" name="name" required>
        </div>

        <div>
          <label>Alamat:</label>
          <input type="text" name="address" required>
        </div>

        <div>
          <label>Jenis Kelamin:</label>
          <select name="gender" required>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <div>
          <label>No Telp:</label>
          <input type="text" name="phone" required>
        </div>

        <div>
          <label>Tanggal Lahir:</label>
          <input type="date" name="dateBirth" required>
        </div>

        <div>
          <label>RW</label>
          <input type="text" name="rw" placeholder="Contoh: 01" required style="width:100%; padding:8px;" />
        </div>

        <input value="<?= htmlspecialchars($adminWard) ?>" type="hidden" name="ward" id="addWard">

        <button type="submit" class="save-btn">Simpan</button>
      </form>
    </div>
  </div>

  <!-- POPUP EDIT -->
  <div id="popupEdit" class="popup-form">
    <div class="form-content">
      <span class="close-btn" onclick="closeEditForm()">&times;</span>
      <h2>Edit Data Warga</h2>

      <form id="editForm" onsubmit="updatePengelola(event)">
        <input type="hidden" id="editId" name="id">

        <label>NIK:</label>
        <input type="text" name="nik" id="editNIK" required minlength="16" maxlength="16" oninput="validateNIK(this)" oninvalid="this.setCustomValidity('NIK harus terdiri dari 16 digit angka')">

        <label>Nama:</label>
        <input type="text" name="name" id="editName" required>

        <label>Alamat:</label>
        <input type="text" name="address" id="editAddress" required>

        <label>Jenis Kelamin:</label>
        <select id="editGender" name="gender" required>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>

        <label>No. Telp:</label>
        <input type="text" name="phone" id="editPhone" required>

        <label>Tanggal Lahir:</label>
        <input type="date" name="dateBirth" id="editDateBirth" required>

        <label>RW: </label>
        <input type="text" name="rw" id="editRW" required style="width:100%; padding:8px;" />
        <input type="hidden" name="ward" id="editWard" value="<?= htmlspecialchars($adminWard) ?>" />

        <button type="submit" class="save-btn">Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const adminWard = "<?= addslashes($adminWard) ?>";
    
    function validateNIK(input) {
      if (!/^\d{16}$/.test(input.value)) {
        input.setCustomValidity("NIK harus terdiri dari 16 digit angka");
      } else {
        input.setCustomValidity("");
      }
    }

    function openAddForm() {
      document.getElementById('popupAdd').classList.add('active');
    }
    function closeAddForm() {
      document.getElementById('popupAdd').classList.remove('active');
    }

    function openEditForm() {
      document.getElementById('popupEdit').classList.add('active');
    }
    function closeEditForm() {
      document.getElementById('popupEdit').classList.remove('active');
    }

    function openDetail(id, nik, name, address, gender, phone, umur, dob, rw) {
      const cont = document.getElementById('detailContainer');
      cont.innerHTML = `
      <h2>Detail Pengelola</h2>
      <div class="detail-row"><div class="detail-label">NIK</div><div class="detail-value">${nik}</div></div>
      <div class="detail-row"><div class="detail-label">Nama</div><div class="detail-value">${name}</div></div>
      <div class="detail-row"><div class="detail-label">Alamat</div><div class="detail-value">${address}</div></div>
      <div class="detail-row"><div class="detail-label">Jenis Kelamin</div><div class="detail-value">${gender}</div></div>
      <div class="detail-row"><div class="detail-label">No. Telp</div><div class="detail-value">${phone}</div></div>
      <div class="detail-row"><div class="detail-label">Status</div><div class="detail-value">Pengelola RW ${rw}</div></div>
      <div class="detail-row"><div class="detail-label">Umur</div><div class="detail-value">${umur} Tahun</div></div>

    <div style="margin-top:12px; display:flex; gap:8px;">
      <button class="edit-btn" onclick="prefillEdit(${id}, '${escapeJS(nik)}', '${escapeJS(name)}', '${escapeJS(address)}', '${gender}', '${escapeJS(phone)}', '${dob}', '${rw}')">Edit</button>
      <button class="delete-btn" onclick="confirmDelete(${id})">Hapus</button>
    </div>
  `;
    }


    // helper escape untuk string yg dimasukkan ke JS onclick
    function escapeJS(str) {
      return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
    }

    // Tambah pengelola
    function addPengelola(e) {
      e.preventDefault();
      const f = document.getElementById('addForm');
      const fd = new FormData(f);
      fd.append('action', 'insert');

      fetch('../../PHP/ProsesPengelola.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(resp => {
          if (resp.success) {
            closeAddForm();
            Swal.fire({
              icon: 'success',
              iconColor: 'var(--Text4)',
              title: 'Berhasil',
              text: resp.message,
              color: 'var(--Text1)',
              timer: 1500,
              showConfirmButton: false
            }).then(() => location.reload());
          } else {
            Swal.fire({
              icon: 'error',
              iconColor: 'red',
              title: 'Gagal',
              color: 'var(--Text1)',
              confirmButtonColor: 'var(--Text4)',
              text: resp.message || 'Terjadi kesalahan'
            });
          }
        }).catch(err => {
          Swal.fire({
            icon: 'error',
            iconColor: 'red',
            title: 'Gagal',
            color: 'var(--Text1)',
            confirmButtonColor: 'var(--Text4)',
            text: 'Terjadi kesalahan jaringan'
          });
        });
    }

    function prefillEdit(id, nik, name, address, gender, phone, dob, rw) {
      document.getElementById('editId').value = id;
      document.getElementById('editNIK').value = nik;
      document.getElementById('editName').value = name;
      document.getElementById('editAddress').value = address;
      document.getElementById('editGender').value = gender;
      document.getElementById('editPhone').value = phone;
      document.getElementById('editDateBirth').value = dob;
      document.getElementById('editRW').value = rw;

      openEditForm();
    }

    function updatePengelola(e) {
      e.preventDefault();
      const f = document.getElementById('editForm');
      const fd = new FormData(f);
      fd.append('action', 'update');

      fetch('../../PHP/ProsesPengelola.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(resp => {
          if (resp.success) {
            closeEditForm();
            Swal.fire({
              icon: 'success',
              iconColor: 'var(--Text4)',
              title: 'Berhasil',
              color: 'var(--Text1)',
              text: resp.message,
              timer: 1200,
              showConfirmButton: false
            }).then(() => location.reload());
          } else {
            Swal.fire({
              icon: 'error',
              iconColor: 'red',
              title: 'Gagal',
              color: 'var(--Text1)',
              confirmButtonColor: 'var(--Text4)',
              text: resp.message || 'Terjadi kesalahan'
            });
          }
        }).catch(() => Swal.fire({
          icon: 'error',
          iconColor: 'red',
          title: 'Gagal',
          color: 'var(--Text1)',
          confirmButtonColor: 'var(--Text4)',
          text: 'Terjadi kesalahan jaringan'
        }));
    }

    function confirmDelete(id) {
      Swal.fire({
        title: 'Hapus Pengelola?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        color: 'var(--Text1)',
        icon: 'warning',
        iconColor: 'var(--Text4)',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        confirmButtonColor: 'red',
        cancelButtonText: 'Batal'
      }).then((res) => {
        if (res.isConfirmed) {
          const fd = new FormData();
          fd.append('action', 'delete');
          fd.append('inhabitantId', id);
          fetch('../../PHP/ProsesPengelola.php', { method: 'POST', body: fd })
            .then(r => r.json()).then(resp => {
              if (resp.success) {
                Swal.fire({
                  icon: 'success',
                  iconColor: 'var(--Text4)',
                  title: 'Terhapus',
                  color: 'var(--Text1)',
                  text: resp.message, timer: 1200,
                  showConfirmButton: false
                }).then(() => location.reload());
              } else {
                Swal.fire({
                  icon: 'error',
                  iconColor: 'red',
                  title: 'Gagal',
                  color: 'var(--Text1)',
                  confirmButtonColor: 'var(--Text4)',
                  text: resp.message || 'Terjadi kesalahan'
                });
              }
            }).catch(() => Swal.fire({
              icon: 'error',
              iconColor: 'red',
              title: 'Gagal',
              color: 'var(--Text1)',
              confirmButtonColor: 'var(--Text4)',
              text: 'Terjadi kesalahan jaringan'
            }));
        }
      });
    }

    let currentPage = 1;
    const rowsPerPage = 5;
    let sortDirection = true;
    let sortColumnIndex = null;

    function displayTableRows() {
      const rows = Array.from(document.querySelectorAll('.scroll-container .Pemberitahuan-list'));
      const keyword = document.getElementById("searchInput").value.toLowerCase();

      const filterStatus = document.getElementById("filterRW").value.toLowerCase();

      let filteredRows = rows.filter(row => {
        const text = row.textContent.toLowerCase();
        const extractedRW = (row.dataset.rw || "").toLowerCase();

        const matchesSearch = text.includes(keyword);
        const matchesRW = filterStatus === "" || extractedRW === filterStatus;

        return matchesSearch && matchesRW;
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

    document.getElementById("searchInput").addEventListener("input", () => {
      currentPage = 1;
      displayTableRows();
    });

    document.getElementById("filterRW").addEventListener("change", () => {
      currentPage = 1;
      displayTableRows();
    });


    window.onload = () => {
      displayTableRows();
    };


  </script>
</body>

</html>