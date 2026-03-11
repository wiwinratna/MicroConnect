<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;
        $tickets = Ticket::where('umkm_id', $umkm->id)->orderBy('updated_at', 'desc')->get();
        
        return view('umkm.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('umkm.tickets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string',
            'judul' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $umkm = auth()->user()->umkm;

        DB::transaction(function () use ($request, $umkm) {
            $ticket = Ticket::create([
                'kode_ticket' => Ticket::generateKode(),
                'umkm_id' => $umkm->id,
                'kategori' => $request->kategori,
                'judul' => $request->judul,
                'status' => 'Open',
            ]);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
            ]);
        });

        return redirect()->route('umkm.tickets.index')->with('success', 'Tiket pengaduan/konsultasi berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $umkm = auth()->user()->umkm;
        
        // Ensure UMKM only views their own ticket
        if ($ticket->umkm_id !== $umkm->id) {
            abort(403);
        }

        $ticket->load('messages.user');

        return view('umkm.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $umkm = auth()->user()->umkm;
        
        if ($ticket->umkm_id !== $umkm->id) {
            abort(403);
        }

        if ($ticket->status === 'Resolved') {
            return back()->with('error', 'Tiket sudah ditutup dan tidak dapat dibalas.');
        }

        $request->validate([
            'message' => 'required|string'
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Touch ticket updated_at so it bumps to top
        $ticket->touch();

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
}
