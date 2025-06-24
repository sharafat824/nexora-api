<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'level' => $this->level,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'formatted_amount' => ($this->amount > 0 ? '+' : '').number_format($this->amount, 2)
        ];
    }
}
