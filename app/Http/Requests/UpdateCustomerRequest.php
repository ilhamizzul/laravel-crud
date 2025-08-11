<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,' . $this->route('customer')],
            'phone' => ['required', 'numeric', 'digits_between:10,12', 'unique:customers,phone,' . $this->route('customer')],
            'account_number' => ['required', 'numeric', 'digits_between:5,10'],
            'about' => ['nullable', 'string', 'max:500'],
        ];
    }
}
