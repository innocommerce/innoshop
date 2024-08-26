@extends('panel::layouts.app')

@section('title', __('panel/role.roles'))

<x-panel::form.right-btns/>

@section('content')

  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('panel/role.roles') }}</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" id="app-form" novalidate
            action="{{ $role->id ? panel_route('roles.update', [$role->id]) : panel_route('roles.store') }}"
            method="POST">
        @csrf
        @method($role->id ? 'PUT' : 'POST')

        <div class="wp-400">
          <x-common-form-input title="{{ __('panel/role.name') }}" name="name" value="{{ old('name', $role->name) }}"
                               required placeholder="{{ __('panel/role.name') }}"/>
        </div>

        <div class="wp-900">
          <x-panel::form.row title="{{ __('panel/role.permissions') }}">
            <div class="roles-wrap">
              <table class="table table-bordered">
                <thead>
                <tr>
                  <th class="bg-light">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-select-all">
                      {{ __('panel/role.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-uncheck">
                      {{ __('panel/role.unselect_all') }}
                    </button>
                  </th>
                </tr>
                </thead>
                <tbody>
                @foreach ($permissions as $item)
                  <tr>
                    <td>
                      <span class="me-2">{{ $item['is_plugin'] ? 'P -' : '' }} {{ $item['label'] }}</span>
                      [<span class="text-secondary cursor-pointer select-list">{{ __('panel/role.select_all') }}</span>
                      /
                      <span class="text-secondary cursor-pointer cancel-list">{{ __('panel/role.unselect_all') }}</span>]
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="d-flex flex-wrap">
                        @foreach ($item['permissions'] as $child)
                          <div class="form-check me-3 mb-2" data-id="{{ $child['route_slug'] }}">
                            <label class="form-check-label">
                              <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $child['route_slug'] }}"
                                     @if ($child['selected']) checked @endif>{{ $child['label'] }}
                            </label>
                          </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          </x-panel::form.row>
        </div>

        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    $(function () {
      $('.btn-select-all').click(function () {
        $(this).closest('table').find('input[type="checkbox"]').prop('checked', true);
      });

      $('.btn-uncheck').click(function () {
        $(this).closest('table').find('input[type="checkbox"]').prop('checked', false);
      });

      $('.select-list').click(function () {
        $(this).closest('tr').next().find('input[type="checkbox"]').prop('checked', true);
      });

      $('.cancel-list').click(function () {
        $(this).closest('tr').next().find('input[type="checkbox"]').prop('checked', false);
      });
    });
  </script>
@endpush