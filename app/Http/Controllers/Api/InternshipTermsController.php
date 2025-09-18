<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternshipTerms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InternshipTermsController extends Controller
{
    /** Default text (tanpa seeder) */
    private function defaultItems(): array
    {
        return [
            "Mengisi Formulir Pendaftaran Magang.",
            "Menyertakan surat pengantar atau surat tugas resmi dari pihak sekolah atau perguruan tinggi.",
            "Telah memperoleh izin atau persetujuan dari orang tua atau wali sebelum mengikuti program magang.",
            "Bersedia berkomitmen untuk menggali keterampilan dan pengalaman selama masa magang berlangsung.",
            "Bersedia menjalani proses pembelajaran yang menuntut kemandirian, kedewasaan, dan kesiapan untuk hidup lebih mandiri setelah magang selesai.",
            "Bersedia berinteraksi secara profesional dengan seluruh karyawan serta menjaga nama baik institusi asal (sekolah/kampus), perusahaan, dan pribadi.",
        ];
    }

    private function ensureRecord(): InternshipTerms
    {
        $rec = InternshipTerms::first();
        if (!$rec) {
            $rec = InternshipTerms::create([
                'subtitle' => 'Syarat & Ketentuan',
                'headline' => 'Persiapkan Dirimu, Tumbuh Bersama Kami.',
                'items'    => $this->defaultItems(),
            ]);
        }
        return $rec;
    }

    private function serialize(InternshipTerms $rec): array
    {
        return [
            'header' => [
                'subtitle' => (string) $rec->subtitle,
                'headline' => (string) $rec->headline,
            ],
            'items' => array_values($rec->items ?: []), // [string, ...]
        ];
    }

    /** ================== PUBLIC ================== */

    /** GET /api/internship/terms */
    public function show()
    {
        $rec = $this->ensureRecord();

        return response()->json([
            'status' => true,
            'data'   => $this->serialize($rec),
        ], 200);
    }

    /** ================== ADMIN ================== */

    /** PUT /api/admin/internship/terms/header  body: { subtitle, headline } */
    public function updateHeader(Request $request)
    {
        $v = Validator::make($request->all(), [
            'subtitle' => 'required|string|max:255',
            'headline' => 'required|string|max:1000',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $rec = $this->ensureRecord();
        $rec->subtitle = $request->subtitle;
        $rec->headline = $request->headline;
        $rec->save();

        return response()->json([
            'status'  => true,
            'message' => 'Header terms updated',
            'data'    => $this->serialize($rec),
        ], 200);
    }

    /**
     * PUT /api/admin/internship/terms/items/{index}
     * path param: index = 1..6
     * body: { text: string }
     */
    public function updateItem(Request $request, int $index)
    {
        $v = Validator::make(array_merge($request->all(), ['index' => $index]), [
            'index' => 'required|integer|min:1|max:6',
            'text'  => 'required|string|max:2000',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $rec = $this->ensureRecord();
        $items = $rec->items ?: [];

        // pastikan array 6 elemen
        for ($i = 0; $i < 6; $i++) {
            if (!isset($items[$i])) $items[$i] = '';
        }

        $items[$index - 1] = $request->text;
        $rec->items = array_values($items);
        $rec->save();

        return response()->json([
            'status'  => true,
            'message' => "Item #{$index} updated",
            'data'    => $this->serialize($rec),
        ], 200);
    }
}