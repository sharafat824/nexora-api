<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralEarningResource extends JsonResource
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
            'amount' => $this->amount,
            'level' => $this->level,
            'type' => $this->type,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'referred_user' => $this->whenLoaded('referredUser', function () {
                return [
                    'id' => $this->referredUser->id,
                    'name' => $this->referredUser->name,
                    'username' => $this->referredUser->username
                ];
            })
        ];
    }
}
