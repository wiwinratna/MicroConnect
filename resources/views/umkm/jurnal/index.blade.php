@extends('layouts.umkm')
@section('title', 'Jurnal Umum')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-1"><strong>Jurnal</strong> Umum</h1>
      <p class="text-muted mb-0">Riwayat pencatatan akuntansi seluruh transaksi sistem dan jurnal manual.</p>
    </div>
    <a href="{{ route('umkm.jurnal.create') }}" class="btn btn-primary">+ Jurnal Manual</a>
  </div>



  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size: 0.88rem;">
          <thead class="table-light text-uppercase text-secondary" style="letter-spacing:0.4px;">
            <tr>
              <th class="ps-3" style="width:110px;">Tanggal</th>
              <th style="min-width:200px;">Keterangan / Referensi</th>
              <th style="min-width:120px;">Kode Akun</th>
              <th>Nama Akun</th>
              <th class="text-end" style="width:150px;">Debit (Rp)</th>
              <th class="text-end" style="width:150px;">Kredit (Rp)</th>
            </tr>
          </thead>
          <tbody>
            @php
              // Group jurnal by tanggal+keterangan+ref_tipe for visual grouping
              $grouped = $jurnal->groupBy(function ($j) {
                return $j->tanggal . '|' . $j->keterangan . '|' . $j->ref_id;
              });
              $rowNum = 0;
            @endphp
            @forelse($grouped as $key => $items)
              @php
                $totalDebit = $items->sum('debit');
                $totalKredit = $items->sum('kredit');
                $isBalanced = abs($totalDebit - $totalKredit) < 0.01;
                $groupRows = $items->count();
                $firstItem = $items->first();

                // Sort: debit entries first (non-zero debit), then kredit entries
                $sorted = $items->sortByDesc(fn($j) => $j->debit > 0 ? 1 : 0)->values();
              @endphp
              @foreach($sorted as $idx => $j)
                <tr class="{{ $idx > 0 ? 'bg-light bg-opacity-50' : 'border-top border-2' }}"
                  style="{{ $idx === 0 ? 'border-top-color: #dee2e6 !important;' : '' }}">
                  @if($idx === 0)
                    <td class="ps-3 text-muted fw-medium" rowspan="{{ $groupRows }}"
                      style="vertical-align: top; padding-top: 12px;">
                      {{ \Carbon\Carbon::parse($j->tanggal)->isoFormat('D MMM YY') }}
                    </td>
                    <td rowspan="{{ $groupRows }}" style="vertical-align: top; padding-top: 12px;">
                      <div class="fw-semibold text-dark" style="line-height: 1.3;">
                        {{ $j->keterangan }}
                      </div>
                      <div class="mt-1">
                        @if(!$isBalanced)
                          <span class="badge bg-warning text-dark" title="Debit ≠ Kredit - cek data ini">⚠ Tidak Balance</span>
                        @endif
                      </div>
                    </td>
                  @endif
                  <td class="font-monospace text-secondary small">{{ $j->kode_akun }}</td>
                  <td>
                    {{ $j->nama_akun }}
                  </td>
                  <td class="text-end fw-medium {{ $j->debit > 0 ? 'text-success' : 'text-muted' }}">
                    {{ $j->debit > 0 ? format_angka($j->debit) : '—' }}
                  </td>
                  <td class="text-end fw-medium {{ $j->kredit > 0 ? 'text-danger' : 'text-muted' }}">
                    {{ $j->kredit > 0 ? format_angka($j->kredit) : '—' }}
                  </td>
                </tr>
              @endforeach
              {{-- Subtotal per transaksi dihapus sesuai permintaan --}}
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-5">Belum ada catatan jurnal.</td>
              </tr>
            @endforelse
          </tbody>
          @if($jurnal->count() > 0)
            <tfoot class="table-light fw-bold">
              <tr class="border-top border-2 border-secondary border-opacity-25">
                <td colspan="4" class="text-end py-3 text-secondary text-uppercase"
                  style="letter-spacing: 0.5px; font-size: 0.8rem;">Total</td>
                <td class="text-end text-success py-3">{{ format_angka($jurnal->sum('debit')) }}</td>
                <td class="text-end text-danger py-3">{{ format_angka($jurnal->sum('kredit')) }}</td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>
    @if($jurnal->hasPages())
      <div class="card-footer bg-white border-top">
        {{ $jurnal->links() }}
      </div>
    @endif
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.toast').forEach(el => setTimeout(() => el.classList.remove('show'), 4000));
    });
  </script>
@endpush