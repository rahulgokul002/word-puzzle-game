<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddWordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'word'      => 'required|string|min:2',
            'puzzle_id' => 'required|integer',
            'code'      => 'required|string',
        ];

    }
    public function messages(): array
    {
        return [
            'word.required'      => 'Please provide a word.',
            'word.min'           => 'Word must be at least 2 letters.',
            'puzzle_id.required' => 'Puzzle ID is required.',
            'code.required'      => 'Puzzle code is required.',
        ];
    }

}
