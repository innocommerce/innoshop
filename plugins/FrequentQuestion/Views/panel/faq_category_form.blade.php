@extends('panel::layouts.app')

@section('title',  __('FrequentQuestion::common.faq_category') )
<x-panel::form.right-btns/>

@push('header')
  <script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
  <form class="needs-validation" novalidate id="app-form"
        action="{{ $faq_category->id ? panel_route('faq_categories.update', [$faq_category->id]) : panel_route('faq_categories.store') }}"
        method="POST">
    @csrf
    @method($faq_category->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-9">
        <div class="card mb-3">
          <div class="card-body">
            <x-common-form-input title="分类名" name="title"
                                 value="{{ old('title', $faq_category->title) }}" required
                                 placeholder="分类名"/>
                                  
            <div class="mb-3">
              <label class="form-label">关联产品</label>                     
              <input type="text" id="product-autocomplete"
                     value="{{ old('product_name', isset($faq_category->product) ? $faq_category->product->translation->name : '') }}" 
                     placeholder="关联产品"
                     class="form-control">
              <input type="hidden" name="product_id" value="{{ old('product_id', $faq_category->product_id) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">关联文章</label>                     
              <input type="text" id="article-autocomplete"
                     value="{{ old('article_name', isset($faq_category->article) ? $faq_category->article->translation->title : '') }}" 
                     placeholder="关联文章"
                     class="form-control">
              <input type="hidden" name="article_id" value="{{ old('article_id', $faq_category->article_id) }}">
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3 ps-md-0">
        <div class="card">
          <div class="card-body">
            <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active"
                                        :value="old('active', $faq_category->active ?? true)"
                                        placeholder="{{ __('panel/common.whether_enable') }}"/>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>
@endsection

@push('footer')
  <script>
    $(function() {
      $('#product-autocomplete').autocomplete({
        'source': function (request, response) {
          const keyword = encodeURIComponent(request.term);
          var name = document.getElementById('product-autocomplete').value;
          axios.get(`${urls.api_base}/products/autocomplete?keyword=${name}`, null, {hload: true})
            .then((res) => {
              response($.map(res.data, function (item) {
                return {label: item['name'], value: item['id']};
              }));
            }).catch((error) => {
              console.error('请求出错:', error);
            });
        },
        'select': function (item) {
          $('#product-autocomplete').val(item.label);
          $('input[name="product_id"]').val(item.value);
          return false;
        }
      });
      $('#article-autocomplete').autocomplete({
        'source': function (request, response) {
          const keyword = encodeURIComponent(request.term);
          var name = document.getElementById('article-autocomplete').value;
          axios.get(`${urls.api_base}/articles/autocomplete?keyword=${name}`, null, {hload: true})
            .then((res) => {
              response($.map(res.data, function (item) {
                return {label: item['name'], value: item['id']};
              }));
            }).catch((error) => {
              console.error('请求出错:', error);
            });
        },
        'select': function (item) {
          $('#article-autocomplete').val(item.label);
          $('input[name="article_id"]').val(item.value);
          return false;
        }
      });
    });
  </script>
@endpush
