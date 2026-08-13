<?php

namespace App\Http\Requests\Notice;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'published_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'published_at' => '公開日',
            'title' => 'タイトル',
            'content' => '本文',
        ];
    }
}
