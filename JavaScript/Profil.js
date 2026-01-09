async function Email() {
    const { value: newEmail } = await Swal.fire({
        title: "Ubah Email",
        color: "#000000",
        input: "email",
        inputPlaceholder: "Masukkan Email Baru",
        showCancelButton: true,
        confirmButtonText: "Kirim Kode",
        confirmButtonColor: "var(--Text4)",
        inputValidator: (value) => {
            if (!value) return "Masukkan email!";
        }
    });

    if (!newEmail) return;

    let fd = new FormData();
    fd.append("email", newEmail);

    fetch("../PHP/SendEmailOTP.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(res => {
        if (res === "OTP_SENT") {
            verifyOTP();
        } else {
            Swal.fire({
                title: "Gagal", 
                text: "Gagal mengirim OTP", 
                icon: "error",
                iconColor: "red",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            });
        }
    });
}

async function verifyOTP() {
    const { value: otp } = await Swal.fire({
        title: "Verifikasi Email",
        color: "#000000",
        input: "text",
        inputPlaceholder: "Masukkan kode OTP",
        showCancelButton: true,
        confirmButtonText: "Verifikasi",
        confirmButtonColor: "var(--Text4)"
    });

    if (!otp) return;

    let fd = new FormData();
    fd.append("otp", otp);

    fetch("../PHP/VerifyEmailOTP.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(res => {
        if (res === "SUCCESS") {
            Swal.fire({
                title: "Berhasil", 
                text: "Email berhasil diperbarui", 
                icon: "success",
                iconColor: "var(--Text4)",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            })
                .then(() => location.reload());
        } else {
            Swal.fire({
                title: "Gagal", 
                text: "OTP salah / kadaluarsa", 
                icon: "error",
                iconColor: "red",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            });
        }
    });
}


async function Phone() {
    const { value: phone } = await Swal.fire({
        title: "Ubah Nomor Telepon",
        color: "var(--Text1)",
        input: "text",
        inputPlaceholder: "Masukkan nomor (contoh: 0812xxxx atau +62812xxxx)",
        confirmButtonText: "Simpan",
        confirmButtonColor: "var(--Text4)",
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return "Nomor telepon tidak boleh kosong!";
        }
    });

    if (!phone) return;

    let formData = new FormData();
    formData.append("phone", phone);

    fetch("../PHP/UpdatePhone.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(result => {
            if (result === "SUCCESS") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    color: "var(--Text1)",
                    text: "Nomor telepon diperbarui!",
                    confirmButtonColor: "var(--Text4)"
                }).then(() => location.reload());
            }
            else if (result === "INVALID_PHONE") {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Format nomor tidak valid!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
            else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Gagal memperbarui nomor telepon!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
        });
}

