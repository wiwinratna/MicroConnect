@extends('layouts.etalase')

@section('title', 'Katalog Produk')

{{-- Inject Search Bar to Navbar Center --}}
@section('navbar_center')
<div class="position-relative w-100" style="max-width: 480px;">
    <i data-feather="search" class="position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px;"></i>
    <input type="text" id="searchInput" class="form-control rounded-pill border-0 bg-light w-100" 
           style="padding-left: 44px; padding-right: 16px; height: 44px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);" 
           placeholder="Cari nama produk...">
</div>
@endsection

@section('content')

<div class="row g-4 position-relative">
    
    {{-- KOLOM KIRI: KATALOG PRODUK --}}
    <div class="col-xl-9 col-lg-8">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 text-dark">Katalog Produk</h4>
            <span class="badge bg-white text-dark border shadow-sm px-3 py-2 rounded-pill">{{ $produk->count() }} Jenis Produk</span>
        </div>

        
        

        {{-- Grid Produk --}}
        <div class="row g-4" id="productGrid">
            @forelse($produk as $p)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 product-item" data-name="{{ strtolower($p->nama_produk) }}">
                    <div class="card h-100 product-card shadow-sm border-0 d-flex flex-column" onclick="addToCart({{ $p->id }}, '{{ addslashes($p->nama_produk) }}', {{ $p->harga_jual }}, '{{ $p->foto_path ? asset('storage/'.$p->foto_path) : '' }}')">
                        <div class="product-img-wrapper position-relative" style="background-color: var(--kasir-primary-light);">
                            @if($p->foto_path)
                                <img src="{{ asset('storage/' . $p->foto_path) }}" alt="{{ $p->nama_produk }}" class="product-img">
                            @else
                                <div class="fallback-img fw-bolder" style="color: var(--kasir-primary);">
                                    {{ strtoupper(substr($p->nama_produk, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-3 d-flex flex-column flex-grow-1">
                            <h6 class="product-title mb-2 fw-bold text-dark fs-5">{{ $p->nama_produk }}</h6>
                            <div class="mt-auto">
                                <div class="product-price text-theme fw-bolder mb-3 fs-5">{{ rupiah($p->harga_jual) }}</div>
                                <button type="button" class="btn btn-theme w-100 fw-bold d-flex justify-content-center align-items-center gap-2 py-2 fs-6">
                                    <i data-feather="shopping-cart" style="width: 18px; height: 18px;"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="bg-white p-5 rounded-4 shadow-sm border text-muted">
                        <i data-feather="box" style="width:64px;height:64px; opacity:0.3;" class="mb-3"></i>
                        <h5 class="fw-bold text-dark">Katalog Kosong</h5>
                        <p class="mb-0">Tidak ditemukan produk aktif dengan resep bahan baku.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- KOLOM KANAN: KERANJANG BELANJA --}}
    <div class="col-xl-3 col-lg-4">
        <div class="cart-container bg-white shadow-sm rounded-4 border p-0 position-sticky d-flex flex-column" 
             style="top: 88px; height: calc(100vh - 110px); overflow: hidden;">
            
            {{-- Cart Header --}}
            <div class="p-3 border-bottom bg-light bg-opacity-50">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i data-feather="shopping-bag" class="text-theme" style="width:18px;"></i> Keranjang
                    </h6>
                    <span class="badge bg-theme rounded-pill" id="cartCountBadge">0</span>
                </div>
            </div>

            {{-- Cart Items --}}
            <div id="emptyCartMsg" class="text-center text-muted flex-grow-1 d-flex flex-column align-items-center justify-content-center p-4">
                <div class="bg-light rounded-circle p-4 mb-3">
                    <i data-feather="shopping-cart" style="width:32px;height:32px;opacity:0.4;"></i>
                </div>
                <h6 class="fw-semibold text-dark">Keranjang Kosong</h6>
                <p class="small mb-0">Klik produk untuk mulai transaksi.</p>
            </div>

            <div class="cart-items flex-grow-1 overflow-auto p-2 d-none" id="cartItems">
                <!-- Data di-inject via JS -->
            </div>

            {{-- Cart Footer / Checkout Form --}}
            <div class="cart-summary p-3 border-top bg-white">
                <div class="d-flex justify-content-between mb-1 text-muted small fw-semibold">
                    <span>Subtotal</span>
                    <span id="subtotalText">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bolder text-dark">Total</h6>
                    <h5 class="mb-0 text-theme fw-bolder" id="totalText">Rp 0</h5>
                </div>

                <form action="{{ route('umkm.etalase.checkout') }}" method="POST" id="checkoutForm">
                    @csrf
                    <input type="hidden" name="items" id="itemsPayload">
                    
                    <div class="input-group mb-2 bg-light rounded-3 p-1 border">
                        <span class="input-group-text border-0 bg-transparent text-muted px-2 py-1"><i data-feather="user" style="width:14px;"></i></span>
                        <input type="text" name="pembeli" class="form-control form-control-sm border-0 bg-transparent fw-semibold px-1" placeholder="Nama Pembeli (Opsional)">
                    </div>
                    
                    <div class="mb-2 position-relative">
                        <label class="form-label text-muted" style="font-size:0.7rem; font-weight:700; margin-bottom:4px;">TUNAI / UANG DIBAYAR</label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-white fw-bold text-muted border-end-0 py-1">Rp</span>
                            <input type="text" id="uangDibayarDisplay" 
                                   class="form-control border-start-0 fw-bolder text-end fs-5 py-1" 
                                   placeholder="0" style="color: var(--kasir-primary);" required inputmode="numeric">
                            <input type="hidden" name="uang_dibayar" id="uangDibayar">
                        </div>
                        <div class="mt-1 text-danger fw-semibold d-none" style="font-size:0.75rem;" id="uangError"><i data-feather="alert-circle" style="width:12px; margin-top:-2px;"></i> Uang kurang!</div>
                        <div class="mt-2 py-1 px-2 bg-success bg-opacity-10 text-success fw-bold rounded-3 d-none d-flex justify-content-between" style="font-size:0.85rem;" id="kembaliText">
                            <span>Kembali:</span> <span id="kembaliVal">Rp 0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-theme w-100 shadow-sm d-flex align-items-center justify-content-center gap-2 mt-3 p-2 fw-bold" id="btnCheckout" disabled>
                        <span>BAYAR SEKARANG</span> <i data-feather="check-circle" style="width:16px;"></i>
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Card Styles */
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        border-radius: 12px;
        overflow: hidden;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
        border-color: var(--kasir-primary-light) !important;
    }
    .product-img-wrapper {
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }
    .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .product-card:hover .product-img {
        transform: scale(1.05);
    }
    .fallback-img {
        font-size: 4rem;
        opacity: 0.8;
    }
    .product-title {
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Cart Items Styling */
    .cart-item-row {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .cart-item-img {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background-color: var(--kasir-primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--kasir-primary);
        font-weight: bold;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .qty-control {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    .qty-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: none;
        background: #fff;
        color: var(--kasir-primary);
        font-weight: 800;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background 0.1s;
    }
    .qty-btn:hover { background: var(--kasir-primary-light); }
    .qty-val {
        width: 32px;
        text-align: center;
        font-weight: bold;
        font-size: 0.95rem;
        background: #f8f9fa;
        color: #333;
    }

    /* Input Uang Hide arrows */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush

@push('scripts')
<script>
    // State Keranjang
    let cart = {}; // { id: { name, price, qty, img } }
    let total = 0;

    // Format Rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    }

    // Tambah ke keranjang via klik produk
    function addToCart(id, name, price, imgPath) {
        if (cart[id]) {
            cart[id].qty++;
        } else {
            let initials = name.substring(0, 2).toUpperCase();
            cart[id] = { name: name, price: price, qty: 1, img: imgPath || initials, isImg: !!imgPath };
        }
        renderCart();
        
        // Visual feedback (bounce the cart icon slightly)
        const badge = document.getElementById('cartCountBadge');
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => { badge.style.transform = 'scale(1)'; }, 200);
    }

    // Ubah kuantitas (tombol +/-)
    function updateQty(id, change) {
        if (!cart[id]) return;
        cart[id].qty += change;
        if (cart[id].qty <= 0) {
            delete cart[id]; 
        }
        renderCart();
    }

    // Render ulang list keranjang di sidebar
    function renderCart() {
        const cartItemsDiv = document.getElementById('cartItems');
        const emptyMsg = document.getElementById('emptyCartMsg');
        const btnCheckout = document.getElementById('btnCheckout');
        const itemsPayload = document.getElementById('itemsPayload');
        
        cartItemsDiv.innerHTML = '';
        total = 0;
        let count = 0;
        let itemsArray = [];

        const keys = Object.keys(cart);
        if (keys.length === 0) {
            emptyMsg.classList.remove('d-none');
            cartItemsDiv.classList.add('d-none');
            btnCheckout.disabled = true;
            document.getElementById('uangDibayar').required = false;
        } else {
            emptyMsg.classList.add('d-none');
            cartItemsDiv.classList.remove('d-none');
            btnCheckout.disabled = false;
            document.getElementById('uangDibayar').required = true;

            keys.forEach(id => {
                const item = cart[id];
                const sub = item.price * item.qty;
                total += sub;
                count += item.qty;

                itemsArray.push({ id: id, qty: item.qty });

                let mediaHTML = item.isImg 
                    ? `<img src="${item.img}" class="cart-item-img">` 
                    : `<div class="cart-item-img">${item.img}</div>`;

                const div = document.createElement('div');
                div.className = 'cart-item-row flex-column align-items-stretch';
                div.innerHTML = `
                    <div class="d-flex w-100 gap-2">
                        ${mediaHTML}
                        <div class="flex-grow-1 min-w-0 d-flex flex-column justify-content-center">
                            <div class="fw-bold text-dark lh-sm text-truncate" style="font-size: 0.9rem;" title="${item.name}">${item.name}</div>
                            <div class="text-muted mt-1 fw-semibold" style="font-size: 0.8rem;">${formatRupiah(item.price)}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 w-100">
                        <div class="qty-control shadow-sm">
                            <button type="button" class="qty-btn" onclick="updateQty(${id}, -1)">-</button>
                            <span class="qty-val">${item.qty}</span>
                            <button type="button" class="qty-btn" onclick="updateQty(${id}, 1)">+</button>
                        </div>
                        <div class="fw-bolder text-theme" style="font-size: 0.95rem;">${formatRupiah(sub)}</div>
                    </div>
                `;
                cartItemsDiv.appendChild(div);
            });
        }

        // Update Text & Badge
        const badge = document.getElementById('cartCountBadge');
        badge.innerText = count;
        
        const totalFormatted = formatRupiah(total);
        document.getElementById('subtotalText').innerText = totalFormatted;
        document.getElementById('totalText').innerText = totalFormatted;
        
        // Update hidden JSON input
        itemsPayload.value = JSON.stringify(itemsArray);

        // Recheck uang
        recalcKembalian();
    }

    // Search functionality live filter
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const keyword = e.target.value.toLowerCase();
        const items = document.querySelectorAll('.product-item');
        let countVisible = 0;
        
        items.forEach(el => {
            if (el.dataset.name.includes(keyword)) {
                el.style.display = 'block';
                countVisible++;
            } else {
                el.style.display = 'none';
            }
        });
    });

    // Validasi Uang Dibayar > Total
    const inputUangDisplay = document.getElementById('uangDibayarDisplay');
    const inputUangHidden = document.getElementById('uangDibayar');
    const errUang = document.getElementById('uangError');
    const txtKembaliContainer = document.getElementById('kembaliText');
    const txtKembaliVal = document.getElementById('kembaliVal');
    const formCheck = document.getElementById('checkoutForm');

    function recalcKembalian() {
        if(total === 0) {
            errUang.classList.add('d-none');
            txtKembaliContainer.classList.add('d-none');
            txtKembaliContainer.classList.remove('d-flex');
            return;
        }
        
        const bayar = parseFloat(inputUangHidden.value) || 0;
        if (bayar >= total) {
            errUang.classList.add('d-none');
            txtKembaliContainer.classList.remove('d-none');
            txtKembaliContainer.classList.add('d-flex');
            txtKembaliVal.innerText = formatRupiah(bayar - total);
        } else {
            txtKembaliContainer.classList.add('d-none');
            txtKembaliContainer.classList.remove('d-flex');
            if (inputUangHidden.value !== '') {
                errUang.classList.remove('d-none');
            }
        }
    }

    inputUangDisplay.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        inputUangHidden.value = raw;
        const num = parseInt(raw) || 0;
        this.value = num > 0 ? num.toLocaleString('id-ID') : '';
        recalcKembalian();
    });

    formCheck.addEventListener('submit', function(e) {
        const bayar = parseFloat(inputUangHidden.value) || 0;
        if (bayar < total) {
            e.preventDefault();
            alert('Uang dibayar (Rp ' + bayar + ') tidak mencukupi total belanja (Rp ' + total + ')!');
            inputUangDisplay.focus();
        }
    });

    // Prevent enter on search input from submitting any form
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
        }
    });
</script>
@endpush
