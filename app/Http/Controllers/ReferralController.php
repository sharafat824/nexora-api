<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReferralService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralResource;
use App\Http\Resources\ReferralEarningResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ReferralController extends Controller
{
      protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }


public function index(Request $request)
{
    $team = collect($request->user()->teamWithCommission); // ← cast to collection
    if(!empty($request->input('search'))) {
        $search = $request->input('search');
        $team = $team->filter(function ($member) use ($search) {
            return str_contains(strtolower($member['name']), strtolower($search)) ||
                   str_contains(strtolower($member['email']), strtolower($search));
        });
    }
    $page = $request->input('page', 1);
    $perPage = 8;

    $paginated = new LengthAwarePaginator(
        $team->forPage($page, $perPage)->values(),
        $team->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return response()->json($paginated);
}

    public function earnings(Request $request)
    {
        $earnings = $request->user()->referralEarnings()
            ->with('referredUser')
            ->latest()
            ->paginate(15);

        return ReferralEarningResource::collection($earnings);
    }

    // public function teamStats(Request $request)
    // {
    //     $stats = $this->referralService->getTeamStats($request->user());

    //     return response()->json([
    //         'levels' => $stats,
    //         'referral_link' => route('register', ['ref' => $request->user()->referral_code])
    //     ]);
    // }
}
