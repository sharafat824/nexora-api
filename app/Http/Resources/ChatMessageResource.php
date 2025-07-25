<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
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
            'message' => $this->message,
            'direction' => $this->direction,
            'read' => $this->read,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'file' => $this->file ? asset('storage/' . $this->file) : null,

        ];
    }
}
