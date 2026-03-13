<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([
                'app_name' => 'My Application',
            ]);
        }
        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_logo' => 'nullable|image|max:10240', // 10MB Max
            'theme' => 'required|string|in:light,dark',
            'registration_enabled' => 'nullable',
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_redirect_url' => 'nullable|string|max:255',
            'google_login_enabled' => 'nullable',
            'facebook_client_id' => 'nullable|string|max:255',
            'facebook_client_secret' => 'nullable|string|max:255',
            'facebook_redirect_url' => 'nullable|string|max:255',
            'facebook_login_enabled' => 'nullable',
        ]);

        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
        }

        $setting->app_name = $request->app_name;
        $setting->theme = $request->theme;
        $setting->registration_enabled = $request->has('registration_enabled');
        $setting->google_login_enabled = $request->has('google_login_enabled');
        $setting->facebook_login_enabled = $request->has('facebook_login_enabled');
        $setting->google_client_id = $request->google_client_id;
        $setting->google_client_secret = $request->google_client_secret;
        $setting->google_redirect_url = $request->google_redirect_url;
        $setting->facebook_client_id = $request->facebook_client_id;
        $setting->facebook_client_secret = $request->facebook_client_secret;
        $setting->facebook_redirect_url = $request->facebook_redirect_url;

        if ($request->hasFile('app_logo')) {
            // Delete old logo if exists
            if ($setting->app_logo && Storage::exists('public/' . $setting->app_logo)) {
                Storage::delete('public/' . $setting->app_logo);
            }

            $path = $request->file('app_logo')->store('settings', 'public');
            $setting->app_logo = $path; // Store relative path, accessor will handle full URL if needed
        } elseif ($request->input('remove_logo') == '1') {
             if ($setting->app_logo && Storage::exists('public/' . $setting->app_logo)) {
                Storage::delete('public/' . $setting->app_logo);
            }
            $setting->app_logo = null;
        }

        $setting->save();

        return redirect()->route('admin.settings.index')->with('status', 'Settings updated successfully!');
    }
}
