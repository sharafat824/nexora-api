<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'username' => $this->username,
            'is_admin' => $this->is_admin,
            'is_blocked' => $this->is_blocked,
            'phone' => $this->phone,
            'withdrawal_address'=>$this->withdrawal_address,
            'avatar' =>$this->avatar
            ? asset('uploads/' . $this->avatar)
            : null,
            'referral_code' => $this->referral_code,
            'created_at' => $this->created_at,
            'wallet' => WalletResource::make($this->whenLoaded('wallet')),
            'withdrawal_password' => '*****************', // Masked for security,
            'referer' => $this->whenLoaded("referrer"),
            'country_code' => $this->country_code,
            'country' => $this->country
        ];
    }
}
