<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeditationRequest extends FormRequest
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
            'title' => [
                'required',
                'max:255'
            ],
            'description' => [
                'required',
                'max:255'
            ],
            'meditationType' => [
                'required',
                Rule::in(['Basic Meditation', 'Advanced Meditation', 'Short Meditation'])
            ],
            'thumbnail' => [
                File::types(['jpeg', 'png', 'jpg'])
                ->max('5mb'),
            ],
            'video' => [
                File::types(['mp4'])
                ->min('1mb')
                ->max('1gb'),
            ]
        ];
    }
}
