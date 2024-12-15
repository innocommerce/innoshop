@extends('panel::layouts.blank')

@section('title', __('enterprise::file_manager.title'))

@include('enterprise::file_manager.main')

@section('page-bottom-btns')
    <div class="text-center">
        <button class="btn btn-primary" onclick="window.app.confirmSelection()">选择提交</button>
    </div>
@endsection
