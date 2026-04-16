@extends('layouts.app')

@section('body-class', 'page-articles')

@section('content')

<x-front-breadcrumb type="route" value="articles.index" title="{{ __('front/article.articles') }}" />

@hookinsert('article.index.top')

@include('shared.articles')

@hookinsert('article.index.bottom')

@endsection