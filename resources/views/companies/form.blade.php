@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / <a href="{{ route('admin.companies.index') }}">契約企業管理</a> / {{ isset($company) ? '契約企業編集' : '契約企業追加' }}</div>
<h2 class="pagettl">{{ isset($company) ? '契約企業編集' : '契約企業追加' }}</h2>

<div class="panel">
    <form method="POST" action="{{ isset($company) ? route('admin.companies.update', $company) : route('admin.companies.store') }}">
        @csrf
        @if (isset($company))
        @method('PUT')
        @endif

        <div class="field">
            <label for="name"><span class="req">必須</span>会社名</label>
            <input id="name" name="name" type="text" value="{{ old('name', $company->name ?? '') }}" required>
            @error('name')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="slug"><span class="req">必須</span>slug</label>
            @if (isset($company))
            <input type="text" value="{{ $company->slug }}" disabled>
            <div class="muted" style="margin-top:4px;">slugはfront環境の自動生成(ディレクトリ名・DB名・サブドメイン)に使われるため作成後は変更できません。</div>
            @else
            <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="例: sample-corp" required>
            <div class="muted" style="margin-top:4px;">半角英小文字・数字・ハイフンのみ、3〜32文字。作成後は変更できません。</div>
            @endif
            @error('slug')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="contact_name"><span class="req">必須</span>担当者氏名</label>
            <input id="contact_name" name="contact_name" type="text" value="{{ old('contact_name', $company->contact_name ?? '') }}" required>
            <div class="muted" style="margin-top:4px;">front側の初回ユーザーの表示名として登録されます。</div>
            @error('contact_name')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="contact_email"><span class="req">必須</span>担当者メールアドレス(ログインID)</label>
            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $company->contact_email ?? '') }}" required>
            <div class="muted" style="margin-top:4px;">front側のログインIDになります。プロビジョニング完了後、このアドレス宛にパスワード設定メールが送信されます。</div>
            @error('contact_email')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        @if (isset($company))
        <div class="field">
            <label for="provision_status"><span class="req">必須</span>プロビジョニング状況</label>
            <select id="provision_status" name="provision_status">
                @foreach (\App\Models\Company::PROVISION_STATUSES as $value => $label)
                <option value="{{ $value }}" {{ old('provision_status', $company->provision_status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('provision_status')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <div class="field">
            <label for="memo">メモ</label>
            <textarea id="memo" name="memo" rows="4">{{ old('memo', $company->memo ?? '') }}</textarea>
            @error('memo')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="formfoot">
            <button class="btn" type="submit">保存</button>
            <a class="btn ghost" href="{{ route('admin.companies.index') }}">戻る</a>
        </div>
    </form>
</div>
@endsection
