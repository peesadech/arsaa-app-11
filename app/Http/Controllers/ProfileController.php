<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'image_base64' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name,
        ];

        if ($request->filled('image_base64')) {
            $base64Image = $request->input('image_base64');
            $image_parts = explode(";base64,", $base64Image);
            $image_base64 = base64_decode($image_parts[1]);

            $fileName = time() . '.jpg';
            $path = 'image/users/' . $fileName;

            if ($user->image_path) {
                $storagePath = str_replace('/storage/', '', $user->image_path);
                Storage::disk('public')->delete($storagePath);
            }

            Storage::disk('public')->put($path, $image_base64);
            $data['image_path'] = '/storage/' . $path;
        }

        $user->update($data);

        return redirect()->back()->with('status', 'Profile updated successfully!');
    }
}
