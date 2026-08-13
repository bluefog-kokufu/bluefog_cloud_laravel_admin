@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / <a href="{{ route('admin.companies.index') }}">契約企業管理</a> / 契約企業詳細</div>
<h2 class="pagettl">契約企業詳細</h2>

<div class="panel">
    @if (session('status'))
    <div class="success-inline">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="field">
            <label>会社名</label>
            <div>{{ $company->name }}</div>
        </div>
        <div class="field">
            <label>slug</label>
            <div>{{ $company->slug }}</div>
        </div>
        <div class="field">
            <label>担当者氏名</label>
            <div>{{ $company->contact_name }}</div>
        </div>
        <div class="field">
            <label>担当者メールアドレス(ログインID)</label>
            <div>{{ $company->contact_email }}</div>
        </div>
        <div class="field">
            <label>プロビジョニング状況</label>
            <div>
                @if ($company->provision_status === 'active')
                <span class="badge paid">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] }}</span>
                @elseif ($company->provision_status === 'failed')
                <span class="badge bad">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] }}</span>
                @else
                <span class="badge gray">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] ?? $company->provision_status }}</span>
                @endif
            </div>
        </div>
        @if ($company->provision_error)
        <div class="field" style="max-width:none;">
            <label>直近のプロビジョニングエラー</label>
            <div class="field-error" style="white-space:pre-wrap;">{{ $company->provision_error }}</div>
        </div>
        @endif
        <div class="field" style="max-width:none;">
            <label>メモ</label>
            <div style="white-space:pre-wrap;">{{ $company->memo ?: '(メモなし)' }}</div>
        </div>
    </div>

    <div class="formfoot">
        @if ($company->provision_status === 'pending')
        <form action="{{ route('admin.companies.provision', $company) }}" method="POST" onsubmit="return confirm('front環境の自動生成を開始します。よろしいですか？');">
            @csrf
            <button class="btn accent" type="submit">プロビジョニング開始</button>
        </form>
        @elseif ($company->provision_status === 'failed')
        <form action="{{ route('admin.companies.provision', $company) }}" method="POST" onsubmit="return confirm('途中まで生成されたファイル・DBを削除して、最初からプロビジョニングをやり直します。よろしいですか？');">
            @csrf
            <button class="btn accent" type="submit">プロビジョニングを再実行</button>
        </form>
        @endif
        <a class="btn" href="{{ route('admin.companies.edit', $company) }}">編集</a>
        <a class="btn ghost" href="{{ route('admin.companies.index') }}">一覧へ戻る</a>
    </div>
</div>
@endsection
