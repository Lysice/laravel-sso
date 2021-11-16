<?php

namespace Lysice\LaravelSSO\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $fields = [];
        foreach (config('laravel-sso.userFields') as $key => $value) {
            $fields[$key] = $this->{$value};
        }
        $merged = callConfigFunction(config('laravel-sso.api.getMerged'), [
            'user' => $this->resource
        ]);
        return array_merge($fields, $merged);
    }
}
