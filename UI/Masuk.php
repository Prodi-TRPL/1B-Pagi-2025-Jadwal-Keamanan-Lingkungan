<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TREE</title>
  <link rel="icon" type="image/png" href="../Asset/Image/Logo.png">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../JavaScript/Masuk.js"></script>
  <link rel="stylesheet" href="../CSS/Masuk.css">
  <link rel="stylesheet" href="../CSS/Main.css">
</head>

<body>
  <div class="logo">
    <img src="../Asset/Image/Logo.png" class="Logo" alt="Logo">
    <p class="LogoText">TREE</p>
  </div>

  <div class="hero">
    <form class="Form" onsubmit="event.preventDefault(); Check();">
      <p class="title">Masuk</p>

      <div class="Username">
        <input type="text" id="nik" name="nik" autocomplete="off" required   minlength="16" maxlength="16" oninput="validateNIK(this)" oninvalid="this.setCustomValidity('NIK harus terdiri dari 16 digit angka')">
        <label for="nik">NIK</label>
      </div>

      <div class="Password">
        <input type="password" id="password" name="password" autocomplete="off" required oninput="validatePassword(this)"  oninvalid="this.setCustomValidity('Kata sandi wajib diisi')">
        <label for="password">Kata Sandi</label>
      </div>

      <p class="Reset" onclick="Forgot()">Lupa Kata Sandi?</p>
      <button class="Login" type="submit">Masuk</button>
    </form>
  </div>

</body>
<script>

  function validateNIK(input) {
    if (!/^\d{16}$/.test(input.value)) {
      input.setCustomValidity("NIK harus terdiri dari 16 digit angka");
    } else {
      input.setCustomValidity("");
    }
  }

  function validatePassword(input) {
    if (input.value.trim() === "") {
      input.setCustomValidity("Kata sandi wajib diisi");
    } else {
      input.setCustomValidity("");
    }
  }
</script>
</html>
