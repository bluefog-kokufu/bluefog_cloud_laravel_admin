@extends('layouts.app')

@section('content')
<h2 class="pagettl">管理画面トップ</h2>
<div class="panel">
    <div class="card">
        <b style="color:var(--navy)">ようこそ、{{ auth()->user()->name }} さん</b>
    </div>
</div>
@endsection