<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternshipHero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InternshipHeroController extends Controller
{
    private function serialize(?InternshipHero $hero): ?array
    {
        if (!$hero) return null;

        return [
            'id'       => $hero->id,
            'subtitle' => $hero->subtitle,
            'title'    => $hero->title,
            'image_url'=> $hero->image_path ? asset('storage/'.$hero->image_path) : null,
        ];
    }

    /** GET /api/internship/hero */
    public function show()
    {
        $hero = InternshipHero::first();

        return response()->json([
            'status' => true,
            'data'   => $this->serialize($hero),
        ], 200);
    }

    /** PUT /api/admin/internship/hero */
    public function updateText(Request $request)
    {
        $v = Validator::make($request->all(), [
            'subtitle' => 'required|string|max:100',
            'title'    => 'required|string|max:3000',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $hero = InternshipHero::firstOrCreate([]);
        $hero->update($v->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Hero text saved',
            'data'    => $this->serialize($hero),
        ], 200);
    }

    /** POST /api/admin/internship/hero/image */
    public function updateImage(Request $request)
    {
        $v = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $hero = InternshipHero::firstOrCreate([]);

        // hapus file lama
        if ($hero->image_path && Storage::disk('public')->exists($hero->image_path)) {
            Storage::disk('public')->delete($hero->image_path);
        }

        $path = $request->file('image')->store('internship', 'public');
        $hero->update(['image_path' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Hero image updated',
            'data'    => $this->serialize($hero),
        ], 200);
    }
}