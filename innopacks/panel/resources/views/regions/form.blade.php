@extends('panel::layouts.app')

@section('title', __('panel/menu.regions'))

<x-panel::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('panel/menu.regions') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation mt-3" id="app-form" novalidate action="{{ $region->id ? panel_route('regions.update', [$region->id]) : panel_route('regions.store') }}" method="POST">
      @csrf
      @method($region->id ? 'PUT' : 'POST')

      <div class="wp-500">
        <x-common-form-input title="{{ __('panel/region.name') }}" name="name" :value="old('name', $region->name ?? '')" required placeholder="{{ __('panel/region.name') }}" />
        <x-common-form-input title="{{ __('panel/region.description') }}" name="description" :value="old('description', $region->description ?? '')" required placeholder="{{ __('panel/region.name') }}" />
        <x-common-form-input title="{{ __('panel/region.position') }}" name="position" :value="old('position', $region->position ?? 0)" required placeholder="{{ __('panel/region.name') }}" />
        <x-panel::form.row title="{{ __('panel/region.region_states') }}" required>
          <table class="table table-bordered regions-table">
            <thead>
              <tr>
                <th width="40%">国家/地区</th>
                <th width="40%">省份</th>
                <th width="20%" class="text-end"></th>
              </tr>
            </thead>
            <tbody>
              @foreach($region->regionStates as $index => $item)
                <tr>
                  <td>
                    <select class="form-select form-select-sm country-select" name="region_states[{{ $index }}][country_id]" required>
                      @foreach($countries as $country)
                        <option value="{{ $country->id }}" @if ($country->id == $item->country_id) selected @endif>{{ $country->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <select class="form-select form-select-sm" name="region_states[{{ $index }}][state_id]" required data-id="{{ $item->state_id }}"></select>
                  </td>
                  <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-tax">{{ __('panel/common.delete')}}</button>
                  </td>
                </tr>
                @php ($index++)
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td colspan="3" class="text-end">
                  <button type="button" class="btn add-tax btn-sm btn-outline-primary">{{ __('panel/common.add') }}</button>
                </td>
              </tr>
            </tfoot>
          </table>
        </x-panel::form.row>
      </div>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>
  let index = @json($index ?? 0);
  const countryCode = @json(old('country_code', system_setting('country_code')));
  const stateCode = @json(old('state_code', system_setting('state_code')));
  let countries = [];
  getCountries()

  $(function () {
    $('.add-tax').click(function () {
      var html = `
        <tr>
          <td>
            <select class="form-select form-select-sm country-select" name="region_states[${index}][country_id]" required>
              <option value="">请选择国家</option>
              ${countries.map(country => `<option value="${country.id}">${country.name}</option>`).join('')}
            </select>
          </td>
          <td>
            <select class="form-select form-select-sm" name="region_states[${index}][state_id]" required>
              <option value="">请选择省份</option>
            </select>
          </td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-tax">{{ __('panel/common.delete')}}</button>
          </td>
        </tr>
      `;
      $('table tbody').append(html);
      index++;
    });

    $(document).on('click', '.remove-tax', function () {
      $(this).closest('tr').remove();
    });

    $('.regions-table tbody tr').each(function (index) {
      var countryId = $(this).find('select[name="region_states[' + index + '][country_id]"]').val();
      var id = $(this).find('select[name="region_states[' + index + '][state_id]"]').data('id');
      getZones(countryId, index, id);
    });
  });

  $(document).on('change', '.country-select', function () {
    var countryId = $(this).val();
    var index = $(this).closest('tr').index();
    getZones(countryId, index);
  });

  function getCountries() {
    axios.get('{{ front_route('countries.index') }}').then(function(res) {
      countries = res.data;
    });
  }

  function getZones(countryId, index, id = null) {
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      var states = res.data;
      var stateSelect = $('select[name="region_states[' + index + '][state_id]"]');
      stateSelect.prop('disabled', false).empty();
      stateSelect.append('<option value="">请选择省份</option>');
      states.forEach(function(state) {
        stateSelect.append('<option value="' + state.id + '"' + (state.id == id ? ' selected' : '') + '>' + state.name + '</option>');
      });
    });
  }
</script>
@endpush