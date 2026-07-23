@extends(request()->header('X-Iframe') ? 'panel::layouts.blank' : 'panel::layouts.app')

@section('title', __('panel/media.title'))

@if(!request()->header('X-Iframe'))
    <x-panel::form.right-btns/>
@endif

@include('panel::media.main')
