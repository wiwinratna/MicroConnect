@extends('layouts.umkm')
@section('title', 'Detail Tiket: ' . $ticket->kode_ticket)

@push('styles')
<style>
    /* ── Chat Container ── */
    .chat-container { border-radius: 16px; min-height: 400px; display: flex; flex-direction: column; background: #fff; }
    .chat-body { flex: 1; padding: 1.5rem; background: #fafbfc; overflow-y: auto; }
    
    /* ── Chat Bubbles ── */
    .bubble { max-width: 80%; padding: 1rem 1.25rem; border-radius: 18px; position: relative; font-size: 0.9rem; line-height: 1.5; margin-bottom: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    
    /* Admin Bubble (Left) - KADIN */
    .bubble-admin { background: #ffffff; border: 1px solid #e2e8f0; color: #1e293b; border-bottom-left-radius: 4px; align-self: flex-start; }
    
    /* User Bubble (Right) - UMKM */
    .bubble-user { background: #2563eb; color: #ffffff; align-self: flex-end; border-bottom-right-radius: 4px; }
    
    /* Meta info below/above bubbles */
    .chat-meta { font-size: 0.7rem; color: #94a3b8; margin-bottom: 1.25rem; }
    .meta-user { text-align: right; }
    .meta-admin { text-align: left; }

    /* ── Avatar Circles ── */
    .avatar-sm { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; background: #e2e8f0; color: #475569; }
    .avatar-admin { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .avatar-user { background: #dbeafe; color: #2563eb; border: 1px solid #bfdbfe; }

    /* ── Reply Form ── */
    .reply-footer { padding: 1.25rem 1.5rem; background: #fff; border-top: 1px solid #f1f5f9; }
    .form-control-chat { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.75rem 1rem; resize: none; font-size: 0.9rem; transition: border-color 0.2s; }
    .form-control-chat:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08); outline: none; }
    
    /* ── Header Metadata ── */
    .ticket-header-pill { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 30px; padding: 4px 12px; font-size: 0.75rem; color: #64748b; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <a href="{{ route('umkm.tickets.index') }}" class="text-decoration-none text-muted small fw-semibold d-flex align-items-center gap-1 mb-2">
        <i data-feather="chevron-left" style="width:14px; height:14px;"></i> Kembali ke Daftar Tiket
    </a>
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="h3 mb-1"><strong>Tiket {{ $ticket->kode_ticket }}</strong></h1>
            <div class="d-flex gap-2 align-items-center">
                <span class="ticket-header-pill">{{ $ticket->kategori }}</span>
                <span class="text-muted small">• Terakhir aktif: {{ $ticket->messages->last()->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="text-end">
            @if($ticket->status === 'Open')
                <span class="badge bg-warning text-dark px-3 py-2" style="border-radius:30px; font-size:0.75rem;">Status: Open</span>
            @elseif($ticket->status === 'In Progress')
                <span class="badge bg-info text-dark px-3 py-2" style="border-radius:30px; font-size:0.75rem;">Status: In Progress</span>
            @else
                <span class="badge bg-success px-3 py-2" style="border-radius:30px; font-size:0.75rem;">Status: Resolved</span>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        {{-- Card Container --}}
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:20px;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center gap-2">
                <i data-feather="message-square" class="text-primary" style="width:18px; height:18px;"></i>
                <h5 class="fw-bold mb-0" style="font-size:0.95rem;">{{ $ticket->judul }}</h5>
            </div>

            <div class="chat-container">
                <div class="chat-body d-flex flex-column" id="chat-body">
                    
                    @foreach($ticket->messages as $msg)
                        @php
                            $isAdmin = $msg->user->user_group === 'admin';
                        @endphp
                        
                        {{-- Message Block --}}
                        <div class="d-flex flex-column {{ $isAdmin ? 'align-items-start' : 'align-items-end' }}">
                            <div class="d-flex gap-2 {{ $isAdmin ? 'flex-row' : 'flex-row-reverse' }} align-items-end">
                                {{-- Avatar Icon --}}
                                <div class="avatar-sm {{ $isAdmin ? 'avatar-admin' : 'avatar-user' }} mb-1 shadow-sm">
                                    <i data-feather="{{ $isAdmin ? 'shield' : 'user' }}" style="width:14px; height:14px;"></i>
                                </div>
                                {{-- Actual Bubble --}}
                                <div class="bubble {{ $isAdmin ? 'bubble-admin' : 'bubble-user shadow-blue' }}">
                                    <div style="white-space: pre-wrap;">{{ $msg->message }}</div>
                                </div>
                            </div>
                            {{-- Meta data --}}
                            <div class="chat-meta mt-1 {{ $isAdmin ? 'meta-admin ps-5' : 'meta-user pe-5' }}">
                                <span class="fw-bold">{{ $isAdmin ? 'Tim KADIN' : 'Anda' }}</span> • {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                            </div>
                        </div>
                    @endforeach

                </div>

                {{-- Footer / Reply Box --}}
                @if($ticket->status !== 'Resolved')
                    <div class="reply-footer">
                        <form action="{{ route('umkm.tickets.reply', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" class="form-control form-control-chat" rows="3" placeholder="Ketik pesan atau balasan Anda di sini..." required></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i data-feather="lock" style="width:12px; height:12px; margin-right:3px; margin-bottom:2px;"></i> Pesan ini terenkripsi dan pribadi.
                                </div>
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="border-radius:10px;">
                                    Kirim Balasan <i data-feather="send" style="width:14px; height:14px;"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="reply-footer bg-light text-center py-4">
                        <div class="d-inline-flex align-items-center gap-2 text-success fw-bold p-2 bg-success bg-opacity-10 rounded-pill px-4">
                            <i data-feather="check-circle" style="width:16px; height:16px;"></i> Tiket ini telah diselesaikan (Resolved)
                        </div>
                        <p class="text-muted mt-2 mb-0 x-small">Riwayat percakapan tetap tersimpan untuk referensi di masa depan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof feather !== 'undefined') feather.replace();
        
        // Auto scroll to bottom of chat
        const chatBody = document.getElementById('chat-body');
        chatBody.scrollTop = chatBody.scrollHeight;
    })
</script>
<style>
    .shadow-blue { box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2) !important; }
    .ps-5 { padding-left: 2.75rem !important; }
    .pe-5 { padding-right: 2.75rem !important; }
</style>
@endpush
