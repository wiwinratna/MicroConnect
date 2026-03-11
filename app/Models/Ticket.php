<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public static function generateKode()
    {
        $prefix = 'TKT-' . date('Ym') . '-';
        $last = self::where('kode_ticket', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        
        if (!$last) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($last->kode_ticket, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    public function umkm()
    {
        return $this->belongsTo(Umkm::class, 'umkm_id');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class, 'ticket_id');
    }
}
