<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AboutController extends Controller
{
    private function serialize(?About $about): ?array
    {
        if (!$about) return null;

        $url = fn ($p) => $p ? asset('storage/' . $p) : null;

        return [
            'id'         => $about->id,
            'subtitle'   => $about->subtitle,
            'headline'   => $about->headline,

            // images (legacy keys)
            'image_url1' => $url($about->hero_image1),
            'image_url2' => $url($about->hero_image2),
            'image_url3' => $url($about->hero_image3),

            // paragraphs (editor kiri/kanan)
            'left_p1'  => $about->left_p1,
            'left_p2'  => $about->left_p2,
            'left_p3'  => $about->left_p3,
            'right_p1' => $about->right_p1,
            'right_p2' => $about->right_p2,
            'left_paragraphs'  => array_values(array_filter([$about->left_p1,$about->left_p2,$about->left_p3], fn($v)=>$v!==null && $v!=='')),
            'right_paragraphs' => array_values(array_filter([$about->right_p1,$about->right_p2], fn($v)=>$v!==null && $v!=='')),

            // core value texts (kiri atas)
            'core_title'     => $about->core_title,
            'core_headline'  => $about->core_headline,
            'core_paragraph' => $about->core_paragraph,
        ];
    }

    /** GET /api/about */
    public function show()
    {
        $about = About::first();

        return response()->json([
            'status' => true,
            'data'   => $this->serialize($about),
        ], 200);
    }

    /** POST /api/admin/about — update subtitle/headline + gambar slot */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'subtitle' => 'required|string|max:100',
            'headline' => 'required|string|max:255',
            'slot'     => 'required|integer|in:1,2,3',
            'image'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $about = About::first() ?: new About();
        $about->subtitle = $request->subtitle;
        $about->headline = $request->headline;

        if ($request->hasFile('image')) {
            $slot   = (int) $request->slot;
            $column = "hero_image{$slot}";
            if ($about->$column && Storage::disk('public')->exists($about->$column)) {
                Storage::disk('public')->delete($about->$column);
            }
            $about->$column = $request->file('image')->store('about', 'public');
        }

        $about->save();
        $about->refresh();

        return response()->json([
            'status'=>true,'message'=>'About saved','data'=>$this->serialize($about)
        ], 200);
    }

    /** POST /api/admin/about/paragraph — update paragraf kiri/kanan per slot */
    public function updateParagraph(Request $request)
    {
        $v = Validator::make($request->all(), [
            'side'    => 'required|string|in:left,right',
            'slot'    => 'required|integer',
            'content' => 'required|string|max:3000',
        ]);

        $v->after(function($validator) use ($request) {
            $side = $request->input('side');
            $slot = (int) $request->input('slot');
            if ($side === 'left'  && !in_array($slot,[1,2,3],true)) $validator->errors()->add('slot','left: slot 1..3');
            if ($side === 'right' && !in_array($slot,[1,2],true))   $validator->errors()->add('slot','right: slot 1..2');
        });

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $about = About::first() ?: new About();
        $column = $request->side === 'left' ? "left_p{$request->slot}" : "right_p{$request->slot}";
        $about->$column = $request->content;
        $about->save();
        $about->refresh();

        return response()->json([
            'status'=>true,'message'=>'Paragraph saved','data'=>$this->serialize($about)
        ], 200);
    }

    /** POST /api/admin/about/core-text — update salah satu: title | headline | paragraph */
    public function updateCoreText(Request $request)
    {
        $v = Validator::make($request->all(), [
            'field'   => 'required|string|in:title,headline,paragraph',
            'content' => 'required|string|max:3000',
        ]);

        if ($v->fails()) {
            return response()->json(['status'=>false,'errors'=>$v->errors()], 422);
        }

        $map = [
            'title'     => 'core_title',
            'headline'  => 'core_headline',
            'paragraph' => 'core_paragraph',
        ];

        $about = About::first() ?: new About();
        $about->{$map[$request->field]} = $request->content;
        $about->save();
        $about->refresh();

        return response()->json([
            'status'=>true,'message'=>'Core text saved','data'=>$this->serialize($about)
        ], 200);
    }
}