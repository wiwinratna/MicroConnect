<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UmkmController extends Controller
{
    public function index()
    {
        $umkm = DB::table('umkm')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.umkm.index', compact('umkm'));
    }

    public function create()
    {
        // sementara tampil form dummy dulu
        return view('admin.umkm.create');
    }

    public function show($id)
    {
        $umkm = DB::table('umkm')->where('id', $id)->first();
        abort_if(!$umkm, 404);

        return view('admin.umkm.show', compact('umkm'));
    }
}
