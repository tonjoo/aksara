<?php

namespace Plugins\SampleMaster\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \UserCapability::hasCapability('add-master-store');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'store_name' => 'required|string',
            'store_phone' => 'nullable|string',
            'store_address' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}

