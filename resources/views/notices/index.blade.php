@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / お知らせ配信管理</div>
<h2 class="pagettl">お知らせ配信管理</h2>

<div class="panel">
    <div class="toolbar" style="justify-content:space-between;">
        <a class="btn accent" href="{{ route('admin.notices.create') }}">新規追加</a>
        <a class="btn ghost" href="{{ route('admin.dashboard') }}">ダッシュボードへ戻る</a>
    </div>

    @if (session('status'))
    <div class="success-inline">{{ session('status') }}</div>
    @endif

    <table class="list">
        <thead>
            <tr>
                <th>公開日</th>
                <th>タイトル</th>
                <th>状態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($notices as $notice)
            <tr>
                <td>{{ $notice->published_at->format('Y.m.d') }}</td>
                <td><a href="{{ route('admin.notices.show', $notice) }}">{{ $notice->title }}</a></td>
                <td>
                    @if ($notice->is_active)
                    <span class="badge paid">公開中</span>
                    @else
                    <span class="badge gray">非公開</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.notices.show', $notice) }}" class="btn small">詳細</a>
                    <a href="{{ route('admin.notices.edit', $notice) }}" class="btn small">編集</a>
                    <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" style="display:inline;" onsubmit="return confirm('削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button class="btn danger small" type="submit">削除</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="muted">お知らせがありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pager">
        @if ($notices->onFirstPage())
        <button class="btn small" type="button" disabled>&lt;</button>
        @else
        <a class="btn small" href="{{ $notices->previousPageUrl() }}">&lt;</a>
        @endif

        <button class="btn small cur" type="button">{{ $notices->currentPage() }}</button>

        @if ($notices->hasMorePages())
        <a class="btn small" href="{{ $notices->nextPageUrl() }}">&gt;</a>
        @else
        <button class="btn small" type="button" disabled>&gt;</button>
        @endif
    </div>
</div>
@endsection
