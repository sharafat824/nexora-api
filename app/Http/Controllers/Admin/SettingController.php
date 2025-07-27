<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlatformSetting;
use App\Models\CommissionLevel;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $general = PlatformSetting::all()->map(function ($setting) {
            // Ensure logo is a full URL
            if ($setting->key === 'site_logo' && $setting->value) {
                // Remove leading slash to avoid double slashes
                $setting->value = asset(ltrim($setting->value, '/'));
            }

            return $setting;
        });

        $data = [
            'general' => $general,
            'commissions' => CommissionLevel::orderBy('level')->get(),
        ];

        return success($data, "success");
    }


    public function updateGeneral(Request $request)
    {
        foreach ($request->settings as $key => $value) {
            // Cast true/false and numbers appropriately
            if ($value === 'true' || $value === '1')
                $value = true;
            elseif ($value === 'false' || $value === '0')
                $value = false;
            elseif (is_numeric($value))
                $value = $value + 0; // convert to int/float

            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'display_name' => ucfirst(str_replace('_', ' ', $key)),
                    'group' => 'general',
                    'description' => null,
                ]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }


    public function updateCommissions(Request $request)
    {
        foreach ($request->commissions as $item) {
            CommissionLevel::updateOrCreate(
                ['level' => $item['level'], 'type' => $item['type']],
                ['percentage' => $item['percentage']]
            );
        }

        return response()->json(['message' => 'Commission levels updated successfully']);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // Save file in public storage
        $path = $request->file('logo')->store('logos', 'public');

        // Update setting in DB
        PlatformSetting::updateOrCreate(
            ['key' => 'site_logo'],
            [
                'value' => '/storage/' . $path,
                'display_name' => 'Site Logo',
                'group' => 'general',
                'description' => 'Logo of the platform',
            ]
        );

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'logo_url' => asset('storage/' . $path),
        ]);
    }

}
