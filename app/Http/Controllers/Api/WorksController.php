<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class WorksController extends Controller
{
    private function serialize(Work $work): array
    {
        return [
            'id'                    => $work->id,
            'heading'               => $work->heading,
            'title'                 => $work->title,
            'subtitle'              => $work->subtitle,
            'hero_url'              => $work->hero ? asset('storage/'.$work->hero) : null,
            'job_position'          => $work->job_position,  // Menambahkan data posisi pekerjaan
            'career_growth_description' => $work->career_growth_description,  // Menambahkan deskripsi karir
        ];
    }

    // PUBLIC: dipakai fetch awal di admin & user
    public function latest()
    {
        $work = Work::latest('id')->first();
        if (!$work) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['data' => $this->serialize($work)], 200);
    }

    // ADMIN: create pertama kali
    public function store(Request $request)
    {
        $data = $request->validate([
            'heading'  => ['required', 'string', 'max:255'],
            'title'    => ['required', 'string'],
            'subtitle' => ['nullable', 'string'],
            'hero'     => [File::image()->max(4 * 1024)], // KB
            'job_position' => ['required', 'string', 'max:255'],  // Posisi pekerjaan wajib diisi
            'career_growth_description' => ['required', 'string'],  // Deskripsi karir wajib diisi
        ]);

        $path = $request->hasFile('hero')
            ? $request->file('hero')->store('works', 'public')
            : null;

        $work = Work::create([
            'heading'  => $data['heading'],
            'title'    => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'hero'     => $path,
            'job_position' => $data['job_position'],  // Menyimpan posisi pekerjaan
            'career_growth_description' => $data['career_growth_description'],  // Menyimpan deskripsi karir
        ]);

        return response()->json(['data' => $this->serialize($work)], 201);
    }

    // ADMIN: partial update + ganti/hapus gambar
    public function update(Request $request, Work $work)
    {
        $data = $request->validate([
            'heading'     => ['sometimes', 'string', 'max:255'],
            'title'       => ['sometimes', 'string'],
            'subtitle'    => ['sometimes', 'nullable', 'string'],
            'hero'        => ['sometimes', File::image()->max(4 * 1024)],
            'hero_reset'  => ['sometimes'], // "1" untuk hapus gambar
            'job_position' => ['sometimes', 'string', 'max:255'],  // Posisi pekerjaan update
            'career_growth_description' => ['sometimes', 'string'],  // Deskripsi karir update
        ]);

        if (array_key_exists('heading', $data))  $work->heading  = $data['heading'];
        if (array_key_exists('title', $data))    $work->title    = $data['title'];
        if (array_key_exists('subtitle', $data)) $work->subtitle = $data['subtitle'];
        if (array_key_exists('job_position', $data)) $work->job_position = $data['job_position'];  // Update posisi pekerjaan
        if (array_key_exists('career_growth_description', $data)) $work->career_growth_description = $data['career_growth_description'];  // Update deskripsi karir

        if ($request->boolean('hero_reset')) {
            if ($work->hero && Storage::disk('public')->exists($work->hero)) {
                Storage::disk('public')->delete($work->hero);
            }
            $work->hero = null;
        } elseif ($request->hasFile('hero')) {
            if ($work->hero && Storage::disk('public')->exists($work->hero)) {
                Storage::disk('public')->delete($work->hero);
            }
            $work->hero = $request->file('hero')->store('works', 'public');
        }

        $work->save();

        return response()->json(['data' => $this->serialize($work)], 200);
    }
}