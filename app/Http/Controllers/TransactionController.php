<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
     public function index(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->latest()
            ->paginate(15);

        return TransactionResource::collection($transactions);
    }
}
