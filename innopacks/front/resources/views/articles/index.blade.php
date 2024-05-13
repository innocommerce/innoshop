@extends('front::layouts.app')

@section('body-class', 'page-news')

@section('content')

@include('front::shared.page-head', ['title' => '新闻资讯'])
@include('front::shared.articles')

@endsection