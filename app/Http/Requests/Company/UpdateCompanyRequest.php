<?php

namespace App\Http\Requests\Company;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * slugは作成後変更不可のためここには含めない(送信されても無視される)
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'provision_status' => ['required', 'string', Rule::in(array_keys(Company::PROVISION_STATUSES))],
            'memo' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '会社名',
            'contact_name' => '担当者氏名',
            'contact_email' => '担当者メールアドレス(ログインID)',
            'provision_status' => 'プロビジョニング状況',
            'memo' => 'メモ',
        ];
    }
}
