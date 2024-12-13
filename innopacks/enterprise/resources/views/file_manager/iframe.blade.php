@extends('panel::layouts.blank')

@section('title', __('enterprise::file_manager.title'))

@include('enterprise::file_manager.main')

@section('page-bottom-btns')
    <button class="btn btn-primary">选择提交</button>
@endsection
