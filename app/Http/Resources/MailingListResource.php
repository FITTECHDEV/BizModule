<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailingListResource extends JsonResource
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
                'list_name' => $this->resource['list_name'] ?? $this->resource['list'] ?? null,
                'domain' => $this->resource['domain'] ?? null,
                'privacy' => $this->resource['privacy'] ?? null,
                'subscribers' => $this->resource['subscribers'] ?? null,
                'subscriber' => $this->resource['subscriber'] ?? null,
                'created_at' => $this->resource['created_at'] ?? null,
                'deleted_at' => $this->resource['deleted_at'] ?? null,
                'added_at' => $this->resource['added_at'] ?? null,
                'removed_at' => $this->resource['removed_at'] ?? null,
                'deleted' => $this->resource['deleted'] ?? false,
                'added' => $this->resource['added'] ?? false,
                'removed' => $this->resource['removed'] ?? false,
            ],
            'mock_mode' => $this->resource['mock'] ?? false,
            'timestamp' => now()->toIso8601String(),
            'meta' => [
                'version' => '1.0',
                'api' => 'cPanel Mailing List Management',
            ],
        ];
    }
}