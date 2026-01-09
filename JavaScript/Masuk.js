function Forgot() {
    Swal.fire({
        title: "Lupa Kata Sandi",
        text: "Masukkan email akun Anda",
        input: "email",
        color: "#000000",
        inputPlaceholder: "email@example.com",
        showCancelButton: true,
        confirmButtonText: "Kirim Email",
        confirmButtonColor: "var(--Text4)"
    }).then(result => {
        if (!result.value) return;

        let fd = new FormData();
        fd.append("email", result.value);

        fetch("../PHP/ForgotPassword.php", {
            method: "POST",
            body: fd
        })
        .then(res => res.text())
        .then(res => {
            if (res === "SENT") {
                Swal.fire({
                title: "Berhasil",
                text: "Link login telah dikirim ke email",
                icon: "success",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
                });
            } else {
                Swal.fire("Gagal", res, "error");
            }
        });
    });
}

function Check() {
  const nik = document.getElementById("nik").value;
  const password = document.getElementById("password").value;

  fetch("../PHP/Masuk.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ nik, password })
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        Swal.fire({
          title: "Berhasil!",
          text: "Login berhasil!",
          color: "var(--Text1)",
          confirmButtonColor: "var(--Text4)",
          icon: "success"
        })
          .then(() => window.location.href = data.redirect);
      } else {
        Swal.fire({
          title: "Gagal",
          text: data.message,
          color: "var(--Text1)",
          confirmButtonColor: "var(--Text4)",
          icon: "error"
        });
      }
    });
}
