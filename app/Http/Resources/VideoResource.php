<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            "resolution_width" => $this->resolution_width,
            "resolution_height" => $this->resolution_height,
            "raw_image_path" => $this->raw_image_path,
            "preview_video_path" => $this->preview_video_path,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
