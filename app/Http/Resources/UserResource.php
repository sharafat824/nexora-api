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
            'username' => $this->username,
            'is_admin' => $this->is_admin,
            'phone' => $this->phone,
            'withdrawal_address'=>$this->withdrawal_address,
            'avatar' => $this->avatar ? asset('storage/'.$this->avatar) : null,
            'referral_code' => $this->referral_code,
            'created_at' => $this->created_at,
            'wallet' => WalletResource::make($this->whenLoaded('wallet')),
            'withdrawal_password' => '*****************', // Masked for security,
            'referer' => $this->whenLoaded("referrer")
        ];
    }
}
