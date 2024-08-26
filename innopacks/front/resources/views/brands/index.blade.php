@extends('layouts.app')
@section('body-class', 'page-brands')

@section('content')
<x-front-breadcrumb type="route" value="brands.index" title="{{ __('front/product.brand') }}" />

@hookinsert('brand.index.top')

<div class="container">
  <div class="btn-group brand-group" role="group">
    @foreach($brands as $first => $items)
      <a href="{{ front_route('brands.index') }}#page-brands-{{ $first }}" class="btn">{{ $first }}</a>
    @endforeach
  </div>
  <div class="brands-wrap">
    @foreach($brands as $first=>$items)
    <div class="item" id="page-brands-{{ $first }}">
      <span class="fw-bold fs-4 mb-2">{{ $first }}</span>
      <ul>
        @foreach($items as $brand)
        <li>
          <a href="{{ $brand->url }}" class="text-secondary">
            <div class="img"><img src="{{ image_resize($brand->logo, 200, 200) }}" class="img-fluid" /></div>
            <span>{{ $brand->name }} </span>
          </a>
        </li>
        @endforeach
      </ul>
    </div>
    @endforeach
  </div>
</div>

@hookinsert('brand.index.bottom')

@endsection