@extends('panel::layouts.app')
@section('body-class', 'page-transaction')
@section('title', __('panel/menu.transactions'))

<x-panel::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/menu.transactions') }}</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" novalidate id="app-form"
            action="{{ $transaction->id ? panel_route('transactions.update', [$transaction->id]) : panel_route('transactions.store') }}"
            method="POST">
        @csrf
        @method($transaction->id ? 'PUT' : 'POST')
        <div class="row">
          <div class="col-12 col-md-6">
            <div class="col-sm-2 required col-form-label text-start client d-flex">
              <span class="fw-bold text-danger">*</span>
              <div>{{ __('panel/transaction.customer') }}</div>
            </div>
            <input type="text" name="customer_name" id="customer-autocomplete"
                   value="{{ old('customer_name', $transaction->customer->name ?? '') }}" placeholder="{{ __('panel/transaction.customer_search') }}"
                   class="form-control">
            <input type="hidden" name="customer_id" value="{{ old('customer_id', $transaction->customer_id) }}">
            <input type="text" name="customer_id" class="d-none" value="{{ $transaction->customer_id }}">
          </div>

          <div class="col-12 col-md-6">
            <x-common-form-input title="{{ __('panel/transaction.amount') }}" name="amount"
                                 value="{{ old('amount', $transaction->amount) }}" required/>
          </div>

          <div class="col-12 col-md-6">
            <x-common-form-select :title="__('panel/transaction.type')" name="type" :emptyOption="false" required
                                  :value="old('type', $transaction->type)" :options="$types" key="code"
                                  label="label"/>
          </div>

          <div class="col-12 col-md-6">
            <x-common-form-textarea title="{{ __('panel/transaction.comment') }}" name="comment"
                                    value="{{ old('comment', $transaction->comment) }}" required/>
          </div>
        </div>

        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    $(function () {
      $('#customer-autocomplete').autocomplete({
        'source': function (request, response) {
          const keyword = encodeURIComponent(request.term);
          var name = document.getElementById('customer-autocomplete').value;
          axios.get(`${urls.api_base}/customers/autocomplete?keyword=${name}`, null, {hload: true})
            .then((res) => {
              response($.map(res.data, function (item) {
                return {label: item['name'] + '('+ item['email'] +')', value: item['id']};
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
