@extends('panel::layouts.app')

@section('title', __('panel/menu.customers'))

<x-panel::form.right-btns/>

@section('content')
  <div class="row">
    <div class="col-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('panel/menu.customers') }}</h5>
        </div>
        <div class="card-body">
          <form class="needs-validation" novalidate id="app-form"
                action="{{ $customer->id ? panel_route('customers.update', [$customer->id]) : panel_route('customers.store') }}"
                method="POST">
            @csrf
            @method($customer->id ? 'PUT' : 'POST')

            <x-common-form-image title="头像" name="avatar" value="{{ old('avatar', $customer->avatar) }}" required/>

            <x-common-form-input title="Email" name="email" value="{{ old('email', $customer->email) }}" required
                                 placeholder="Email"/>

            <x-common-form-input title="名字" name="name" value="{{ old('name', $customer->name) }}" required
                                 placeholder="名字"/>

            <x-common-form-input title="密码" name="password" value="" placeholder="密码"/>

            <x-common-form-input title="来源" name="from" value="{{ old('from', $customer->from) }}"
                                 placeholder="来源"/>

            <x-common-form-select title="用户组" name="customer_group_id" :options="$groups" key="id" label="name"
                                  value="{{ old('customer_group_id', $customer->customer_group_id) }}"/>

            <x-common-form-select title="语言" name="locale" :options="$locales" key="code" label="name"
                                  value="{{ old('locale', $customer->locale) }}"/>

            <x-common-form-switch-radio title="{{ __('panel/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)"
                                        placeholder="{{ __('panel/common.whether_enable') }}"/>

            <button type="submit" class="d-none"></button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">{{ __('panel/customer.address') }}</h5>
          <button class="btn btn-sm add-address btn-outline-primary">{{ __('panel/common.add') }}</button>
        </div>
        <div class="card-body">
          {{-- @dd($customer->addresses) --}}
          <table class="table table-bordered">
            <thead>
            <tr>
              <th>{{ __('common/address.name') }}</th>
              <th>{{ __('common/address.address') }}</th>
              <th>{{ __('common/address.phone') }}</th>
              <th class="text-end"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($addresses as $address)
              <tr data-id="{{ $address['id'] }}">
                <td>{{ $address['name'] }}</td>
                <td>{{ $address['address_1'] }}</td>
                <td>{{ $address['phone'] }}</td>
                <td class="text-end">
                  <button type="button"
                          class="btn btn-sm edit-address btn-outline-primary">{{ __('panel/common.edit') }}</button>
                  <button type="button" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete') }}</button>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
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

    function updataAddress(params) {
      layer.msg('需要接口');
      return;
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