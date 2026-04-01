@extends('layouts.umkm')

@section('title', 'Pilih Level UMKM')

@section('content')
    <h1 class="h3 mb-2 text-center"><strong>Pilih Level UMKM</strong></h1>
    <p class="text-muted mb-4 text-center">
        Pilih paket fitur yang paling sesuai untuk usahamu.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger text-center">
            {{ $errors->first() }}
        </div>
    @endif

    <form id="levelForm" method="POST" action="{{ route('umkm.level.store') }}">
        @csrf

        <div class="row g-4 justify-content-center">
            @foreach ($levels as $level)
                {{-- d-flex + align-items-stretch supaya semua kolom punya tinggi sama --}}
                <div class="col-md-4 col-sm-6 d-flex align-items-stretch">
                    <label class="level-card-container">

                        {{-- RADIO (hidden) --}}
                        <input type="radio"
                               name="level_id"
                               value="{{ $level->id }}"
                               class="level-radio"
                               hidden>

                        {{-- CARD --}}
                        <div class="level-card">
                            <div class="level-icon mb-3">
                                @if($loop->iteration == 1)
                                    📦
                                @elseif($loop->iteration == 2)
                                    🚀
                                @else
                                    👑
                                @endif
                            </div>

                            <h4 class="fw-bold">{{ $level->nama_level }}</h4>

                            <p class="small text-muted mb-3">{{ $level->deskripsi }}</p>

                            <div class="price-box mb-3">
                                @if($level->iuran_bulanan > 0)
                                    <span class="price">
                                        {{ rupiah($level->iuran_bulanan) }}
                                    </span>
                                    <span class="price-sub text-muted">/bulan</span>
                                @else
                                    <span class="price">Gratis</span>
                                @endif
                            </div>

                            <ul class="features text-start mb-3">
                                @foreach ($level->fitur as $f)
                                    <li>✔ {{ ucwords(str_replace('_', ' ', $f)) }}</li>
                                @endforeach
                            </ul>

                            <button type="button" class="select-btn">
                                Pilih Level Ini
                            </button>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
    </form>

    @push('styles')
    <style>
        .level-card-container {
            width: 100%;
            max-width: 330px;
            cursor: pointer;
            display: flex;          /* penting: biar card bisa di-stretch */
        }

        .level-card {
            flex: 1;                /* penuh tinggi parent */
            border: 2px solid transparent;
            border-radius: 22px;
            padding: 24px;
            text-align: center;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
            transition: .2s;
            display: flex;
            flex-direction: column; /* susun vertikal */
        }

        .level-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.12);
        }

        .level-icon {
            font-size: 42px;
        }

        .price {
            font-size: 28px;
            font-weight: 700;
        }

        .price-sub {
            font-size: 14px;
        }

        .features {
            list-style: none;
            padding: 0;
            font-size: 14px;
            color: #374151;
            flex-grow: 1;          /* fitur boleh memanjang, tapi card tetap sama tinggi */
        }

        .features li {
            margin-bottom: 4px;
        }

        .select-btn {
            width: 100%;
            border-radius: 14px;
            padding: 10px 0;
            border: 1px solid #3b82f6;
            background-color: #3b82f6;
            color: #fff;
            font-weight: 600;
            transition: .15s;
            margin-top: auto;      /* dorong tombol ke paling bawah card */
        }

        .select-btn:hover {
            background-color: #2563eb;
        }

        .level-card.selected {
            border-color: #2563eb;
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.25);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.querySelectorAll('.level-card-container').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.level-card').forEach(c => c.classList.remove('selected'));

                let levelCard = this.querySelector('.level-card');
                levelCard.classList.add('selected');

                let radio = this.querySelector('.level-radio');
                radio.checked = true;

                setTimeout(() => {
                    document.getElementById('levelForm').submit();
                }, 400);
            });
        });
    </script>
    @endpush

@endsection
