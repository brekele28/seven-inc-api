<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Logo;

class LogoController extends Controller
{
    // GET /api/admin/logo  -> ambil logo navbar terbaru
    public function show()
    {
        $logo = Logo::where('type','navbar')->latest()->first();

        return response()->json([
            'status' => true,
            'data' => $logo ? [
                'id'  => $logo->id,
                'url' => asset('storage/'.$logo->path),
                'path'=> $logo->path,
            ] : null
        ]);
    }

    // POST /api/admin/logo  -> upload & ganti
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // hapus file lama (optional)
        $old = Logo::where('type','navbar')->latest()->first();
        if ($old && Storage::disk('public')->exists($old->path)) {
            Storage::disk('public')->delete($old->path);
        }

        $path = $request->file('image')->store('logos', 'public');

        $logo = Logo::create([
            'path' => $path,
            'type' => 'navbar'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Logo navbar diperbarui',
            'data' => [
                'id'  => $logo->id,
                'url' => asset('storage/'.$logo->path),
            ]
        ], 201);
    }
}