// Dropdown "Kategori usaha" mengikuti "Jenis usaha" (barang/jasa).
// Opsi pertama adalah teks bantu; opsi lain memakai data-untuk: barang, jasa, atau ditentukan_umkm.
function buatSaringKategori(select) {
	const semua = Array.from(select.options).slice(1);

	return function (jenis) {
		const cocok = semua.filter(function (o) {
			return jenis !== '' && (o.dataset.untuk === jenis || o.dataset.untuk === 'ditentukan_umkm');
		});
		const nilai = select.value;

		select.length = 1;
		cocok.forEach(function (o) { select.add(o); });
		select.value = cocok.some(function (o) { return o.value === nilai; }) ? nilai : '';
		select.disabled = jenis === '';
	};
}
