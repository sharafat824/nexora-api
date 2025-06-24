<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'joined_at' => $this->created_at->format('Y-m-d'),
            'wallet' => $this->whenLoaded('wallet', function () {
                return [
                    'balance' => $this->wallet->balance
                ];
            })
        ];
    }
}
