@extends('panel::layouts.app')

@section('title', $coupon->exists ? __('Coupon::panel.edit_coupon')  : __('Coupon::panel.add_coupon'))

@section('content')
    <div class="card h-min-600">
        <div class="card-body">
            <form class="needs-validation" novalidate
                  action="{{ $coupon->exists ? panel_route('coupons.update', [$coupon->id]) : panel_route('coupons.store') }}"
                  method="POST">
                @csrf
                @method($coupon->exists ? 'PUT' : 'POST')
                <div class="row">
                    <div class="col-12">
                        <x-common-form-input title="{{ __('Coupon::panel.coupon_code') }}" name="code" :value="old('code', $coupon->code ?? '')" required="required" />
                        <x-common-form-select title="{{ __('Coupon::panel.coupon_type') }}" name="type" :value="old('type', $coupon->type ?? 'percentage')"
                                              :options="$options" required/>
                        <x-common-form-input title="{{ __('Coupon::panel.value') }}" name="value" type="number" :value="old('value', $coupon->value ?? '')" placeholder="{{ __('Coupon::panel.enter_value') }}" required="required" id="valueInput"/>
                        <x-common-form-input title="总使用次数" name="max_uses" type="number" :value="old('max_uses', $coupon->max_uses ?? 1)"/>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="每用户总使用次数限制"
                                    name="max_uses_per_user"
                                    type="number"
                                    :value="old('max_uses_per_user', $coupon->max_uses_per_user ?? 1)"/>
                            </div>
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="每用户每天使用限制"
                                    name="daily_limit"
                                    type="number"
                                    :value="old('daily_limit', $coupon->daily_limit ?? 1)"/>
                            </div>
                            <div class="col-md-4">
                                <x-common-form-input
                                    title="使用最小间隔（小时）"
                                    name="use_interval"
                                    type="number"
                                    :value="old('use_interval', $coupon->use_interval ?? 24)"/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <x-common-form-input
                                    title="{{ __('Coupon::panel.start_date') }}"
                                    name="start_at"
                                    type="date"
                                    :value="old('start_at', $coupon->start_at instanceof \Illuminate\Support\Carbon ? $coupon->start_at->format('Y-m-d') : now()->format('Y-m-d'))"
                                    required="required"/>
                            </div>
                            <div class="col-md-6">
                                <x-common-form-input
                                    title="{{ __('Coupon::panel.end_date') }}"
                                    name="end_at"
                                    type="date"
                                    :value="old('end_at', $coupon->end_at ? $coupon->end_at->format('Y-m-d') : '')"
                                    placeholder="{{ __('Coupon::panel.no_end_date') }}"/>
                            </div>
                        </div>

                        <x-common-form-switch-radio title="{{ __('Coupon::panel.is_active') }}" name="active" :value="old('active', $coupon->active ?? true)"/>
                        <div>
                            <label>{{ __('Coupon::panel.is_used') }}：</label>
                            <span>{{ $coupon->is_used ? __('Coupon::panel.used') : __('Coupon::panel.not_used') }}</span>
                            @if($coupon->is_used)<a href="{{ panel_route('orders.show',$coupon->order_id) }}">{{ __('Coupon::panel.view_order') }}</a>  @endif
                        </div>
                    </div>
                </div>
                <x-panel::form.bottom-btns/>
            </form>
        </div>
    </div>
@endsection

@push('footer')
<script>

</script>
@endpush
