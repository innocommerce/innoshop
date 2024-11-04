@extends('layouts.app')

@section('body-class', 'page-news')

@section('content')

  <x-front-breadcrumb type="catalog" :value="$catalog"/>

  @hookinsert('catalog.show.top')

  @include('shared.articles')

  @hookinsert('catalog.show.bottom')

@endsection

