@extends('layouts.app')
@section('body-class', 'page-addresses')

@section('content')
  <x-front-breadcrumb type="route" value="account.addresses.index" title="{{ __('front/account.addresses') }}" />

  @hookinsert('account.addresses.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('common/address.address') }}</span>
            <button type="button" class="btn btn-primary add-address">{{ __('common/address.add_new_address') }}</button>
          </div>
          <div class="row">
            @foreach($addresses as $index => $address)
              <div class="col-12 col-md-6">
                <div class="address-card" data-id="{{ $address['id'] }}">
                  <div class="address-card-header">
                    <h5 class="address-card-title">{{ $address['name'] }}</h5>
                    <div class="address-card-actions">
                      <button type="button" class="btn btn-link edit-address">{{ __('front/common.edit') }}</button>
                      <button type="button" class="btn btn-link delete-address">{{ __('front/common.delete') }}</button>
                    </div>
                  </div>
                  <div class="address-card-body">
                    <p>{{ $address['name'] }}</p>
                    <p>{{ $address['address_1'] }} {{ $address['address_2'] }}</p>
                    <p>{{ $address['city'] }}</p>
                    <p>{{ $address['state'] }}, {{ $address['country_name'] }}</p>
                    <p>Phone: {{ $address['phone'] }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addressModalLabel">{{ __('common/address.address') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('shared.address-form')
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.addresses.bottom')

@endsection

@push('footer')
  <script>
    const addresses = @json($addresses);
    console.log(addresses);

    $('.add-address').on('click', function () {
      $('.address-form').find('input, select').each(function () {
        $(this).val('')
      })

      $('#addressModal').modal('show');
    });

    $('.edit-address').on('click', function () {
      const id = $(this).closest('.address-card').data('id');
      const address = addresses.find(address => address.id === id);

      getZones(address.country_code, function () {
        $('.address-form').find('input, select').each(function () {
          $(this).val(address[$(this).attr('name')])
        })
      })

      $('#addressModal').modal('show');
    });

    $('.delete-address').on('click', function () {
      const id = $(this).closest('.address-card').data('id');

      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: [ '{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}' ]
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

    function updataAddress(params) {
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