async function Password() {
    const { value: formValues } = await Swal.fire({
        title: "Ubah Kata Sandi",
        color: "var(--Text1)",
        html: `
            <input id="currentPass" class="swal2-input swal-pass" placeholder="Password Saat Ini" type="password">
            <input id="newPass" class="swal2-input swal-pass" placeholder="Password Baru" type="password">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Simpan",
        confirmButtonColor: "var(--Text4)",
        cancelButtonText: "Batal",
        customClass: {
            popup: "swal-pass-popup"
        },
        preConfirm: () => {
            const currentPass = document.getElementById("currentPass").value;
            const newPass = document.getElementById("newPass").value;

            if (!currentPass || !newPass)
                return Swal.showValidationMessage("Isi semua field!");
            if (newPass.length < 6)
                return Swal.showValidationMessage("Minimal 6 karakter!");

            return { currentPass, newPass };
        }
    });

    if (!formValues) return;

    let formData = new FormData();
    formData.append("currentPass", formValues.currentPass);
    formData.append("newPass", formValues.newPass);

    fetch("../PHP/UpdatePassword.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(response => {
            if (response === "WRONG_PASSWORD") {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Password lama salah!",
                    confirmButtonColor: "var(--Text4)"
                });
            } else if (response === "SUCCESS") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    color: "var(--Text1)",
                    text: "Password diperbarui!",
                    confirmButtonColor: "var(--Text4)"
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    color: "var(--Text1)",
                    text: "Terjadi kesalahan!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
        });
}

// Admin Region

async function EmailAdmin() {
    const { value: newEmail } = await Swal.fire({
        title: "Ubah Email",
        color: "#000000",
        input: "email",
        inputPlaceholder: "Masukkan Email Baru",
        showCancelButton: true,
        confirmButtonText: "Kirim Kode",
        confirmButtonColor: "var(--Text4)",
        inputValidator: (value) => {
            if (!value) return "Masukkan email!";
        }
    });

    if (!newEmail) return;

    let fd = new FormData();
    fd.append("email", newEmail);

    fetch("../PHP/SendEmailOTPAdmin.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(res => {
        if (res === "OTP_SENT") {
            verifyOTPAdmin();
        } else {
            Swal.fire({
                title: "Gagal", 
                text: "Gagal mengirim OTP", 
                icon: "error",
                iconColor: "red",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            });
        }
    });
}

async function verifyOTPAdmin() {
    const { value: otp } = await Swal.fire({
        title: "Verifikasi Email",
        color: "#000000",
        input: "text",
        inputPlaceholder: "Masukkan kode OTP",
        showCancelButton: true,
        confirmButtonText: "Verifikasi",
        confirmButtonColor: "var(--Text4)"
    });

    if (!otp) return;

    let fd = new FormData();
    fd.append("otp", otp);

    fetch("../PHP/VerifyEmailOTPAdmin.php", {
        method: "POST",
        body: fd
    })
    .then(res => res.text())
    .then(res => {
        if (res === "SUCCESS") {
            Swal.fire({
                title: "Berhasil", 
                text: "Email berhasil diperbarui", 
                icon: "success",
                iconColor: "var(--Text4)",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            })
                .then(() => location.reload());
        } else {
            Swal.fire({
                title: "Gagal", 
                text: "OTP salah / kadaluarsa", 
                icon: "error",
                iconColor: "red",
                color: "#000000",
                confirmButtonColor: "var(--Text4)"
            });
        }
    });
}

async function PhoneAdmin() {
    const { value: phone } = await Swal.fire({
        title: "Ubah Nomor Telepon",
        color: "var(--Text1)",
        input: "text",
        inputPlaceholder: "Masukkan nomor (contoh: 0812xxxx atau +62812xxxx)",
        confirmButtonText: "Simpan",
        confirmButtonColor: "var(--Text4)",
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) return "Nomor telepon tidak boleh kosong!";
        }
    });

    if (!phone) return;

    let formData = new FormData();
    formData.append("phone", phone);

    fetch("../PHP/UpdatePhoneAdmin.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(result => {
            if (result === "SUCCESS") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    color: "var(--Text1)",
                    text: "Nomor telepon diperbarui!",
                    confirmButtonColor: "var(--Text4)"
                }).then(() => location.reload());
            }
            else if (result === "INVALID_PHONE") {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Format nomor tidak valid!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
            else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Gagal memperbarui nomor telepon!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
        });
}

async function PasswordAdmin() {
    const { value: formValues } = await Swal.fire({
        title: "Ubah Kata Sandi",
        color: "var(--Text1)",
        html: `
            <input id="currentPass" class="swal2-input swal-pass" placeholder="Password Saat Ini" type="password">
            <input id="newPass" class="swal2-input swal-pass" placeholder="Password Baru" type="password">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Simpan",
        confirmButtonColor: "var(--Text4)",
        cancelButtonText: "Batal",
        customClass: {
            popup: "swal-pass-popup"
        },
        preConfirm: () => {
            const currentPass = document.getElementById("currentPass").value;
            const newPass = document.getElementById("newPass").value;

            if (!currentPass || !newPass)
                return Swal.showValidationMessage("Isi semua field!");
            if (newPass.length < 6)
                return Swal.showValidationMessage("Minimal 6 karakter!");

            return { currentPass, newPass };
        }
    });

    if (!formValues) return;

    let formData = new FormData();
    formData.append("currentPass", formValues.currentPass);
    formData.append("newPass", formValues.newPass);

    fetch("../PHP/UpdatePasswordAdmin.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.text())
        .then(response => {
            if (response === "WRONG_PASSWORD") {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    color: "var(--Text1)",
                    text: "Password lama salah!",
                    confirmButtonColor: "var(--Text4)"
                });
            } else if (response === "SUCCESS") {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    color: "var(--Text1)",
                    text: "Password diperbarui!",
                    confirmButtonColor: "var(--Text4)"
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    color: "var(--Text1)",
                    text: "Terjadi kesalahan!",
                    confirmButtonColor: "var(--Text4)"
                });
            }
        });
}

function Keluar() {
    Swal.fire({
        title: "Apakah anda yakin ingin keluar?",
        icon: "warning",
        iconColor: "var(--Text4)",
        showCancelButton: true,
        confirmButtonText: "Ya",
        cancelButtonText: "Tidak",
        confirmButtonColor: "red",
        allowOutsideClick: false,
        color: "var(--Text1)"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: "success",
                title: "Berhasil Keluar!",
                showConfirmButton: false,
                timer: 1500,
                color: "var(--Text1)"
            }).then(() => {
                window.location.href = "../PHP/Keluar.php";
            });
        }
    });
}









