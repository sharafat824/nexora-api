<?php

namespace App\Http\Controllers\Adimn;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminSettingController extends Controller
{
    // AdminSettingController
public function getAnnouncement()
{
    return Announcement::latest()->first();
}

public function saveAnnouncement(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'message' => 'nullable|string',
        'expected_date' => 'nullable|date',
        'status' => 'required|in:coming_soon,in_progress,released',
    ]);

    Announcement::updateOrCreate(
        ['id' => 1],
        $request->only('title', 'message', 'expected_date', 'status')
    );

    return response()->json(['message' => 'Saved']);
}

}
