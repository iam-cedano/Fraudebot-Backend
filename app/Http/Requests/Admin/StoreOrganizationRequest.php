<?php

namespace App\Http\Requests\Admin;

class StoreOrganizationRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'size:2'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
