<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    // ===== Helpers =====
    private function serialize(?News $n): ?array
    {
        if (!$n) return null;

        return [
            'id'           => $n->id,
            'title'        => $n->title,
            'slug'         => $n->slug,
            'excerpt'      => $n->excerpt,
            'body'         => $n->body,
            'cover_url'    => $n->cover_url,
            'is_published' => $n->is_published,
            'published_at' => optional($n->published_at)->toIso8601String(),
            'updated_at'   => optional($n->updated_at)->toIso8601String(),
            'author'       => $n->author,
        ];
    }

    private function findByIdOrSlug(string $idOrSlug): ?News
    {
        return is_numeric($idOrSlug)
            ? News::find($idOrSlug)
            : News::where('slug', $idOrSlug)->first();
    }

    // ===== Public Endpoints =====

    /**
     * GET /api/news
     * Query params:
     *  - page (default 1)
     *  - per_page (default 9) -> untuk grid
     *  - q (optional search di title)
     */
    public function index(Request $request)
    {
        $perPage = (int)($request->integer('per_page') ?: 9);
        $q = trim((string)$request->get('q', ''));

        $base = News::published();

        if ($q !== '') {
            $base->where('title', 'like', '%'.$q.'%');
        }

        // featured = terbaru berdasarkan updated_at
        $featured = (clone $base)->ordered()->first();

        $listQuery = (clone $base)
            ->when($featured, fn($qq) => $qq->where('id', '!=', $featured->id))
            ->ordered();

        $p = $listQuery->paginate($perPage)->appends($request->query());

        return response()->json([
            'status' => true,
            'data'   => [
                'featured' => $this->serialize($featured),
                'list'     => collect($p->items())->map(fn($n) => $this->serialize($n))->values(),
                'meta'     => [
                    'current_page' => $p->currentPage(),
                    'per_page'     => $p->perPage(),
                    'total'        => $p->total(),
                    'last_page'    => $p->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * GET /api/news/{idOrSlug}
     */
    public function show(string $idOrSlug)
    {
        $news = $this->findByIdOrSlug($idOrSlug);
        if (!$news || !$news->is_published) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $this->serialize($news)], 200);
    }

    // ===== Admin (protected by auth:sanctum) =====

    /**
     * POST /api/admin/news
     * fields: title (req), body (req), excerpt (opt), cover (file opt),
     *         is_published (bool), author (opt)
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'excerpt'      => 'nullable|string|max:500',
            'body'         => 'required|string',
            'cover'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_published' => 'nullable|boolean',
            'author'       => 'nullable|string|max:120',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $news = new News($v->validated());

        if ($request->hasFile('cover')) {
            $news->cover_path = $request->file('cover')->store('news', 'public');
        }

        $news->save();
        $news->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'News created',
            'data'    => $this->serialize($news),
        ], 201);
    }

    /**
     * PATCH /api/admin/news/{idOrSlug}
     * (meng-update berita; updated_at otomatis berubah → jadi featured terbaru)
     */
    public function update(Request $request, string $idOrSlug)
    {
        $news = $this->findByIdOrSlug($idOrSlug);
        if (!$news) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $v = Validator::make($request->all(), [
            'title'        => 'sometimes|required|string|max:255',
            'excerpt'      => 'sometimes|nullable|string|max:500',
            'body'         => 'sometimes|required|string',
            'cover'        => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'is_published' => 'sometimes|boolean',
            'author'       => 'sometimes|nullable|string|max:120',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $news->fill($v->validated());

        if ($request->hasFile('cover')) {
            if ($news->cover_path && Storage::disk('public')->exists($news->cover_path)) {
                Storage::disk('public')->delete($news->cover_path);
            }
            $news->cover_path = $request->file('cover')->store('news', 'public');
        }

        // published_at setter
        if ($news->is_published && !$news->published_at) {
            $news->published_at = now();
        }

        $news->save();
        $news->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'News updated',
            'data'    => $this->serialize($news),
        ], 200);
    }

    /**
     * DELETE /api/admin/news/{idOrSlug}
     * Soft delete
     */
    public function destroy(string $idOrSlug)
    {
        $news = $this->findByIdOrSlug($idOrSlug);
        if (!$news) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $news->delete();

        return response()->json(['status' => true, 'message' => 'News deleted'], 200);
    }

    /**
     * POST /api/admin/news/{idOrSlug}/publish
     * body: { is_published: boolean }
     */
    public function togglePublish(Request $request, string $idOrSlug)
    {
        $news = $this->findByIdOrSlug($idOrSlug);
        if (!$news) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $v = Validator::make($request->all(), [
            'is_published' => 'required|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['status' => false, 'errors' => $v->errors()], 422);
        }

        $news->is_published = (bool)$request->boolean('is_published');
        if ($news->is_published && !$news->published_at) {
            $news->published_at = now();
        }
        $news->save();

        return response()->json([
            'status'  => true,
            'message' => 'Publish status updated',
            'data'    => $this->serialize($news),
        ], 200);
    }
}