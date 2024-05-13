@extends('layouts.app')
@section('body-class', 'page-categories')

@section('content')
  <x-front-breadcrumb type="category" :value="$category"/>

  @hookinsert('category.show.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-3">
        @include('shared.filter_sidebar')
      </div>
      <div class="col-12 col-md-9">
        <div class="category-wrap">
          <div class="top-order-wrap">
            <div class="d-none d-md-block">
              Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} items
            </div>
            <div class="right">
              <div class="order-item">
                <span class="d-none d-md-block">Sort:</span>
                <select class="form-select">
                  <option selected>Default sorting</option>
                  <option value="1">One</option>
                  <option value="2">Two</option>
                  <option value="3">Three</option>
                </select>
              </div>
              <div class="order-item">
                <span class="d-none d-md-block">Show:</span>
                <select class="form-select">
                  <option selected>20</option>
                  <option value="1">40</option>
                  <option value="2">60</option>
                  <option value="3">80</option>
                </select>
              </div>
              <div class="order-item">
                <a href="" class="order-icon active"><i class="bi bi-grid"></i></a>
                <a href="" class="order-icon"><i class="bi bi-list"></i></a>
              </div>
            </div>
          </div>
        </div>

        <div class="row gx-3 gx-lg-4">
          @foreach ($products as $product)
            <div class="col-6 col-md-4">
              @include('shared.product')
            </div>
          @endforeach
        </div>

        {{ $products->links('panel::vendor/pagination/bootstrap-4') }}
      </div>
    </div>
  </div>

  @hookinsert('category.show.bottom')

@endsection