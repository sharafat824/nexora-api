<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'balance' => $this->balance,
            'active_balance' => $this->active_balance,
            'total_earnings' => $this->total_earnings,
            'total_withdrawals' => $this->total_withdrawals
        ];
    }
}
