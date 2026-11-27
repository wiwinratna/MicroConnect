// Tambah dan buang baris pilihan jawaban (minimal 2, maksimal 10).
(function () {
    const daftar = document.getElementById('daftarPilihan');
    const templat = document.getElementById('templatPilihan');
    const tambah = document.getElementById('tambahPilihan');
    let berikutnya = Number(daftar.dataset.berikutnya);

    function atur() {
        const jumlah = daftar.querySelectorAll('.baris-pilihan').length;
        tambah.disabled = jumlah >= 10;
        daftar.querySelectorAll('.hapus-pilihan').forEach(function (b) { b.disabled = jumlah <= 2; });
    }

    tambah.addEventListener('click', function () {
        daftar.insertAdjacentHTML('beforeend', templat.innerHTML.replaceAll('__i__', String(berikutnya++)));
        atur();
    });

    daftar.addEventListener('click', function (event) {
        const tombol = event.target.closest('.hapus-pilihan');
        if (!tombol) return;
        tombol.closest('.baris-pilihan').remove();
        atur();
    });

    atur();
})();
