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
            'joined_at' => $this->created_at->format('Y-m-d H:i:s'),
            'level' => $this->when(isset($this->level), $this->level),
            'commission' => $this->when(isset($this->commission), function () {
                return [
                    'amount' => $this->commission,
                    'formatted' => '$' . number_format($this->commission, 2)
                ];
            }),
            'wallet' => $this->whenLoaded('wallet', function () {
                return [
                    'balance' => $this->wallet->balance,
                    'formatted_balance' => '$' . number_format($this->wallet->balance, 2)
                ];
            }),
            'status' => $this->when(isset($this->is_active), function () {
                return $this->is_active ? 'Active' : 'Inactive';
            }),
            'referral_code' => $this->when(isset($this->referral_code), $this->referral_code),
            'avatar' => $this->when(isset($this->avatar), $this->avatar),
        ];
    }
}
