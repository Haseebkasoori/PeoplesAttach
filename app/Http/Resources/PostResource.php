<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    //Transform the resource into an array.
    //param  \Illuminate\Http\Request  $request
    //return array
    public function toArray($request)
    {
        return [
            'text' => $this->id,
            'user_id' => $this->user_id,
            'visibility' => $this->visibility,
            'attachment' => $this->attachment,
            'created_at' => $this->created_at,
            'post_attachment_path'=>storage_path('api_data\posts\\'),
            'comments_attachment_path'=>storage_path('\api_data\comments\\'),
            'Comments'=> $this->when(!empty($this->Comments()->get()), $this->Comments()->get())
        ];
    }
}
