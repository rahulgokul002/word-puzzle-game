<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPuzzleRequest extends FormRequest
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
            'puzzle_id' => 'required|integer|exists:puzzles,id',
            'user_id'   => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'puzzle_id.required' => 'Puzzle ID is required.',
            'puzzle_id.integer'  => 'Puzzle ID must be a number.',
            'puzzle_id.exists'   => 'Puzzle does not exist.',

            'user_id.required'   => 'User ID is required.',
            'user_id.integer'    => 'User ID must be a number.',
            'user_id.exists'     => 'User does not exist.',
        ];
    }
}
