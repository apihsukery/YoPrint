<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusColors = [
            'pending' => 'bg-gray-100 text-gray-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'failed' => 'bg-red-100 text-red-800',
            'completed' => 'bg-green-100 text-green-800',
        ];

        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'original_name' => $this->original_name,
            'status' => $this->status,
            'status_color' => $statusColors[$this->status] ?? 'bg-gray-100 text-gray-800',
            'created_at' => $this->created_at->format('Y-m-d g:ia'),
            'created_at_timestamp' => $this->created_at->timestamp,
            'relative_time' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d g:ia'),
        ];
    }
}
