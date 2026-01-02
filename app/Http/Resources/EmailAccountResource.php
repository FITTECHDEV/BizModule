<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'data' => [
                'email' => $this->resource['email'] ?? null,
                'domain' => $this->resource['domain'] ?? null,
                'quota' => $this->resource['quota'] ?? null,
                'disk_used' => $this->resource['diskused'] ?? null,
                'disk_quota' => $this->resource['diskquota'] ?? null,
                'created_at' => $this->resource['created_at'] ?? null,
                'updated_at' => $this->resource['updated_at'] ?? null,
                'deleted_at' => $this->resource['deleted_at'] ?? null,
                'deleted' => $this->resource['deleted'] ?? false,
                'password_updated' => $this->resource['password_updated'] ?? false,
            ],
            'mock_mode' => $this->resource['mock'] ?? false,
            'timestamp' => now()->toIso8601String(),
            'meta' => [
                'version' => '1.0',
                'api' => 'cPanel Email Management',
            ],
        ];
    }
}