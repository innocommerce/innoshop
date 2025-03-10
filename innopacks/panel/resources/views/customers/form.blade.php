@extends('panel::layouts.app')

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
            @hookinsert('panel.customer.edit.tab.nav.bottom')
          </ul>

          <div class="tab-content" id="customerTabContent">
            <div class="tab-pane fade show active" id="info-tab-pane" role="tabpanel" tabindex="0">
              <form class="needs-validation" novalidate id="app-form"
                    action="{{ $customer->id ? panel_route('customers.update', [$customer->id]) : panel_route('customers.store') }}"
                    method="POST">
                @csrf
                @method($customer->id ? 'PUT' : 'POST')

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <x-common-form-image title="{{ __('panel/customer.avatar') }}" name="avatar" value="{{ old('avatar', $customer->avatar) }}" required/>
                    </div>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/customer.from') }}" name="from" value="{{ old('from', $customer->from) }}" placeholder="{{ __('panel/customer.from') }}"/>
                    </div>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/customer.name') }}" name="name" value="{{ old('name', $customer->name) }}" required placeholder="{{ __('panel/customer.name') }}"/>
                    </div>
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/customer.password') }}" name="password" value="" placeholder="{{ __('panel/customer.password') }}"/>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <x-common-form-input title="{{ __('panel/customer.email') }}" name="email" value="{{ old('email', $customer->email) }}" required placeholder="{{ __('panel/customer.email') }}"/>
                    </div>
                    <div class="mb-3 customersmt">
                      <x-common-form-select title="{{ __('panel/customer.group') }}" name="customer_group_id" :options="$groups" key="id" label="name" value="{{ old('customer_group_id', $customer->customer_group_id) }}"/>
                    </div>
                    @hookinsert('panel.customer.form.group.after')
                    <div class="mb-3">
                      <x-common-form-select title="{{ __('panel/customer.locale') }}" name="locale" :options="$locales" key="code" label="name" value="{{ old('locale', $customer->locale) }}"/>
                    </div>
                    <div class="mb-3">
                      <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('panel/common.whether_enable') }}"/>
                    </div>
                  </div>
                </div>

                <div class="text-center mt-3">
                  <button type="submit" class="btn btn-primary">{{ __('提交') }}</button>
                </div>
              </form>
            </div>

            <div class="tab-pane fade" id="address-tab-pane" role="tabpanel" tabindex="0">
              <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-sm add-address btn-outline-primary">{{ __('panel/common.add') }}</button>
              </div>
              <table class="table table-bordered">
                <thead>
                <tr>
                  <th>{{ __('panel/common.id') }}</th>
                  <th>{{ __('common/address.name') }}</th>
                  <th>{{ __('common/address.address') }}</th>
                  <th>{{ __('common/address.phone') }}</th>
                  <th>{{ __('panel/common.created_at') }}</th>
                  <th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($addresses as $address)
                  <tr data-id="{{ $address['id'] }}">
                    <td>{{ $address['id'] }}</td>
                    <td>{{ $address['name'] }}</td>
                    <td>{{ $address['address_1'] }}</td>
                    <td>{{ $address['phone'] }}</td>
                    <td>{{ $address['created_at'] }}</td>
                    <td class="text-end">
                      <button type="button" class="btn btn-sm edit-address btn-outline-primary">{{ __('panel/common.edit') }}</button>
                      <button type="button" class="btn btn-sm btn-outline-danger delete-address">{{ __('panel/common.delete') }}</button>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>

            @hookinsert('panel.customer.edit.tab.pane.bottom')
            
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
          @include('panel::shared.address-form')
        </div>
      </div>
    </div>
  </div>
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