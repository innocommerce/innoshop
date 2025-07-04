@extends('panel::layouts.app')
@section('body-class', 'page-customer-withdrawal')
@section('title', __('panel/withdrawal.detail'))

<x-panel::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">{{ __('panel/withdrawal.detail') }}</h5>
      <div class="status-badge">
        @switch($withdrawal->status)
          @case('pending')
            <span class="badge bg-warning fs-6 px-3 py-2">
              <i class="bi bi-clock"></i> {{ $withdrawal->status_format }}
            </span>
            @break
          @case('approved')
            <span class="badge bg-info fs-6 px-3 py-2">
              <i class="bi bi-check-circle"></i> {{ $withdrawal->status_format }}
            </span>
            @break
          @case('paid')
            <span class="badge bg-success fs-6 px-3 py-2">
              <i class="bi bi-check-circle-fill"></i> {{ $withdrawal->status_format }}
            </span>
            @break
          @case('rejected')
            <span class="badge bg-danger fs-6 px-3 py-2">
              <i class="bi bi-x-circle"></i> {{ $withdrawal->status_format }}
            </span>
            @break
          @default
            <span class="badge bg-secondary fs-6 px-3 py-2">{{ $withdrawal->status_format }}</span>
        @endswitch
      </div>
    </div>
    <div class="card-body">

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="row">
        <!-- Customer Information -->
        <div class="col-12 mb-4">
          <h6 class="mb-3">{{ __('panel/withdrawal.customer_info') }}</h6>
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.customer_name') }}</div>
              <div class="text-muted">
                <a href="{{ panel_route('customers.edit', [$withdrawal->customer->id]) }}" class="text-decoration-none">
                  {{ $withdrawal->customer->name ?? '' }}
                </a>
              </div>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.customer_email') }}</div>
              <div class="text-muted">{{ $withdrawal->customer->email ?? '' }}</div>
            </div>
          </div>
        </div>

        <!-- Withdrawal Information -->
        <div class="col-12 mb-4">
          <h6 class="mb-3">{{ __('panel/withdrawal.info') }}</h6>
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.amount') }}</div>
              <div class="text-primary fs-5 fw-bold">{{ currency_format($withdrawal->amount) }}</div>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.created_at') }}</div>
              <div class="text-muted">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</div>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="col-12 mb-4">
          <h6 class="mb-3">{{ __('panel/withdrawal.account_info') }}</h6>
          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.account_type') }}</div>
              <div class="text-muted">{{ $withdrawal->account_type_format }}</div>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.account_number') }}</div>
              <div class="text-muted font-monospace">{{ $withdrawal->account_number }}</div>
            </div>
            @if($withdrawal->bank_name)
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.bank_name') }}</div>
              <div class="text-muted">{{ $withdrawal->bank_name }}</div>
            </div>
            @endif
            @if($withdrawal->bank_account)
            <div class="col-12 col-md-6 mb-3">
              <div class="fw-bold">{{ __('panel/withdrawal.bank_account') }}</div>
              <div class="text-muted">{{ $withdrawal->bank_account }}</div>
            </div>
            @endif
          </div>
        </div>

        <!-- Comments -->
        @if($withdrawal->comment)
        <div class="col-12 mb-4">
          <div class="fw-bold">{{ __('panel/withdrawal.comment') }}</div>
          <div class="text-muted">{{ $withdrawal->comment }}</div>
        </div>
        @endif

        @if($withdrawal->admin_comment)
        <div class="col-12 mb-4">
          <div class="fw-bold">{{ __('panel/withdrawal.admin_comment') }}</div>
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle"></i>
            {{ $withdrawal->admin_comment }}
          </div>
        </div>
        @endif

        <!-- Status Operations -->
        @if($withdrawal->status !== 'paid')
        <div class="col-12">
          <h6 class="mb-3">{{ __('panel/withdrawal.change_status') }}</h6>
          <div class="d-flex gap-2 flex-wrap">
            @if($withdrawal->status === 'pending')
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-circle"></i> {{ __('panel/withdrawal.approve') }}
              </button>
              <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle"></i> {{ __('panel/withdrawal.reject') }}
              </button>
            @endif
            @if($withdrawal->status === 'approved')
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                <i class="bi bi-credit-card"></i> {{ __('panel/withdrawal.pay') }}
              </button>
              <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle"></i> {{ __('panel/withdrawal.reject') }}
              </button>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Approve Modal -->
  <div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('panel/withdrawal.approve') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>{{ __('panel/withdrawal.approve_confirm') }}</p>
          <form id="approveForm" method="POST" action="{{ panel_route('withdrawals.change_status', $withdrawal->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="approved">
            <div class="mb-3">
              <label class="form-label">{{ __('panel/withdrawal.admin_comment') }}</label>
              <textarea class="form-control" name="admin_comment" rows="3" placeholder="{{ __('panel/withdrawal.admin_comment_placeholder') }}"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.cancel') }}</button>
          <button type="submit" form="approveForm" class="btn btn-success">{{ __('panel/withdrawal.approve') }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Modal -->
  <div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('panel/withdrawal.reject') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>{{ __('panel/withdrawal.reject_confirm') }}</p>
          <form id="rejectForm" method="POST" action="{{ panel_route('withdrawals.change_status', $withdrawal->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <div class="mb-3">
              <label class="form-label">{{ __('panel/withdrawal.admin_comment') }} <span class="text-danger">*</span></label>
              <textarea class="form-control" name="admin_comment" rows="3" placeholder="{{ __('panel/withdrawal.admin_comment_placeholder') }}" required></textarea>
              <div class="form-text">{{ __('panel/withdrawal.admin_comment_required') }}</div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.cancel') }}</button>
          <button type="submit" form="rejectForm" class="btn btn-danger">{{ __('panel/withdrawal.reject') }}</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pay Modal -->
  <div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('panel/withdrawal.pay') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>{{ __('panel/withdrawal.pay_confirm') }}</p>
          <form id="payForm" method="POST" action="{{ panel_route('withdrawals.change_status', $withdrawal->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" value="paid">
            <div class="mb-3">
              <label class="form-label">{{ __('panel/withdrawal.admin_comment') }}</label>
              <textarea class="form-control" name="admin_comment" rows="3" placeholder="{{ __('panel/withdrawal.admin_comment_placeholder') }}"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('panel/common.cancel') }}</button>
          <button type="submit" form="payForm" class="btn btn-primary">{{ __('panel/withdrawal.pay') }}</button>
        </div>
      </div>
    </div>
  </div>
@endsection
