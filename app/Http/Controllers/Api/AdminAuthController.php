<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    // Fungsi Register Admin
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Admin berhasil terdaftar',
            'admin' => $admin,
            'token' => $token
        ], 201);
    }

    // Fungsi Login Admin
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $admin->avatar ? asset('storage/' . $admin->avatar) : null,
            ],
            'token' => $token
        ], 200);
    }

    // Edit Profil
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $admin = $request->user();

        // Hapus avatar lama jika ada
        if ($admin->avatar && Storage::disk('public')->exists($admin->avatar)) {
            Storage::disk('public')->delete($admin->avatar);
        }

        // Simpan file baru
        $path = $request->file('avatar')->store('avatars', 'public');

        $admin->avatar = $path;
        $admin->save();

        return response()->json([
            'status' => true,
            'message' => 'Avatar berhasil diperbarui',
            'avatar_url' => asset('storage/' . $path)
        ]);
    }

    // Fungsi Update Nama Admin
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:6',
        ]);

        $admin = $request->user();
        $admin->name = $request->name;
        $admin->save();

        return response()->json([
            'status' => true,
            'message' => 'Nama berhasil diperbarui',
            'admin' => $admin
        ], 200);
    }

    // 🔹 Fungsi Logout Admin
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }
}