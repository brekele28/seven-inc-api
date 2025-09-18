<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requirement;
use App\Models\RequirementItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    // ======== PUBLIC ========

    // GET /api/requirements/by-job/{jobWorkId}
    public function showByJob(int $jobWorkId)
    {
        $req = Requirement::with(['items' => function ($q) {
                $q->orderBy('type')->orderBy('sort_order');
            }])
            ->where('job_work_id', $jobWorkId)
            ->where('is_published', true)
            ->first();

        if (!$req) {
            return response()->json([
                'status'  => false,
                'message' => 'Requirements not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatRequirement($req),
        ], 200);
    }

    // (opsional) GET /api/requirements/{id}
    public function showPublicById(int $id)
    {
        $req = Requirement::with(['items' => function ($q) {
                $q->orderBy('type')->orderBy('sort_order');
            }])
            ->where('id', $id)
            ->where('is_published', true)
            ->first();

        if (!$req) {
            return response()->json([
                'status'  => false,
                'message' => 'Requirements not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatRequirement($req),
        ], 200);
    }

    // ======== ADMIN (AUTH SANCTUM) ========

    // POST /api/admin/requirements
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_work_id' => ['nullable', 'integer', 'exists:job_works,id', 'unique:requirements,job_work_id'],
            'intro_text'  => ['required', 'string'],
            'is_published'=> ['sometimes', 'boolean'],
        ]);

        $req = Requirement::create([
            'job_work_id'  => $validated['job_work_id'] ?? null,
            'intro_text'   => $validated['intro_text'],
            'is_published' => $validated['is_published'] ?? true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Requirements created.',
            'data'    => $this->formatRequirement($req->load('items')),
        ], 201);
    }

    // GET /api/admin/requirements/{id}
    public function showAdmin(int $id)
    {
        $req = Requirement::with(['items' => function ($q) {
                $q->orderBy('type')->orderBy('sort_order');
            }])
            ->find($id);

        if (!$req) {
            return response()->json([
                'status'  => false,
                'message' => 'Requirements not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatRequirement($req),
        ], 200);
    }

    // PATCH /api/admin/requirements/{id}
    public function update(Request $request, int $id)
    {
        $req = Requirement::find($id);
        if (!$req) {
            return response()->json([
                'status'  => false,
                'message' => 'Requirements not found.',
            ], 404);
        }

        $validated = $request->validate([
            'job_work_id' => [
                'nullable',
                'integer',
                'exists:job_works,id',
                Rule::unique('requirements', 'job_work_id')->ignore($req->id),
            ],
            'intro_text'   => ['sometimes', 'required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $req->fill($validated)->save();

        return response()->json([
            'status'  => true,
            'message' => 'Requirements updated.',
            'data'    => $this->formatRequirement($req->fresh('items')),
        ], 200);
    }

    // DELETE /api/admin/requirements/{id}
    public function destroy(int $id)
    {
        $req = Requirement::find($id);
        if (!$req) {
            return response()->json([
                'status'  => false,
                'message' => 'Requirements not found.',
            ], 404);
        }

        $req->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Requirements deleted.',
        ], 200);
    }

    // POST /api/admin/requirements/{id}/items
    public function storeItem(Request $request, int $id)
    {
        $req = Requirement::find($id);
        if (!$req) {
            return response()->json(['status' => false, 'message' => 'Requirements not found.'], 404);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['umum','khusus','tanggung_jawab','benefit'])],
            'text' => ['required', 'string'],
        ]);

        $nextOrder = (int) RequirementItem::where('requirement_id', $req->id)
            ->where('type', $validated['type'])
            ->max('sort_order') + 1;

        $item = RequirementItem::create([
            'requirement_id' => $req->id,
            'type'           => $validated['type'],
            'text'           => trim($validated['text']),
            'sort_order'     => $nextOrder,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Item created.',
            'data'    => $item,
        ], 201);
    }

    // PATCH /api/admin/requirements/{id}/items/{itemId}
    public function updateItem(Request $request, int $id, int $itemId)
    {
        $item = RequirementItem::where('id', $itemId)
            ->where('requirement_id', $id)
            ->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found.'], 404);
        }

        $validated = $request->validate([
            'type' => ['sometimes', Rule::in(['umum','khusus','tanggung_jawab','benefit'])],
            'text' => ['sometimes', 'required', 'string'],
        ]);

        // Jika pindah kategori, letakkan di urutan terakhir kategori baru
        if (isset($validated['type']) && $validated['type'] !== $item->type) {
            $validated['sort_order'] = (int) RequirementItem::where('requirement_id', $id)
                ->where('type', $validated['type'])
                ->max('sort_order') + 1;
        }

        $item->fill($validated)->save();

        return response()->json([
            'status'  => true,
            'message' => 'Item updated.',
            'data'    => $item,
        ], 200);
    }

    // DELETE /api/admin/requirements/{id}/items/{itemId}
    public function destroyItem(int $id, int $itemId)
    {
        $item = RequirementItem::where('id', $itemId)
            ->where('requirement_id', $id)
            ->first();

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found.'], 404);
        }

        $item->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Item deleted.',
        ], 200);
    }

    // PUT /api/admin/requirements/{id}/items/bulk
    public function bulkUpsertItems(Request $request, int $id)
    {
        $req = Requirement::find($id);
        if (!$req) {
            return response()->json(['status' => false, 'message' => 'Requirements not found.'], 404);
        }

        $validated = $request->validate([
            'type'  => ['required', Rule::in(['umum','khusus','tanggung_jawab','benefit'])],
            'mode'  => ['sometimes', Rule::in(['replace','upsert'])],
            'items' => ['required', 'array'],
            'items.*.id'   => ['sometimes', 'integer', 'exists:requirement_items,id'],
            'items.*.text' => ['required', 'string'],
        ]);

        $type = $validated['type'];
        $mode = $validated['mode'] ?? 'upsert';

        return DB::transaction(function () use ($id, $type, $mode, $validated) {
            if ($mode === 'replace') {
                RequirementItem::where('requirement_id', $id)->where('type', $type)->delete();
            }

            $order = 1;
            $payload = [];

            foreach ($validated['items'] as $row) {
                if (!empty($row['id']) && $mode === 'upsert') {
                    // update
                    RequirementItem::where('id', $row['id'])
                        ->where('requirement_id', $id)
                        ->update([
                            'text'       => trim($row['text']),
                            'type'       => $type,
                            'sort_order' => $order++,
                        ]);
                } else {
                    // insert
                    $payload[] = [
                        'requirement_id' => $id,
                        'type'           => $type,
                        'text'           => trim($row['text']),
                        'sort_order'     => $order++,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
            }

            if (!empty($payload)) {
                RequirementItem::insert($payload);
            }

            $items = RequirementItem::where('requirement_id', $id)
                ->where('type', $type)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Items synchronized.',
                'data'    => $items,
            ], 200);
        });
    }

    // PUT /api/admin/requirements/{id}/items/reorder
    public function reorderItems(Request $request, int $id)
    {
        $validated = $request->validate([
            'type'   => ['required', Rule::in(['umum','khusus','tanggung_jawab','benefit'])],
            'orders' => ['required', 'array'],
            'orders.*.id'         => ['required', 'integer', 'exists:requirement_items,id'],
            'orders.*.sort_order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $id) {
            foreach ($validated['orders'] as $o) {
                RequirementItem::where('id', $o['id'])
                    ->where('requirement_id', $id)
                    ->where('type', $validated['type'])
                    ->update(['sort_order' => (int)$o['sort_order']]);
            }
        });

        $items = RequirementItem::where('requirement_id', $id)
            ->where('type', $validated['type'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Order updated.',
            'data'    => $items,
        ], 200);
    }

    // ======== Helpers ========

    private function formatRequirement(Requirement $req): array
    {
        $grouped = [
            'umum'            => [],
            'khusus'          => [],
            'tanggung_jawab'  => [],
            'benefit'         => [],
        ];

        foreach ($req->items as $it) {
            $grouped[$it->type][] = [
                'id'         => $it->id,
                'text'       => $it->text,
                'sort_order' => $it->sort_order,
            ];
        }

        return [
            'id'           => $req->id,
            'job_work_id'  => $req->job_work_id,
            'intro_text'   => $req->intro_text,
            'items'        => $grouped,
            'is_published' => $req->is_published,
            'updated_at'   => $req->updated_at ? $req->updated_at->toISOString() : null,
        ];
    }
}