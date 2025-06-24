<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReferralService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralResource;
use App\Http\Resources\ReferralEarningResource;

class ReferralController extends Controller
{
      protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        $referrals = $request->user()->referrals()
            ->with('wallet')
            ->latest()
            ->paginate(15);

        return ReferralResource::collection($referrals);
    }

    public function earnings(Request $request)
    {
        $earnings = $request->user()->referralEarnings()
            ->with('referredUser')
            ->latest()
            ->paginate(15);

        return ReferralEarningResource::collection($earnings);
    }

    public function teamStats(Request $request)
    {
        $stats = $this->referralService->getTeamStats($request->user());

        return response()->json([
            'levels' => $stats,
            'referral_link' => route('register', ['ref' => $request->user()->referral_code])
        ]);
    }
}
