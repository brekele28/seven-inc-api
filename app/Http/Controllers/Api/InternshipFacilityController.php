<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternshipFacility;
use App\Models\InternshipFacilityItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InternshipFacilityController extends Controller
{
    private function ensureInit(): InternshipFacility
    {
        $f = InternshipFacility::first();
        if ($f) return $f;

        $f = InternshipFacility::create([
            'subtitle' => 'FASILITAS YANG DIDAPAT',
            'headline' => 'Karena Belajar Butuh Lingkungan yang Mendukung.',
        ]);

        $items = [
            'Setiap peserta magang akan mendapatkan bimbingan langsung dari tim internal yang berpengalaman.',
            'Disediakan sesi pengembangan keterampilan tambahan yang dapat diikuti secara sukarela di luar jam kerja reguler.',
            'Peserta akan memperoleh sertifikat magang serta seragam resmi dari MagangJogja.com sebagai bukti keikutsertaan.',
            'Disediakan koneksi internet tanpa biaya tambahan bagi peserta magang yang menjalankan aktivitas di kantor.',
            'Bagi peserta yang berasal dari luar kota, tim kami akan memberikan informasi seputar kost atau hunian dengan harga terjangkau.',
            'Tersedia minuman hangat secara cuma-cuma sebagai bentuk dukungan kenyamanan selama kegiatan magang berlangsung.',
            'Bagi peserta yang menunjukkan performa baik, akan diberikan surat rekomendasi untuk mendukung karier atau studi lanjutan.',
            'Peserta berpeluang untuk terlibat aktif dalam berbagai proyek nyata yang dijalankan oleh tim profesional Seven INC.',
            'Magang di Seven INC. memberikan akses untuk membangun koneksi profesional dan pengalaman langsung di lingkungan kerja multisektor.',
        ];

        foreach ($items as $i => $t) {
            InternshipFacilityItem::create([
                'facility_id' => $f->id,
                'text'        => $t,
                'order'       => $i + 1,
            ]);
        }
        return $f;
    }

    private function serialize(InternshipFacility $f): array
    {
        return [
            'header' => [
                'subtitle' => $f->subtitle,
                'headline' => $f->headline,
            ],
            'items' => $f->items()->pluck('text')->toArray(),
        ];
    }

    // PUBLIC
    public function index()
    {
        $f = $this->ensureInit();
        return response()->json(['status'=>true,'data'=>$this->serialize($f)], 200);
    }

    // ADMIN
    public function updateHeader(Request $req)
    {
        $v = Validator::make($req->all(), [
            'subtitle' => 'required|string|max:255',
            'headline' => 'required|string|max:255',
        ]);
        if ($v->fails()) return response()->json(['status'=>false,'errors'=>$v->errors()], 422);

        $f = $this->ensureInit();
        $f->fill($v->validated())->save();

        return response()->json(['status'=>true,'message'=>'Header updated','data'=>$this->serialize($f)], 200);
    }

    public function updateItem(Request $req, int $index)
    {
        $v = Validator::make($req->all(), ['text'=>'required|string|max:2000']);
        if ($v->fails()) return response()->json(['status'=>false,'errors'=>$v->errors()], 422);

        $f = $this->ensureInit();
        $item = $f->items()->where('order', $index)->first();
        if (!$item) return response()->json(['status'=>false,'message'=>'Item not found'], 404);

        $item->text = $req->text;
        $item->save();

        return response()->json(['status'=>true,'message'=>'Item updated','data'=>$this->serialize($f)], 200);
    }
}