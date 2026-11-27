// Menambahkan tanda * pada label setiap isian yang wajib diisi (atribut "required").
(function () {
    function labelUntuk(isian) {
        const kelompok = isian.type === 'radio';

        // Radio: bintang pada judul kelompoknya, bukan pada tiap pilihan.
        if (!kelompok && isian.labels && isian.labels.length) return isian.labels[0];

        let wadah = isian.parentElement;
        for (let i = 0; wadah && wadah.tagName !== 'FORM' && i < 4; i++, wadah = wadah.parentElement) {
            const judul = wadah.querySelector(kelompok
                ? ':scope > legend, :scope > .form-label'
                : ':scope > label, :scope > .form-label');
            if (judul) return judul;
        }

        return null;
    }

    function tandai() {
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(function (isian) {
            if (isian.type === 'hidden') return;

            const label = labelUntuk(isian);
            // Lewati bila sudah bertanda, termasuk label yang bintangnya ditulis langsung di halaman.
            if (!label || label.textContent.includes('*')) return;

            const bintang = document.createElement('span');
            bintang.className = 'mn-wajib';
            bintang.title = 'Wajib diisi';
            bintang.textContent = ' *';

            // Label yang membungkus isiannya (mis. persetujuan): bintang mengikuti teksnya.
            const teks = label.contains(isian) ? label.querySelector('span') : null;
            (teks || label).appendChild(bintang);
        });
    }

    tandai();
    // Sebagian isian baru muncul atau baru menjadi wajib setelah pengguna memilih sesuatu.
    new MutationObserver(tandai).observe(document.body, {
        childList: true, subtree: true, attributes: true, attributeFilter: ['required'],
    });
})();
