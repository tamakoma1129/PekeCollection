<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "duration" => $this->duration,
            "raw_image_path" => $this->raw_image_path,
            "preview_audio_path" => $this->preview_audio_path,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
