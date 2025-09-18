<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternshipCoreHeader;
use App\Models\InternshipCoreCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InternshipCoreValueController extends Controller
{
    /* ============================
     *  DEFAULTS (tanpa seeder)
     * ============================ */
    private function defaultHeader(): array
    {
        return [
            'core_title'     => 'CORE VALUE PERUSAHAAN',
            'core_headline'  => 'Prinsip Utama yang Menjadi Dasar Tumbuh Bersama',
            'core_paragraph' => 'Sembilan nilai inti ini menjadi pedoman tim Seven INC. dalam membangun budaya kerja profesional, kolaboratif, dan berkelanjutan menuju visi perusahaan yang terus berkembang.',
        ];
    }

    private function defaultCards(): array
    {
        // Urutan & styleType mengikuti UI: 1,2,1,2,1,2,1,2,1
        $titles = [
            'Integritas', 'Positive Vibe', 'Fokus', 'Solusi',
            'Trust', 'Komitmen', 'Kaizen', 'Tata Krama', 'Menjaga Habit Baik',
        ];

        // Dua deskripsi sesuai contoh UI
        $descStyle1 = 'Sikap dasar setiap insan Seven INC. untuk selaras antara pikiran, ucapan, dan tindakan, menjaga kejujuran, tanggung jawab, serta kerahasiaan sesuai visi dan amanah perusahaan.';
        $descStyle2 = 'Menciptakan lingkungan kerja yang suportif melalui sikap positif, penyampaian informasi yang membangun, serta menghindari gosip dan prasangka yang merugikan tim.';

        $styleByIndex = [1,2,1,2,1,2,1,2,1];

        $items = [];
        foreach ($titles as $i => $t) {
            $order = $i + 1;
            $style = $styleByIndex[$i];
            $items[] = [
                'title'       => $t,
                'description' => $style === 1 ? $descStyle1 : $descStyle2,
                'style_type'  => $style,
                'order'       => $order,
            ];
        }
        return $items;
    }

    private function getFallbackImage(int $styleType): string
    {
        // Fallback path mengikuti aset FE-mu
        return $styleType === 2
            ? '/assets/img/vectorPerson.png'
            : '/assets/img/vectorSalaman.png';
    }

    private function serializeHeader(?InternshipCoreHeader $h): ?array
    {
        if (!$h) return null;
        return [
            'id'             => $h->id,
            'core_title'     => $h->core_title,
            'core_headline'  => $h->core_headline,
            'core_paragraph' => $h->core_paragraph,
        ];
    }

    private function serializeCard(?InternshipCoreCard $c): ?array
    {
        if (!$c) return null;

        // Jika belum ada upload, gunakan fallback image sesuai style_type
        $imageUrl = $c->image_path
            ? asset('storage/'.$c->image_path)
            : $this->getFallbackImage((int) $c->style_type);

        return [
            'id'          => $c->id,
            'title'       => $c->title,
            'description' => $c->description,
            'style_type'  => (int) $c->style_type,
            'order'       => (int) $c->order,
            'image_url'   => $imageUrl,
        ];
    }

    /**
     * Pastikan ada 1 header + 9 cards dengan data default
     * dipanggil dari index() agar bootstrap terjadi otomatis tanpa seeder
     */
    private function ensureDefaults(): void
    {
        // Header
        if (!InternshipCoreHeader::first()) {
            InternshipCoreHeader::create($this->defaultHeader());
        }

        // Cards
        $count = InternshipCoreCard::count();
        if ($count < 9) {
            // Hapus duplikat order kalau ada data tidak lengkap/berantakan (opsional)
            // Di sini kita cukup melengkapi hingga 9 baris.
            $existingOrders = InternshipCoreCard::pluck('order')->toArray();
            $defaults = $this->defaultCards();

            foreach ($defaults as $def) {
                if (!in_array($def['order'], $existingOrders, true)) {
                    InternshipCoreCard::create($def);
                }
            }
        }
    }

    /** GET /api/internship/core-values (public) */
    public function index()
    {
        $this->ensureDefaults();

        $header = InternshipCoreHeader::first();
        $cards  = InternshipCoreCard::orderBy('order')->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'header' => $this->serializeHeader($header),
                'cards'  => $cards->map(fn($c) => $this->serializeCard($c))->values(),
            ],
        ], 200);
    }

    /** PUT /api/admin/internship/core-values/header */
    public function updateHeader(Request $request)
    {
        $v = Validator::make($request->all(), [
            'core_title'     => 'required|string|max:100',
            'core_headline'  => 'required|string|max:255',
            'core_paragraph' => 'required|string|max:3000',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $header = InternshipCoreHeader::first() ?? new InternshipCoreHeader();
        $header->fill($v->validated());
        $header->save();
        $header->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'Core header saved',
            'data'    => $this->serializeHeader($header),
        ], 200);
    }

    /** PUT /api/admin/internship/core-values/cards/{card} */
    public function updateCard(Request $request, InternshipCoreCard $card)
    {
        $v = Validator::make($request->all(), [
            'title'       => 'sometimes|required|string|max:120',
            'description' => 'sometimes|nullable|string|max:3000',
            'style_type'  => 'sometimes|required|integer|in:1,2',
            'order'       => 'sometimes|required|integer|min:1|max:9|unique:internship_core_cards,`order`,' . $card->id,
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $card->fill($v->validated());
        $card->save();
        $card->refresh();

        return response()->json([
            'status'  => true,
            'message' => 'Core card updated',
            'data'    => $this->serializeCard($card),
        ], 200);
    }

    /** POST /api/admin/internship/core-values/cards/{card}/image */
    public function updateCardImage(Request $request, InternshipCoreCard $card)
    {
        $v = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        if ($card->image_path && Storage::disk('public')->exists($card->image_path)) {
            Storage::disk('public')->delete($card->image_path);
        }

        $path = $request->file('image')->store('internship/core-values', 'public');
        $card->image_path = $path;
        $card->save();

        return response()->json([
            'status'  => true,
            'message' => 'Core card image updated',
            'data'    => $this->serializeCard($card),
        ], 200);
    }

    /** (Opsional) PUT /api/admin/internship/core-values/cards/reorder */
    public function reorder(Request $request)
    {
        $v = Validator::make($request->all(), [
            'items'               => 'required|array|size:9',
            'items.*.id'          => 'required|integer|exists:internship_core_cards,id',
            'items.*.order'       => 'required|integer|min:1|max:9|distinct',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        foreach ($request->items as $it) {
            InternshipCoreCard::where('id', $it['id'])->update(['order' => $it['order']]);
        }

        $cards = InternshipCoreCard::orderBy('order')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Core cards reordered',
            'data'    => $cards->map(fn($c) => $this->serializeCard($c))->values(),
        ], 200);
    }
}