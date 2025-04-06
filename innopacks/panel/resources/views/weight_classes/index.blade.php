@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.weight_classes'))
@section('page-title-right')
<a href="{{ panel_route('weight_classes.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('panel/common.create') }}
</a>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0">{{ __('panel/weight_class.list') }}</h4>
    </div>
  </div>

  <div class="card-body">
    @if ($weightClasses->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>{{ __('panel/common.id') }}</th>
            <th>{{ __('panel/weight_class.name') }}</th>
            <th>{{ __('panel/weight_class.code') }}</th>
            <th>{{ __('panel/weight_class.unit') }}</th>
            <th>{{ __('panel/weight_class.value') }}</th>
            <th>{{ __('panel/weight_class.position') }}</th>
            <th>{{ __('panel/common.active') }}</th>
            <th>{{ __('panel/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($weightClasses as $weightClass)
          <tr>
            <td>{{ $weightClass->id }}</td>
            <td>
              {{ $weightClass->name }}
              @if($weightClass->code === system_setting('default_weight_class'))
              <span class="badge bg-success">{{ __('panel/common.default') }}</span>
              @endif
            </td>
            <td>{{ $weightClass->code }}</td>
            <td>{{ $weightClass->unit }}</td>
            <td>{{ $weightClass->value }}</td>
            <td>{{ $weightClass->position }}</td>
            <td>
              @include('panel::shared.list_switch', [
              'value' => $weightClass->active,
              'url' => panel_route('weight_classes.active', $weightClass->id)
              ])
            </td>
            <td class="table-action">
              <div class="d-flex gap-2">
                <a href="{{ panel_route('weight_classes.edit', $weightClass->id) }}"
                  class="btn btn-sm btn-outline-primary px-2">
                  <i class="bi bi-pencil-square me-1"></i>{{ __('panel/common.edit') }}
                </a>
                @if($weightClass->code !== system_setting('default_weight_class'))
                <x-common-delete-button :id="$weightClass->id"
                  :route="panel_route('weight_classes.destroy', $weightClass->id)" />
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection

@push('footer')

@endpush