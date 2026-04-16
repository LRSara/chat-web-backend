<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|string',
            'nick' => 'required|string|min:3|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'A senha é obrigatória.',
            'nick.required' => 'O nick é obrigatório.',
            'nick.min' => 'O nick deve ter no mínimo 3 caracteres.',
            'nick.max' => 'O nick deve ter no máximo 50 caracteres.',
        ];
    }
}
