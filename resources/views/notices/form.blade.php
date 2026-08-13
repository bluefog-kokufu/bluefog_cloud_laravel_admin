@extends('layouts.app')

@section('content')
<div class="crumb"><a href="{{ route('admin.dashboard') }}">ホーム</a> / <a href="{{ route('admin.notices.index') }}">お知らせ配信管理</a> / {{ isset($notice) ? 'お知らせ編集' : 'お知らせ追加' }}</div>
<h2 class="pagettl">{{ isset($notice) ? 'お知らせ編集' : 'お知らせ追加' }}</h2>

<div class="panel">
    <form method="POST" action="{{ isset($notice) ? route('admin.notices.update', $notice) : route('admin.notices.store') }}">
        @csrf
        @if (isset($notice))
        @method('PUT')
        @endif

        <div class="field">
            <label for="published_at"><span class="req">必須</span>公開日</label>
            <input id="published_at" name="published_at" type="date" value="{{ old('published_at', isset($notice) ? $notice->published_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
            @error('published_at')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="title"><span class="req">必須</span>タイトル</label>
            <input id="title" name="title" type="text" value="{{ old('title', $notice->title ?? '') }}" required>
            @error('title')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="content">本文</label>
            <textarea id="content" name="content" rows="6">{{ old('content', $notice->content ?? '') }}</textarea>
            @error('content')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label>
                <input type="checkbox" name="is_active" value="1" style="width:auto;" {{ old('is_active', $notice->is_active ?? true) ? 'checked' : '' }}>
                公開する
            </label>
        </div>

        <div class="formfoot">
            <button class="btn" type="submit">保存</button>
            <a class="btn ghost" href="{{ route('admin.notices.index') }}">戻る</a>
        </div>
    </form>
</div>
@endsection
