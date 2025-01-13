@extends('layouts.app')
@section('body-class', 'page-news')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getKeywords())

@section('content')

  <x-front-breadcrumb type="catalog" :value="$catalog"/>

  @hookinsert('catalog.show.top')

  @include('shared.articles')

  @hookinsert('catalog.show.bottom')

@endsection

