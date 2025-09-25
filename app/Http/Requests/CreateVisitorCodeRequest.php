<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVisitorCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->usertype === 'resident';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visitor_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'destination' => 'required|string|max:255',
            'number_of_visitors' => 'required|integer|min:1|max:10',
            'expires_at' => 'required|date|after:now',
            'additional_notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'visitor_name.required' => 'Visitor name is required',
            'destination.required' => 'Destination is required',
            'number_of_visitors.required' => 'Number of visitors is required',
            'number_of_visitors.min' => 'Number of visitors must be at least 1',
            'number_of_visitors.max' => 'Number of visitors cannot exceed 10',
            'expires_at.required' => 'Expiration time is required',
            'expires_at.date' => 'Please enter a valid date and time',
            'expires_at.after' => 'Expiration time must be in the future'
        ];
    }
}
