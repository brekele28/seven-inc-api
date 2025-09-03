<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WorkController extends Controller
{
    private function serialize(?Work $w): ?array
    {
        if (!$w) return null;

        return [
            'id'         => $w->id,
            'heading'    => $w->heading,
            'title'      => $w->title,
            'subtitle'   => $w->subtitle,
            'hero_url'   => $w->hero_url, // accessor dari model
            'created_at' => optional($w->created_at)->toIso8601String(),
            'updated_at' => optional($w->updated_at)->toIso8601String(),
        ];
    }

    /** GET /api/works?per_page=10  (public) */
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('per_page') ?: 10);
        $p = Work::orderByDesc('updated_at')->orderByDesc('id')
            ->paginate($perPage)
            ->appends($request->query());

        return response()->json([
            'status' => true,
            'data'   => [
                'list' => collect($p->items())->map(fn ($w) => $this->serialize($w))->values(),
                'meta' => [
                    'current_page' => $p->currentPage(),
                    'per_page'     => $p->perPage(),
                    'total'        => $p->total(),
                    'last_page'    => $p->lastPage(),
                ],
            ],
        ], 200);
    }

    /** GET /api/works/latest  (public) */
    public function latest()
    {
        $w = Work::orderByDesc('updated_at')->orderByDesc('id')->first();
        if (!$w) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->serialize($w)], 200);
    }

    /** GET /api/works/{id}  (public) */
    public function show(int $id)
    {
        $w = Work::find($id);
        if (!$w) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $this->serialize($w)], 200);
    }

    /** POST /api/admin/works  (auth:sanctum) */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'heading'  => 'required|string|max:120',
            'title'    => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'hero'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $w = new Work();
        $w->heading  = $request->input('heading');
        $w->title    = $request->input('title');      // simpan \n apa adanya
        $w->subtitle = $request->input('subtitle');

        if ($request->hasFile('hero')) {
            $w->hero_path = $request->file('hero')->store('works', 'public');
        }

        $w->save();
        $w->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'Work created',
            'data'    => $this->serialize($w),
        ], 201);
    }

    /** PATCH/POST(_method=PATCH) /api/admin/works/{id}  (auth:sanctum) */
    public function update(Request $request, int $id)
    {
        $w = Work::find($id);
        if (!$w) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'heading'    => 'sometimes|required|string|max:120',
            'title'      => 'sometimes|required|string|max:255',
            'subtitle'   => 'sometimes|nullable|string|max:500',
            'hero'       => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'hero_reset' => 'sometimes|boolean', // flag reset gambar
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        // Update teks yang dikirim (tidak bergantung pada upload)
        $data = $v->validated();
        foreach (['heading', 'title', 'subtitle'] as $f) {
            if (array_key_exists($f, $data)) {
                $w->{$f} = $data[$f];
            }
        }

        // Reset gambar ke null bila diminta
        if ($request->boolean('hero_reset')) {
            if ($w->hero_path && Storage::disk('public')->exists($w->hero_path)) {
                Storage::disk('public')->delete($w->hero_path);
            }
            $w->hero_path = null;
        }

        // Ganti gambar bila ada file baru
        if ($request->hasFile('hero')) {
            if ($w->hero_path && Storage::disk('public')->exists($w->hero_path)) {
                Storage::disk('public')->delete($w->hero_path);
            }
            $w->hero_path = $request->file('hero')->store('works', 'public');
        }

        $w->save();
        $w->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'Work updated',
            'data'    => $this->serialize($w),
        ], 200);
    }

    /** DELETE /api/admin/works/{id}  (auth:sanctum) */
    public function destroy(int $id)
    {
        $w = Work::find($id);
        if (!$w) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        if ($w->hero_path && Storage::disk('public')->exists($w->hero_path)) {
            Storage::disk('public')->delete($w->hero_path);
        }

        $w->delete();

        return response()->json(['status' => true, 'message' => 'Work deleted'], 200);
    }
}