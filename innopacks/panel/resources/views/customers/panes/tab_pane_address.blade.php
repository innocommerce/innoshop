<div class="tab-pane fade" id="address-tab-pane" role="tabpanel" tabindex="0">
  <div class="d-flex justify-content-end mb-3">
    <button class="btn btn-sm add-address btn-outline-primary">{{ __('panel/common.add') }}</button>
  </div>
  <table class="table table-bordered mb-0">
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