@extends('layouts.app')
@section('body-class', 'page-news')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($tag)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($tag)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($tag)->getKeywords())

@section('content')

  <x-front-breadcrumb type="tag" :value="$tag" />

  @hookinsert('tag.show.top')

  @include('shared.articles')

  @hookinsert('tag.show.top')

@endsection

