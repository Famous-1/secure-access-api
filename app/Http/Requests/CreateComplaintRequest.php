<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can create complaints
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:complaint,suggestion',
            'category' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Type is required',
            'type.in' => 'Type must be either complaint or suggestion',
            'category.required' => 'Category is required',
            'severity.required' => 'Severity is required',
            'severity.in' => 'Severity must be low, medium, high, or critical',
            'title.required' => 'Title is required',
            'title.max' => 'Title cannot exceed 255 characters',
            'description.required' => 'Description is required',
            'description.max' => 'Description cannot exceed 2000 characters'
        ];
    }
}
