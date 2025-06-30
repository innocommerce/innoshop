@extends('panel::layouts.app')
@section('body-class', 'page-customer')

@section('title', __('panel/menu.customers'))

@section('page-title-right')
<div class="title-right-btns">
  <a href="{{ panel_route('customers.login', [$customer->id]) }}" target="_blank" class="btn btn-primary">
    {{ __('panel/customer.login_frontend')}}
  </a>
  <button type="button" class="btn btn-outline-secondary ms-2 btn-back" onclick="window.history.back()">{{
    __('panel/common.btn_back') }}</button>
</div>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <ul class="nav nav-tabs mb-3" id="customerTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab">{{ __('panel/customer.basic_info') }}</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address-tab-pane" type="button" role="tab">{{ __('panel/customer.address_manage') }}</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="balance-tab" data-bs-toggle="tab" data-bs-target="#balance-tab-pane" type="button" role="tab">{{ __('panel/customer.balance_manage') }}</button>
            </li>
            @hookinsert('panel.customer.edit.tab.nav.bottom')
          </ul>

          <div class="tab-content" id="customerTabContent">
            @include('panel::customers.panes.tab_pane_info', $customer)

            @include('panel::customers.panes.tab_pane_address', $customer)

            @include('panel::customers.panes.tab_pane_balance', $customer)

            @hookinsert('panel.customer.edit.tab.pane.bottom')
            
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('panel::customers.panes.address_modal')
@endsection

@push('footer')
  <script>
    const addresses = @json($addresses);

    $('.add-address').on('click', function () {
      $('.address-form').find('input, select').each(function () {
        $(this).val('')
      })

      $('#addressModal').modal('show');
    });

    $('.edit-address').on('click', function () {
      const id = $(this).parents('tr').data('id');
      const address = addresses.find(address => address.id === id);

      getZones(address.country_code, function () {
        $('.address-form').find('input, select').each(function () {
          $(this).val(address[$(this).attr('name')])
        })
      })

      $('#addressModal').modal('show');
    });

    $('.delete-address').on('click', function () {
      const id = $(this).parents('tr').data('id');

      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: ['{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}']
      }, function () {
        axios.delete(`{{ account_route('addresses.index') }}/${id}`).then(function (res) {
          if (res.success) {
            layer.msg(res.message, {icon: 1, time: 1000}, function () {
              window.location.reload()
            });
          }
        })
      });
    });

    function updateAddress(params) {
      const id = new URLSearchParams(params).get('id');
      const href = @json(account_route('addresses.index'));
      const method = id ? 'put' : 'post'
      const url = id ? `${href}/${id}` : href

      axios[method](url, params).then(function (res) {
        if (res.success) {
          $('#addressModal').modal('hide');
          inno.msg(res.message);
          window.location.reload();
        }
      })
    }
  </script>
@endpush