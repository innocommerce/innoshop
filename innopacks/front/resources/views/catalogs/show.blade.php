@extends('layouts.app')
@section('body-class', 'page-articles')

@section('title', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getTitle())
@section('description', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getDescription())
@section('keywords', \InnoShop\Common\Libraries\MetaInfo::getInstance($catalog)->getKeywords())

@section('content')

  <x-front-breadcrumb type="catalog" :value="$catalog"/>

  @hookinsert('catalog.show.top')

  <div class="container mt-3 mt-md-5">
    <div class="row">
      {{-- 主内容区 --}}
      <div class="col-12 col-md-9">
        @if($catalog->image || $catalog->translation?->summary)
        <div class="pb-3 mb-4 border-bottom">
          <div class="row align-items-center">
            @if($catalog->image)
            <div class="col-4 col-md-3 mb-3 mb-md-0">
              <img src="{{ image_resize($catalog->image, 200, 200) }}" alt="{{ $catalog->translation?->title ?? '' }}" class="img-fluid rounded-3">
            </div>
            @endif
            <div class="{{ $catalog->image ? 'col-8 col-md-9' : 'col-12' }}">
              <h1 class="catalog-intro-title mb-2">{{ $catalog->translation?->title ?? '' }}</h1>
              @if($catalog->translation?->summary)
              <p class="catalog-intro-summary mb-0">{{ $catalog->translation->summary }}</p>
              @endif
            </div>
          </div>
        </div>
        @endif

        @include('shared.articles_list')
      </div>

      {{-- 侧边栏 --}}
      <div class="col-12 col-md-3">
        @include('shared.articles_sidebar', ['currentCatalogId' => $catalog->id])
      </div>
    </div>
  </div>

  @hookinsert('catalog.show.bottom')

@endsection
