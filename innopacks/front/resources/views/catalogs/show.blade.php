@extends('front::layouts.app')

@section('body-class', 'page-news')

@section('content')

@include('front::shared.page-head', ['title' => $catalog->translation->title])
@include('front::shared.articles')

@endsection

