<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternshipFormation;
use App\Models\InternshipFormationCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InternshipFormationController extends Controller
{
    private function ensureInit(): InternshipFormation
    {
        $f = InternshipFormation::first();
        if ($f) return $f;

        $f = InternshipFormation::create([
            'subtitle' => 'FORMASI INTERNSHIP',
            'headline' => 'Bangun Kompetensi dan Karakter Bersama Seven INC.',
            'paragraph' => 'Program magang dan internship di Seven INC. dirancang untuk menjadi wadah bagi siswa, mahasiswa, maupun individu umum yang ingin mengembangkan keterampilan praktis dan kesiapan kerja melalui kolaborasi dan pengalaman nyata di berbagai divisi.',
        ]);

        $titles = [
            'Administrasi','Animasi','Content Planner','Content Writer','Desain Grafis','Digital Market',
            'Host / Presenter','Human Resource','Las','Marketing & Sales','Public Relation','Photographer Videographer',
            'Programmer','Project Manager','Social Media Specialist','TikTok Creator','UI / UX Designer','Voice Over Talent',
        ];

        foreach ($titles as $i => $t) {
            InternshipFormationCard::create([
                'formation_id' => $f->id,
                'title'        => $t,
                'order'        => $i + 1,
                'image_path'   => null,
            ]);
        }
        return $f;
    }

    private function serialize(InternshipFormation $f): array
    {
        return [
            'header' => [
                'subtitle'  => $f->subtitle,
                'headline'  => $f->headline,
                'paragraph' => $f->paragraph,
            ],
            'cards'  => $f->cards->map(fn($c) => [
                'id'        => $c->id,
                'title'     => $c->title,
                'order'     => $c->order,
                'image_url' => $c->image_url,
            ])->values(),
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
            'subtitle'  => 'required|string|max:255',
            'headline'  => 'required|string|max:255',
            'paragraph' => 'nullable|string|max:5000',
        ]);
        if ($v->fails()) return response()->json(['status'=>false,'errors'=>$v->errors()], 422);

        $f = $this->ensureInit();
        $f->fill($v->validated())->save();

        return response()->json(['status'=>true,'message'=>'Header updated','data'=>$this->serialize($f)], 200);
    }

    public function updateCard(Request $req, InternshipFormationCard $card)
    {
        $v = Validator::make($req->all(), [
            'title' => 'required|string|max:120',
            'order' => 'sometimes|integer|min:1|max:999',
        ]);
        if ($v->fails()) return response()->json(['status'=>false,'errors'=>$v->errors()], 422);

        $card->fill($v->validated())->save();
        $card->refresh();

        return response()->json(['status'=>true,'data'=>[
            'id'=>$card->id, 'title'=>$card->title, 'order'=>$card->order, 'image_url'=>$card->image_url
        ]], 200);
    }

    public function updateCardImage(Request $req, InternshipFormationCard $card)
    {
        $req->validate(['image'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096']);

        if ($card->image_path && Storage::disk('public')->exists($card->image_path)) {
            Storage::disk('public')->delete($card->image_path);
        }
        $path = $req->file('image')->store('internship/formations', 'public');
        $card->image_path = $path;
        $card->save();

        return response()->json(['status'=>true,'data'=>[
            'id'=>$card->id, 'image_url'=>$card->image_url
        ]], 200);
    }
}