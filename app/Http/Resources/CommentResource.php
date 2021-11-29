<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    //Transform the resource into an array.
    //param  \Illuminate\Http\Request  $request
    //return array
    public function toArray($request)
    {
        return [
            'text' => $this->id,
            'user_id' => $this->user_id,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at,
            'comments_attachment_path'=>storage_path('\api_data\comments\\'),
        ];
    }
}
