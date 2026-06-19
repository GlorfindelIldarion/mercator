<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateSiteRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize() : bool
    {
        return $this->authorizeEdit();
    }

    public function rules() : array
    {
        return [
            'name' => [
                'min:3',
                'max:32',
                'required',
                Rule::unique('sites')
                    ->ignore($this->route('site')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'iconFile' => ['nullable', 'file', 'mimes:png', 'max:65535'],
            'description' => ['nullable', 'string'],
        ];
    }
}
