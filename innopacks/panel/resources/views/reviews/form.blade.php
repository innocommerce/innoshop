@extends('panel::layouts.app')

@section('title', __('panel/review.review'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ $review->id ? __('panel/common.edit') : __('panel/common.create') }} {{ __('panel/review.review') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $review->id ? panel_route('reviews.update', [$review->id]) : panel_route('reviews.store') }}"
      method="POST">
      @csrf
      @method($review->id ? 'PUT' : 'POST')

      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">
              {{ __('panel/review.product') }}
              <span class="text-danger">*</span>
            </label>
            <input type="text" id="product-autocomplete"
                   value="{{ old('product_name', isset($review->product) ? $review->product->translation->name : '') }}" 
                   placeholder="{{ __('panel/review.product') }}"
                   class="form-control"
                   required>
            <input type="hidden" name="product_id" value="{{ old('product_id', $review->product_id) }}" required>
            <div class="invalid-feedback">
              {{ __('panel/review.please_select_product') }}
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label class="form-label">
              {{ __('panel/review.customer') }}
              <span class="text-danger">*</span>
            </label>
            <input type="text" id="customer-autocomplete"
                   value="{{ old('customer_name', isset($review->customer) ? $review->customer->name : '') }}" 
                   placeholder="{{ __('panel/review.customer') }}"
                   class="form-control"
                   required>
            <input type="hidden" name="customer_id" value="{{ old('customer_id', $review->customer_id) }}" required>
            <div class="invalid-feedback">
              {{ __('panel/review.please_select_customer') }}
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="order_item_id" value="0">

      <div class="mb-3">
        <label class="form-label">
          {{ __('panel/review.rating') }}
          <span class="text-danger">*</span>
        </label>
        <div class="d-flex gap-3">
          @for($i = 1; $i <= 5; $i++)
            <div class="form-check">
              <input class="form-check-input" type="radio" name="rating" id="rating{{ $i }}" 
                     value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'checked' : '' }} required>
              <label class="form-check-label" for="rating{{ $i }}">
                {{ $i }}
              </label>
            </div>
          @endfor
        </div>
        <div class="invalid-feedback">
          {{ __('panel/review.please_select_rating') }}
        </div>
      </div>

      <x-common-form-textarea 
        title="{{ __('panel/review.review_content') }}" 
        name="content" 
        class="tinymce"
        :value="old('content', $review->content)"
        required 
      />

      <x-common-form-switch-radio 
        title="{{ __('panel/common.whether_enable') }}" 
        name="active" 
        :value="old('active', $review->active ?? true)"
      />

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
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

    $('#customer-autocomplete').autocomplete({
      'source': function (request, response) {
        const keyword = encodeURIComponent(request.term);
        var name = document.getElementById('customer-autocomplete').value;
        axios.get(`${urls.api_base}/customers/autocomplete?keyword=${name}`, null, {hload: true})
          .then((res) => {
            response($.map(res.data, function (item) {
              return {label: item['name'], value: item['id']};
            }));
          }).catch((error) => {
            console.error('请求出错:', error);
          });
      },
      'select': function (item) {
        $('#customer-autocomplete').val(item.label);
        $('input[name="customer_id"]').val(item.value);
        return false;
      }
    });
  });
</script>
@endpush