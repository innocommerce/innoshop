@extends('panel::layouts.app')

@section('title', __('panel/menu.attributes'))

<x-panel::form.right-btns/>

@section('content')
  <form class="needs-validation" novalidate id="app-form"
        action="{{ $attribute->id ? panel_route('attributes.update', [$attribute->id]) : panel_route('attributes.store') }}"
        method="POST">
    @csrf
    @method($attribute->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-6">
        <div class="card h-min-600">
          <div class="card-header">
            <h5 class="card-title mb-0">{{ __('panel/menu.attributes') }}</h5>
          </div>
          <div class="card-body">
            <x-common-form-input title="{{ __('panel/common.name') }}" :multiple="true" name="name"
                                 :value="old('translations', $attribute->translations)" required
                                 placeholder="{{ __('panel/common.name') }}"/>

            <x-common-form-select title="{{ __('panel/menu.attribute_groups') }}" name="attribute_group_id"
                                  :options="$attribute_groups" key="id" label="name"
                                  value="{{ old('attribute_group_id', $attribute->attribute_group_id) }}" required
                                  placeholder="{{ __('panel/menu.attribute_groups') }}"/>

            <x-common-form-input title="{{ __('panel/common.position') }}" name="name"
                                 :value="old('position', $attribute->position)" required
                                 placeholder="{{ __('panel/common.position') }}"/>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card h-min-600">
          <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">{{ __('panel/attribute.attribute_value') }}</h5>
            <button type="button"
                    class="btn btn-sm btn-outline-primary add-value">{{ __('panel/common.add') }}</button>
          </div>
          <div class="card-body">
            <table class="table align-middle">
              <thead>
              <tr>
                <td>{{ __('panel/common.id') }}</td>
                <td>{{ __('panel/common.name') }}</td>
                <td class="text-end">{{ __('panel/common.actions') }}</td>
              </tr>
              </thead>
              <tbody>
              @foreach($attribute->values as $item)
                <tr data-id="{{ $item->id }}">
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->translation->name }}</td>
                  <td class="text-end">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary edit-value">{{ __('panel/common.edit') }}</button>
                    <button type="submit"
                            class="btn btn-sm btn-outline-danger delete-value">{{ __('panel/common.delete') }}</button>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>

  <div class="modal fade" id="attributeValuesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addressModalLabel">{{ __('panel/attribute.attribute_value') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          {{-- <ul class="nav nav-tabs mb-2" id="myTab" role="tablist">
            @foreach (locales() as $locale)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : ''}}" id="{{ $locale['code'] }}" data-bs-toggle="tab" data-bs-target="#values-{{ $locale['code'] }}-pane" type="button">
                  <img src="{{ asset('images/flag/'. $locale['code'] .'.png') }}" class="me-2" style="width: 20px;">
                  {{ $locale['name'] }}
                </button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content pb-3">
            @foreach (locales() as $locale)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : ''}}" id="values-{{ $locale['code'] }}-pane" role="tabpanel" aria-labelledby="{{ $locale['code'] }}">
              <input type="text" name="translations[{{ $locale['code'] }}][values]" class="form-control" value="" placeholder="{{ $locale['name'] }}" />
            </div>
            @endforeach
          </div> --}}

          <form class="values-input-wrap">
            <x-common-form-input title="" :multiple="true" name="values" :value="null" required
                                 placeholder="{{ __('panel/common.name') }}"/>

            <div class="mt-4 d-flex justify-content-center">
              <button type="button" class="btn btn-primary w-50 form-submit">{{ __('panel/common.btn_save') }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    const attributeValues = @json($attribute->values ?? []);
    let id = null;

    $(document).on('click', '.edit-value', function () {
      const tr = $(this).closest('tr');
      id = tr.data('id');
      let value = attributeValues.find(item => item.id === id);
      value.translations.forEach(item => {
        $(`input[name="translations[${item.locale}][values]"]`).val(item.name);
      });
      // 打开模态框
      $('#attributeValuesModal').modal('show');
    });

    $('.add-value').on('click', function () {
      id = null;
      $('.values-input-wrap .form-control').val('');
      $('#attributeValuesModal').modal('show');
    });

    inno.validateAndSubmitForm('.values-input-wrap', (res) => {
      console.log(res);
    });
  </script>
@endpush