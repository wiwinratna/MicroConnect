<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('umkm');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->get();

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['umkm', 'messages.user']);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
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

        // Auto move 'Open' to 'In Progress' when Admin replies
        if ($ticket->status === 'Open') {
            $ticket->update(['status' => 'In Progress']);
        } else {
            // Touch to bump thread ordering
            $ticket->touch();
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved'
        ]);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Status tiket berhasil diperbarui menjadi ' . $request->status . '.');
    }
}
