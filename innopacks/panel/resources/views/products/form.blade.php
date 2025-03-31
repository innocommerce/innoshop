@extends('panel::layouts.app')
@section('body-class', 'page-product-form')
@section('title', __('panel/menu.products'))

<x-panel::form.right-btns formid="product-form" />

@php
  $weightClasses = [
      ['id' => 0, 'name' => 'g'],
      ['id' => 1, 'name' => 'kg'],
      ['id' => 2, 'name' => 'lb'],
      ['id' => 3, 'name' => 'oz'],
  ];
@endphp

@section('content')
  <form class="needs-validation no-load" novalidate
    action="{{ $product->id ? panel_route('products.update', [$product->id]) : panel_route('products.store') }}"
    method="POST" id="product-form">
    @csrf
    @method($product->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-12">
        <div class="card mb-3">
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                  type="button" role="tab" aria-controls="basic-tab-pane"
                  aria-selected="true">{{ __('panel/product.basic_information') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="translation-tab" data-bs-toggle="tab" data-bs-target="#translation-tab-pane"
                  type="button" role="tab" aria-controls="translation-tab-pane"
                  aria-selected="false">{{ __('panel/product.product_description') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="specification-tab" data-bs-toggle="tab"
                  data-bs-target="#specification-tab-pane" type="button" role="tab"
                  aria-controls="specification-tab-pane"
                  aria-selected="false">{{ __('panel/product.specification_attribute') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="addition-tab" data-bs-toggle="tab" data-bs-target="#addition-tab-pane"
                  type="button" role="tab" aria-controls="addition-tab-pane"
                  aria-selected="false">{{ __('panel/product.extend_information') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button"
                  role="tab" aria-controls="seo-tab-pane" aria-selected="false">{{ __('panel/product.seo') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="relation-tab" data-bs-toggle="tab" data-bs-target="#relation-tab-pane"
                  type="button" role="tab" aria-controls="relation-tab-pane"
                  aria-selected="false">{{ __('panel/product.related_products') }}</button>
              </li>
              @hookinsert('panel.product.edit.tab.nav.bottom')
            </ul>

            <div class="tab-content" id="myTabContent">
              @include('panel::products.form.tab_pane_basic', $product)

              @include('panel::products.form.tab_pane_content', $product)

              @include('panel::products.form.tab_pane_specification', $product)

              @include('panel::products.form.tab_pane_addition', $product)

              @include('panel::products.form.tab_pane_seo', $product)

              @include('panel::products.form.tab_pane_related', $product)

              @hookinsert('panel.product.edit.tab.pane.bottom')
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('footer')
  <script>
    $(document).ready(function() {
      $('#product-form').on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
        }
      });

      $('.category-search input').on('input', function() {
        const val = $(this).val().trim();
        var lists = $('.category-select li');
        lists.each(function() {
          var text = $(this).find('.name').text();
          if (text.indexOf(val) > -1) {
            $(this).show();
            $(this).find('.name').html(text.replace(val, '<span style="color: red;">' + val + '</span>'));
          } else {
            $(this).hide();
          }
        });
      });
    });
  </script>
@endpush
