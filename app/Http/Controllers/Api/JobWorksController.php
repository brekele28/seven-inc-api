<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobWork;
use Illuminate\Http\Request;

class JobWorksController extends Controller
{
    // Menampilkan semua lowongan pekerjaan dengan pagination
    public function index(Request $request)
    {
        $page = $request->input('page', 1); // Mendapatkan nomor halaman yang diminta
        $jobs = JobWork::paginate(5); // Setiap halaman hanya 5 data

        return response()->json($jobs, 200); // Mengembalikan data dengan pagination
    }

    // Menampilkan lowongan pekerjaan berdasarkan ID
    public function show($id)
    {
        $job = JobWork::findOrFail($id);
        return response()->json($job, 200);
    }

    // Menambahkan lowongan pekerjaan baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'company' => 'required|string',
            'location' => 'required|string',
            'close_date' => 'required|date',
        ]);

        $job = JobWork::create($data);
        return response()->json($job, 201);
    }

    // Memperbarui lowongan pekerjaan
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'company' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'close_date' => 'sometimes|required|date',
        ]);

        $job = JobWork::findOrFail($id);
        $job->update($data);
        return response()->json($job, 200);
    }

    // Menghapus lowongan pekerjaan
    public function destroy($id)
    {
        $job = JobWork::findOrFail($id);
        $job->delete();
        return response()->json(null, 204);
    }
}