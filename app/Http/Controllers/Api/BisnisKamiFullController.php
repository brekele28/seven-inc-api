<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BisnisKamiFull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class BisnisKamiFullController extends Controller
{
    /** Kolom gambar yang dikelola */
    private array $imageFields = [
        'header_image',
        'seven_tech_image',
        'seven_style_image',
        'seven_serve_image',
        'seven_edu_image',
    ];

    /** Ambil satu-satunya row; buat kosong jika belum ada */
    private function getSingleton(): BisnisKamiFull
    {
        return BisnisKamiFull::first() ?? BisnisKamiFull::create([]);
    }

    /** Ubah /storage/... atau http(s)://.../storage/... menjadi URL absolut */
    private function toAbsoluteUrl(?string $val): ?string
    {
        if (!$val) return $val;
        if (Str::startsWith($val, ['http://', 'https://'])) return $val;
        if (Str::startsWith($val, '/storage/')) return url($val);
        return $val;
    }

    /**
     * Dari URL publik → path relatif untuk disk 'public'
     * contoh input:  http://host/storage/bisnis_kami_full/abc.jpg
     * contoh output: bisnis_kami_full/abc.jpg
     */
    private function publicUrlToStoragePath(?string $val): ?string
    {
        if (!$val) return null;

        $path = $val;
        if (Str::startsWith($val, ['http://', 'https://'])) {
            $parsed = parse_url($val, PHP_URL_PATH); // /storage/bisnis_kami_full/abc.jpg
            $path = $parsed ?: $val;
        }

        if (Str::startsWith($path, '/storage/')) {
            return ltrim(Str::after($path, '/storage/'), '/');
        }
        if (Str::startsWith($path, 'storage/')) {
            return ltrim(Str::after($path, 'storage/'), '/');
        }

        return null;
    }

    /** Normalisasi URL gambar menjadi absolut pada payload response */
    private function normalizeImageUrls(array $data): array
    {
        foreach ($this->imageFields as $f) {
            if (isset($data[$f])) {
                $data[$f] = $this->toAbsoluteUrl($data[$f]);
            }
        }
        return $data;
    }

    // GET /api/bisnis-kami-full  (publik)
    public function show()
    {
        $row = $this->getSingleton();
        $data = $this->normalizeImageUrls($row->toArray());
        return response()->json($data);
    }

    // PUT /api/admin/bisnis-kami-full/text  (auth)
    public function updateText(Request $request)
    {
        $fields = [
            'header_subtitle' => 'nullable|string|max:255',
            'header_title'    => 'nullable|string|max:255',
            'general_description' => 'nullable|string',

            'seven_tech_title' => 'nullable|string|max:255',
            'seven_tech_text'  => 'nullable|string',

            'seven_style_title' => 'nullable|string|max:255',
            'seven_style_text'  => 'nullable|string',

            'seven_serve_title' => 'nullable|string|max:255',
            'seven_serve_text'  => 'nullable|string',

            'seven_edu_title' => 'nullable|string|max:255',
            'seven_edu_text'  => 'nullable|string',
        ];

        $data = $request->validate($fields);
        $row = $this->getSingleton();
        $row->fill($data)->save();

        return response()->json([
            'message' => 'Text updated',
            'data'    => $this->normalizeImageUrls($row->toArray()),
        ]);
    }

    // POST /api/admin/bisnis-kami-full/image  (auth)
    public function updateImage(Request $request)
    {
        $request->validate([
            'field' => ['required', Rule::in($this->imageFields)],
            'image' => 'required|image|max:2048', // 2MB
        ]);

        $field = $request->input('field');
        $file  = $request->file('image');

        // Simpan ke disk 'public' → storage/app/public/bisnis_kami_full/xxx.ext
        $path = $file->store('bisnis_kami_full', 'public');

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        // URL publik /storage/...
        $publicUrl   = $disk->url($path);   // "/storage/bisnis_kami_full/xxx.ext"
        $absoluteUrl = url($publicUrl);     // "http://host/storage/bisnis_kami_full/xxx.ext"

        $row = $this->getSingleton();

        // Hapus file lama jika ada
        $old = $row->{$field};
        if (!empty($old)) {
            if ($toDelete = $this->publicUrlToStoragePath($old)) {
                if ($disk->exists($toDelete)) {
                    $disk->delete($toDelete);
                }
            }
        }

        // Simpan URL absolut di DB
        $row->{$field} = $absoluteUrl;
        $row->save();

        // Kembalikan data yang sudah dinormalisasi
        $data = $this->normalizeImageUrls($row->toArray());

        return response()->json([
            'message' => 'Image updated',
            'field'   => $field,
            'url'     => $absoluteUrl,
            'data'    => $data,
        ]);
    }
}