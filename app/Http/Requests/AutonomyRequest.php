<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutonomyRequest extends FormRequest
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
            'site_id' => 'required|exists:sites,id',
            'battery_type' => "required|in:tubular,'dry cell',lithium",
            'number_of_hours' => 'required|integer|min:1',
            'load_power' => 'nullable|integer|min:0',
            'request_type' => 'nullable|string|max:255',
            'battery_capacity' => 'nullable|numeric|min:0',
            'battery_efficiency' => 'nullable|numeric|min:0',
            'battery_voltage' => 'nullable|numeric|min:0',
        ];
    }
}
