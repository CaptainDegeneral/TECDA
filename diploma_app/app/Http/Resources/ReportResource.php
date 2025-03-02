<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Report
 */
class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $formattedDate = Carbon::parse($this->created_at)->format('d.m.Y');

        return [
            'id' => $this->id,
            'title' => $this->whenHas('name', function () use ($formattedDate) {
                return "Отчет \"$this->name\" от $formattedDate";
            }),
            'name' => $this->whenHas('name'),
            'data' => $this->whenHas('data'),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
        ];
    }
}
