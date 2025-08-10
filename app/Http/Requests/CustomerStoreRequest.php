<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048'
            ],
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:50'
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:50'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:customers,email'
            ],
            'phone' => [
                'required',
                'numeric',
                'digits_between:10,12',
                'unique:customers,phone'
            ],
            'account_number' => [
                'required',
                'numeric',
                'digits_between:5,10',
            ],
            'about' => [
                'nullable',
                'string',
                'max:500'
            ],
        ];
    }
}
