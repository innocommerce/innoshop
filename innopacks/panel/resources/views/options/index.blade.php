@extends('panel::layouts.app')
@section('body-class', 'page-product-option-group')
@section('title', panel_trans('options.option_management'))

@section('page-title-right')
<a href="{{ panel_route('options.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('common/base.create') }}
</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <x-panel-data-data-search
      :action="panel_route('options.index')"
      :searchFields="$searchFields ?? []"
      :filters="$filterButtons ?? []"
      :enableDateRange="false"
    />

    @if ($option_groups->count())
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <th>{{ __('common/base.id') }}</th>
            <th>{{ __('common/base.name') }}</th>
            <th>{{ panel_trans('options.description') }}</th>
            <th>{{ panel_trans('options.type') }}</th>
            <th>{{ panel_trans('options.is_required') }}</th>
            <th>{{ panel_trans('options.sort') }}</th>
            <th>{{ __('panel/common.active') }}</th>
            <th>{{ __('common/base.created_at') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($option_groups as $optionGroup)
            <tr>
              <td>{{ $optionGroup->id }}</td>
              <td>{{ $optionGroup->currentName }}</td>
              <td>
                <div class="text-muted small" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                     title="{{ $optionGroup->getLocalizedDescription() }}">
                  {{ $optionGroup->getLocalizedDescription() ?: panel_trans('options.no_description') }}
                </div>
              </td>
              <td>
                @switch($optionGroup->type)
                  @case('select')
                    <span class="badge bg-primary">{{ panel_trans('options.dropdown_select') }}</span>
                    @break
                  @case('radio')
                    <span class="badge bg-info">{{ panel_trans('options.radio_button') }}</span>
                    @break
                  @case('checkbox')
                    <span class="badge bg-success">{{ panel_trans('options.checkbox') }}</span>
                    @break
                  @case('text')
                    <span class="badge bg-warning">{{ panel_trans('options.text_input') }}</span>
                    @break
                  @case('textarea')
                    <span class="badge bg-secondary">{{ panel_trans('options.textarea') }}</span>
                    @break
                  @default
                    <span class="badge bg-light text-dark">{{ $optionGroup->type }}</span>
                @endswitch
              </td>
              <td>
                @if($optionGroup->required)
                  <span class="badge bg-danger">{{ panel_trans('options.required') }}</span>
                @else
                  <span class="badge bg-secondary">{{ panel_trans('options.optional') }}</span>
                @endif
              </td>
              <td>{{ $optionGroup->position }}</td>
              <td>
                @include('panel::shared.list_switch', [
                  'value' => $optionGroup->active,
                  'url' => panel_route('options.active', $optionGroup->id)
                ])
              </td>
              <td>{{ $optionGroup->created_at }}</td>
              <td>
                <div class="d-flex gap-2">
                  <a href="{{ panel_route('options.edit', [$optionGroup->id]) }}" class="btn btn-sm btn-outline-primary">
                    {{ __('common/base.edit') }}
                  </a>
                  <form action="{{ panel_route('options.destroy', [$optionGroup->id]) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('{{ panel_trans('options.confirm_delete_option_group') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      {{ __('common/base.delete') }}
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>

      {{ $option_groups->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
      <x-common-no-data />
    @endif
  </div>
</div>
@endsection
