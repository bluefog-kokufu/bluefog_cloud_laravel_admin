@extends('layouts.app')

@section('content')
<h2 class="pagettl">管理画面トップ</h2>
<div class="panel">
    <div class="card">
        <b style="color:var(--navy)">ようこそ、{{ auth()->user()->name }} さん</b>
        <p class="muted" style="margin-top:8px">
            契約企業管理・お知らせ配信管理・スタッフアカウント管理の各機能は今後追加予定です。
        </p>
    </div>
</div>
@endsection
