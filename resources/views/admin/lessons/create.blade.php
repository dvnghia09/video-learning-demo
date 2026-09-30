@extends('layouts.admin')
@section('title', 'Thêm bài học')
@section('header', 'Thêm bài học')
@section('content')
<div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 max-w-2xl">
    @include('admin.lessons._form')
</div>
@endsection
