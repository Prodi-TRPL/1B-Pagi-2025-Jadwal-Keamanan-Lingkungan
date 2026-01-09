const myForm = document.getElementById('Form-Laporan')
function Kirim(event) {
  event.preventDefault();
  Swal.fire({
    icon: 'success',
    title: "Berhasil",
    text: "Laporan berhasil dikirim!",
    showConfirmButton: false,
    color: "var(--Text1)",
    allowOutsideClick: false,
    timer: 2000
  }).then(() => {
    myForm.reset();
    myForm.submit();
  })
}