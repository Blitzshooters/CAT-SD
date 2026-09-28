<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Login - Validate credentials and return JWT token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token, coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token'   => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'username'    => $user->username,
                'nomor_induk' => $user->nomor_induk,
                'grade'       => $user->grade,
                'avatar'      => $user->avatar,
                'is_admin'    => (bool)$user->is_admin,
            ],
        ]);
    }

    /**
     * Get currently logged in user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'success' => true,
            'user'    => [
                'id'          => $user->id,
                'name'        => $user->name,
                'username'    => $user->username,
                'nomor_induk' => $user->nomor_induk,
                'grade'       => $user->grade,
                'avatar'      => $user->avatar,
                'is_admin'    => (bool)$user->is_admin,
            ],
        ]);
    }

    /**
     * Logout - Invalidate the JWT token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout, token tidak valid',
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'success'    => true,
                'token'      => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak bisa diperbarui',
            ], 401);
        }
    }

    /**
     * Update user profile (name, avatar, grade)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('avatar')) {
            $user->avatar = $request->input('avatar');
        }
        if ($request->filled('grade')) {
            $user->grade = (int)$request->input('grade');
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id'          => $user->id,
                'name'        => $user->name,
                'username'    => $user->username,
                'nomor_induk' => $user->nomor_induk,
                'grade'       => $user->grade,
                'avatar'      => $user->avatar,
                'is_admin'    => (bool)$user->is_admin,
            ]
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'         => 'required|string',
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah!'
        ]);
    }

    /**
     * Upload custom avatar picture
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $user = User::where('username', $username)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $path = $file->store('avatars', 'public');
            $url = asset('storage/' . $path);
            $user->avatar = $url;
            $user->save();

            return response()->json([
                'success'    => true,
                'message'    => 'Foto profil berhasil diunggah',
                'avatar_url' => $url
            ]);
        }

        if ($request->filled('avatar_base64')) {
            $base64Input = $request->input('avatar_base64');
            $extension = 'jpg';
            $rawBase64 = $base64Input;

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Input, $matches)) {
                $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
                $rawBase64 = substr($base64Input, strpos($base64Input, ',') + 1);
            }

            $imageData = base64_decode($rawBase64, true);
            if ($imageData === false || strlen($imageData) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format gambar tidak valid',
                ], 422);
            }

            if ($user->avatar && str_contains($user->avatar, '/storage/avatars/')) {
                $oldPath = Str::after($user->avatar, '/storage/');
                Storage::disk('public')->delete($oldPath);
            }

            $filename = 'avatars/' . $user->username . '_' . time() . '.' . $extension;
            Storage::disk('public')->put($filename, $imageData);

            $storedPath = '/storage/' . $filename;
            $user->avatar = $storedPath;
            $user->save();

            $avatarUrl = rtrim($request->getSchemeAndHttpHost(), '/') . $storedPath;

            return response()->json([
                'success'    => true,
                'message'    => 'Foto profil berhasil diperbarui',
                'avatar_url' => $avatarUrl,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada file gambar yang dikirim'], 400);
    }
}
