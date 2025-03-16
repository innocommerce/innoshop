@extends('panel::layouts.app')
@section('body-class', 'page-cloaks')

@section('title', __('Cloak::common.management'))

@section('content')
    <div class="card h-min-600">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ panel_route('cloaks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-square"></i> {{ __('Cloak::panel.add_cloak') }}
                </a>
            </div>

            <!-- Filter form -->
            <form method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label for="status" class="form-label">{{ __('Cloak::common.status') }}</label>
                        <select id="status" name="status" class="form-select">
                            <option value="" {{ request()->get('status') == '' ? 'selected' : '' }}>{{ __('Cloak::common.all') }}</option>
                            <option value="active" {{ request()->get('status') == 'active' ? 'selected' : '' }}>{{ __('Cloak::common.active') }}</option>
                            <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>{{ __('Cloak::common.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-info">{{ __('Cloak::common.filter') }}</button>
                    </div>
                </div>
            </form>

            <!-- Cloaks list table -->
            <table class="table">
                <thead>
                <tr>
                    <td>{{ __('Cloak::common.id') }}</td>
                    <td>{{ __('Cloak::common.name') }}</td>
                    <td>{{ __('Cloak::common.target_url') }}</td>
                    <td>{{ __('Cloak::common.safe_url') }}</td>
                    <td>{{ __('Cloak::common.visits') }}</td>
                    <td>{{ __('Cloak::common.redirects') }}</td>
                    <td>{{ __('Cloak::common.status') }}</td>
                    <td>{{ __('Cloak::common.operations') }}</td>
                </tr>
                </thead>
                @if ($cloaks->count())
                    <tbody>
                    @foreach($cloaks as $cloak)
                        <tr>
                            <td>{{ $cloak->id }}</td>
                            <td>
                                {{ $cloak->name }}
                                <div class="small text-muted">
                                    <strong>URL:</strong> <a href="{{ front_route('cloak.process', [$cloak->id]) }}" target="_blank" class="text-decoration-none">{{ front_route('cloak.process', [$cloak->id]) }}</a>
                                </div>
                            </td>
                            <td>
                                <a href="{{ $cloak->target_url }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $cloak->target_url }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ $cloak->safe_url }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $cloak->safe_url }}
                                </a>
                            </td>
                            <td>{{ $cloak->visits_count }}</td>
                            <td>{{ $cloak->redirects_count }}</td>
                            <td>
                                <span class="badge {{ $cloak->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $cloak->is_active ? __('Cloak::common.active') : __('Cloak::common.inactive') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ front_route('cloak.process', [$cloak->id]) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Test Cloak">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="{{ front_route('cloak.process', [$cloak->id]) }}?test=cloak" target="_blank" class="btn btn-sm btn-outline-warning" title="Test Safe Page">
                                        <i class="bi bi-shield-check"></i>
                                    </a>
                                    <a href="{{ panel_route('cloaks.edit', [$cloak->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> {{ __('Cloak::common.edit') }}
                                    </a>
                                    <form action="{{ panel_route('cloaks.destroy', [$cloak->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> {{ __('Cloak::common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                @else
                    <tbody><tr><td colspan="8"><x-common-no-data /></td></tr></tbody>
                @endif
            </table>
            {{ $cloaks->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            // Auto-submit form when select element's options change
            $('#status').change(function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@endpush
