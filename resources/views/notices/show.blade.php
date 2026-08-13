@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / <a href="{{ route('admin.notices.index') }}">お知らせ配信管理</a> / お知らせ詳細</div>
<h2 class="pagettl">お知らせ詳細</h2>

<div class="panel">
    <div class="card">
        <div class="field">
            <label>公開日</label>
            <div>{{ $notice->published_at->format('Y.m.d') }}</div>
        </div>
        <div class="field">
            <label>状態</label>
            <div>
                @if ($notice->is_active)
                <span class="badge paid">公開中</span>
                @else
                <span class="badge gray">非公開</span>
                @endif
            </div>
        </div>
        <div class="field">
            <label>タイトル</label>
            <div>{{ $notice->title }}</div>
        </div>
        <div class="field" style="max-width:none;">
            <label>本文</label>
            <div style="white-space:pre-wrap;">{{ $notice->content ?: '(本文なし)' }}</div>
        </div>
    </div>

    <div class="formfoot">
        <a class="btn" href="{{ route('admin.notices.edit', $notice) }}">編集</a>
        <a class="btn ghost" href="{{ route('admin.notices.index') }}">一覧へ戻る</a>
    </div>
</div>
@endsection
