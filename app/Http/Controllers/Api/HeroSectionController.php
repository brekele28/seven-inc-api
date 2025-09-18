<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class HeroSectionController extends Controller
{
    // ====== Helper serialize ======
    private function serialize(HeroSection $hero): array
    {
        return [
            'id'          => $hero->id,
            'heading'     => $hero->heading,
            'subheading'  => $hero->subheading,
            'image_url'   => $hero->image_path ? asset('storage/' . $hero->image_path) : null,
            'is_published'=> (bool) $hero->is_published,
            'updated_at'  => $hero->updated_at?->toISOString(),
        ];
    }

    // ====== PUBLIC ======
    // GET /api/hero  -> ambil entri terbaru yang published
    public function show()
    {
        $hero = HeroSection::where('is_published', true)
            ->latest('id')
            ->first();

        if (!$hero) {
            return response()->json([
                'status'  => false,
                'message' => 'Hero section not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->serialize($hero),
        ], 200);
    }

    // ====== ADMIN (SANCTUM) ======
    // POST /api/admin/hero  (create baru)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'heading'      => ['required', 'string'],
            'subheading'   => ['nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'image'        => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4 * 1024)],
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('hero', 'public');
        }

        $hero = HeroSection::create([
            'heading'      => $validated['heading'],
            'subheading'   => $validated['subheading'] ?? null,
            'image_path'   => $path,
            'is_published' => $validated['is_published'] ?? true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Hero section created.',
            'data'    => $this->serialize($hero),
        ], 201);
    }

    // GET /api/admin/hero/{id}
    public function showAdmin(int $id)
    {
        $hero = HeroSection::find($id);
        if (!$hero) {
            return response()->json(['status' => false, 'message' => 'Hero section not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->serialize($hero),
        ], 200);
    }

    // PATCH /api/admin/hero/{id}  (partial update + ganti/hapus gambar)
    public function update(Request $request, int $id)
    {
        $hero = HeroSection::find($id);
        if (!$hero) {
            return response()->json(['status' => false, 'message' => 'Hero section not found.'], 404);
        }

        $validated = $request->validate([
            'heading'      => ['sometimes', 'required', 'string'],
            'subheading'   => ['sometimes', 'nullable', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'image'        => ['sometimes', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(4 * 1024)],
            'image_reset'  => ['sometimes'], // truthy -> hapus gambar
        ]);

        if (array_key_exists('heading', $validated))     $hero->heading     = $validated['heading'];
        if (array_key_exists('subheading', $validated))  $hero->subheading  = $validated['subheading'];
        if (array_key_exists('is_published', $validated))$hero->is_published= $validated['is_published'];

        // Hapus gambar jika diminta
        if ($request->boolean('image_reset')) {
            if ($hero->image_path && Storage::disk('public')->exists($hero->image_path)) {
                Storage::disk('public')->delete($hero->image_path);
            }
            $hero->image_path = null;
        }

        // Upload gambar baru
        if ($request->hasFile('image')) {
            if ($hero->image_path && Storage::disk('public')->exists($hero->image_path)) {
                Storage::disk('public')->delete($hero->image_path);
            }
            $hero->image_path = $request->file('image')->store('hero', 'public');
        }

        $hero->save();

        return response()->json([
            'status'  => true,
            'message' => 'Hero section updated.',
            'data'    => $this->serialize($hero),
        ], 200);
    }

    // (Opsional) DELETE /api/admin/hero/{id}
    public function destroy(int $id)
    {
        $hero = HeroSection::find($id);
        if (!$hero) {
            return response()->json(['status' => false, 'message' => 'Hero section not found.'], 404);
        }

        if ($hero->image_path && Storage::disk('public')->exists($hero->image_path)) {
            Storage::disk('public')->delete($hero->image_path);
        }

        $hero->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Hero section deleted.',
        ], 200);
    }
}