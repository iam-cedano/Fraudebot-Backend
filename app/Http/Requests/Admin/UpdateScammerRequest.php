<?php

namespace App\Http\Requests\Admin;

class UpdateScammerRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
