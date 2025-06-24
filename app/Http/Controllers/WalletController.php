<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\WalletResource;

class WalletController extends Controller
{
      public function getWallet(Request $request)
    {
        $wallet = $request->user()->wallet;

        return new WalletResource($wallet);
    }
}
