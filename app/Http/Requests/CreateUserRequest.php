<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->usertype === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'apartment_unit' => 'required|string|max:255',
            'full_address' => 'required|string',
            'usertype' => 'required|in:resident,admin,maintainer',
            'status' => 'required|in:active,inactive,suspended'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'firstname.required' => 'Full name is required',
            'lastname.required' => 'Last name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email address is already registered',
            'phone.required' => 'Phone number is required',
            'apartment_unit.required' => 'Apartment/Unit is required',
            'full_address.required' => 'Full address is required',
            'usertype.required' => 'User type is required',
            'usertype.in' => 'User type must be resident, admin, or maintainer',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be active, inactive, or suspended'
        ];
    }
}
