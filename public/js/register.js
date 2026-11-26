// Two-step registration form. Needs filter-kategori.js to be loaded first.
(function () {
	const form = document.getElementById('formDaftar');
	const l1 = document.getElementById('langkah1'), l2 = document.getElementById('langkah2');
	const saringKategori = buatSaringKategori(document.getElementById('jenis_usaha_id'));
	const kotak = {
		barang: document.getElementById('kotakBarang'),
		jasa: document.getElementById('kotakJasa'),
	};

	// Tampilkan hanya kotak yang relevan; yang tersembunyi dinonaktifkan agar tidak ikut terkirim.
	function atur(box, tampil) {
		box.hidden = !tampil;
		box.querySelectorAll('input').forEach(function (el) {
			el.disabled = !tampil;
			el.required = tampil && el.type === 'radio' && el === box.querySelector('input');
		});
	}

	function segarkan() {
		const dipilih = form.querySelector('input[name="rumpun"]:checked');
		const jenis = dipilih ? dipilih.value : '';

		saringKategori(jenis);
		atur(kotak.barang, jenis === 'barang');
		atur(kotak.jasa, jenis === 'jasa');
	}

	form.querySelectorAll('input[name="rumpun"]').forEach(function (r) { r.addEventListener('change', segarkan); });
	segarkan();

	function tampilkanLangkah(nomor) {
		l1.hidden = nomor !== 1;
		l2.hidden = nomor !== 2;
		[1, 2].forEach(function (n) {
			const tanda = document.getElementById('tanda' + n);
			if (n === nomor) { tanda.setAttribute('aria-current', 'step'); }
			else { tanda.removeAttribute('aria-current'); }
		});
		window.scrollTo(0, 0);
	}

	document.getElementById('btnLanjut').addEventListener('click', function () {
		const isian = l1.querySelectorAll('input, select, textarea');
		for (const el of isian) {
			if (!el.disabled && !el.reportValidity()) { return; }
		}
		tampilkanLangkah(2);
	});

	document.getElementById('btnKembali').addEventListener('click', function () {
		tampilkanLangkah(1);
	});

	// Cegah klik ganda: tombol dikunci setelah formulir lolos pemeriksaan browser.
	form.addEventListener('submit', function () {
		const t = document.getElementById('btnKirim');
		t.disabled = true; t.textContent = 'Mengirim...';
	});
})();
