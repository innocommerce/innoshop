@extends('layouts.app')

@section('body-class', 'page-news')

@section('content')

  <x-front-breadcrumb type="tag" :value="$tag" />

  @hookinsert('tag.show.top')

  @include('shared.articles')

  @hookinsert('tag.show.top')

@endsection

