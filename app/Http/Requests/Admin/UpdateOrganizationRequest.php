<?php

namespace App\Http\Requests\Admin;

class UpdateOrganizationRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'country' => ['sometimes', 'nullable', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
