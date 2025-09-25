<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MCQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'correct_answers' => 'required|array|min:1',
            'correct_answers.*' => 'integer|min:0', 
        ];
    }
}
