@extends('front::layouts.app')

@section('body-class', 'page-news')

@section('content')

@include('front::shared.page-head', ['title' => $tag->translation->name])
@include('front::shared.articles')

@endsection

