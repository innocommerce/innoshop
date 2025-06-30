@extends('layouts.app')
@section('body-class', 'page-news-details')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@section('content')
  @if($page->show_breadcrumb)
      <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @if(isset($result))
    {!! $result !!}
  @else
    <div class="container mt-3 mt-md-5">
      <div class="row justify-content-center">
        <div class="col-12">
          <div class="newest-box">
            <div class="newes-title">{{ $page->translation->title }}</div>
            <div class="newes-content">
              {!! $page->translation->content !!}
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @hookinsert('page.show.bottom')
@endsection
