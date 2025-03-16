@extends('panel::layouts.app')

@section('title', $cloak->exists ? __('Cloak::panel.edit_cloak') : __('Cloak::panel.add_cloak'))

@section('content')
    <div class="card h-min-600">
        <div class="card-body">
            <form class="needs-validation" novalidate
                  action="{{ $cloak->exists ? panel_route('cloaks.update', [$cloak->id]) : panel_route('cloaks.store') }}"
                  method="POST">
                @csrf
                @method($cloak->exists ? 'PUT' : 'POST')

                <div class="row">
                    <div class="col-12">
                        <!-- Basic Information -->
                        <h4 class="mb-3">{{ __('Cloak::panel.basic_information') }}</h4>

                        <x-common-form-input
                            title="{{ __('Cloak::panel.name') }}"
                            name="name"
                            :value="old('name', $cloak->name ?? '')"
                            required="required"
                            placeholder="{{ __('Cloak::panel.enter_name') }}"
                        />

                        <x-common-form-input
                            title="{{ __('Cloak::panel.description') }}"
                            name="description"
                            :value="old('description', $cloak->description ?? '')"
                            placeholder="{{ __('Cloak::panel.enter_description') }}"
                        />

                        <x-common-form-input
                            title="{{ __('Cloak::panel.target_url') }}"
                            name="target_url"
                            :value="old('target_url', $cloak->target_url ?? '')"
                            required="required"
                            placeholder="{{ __('Cloak::panel.enter_target_url') }}"
                        />

                        <x-common-form-input
                            title="{{ __('Cloak::panel.safe_url') }}"
                            name="safe_url"
                            :value="old('safe_url', $cloak->safe_url ?? '')"
                            help="{{ __('Cloak::panel.safe_url_help') }}"
                            placeholder="{{ __('Cloak::panel.enter_safe_url') }}"
                        />

                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-common-form-switch-radio
                                    title="{{ __('Cloak::panel.is_active') }}"
                                    name="is_active"
                                    :value="old('is_active', $cloak->is_active ?? true)"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-common-form-switch-radio
                                    title="{{ __('Cloak::panel.detect_bots') }}"
                                    name="detect_bots"
                                    :value="old('detect_bots', $cloak->detect_bots ?? true)"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-common-form-switch-radio
                                    title="{{ __('Cloak::panel.one_time_redirect') }}"
                                    name="one_time_redirect"
                                    :value="old('one_time_redirect', $cloak->one_time_redirect ?? false)"
                                />
                            </div>
                        </div>

                        <!-- Filtering Options -->
                        <h4 class="mt-4 mb-3">{{ __('Cloak::panel.filtering_options') }}</h4>
                        <p class="text-muted">{{ __('Cloak::panel.filter_description') }}</p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ip_filters" class="form-label">{{ __('Cloak::panel.ip_filters') }}</label>
                                    <textarea id="ip_filters" name="ip_filters" class="form-control" rows="3" placeholder="{{ __('Cloak::panel.enter_ip_filters') }}">{{ old('ip_filters', !empty($cloak->ip_filters) ? implode("\n", $cloak->ip_filters) : '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="country_filters" class="form-label">{{ __('Cloak::panel.country_filters') }}</label>
                                    <textarea id="country_filters" name="country_filters" class="form-control" rows="3" placeholder="{{ __('Cloak::panel.enter_country_filters') }}">{{ old('country_filters', !empty($cloak->country_filters) ? implode("\n", $cloak->country_filters) : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_agent_filters" class="form-label">{{ __('Cloak::panel.user_agent_filters') }}</label>
                                    <textarea id="user_agent_filters" name="user_agent_filters" class="form-control" rows="3" placeholder="{{ __('Cloak::panel.enter_user_agent_filters') }}">{{ old('user_agent_filters', !empty($cloak->user_agent_filters) ? implode("\n", $cloak->user_agent_filters) : '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="referrer_filters" class="form-label">{{ __('Cloak::panel.referrer_filters') }}</label>
                                    <textarea id="referrer_filters" name="referrer_filters" class="form-control" rows="3" placeholder="{{ __('Cloak::panel.enter_referrer_filters') }}">{{ old('referrer_filters', !empty($cloak->referrer_filters) ? implode("\n", $cloak->referrer_filters) : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- UTM Parameters -->
                        <h4 class="mt-4 mb-3">{{ __('Cloak::panel.utm_parameters') }}</h4>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="{{ __('Cloak::panel.utm_source') }}"
                                    name="utm_source"
                                    :value="old('utm_source', $cloak->utm_source ?? '')"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="{{ __('Cloak::panel.utm_medium') }}"
                                    name="utm_medium"
                                    :value="old('utm_medium', $cloak->utm_medium ?? '')"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="{{ __('Cloak::panel.utm_campaign') }}"
                                    name="utm_campaign"
                                    :value="old('utm_campaign', $cloak->utm_campaign ?? '')"
                                />
                            </div>
                        </div>

                        @if($cloak->exists)
                        <!-- Statistics -->
                        <h4 class="mt-4 mb-3">{{ __('Cloak::panel.performance_stats') }}</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ __('Cloak::common.visits') }}</h5>
                                        <p class="card-text display-6">{{ $cloak->visits_count }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ __('Cloak::common.redirects') }}</h5>
                                        <p class="card-text display-6">{{ $cloak->redirects_count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <x-panel::form.bottom-btns/>
            </form>
        </div>
    </div>
@endsection

@push('footer')
<script>
    $(document).ready(function() {
        // Process textarea inputs to convert to arrays
        $('form').on('submit', function() {
            ['ip_filters', 'country_filters', 'user_agent_filters', 'referrer_filters'].forEach(function(field) {
                const textarea = $('#' + field);
                const lines = textarea.val().split('\n').filter(line => line.trim() !== '');

                // Remove the textarea from form submission
                textarea.prop('disabled', true);

                // Add hidden inputs for each line
                lines.forEach(function(line, index) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: field + '[]',
                        value: line.trim()
                    }).appendTo('form');
                });
            });

            return true;
        });
    });
</script>
@endpush
