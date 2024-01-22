<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'chat_id' => $this->chat_id,
            'message' => $this->chat_id,
            'media_URL' => $this->chat_id,
            'message_type' => $this->chat_id,
            'is_read' => $this->chat_id,
            'is_edite' => $this->chat_id,
            'is_starred' => $this->chat_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}