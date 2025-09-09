<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    // tambahkan 'gforms' untuk link Google Form
    private const PLATFORMS = ['linkedin', 'instagram', 'facebook', 'x', 'gforms'];

    // GET /api/social-links (publik/opsional) → hanya yang aktif
    public function publicIndex()
    {
        $items = SocialLink::whereIn('platform', self::PLATFORMS)
            ->where('is_active', true)
            ->orderByRaw("FIELD(platform,'" . implode("','", self::PLATFORMS) . "')")
            ->get(['platform', 'url', 'is_active']);

        return response()->json($items, 200);
    }

    // GET /api/admin/social-links (admin) → kembalikan semua platform + default bila kosong
    public function adminIndex()
    {
        $rows = SocialLink::whereIn('platform', self::PLATFORMS)->get()->keyBy('platform');

        $out = [];
        foreach (self::PLATFORMS as $p) {
            $row = $rows->get($p);
            $out[] = [
                'platform'  => $p,
                'url'       => $row->url ?? null,
                'is_active' => $row->is_active ?? true,
            ];
        }

        return response()->json($out, 200);
    }

    // PUT /api/admin/social-links (admin) → bulk upsert
    // Payload: { links: [ {platform,url,is_active}, ... ] }
    public function bulkUpsert(Request $request)
    {
        $data = $request->validate([
            'links'             => 'required|array|min:1',
            'links.*.platform'  => 'required|string|in:' . implode(',', self::PLATFORMS),
            'links.*.url'       => 'nullable|url',
            'links.*.is_active' => 'required|boolean',
        ]);

        foreach ($data['links'] as $link) {
            $url = $link['url'] ?? null;
            if (is_string($url) && trim($url) === '') {
                $url = null;
            }

            SocialLink::updateOrCreate(
                ['platform' => $link['platform']],
                ['url' => $url, 'is_active' => (bool) $link['is_active']]
            );
        }

        return response()->json(['message' => 'Social links updated'], 200);
    }
}