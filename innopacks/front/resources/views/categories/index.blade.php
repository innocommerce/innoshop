@extends('layouts.app')
@section('body-class', 'page-categories')

@section('content')
<x-front-breadcrumb type="route" value="categories.index" title="{{ __('front/category.categories') }}" />

@hookinsert('category.index.top')

<div class="container">
  @if($keyword)
    <div class="mb-4">
      <h2 class="h4">{{ __('front/common.search_results') }}: "{{ $keyword }}"</h2>
      <p class="text-muted">{{ __('front/common.found_categories', ['count' => $categories->total()]) }}</p>
    </div>
  @endif

  @if($categories->count() > 0)
    <div class="row g-4">
      @foreach($categories as $category)
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            @if($category->image)
              <a href="{{ $category->url }}">
                <img src="{{ image_resize($category->image, 300, 200) }}" 
                     class="card-img-top" 
                     alt="{{ $category->fallbackName() }}">
              </a>
            @endif
            <div class="card-body text-center">
              <h5 class="card-title">
                <a href="{{ $category->url }}" class="text-decoration-none">
                  {{ $category->fallbackName() }}
                </a>
              </h5>
              @if($category->translation && $category->translation->description)
                <p class="card-text text-muted small">
                  {{ mb_substr(strip_tags($category->translation->description), 0, 100) }}...
                </p>
              @endif
              <a href="{{ $category->url }}" class="btn btn-sm btn-outline-primary">
                {{ __('front/common.view_more') }}
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{ $categories->appends(request()->query())->links('panel::vendor/pagination/bootstrap-4') }}
  @else
    <div class="text-center py-5">
      <x-common-no-data />
      @if($keyword)
        <p class="text-muted mt-3">{{ __('front/common.no_search_results') }}</p>
      @endif
    </div>
  @endif
</div>

@hookinsert('category.index.bottom')

@endsection

