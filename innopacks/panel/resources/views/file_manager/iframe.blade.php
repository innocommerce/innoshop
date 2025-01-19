@extends('panel::layouts.blank')

@section('title', __('panel/menu.file_manager'))

@include('panel::file_manager.main')

@section('page-bottom-btns')
    <div class="text-center">
        <button class="btn btn-primary" onclick="window.app.confirmSelection()">选择提交</button>
    </div>
@endsection
