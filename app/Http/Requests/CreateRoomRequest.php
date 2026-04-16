<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3|max:100|unique:rooms,name',
            'password' => 'required|string|min:3|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da sala é obrigatório.',
            'name.min' => 'O nome da sala deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome da sala deve ter no máximo 100 caracteres.',
            'name.unique' => 'Já existe uma sala com este nome.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 3 caracteres.',
        ];
    }
}
