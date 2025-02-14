@extends('panel::layouts.app')
@section('body-class', 'page-coupons')

@section('title', __('Coupon::common.management'))

@section('content')
    <div class="card h-min-600">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-4">
                <a href="{{ panel_route('coupons.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
                    {{ __('Coupon::panel.add_coupon') }}</a>
            </div>

            <!-- 筛选表单 -->
            <form method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label for="status" class="form-label">{{ __('Coupon::common.status') }}</label>
                        <select id="status" name="status" class="form-select">
                            <option value="" {{ request()->get('status') == '' ? 'selected' : '' }}>{{ __('Coupon::common.all') }}</option>
                            <option value="used" {{ request()->get('status') == 'used' ? 'selected' : '' }}>{{ __('Coupon::common.used') }}</option>
                            <option value="not_used" {{ request()->get('status') == 'not_used' ? 'selected' : '' }}>{{ __('Coupon::common.not_used') }}</option>
                            <option value="expired" {{ request()->get('status') == 'expired' ? 'selected' : '' }}>{{ __('Coupon::common.expired') }}</option>
                            <option value="not_expired" {{ request()->get('status') == 'not_expired' ? 'selected' : '' }}>{{ __('Coupon::common.not_expired') }}</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="type" class="form-label">{{ __('Coupon::common.type') }}</label>
                        <select id="type" name="type" class="form-select">
                            <option value="" {{ request()->get('type') == '' ? 'selected' : '' }}>{{ __('Coupon::common.all') }}</option>
                            <option value="percentage" {{ request()->get('type') == 'percentage' ? 'selected' : '' }}>{{ __('Coupon::common.discount') }}</option>
                            <option value="fixed" {{ request()->get('type') == 'fixed' ? 'selected' : '' }}>{{ __('Coupon::common.fixed_amount') }}</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-info">{{ __('Coupon::common.filter') }}</button>
                    </div>
                </div>
            </form>

            <!-- 优惠券列表表格 -->
            <table class="table">
                <thead>
                <tr>
                    <td>{{ __('Coupon::common.id') }}</td>
                    <td>{{ __('Coupon::common.coupon_code') }}</td>
                    <td>{{ __('Coupon::common.type') }}</td>
                    <td>{{ __('Coupon::common.value') }}</td>
                    <td>{{ __('Coupon::common.start_date') }}</td>
                    <td>{{ __('Coupon::common.end_date') }}</td>
                    <td>{{ __('Coupon::common.total_uses') }}</td>
                    <td>{{ __('Coupon::common.daily_limit') }}</td>
                    <td>{{ __('Coupon::common.user_limit') }}</td>
                    <td>{{ __('Coupon::common.is_used') }}</td>
                    <td>{{ __('Coupon::common.operations') }}</td>
                </tr>
                </thead>
                @if ($coupons->count())
                    <tbody>
                    @foreach($coupons as $coupon)
                        <tr>
                            <td>{{ $coupon->id }}</td>
                            <td>
                                <a href="javascript:void(0)"
                                   class="text-decoration-none copy-code"
                                   data-code="{{ $coupon->code }}"
                                   title="{{ __('Coupon::common.click_to_copy') }}">
                                    {{ $coupon->code }}
                                    <i class="bi bi-clipboard ms-1"></i>
                                </a>
                            </td>
                            <td>{{ __('Coupon::common.'.$coupon->type) }}</td>
                            <td>
                                @if ($coupon->type == 'fixed')
                                    -{{ number_format($coupon->value, 2) }}
                                @elseif ($coupon->type == 'percentage')
                                    -{{ number_format($coupon->value, 2) }}%
                                @endif
                            </td>
                            <td>{{ $coupon->start_at }}</td>
                            <td>{{ $coupon->end_at ?? __('Coupon::panel.long-term') }}</td>
                            <td>{{ $coupon->times_used }} / {{ $coupon->max_uses ?? __('Coupon::common.unlimited') }}</td>
                            <td>{{ $coupon->daily_limit ?? __('Coupon::common.unlimited') }}</td>
                            <td>{{ $coupon->max_uses_per_user ?? __('Coupon::common.unlimited') }}</td>
                            <td>
                                @if($coupon->max_uses <= 1)
                                    {{ $coupon->is_used ? __('Coupon::common.yes') : __('Coupon::common.no') }}
                                @else
                                    <a href="javascript:void(0)" class="text-primary" data-bs-toggle="modal" data-bs-target="#usageModal{{ $coupon->id }}">
                                        <i class="bi bi-eye"></i> {{ $coupon->times_used }} {{ __('Coupon::common.times_used') }}
                                    </a>

                                    <!-- 使用详情 Modal -->
                                    <div class="modal fade" id="usageModal{{ $coupon->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        {{ __('Coupon::common.usage_details') }} - {{ $coupon->code }}
                                                        <small class="text-muted">({{ $coupon->times_used }} {{ __('Coupon::common.times_used') }})</small>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="table-responsive">
                                                        @php
                                                            $redemptions = $coupon->redemptions()->with(['user', 'order'])->latest('last_used_at')->get();
                                                        @endphp

                                                        @if($redemptions->count() > 0)
                                                            <table class="table table-striped table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('Coupon::common.user') }}</th>
                                                                        <th>{{ __('Coupon::common.order') }}</th>
                                                                        <th>{{ __('Coupon::common.used_at') }}</th>
                                                                        <th>{{ __('Coupon::common.date_used') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($redemptions as $redemption)
                                                                        <tr>
                                                                            <td>{{ optional($redemption->user)->customer->name ?? 'N/A' }}</td>
                                                                            <td>
                                                                                @if($redemption->order_id)
                                                                                    <a href="{{ panel_route('orders.show', $redemption->order_id) }}" target="_blank">
                                                                                        #{{ $redemption->order_id }}
                                                                                    </a>
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if($redemption->last_used_at instanceof \Carbon\Carbon)
                                                                                    {{ $redemption->last_used_at->format('Y-m-d H:i:s') }}
                                                                                @else
                                                                                    {{ $redemption->last_used_at }}
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $redemption->date_used }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        @else
                                                            <div class="text-center py-4 text-muted">
                                                                <i class="bi bi-inbox fs-2"></i>
                                                                <p class="mt-2">{{ __('Coupon::common.no_usage_records') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Coupon::common.close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ panel_route('coupons.edit', [$coupon->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('Coupon::common.edit') }}</a>
                                <form action="{{ panel_route('coupons.destroy', [$coupon->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Coupon::common.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                @else
                    <tbody><tr><td colspan="8"><x-common-no-data /></td></tr></tbody>
                @endif
            </table>
            {{ $coupons->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            // 当 select 元素的选项发生变化时，自动提交表单
            $('#status, #type').change(function() {
                $(this).closest('form').submit();
            });

            // 处理复制功能
            $('.copy-code').on('click', function(e) {
                e.preventDefault();
                const code = $(this).data('code');

                // 使用临时输入框来复制文本
                const tempInput = document.createElement('input');
                tempInput.value = code;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);

                // 更新图标显示
                const icon = $(this).find('i');
                icon.removeClass('bi-clipboard').addClass('bi-clipboard-check text-success');

                // 2秒后恢复原始图标
                setTimeout(() => {
                    icon.removeClass('bi-clipboard-check text-success').addClass('bi-clipboard');
                }, 2000);

                // 如果有 toast 组件，显示提示
                if (typeof toast !== 'undefined') {
                    toast.success('{{ __("Coupon::common.code_copied") }}');
                }
            });

            // 鼠标悬停效果
            $('.copy-code').hover(
                function() {
                    $(this).find('i').addClass('text-primary');
                },
                function() {
                    $(this).find('i').removeClass('text-primary');
                }
            );
        });
    </script>
@endpush
