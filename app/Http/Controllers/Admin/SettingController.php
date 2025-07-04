<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlatformSetting;
use App\Models\CommissionLevel;

class SettingController extends Controller
{
    public function index()
    {
        $data = [
         'general' => PlatformSetting::all(),
            'commissions' => CommissionLevel::orderBy('level')->get(),
        ];

        return success($data, "success");
    }

    public function updateGeneral(Request $request)
    {
        foreach ($request->settings as $key => $value) {
          PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'display_name' => $data['display_name'] ?? ucfirst(str_replace('_', ' ', $key)),
                    'group' => $data['group'] ?? 'general',
                    'description' => $data['description'] ?? null,
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
}
