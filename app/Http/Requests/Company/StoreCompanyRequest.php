<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:32',
                'regex:/^[a-z0-9][a-z0-9-]{2,31}$/',
                Rule::notIn(config('tenant.reserved_slugs')),
                Rule::unique('companies', 'slug'),
            ],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'memo' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '会社名',
            'slug' => 'slug',
            'contact_name' => '担当者氏名',
            'contact_email' => '担当者メールアドレス(ログインID)',
            'memo' => 'メモ',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'slugは半角英小文字・数字・ハイフンのみ、3〜32文字で入力してください。',
            'slug.not_in' => 'このslugは予約語のため使用できません。',
        ];
    }
}
