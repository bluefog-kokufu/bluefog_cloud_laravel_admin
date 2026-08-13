@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / 契約企業管理</div>
<h2 class="pagettl">契約企業管理</h2>

<div class="panel">
    <div class="toolbar" style="justify-content:space-between;">
        <a class="btn accent" href="{{ route('admin.companies.create') }}">新規追加</a>
        <a class="btn ghost" href="{{ route('admin.dashboard') }}">ダッシュボードへ戻る</a>
    </div>

    @if (session('status'))
    <div class="success-inline">{{ session('status') }}</div>
    @endif

    <table class="list">
        <thead>
            <tr>
                <th>会社名</th>
                <th>slug</th>
                <th>プロビジョニング状況</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($companies as $company)
            <tr>
                <td><a href="{{ route('admin.companies.show', $company) }}">{{ $company->name }}</a></td>
                <td>{{ $company->slug }}</td>
                <td>
                    @if ($company->provision_status === 'active')
                    <span class="badge paid">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] }}</span>
                    @elseif ($company->provision_status === 'failed')
                    <span class="badge bad">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] }}</span>
                    @else
                    <span class="badge gray">{{ \App\Models\Company::PROVISION_STATUSES[$company->provision_status] ?? $company->provision_status }}</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn small">詳細</a>
                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn small">編集</a>
                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display:inline;" onsubmit="return confirm('削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button class="btn danger small" type="submit">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="muted">契約企業がありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pager">
        @if ($companies->onFirstPage())
        <button class="btn small" type="button" disabled>&lt;</button>
        @else
        <a class="btn small" href="{{ $companies->previousPageUrl() }}">&lt;</a>
        @endif

        <button class="btn small cur" type="button">{{ $companies->currentPage() }}</button>

        @if ($companies->hasMorePages())
        <a class="btn small" href="{{ $companies->nextPageUrl() }}">&gt;</a>
        @else
        <button class="btn small" type="button" disabled>&gt;</button>
        @endif
    </div>
</div>
@endsection
