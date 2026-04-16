@extends('layouts.app')
@section('body-class', 'page-articles')

@section('content')

<x-front-breadcrumb type="route" value="catalogs.index" title="{{ __('front/article.news_classification') }}" />

<div class="container mt-3 mt-md-5">
  <div class="row g-3">
    @foreach($catalogs as $catalog)
    <div class="col-6 col-md-4 col-lg-3">
      <a href="{{ $catalog->url }}" class="text-decoration-none">
        <div class="card h-100 border-0 shadow-sm catalog-card">
          @if($catalog->image)
          <div class="overflow-hidden">
            <img src="{{ image_resize($catalog->image, 400, 400) }}" class="card-img-top w-100" style="object-fit: cover;" alt="{{ $catalog->translation?->title ?? '' }}">
          </div>
          @else
          <div class="card-img-top bg-light d-flex align-items-center justify-content-center catalog-placeholder">
            <i class="bi bi-folder2-open fs-1 text-muted"></i>
          </div>
          @endif
          <div class="card-body py-2 px-3">
            <h6 class="card-title mb-1">{{ $catalog->translation?->title ?? '' }}</h6>
            @if($catalog->translation?->summary)
            <p class="card-text mb-0">{{ sub_string($catalog->translation->summary, 60) }}</p>
            @endif
          </div>
        </div>
      </a>
    </div>
    @endforeach
  </div>

  @if($catalogs->isEmpty())
  @include('shared.no-data', ['text' => '没有数据 ~'])
  @endif
</div>

@endsection
