@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('SubNews::common.sub_mails'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      @if ($mails->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('panel/common.id') }}</td>
              <td>{{ __('panel/admin.name') }}</td>
              <td>{{ __('panel/admin.email') }}</td>
              <td>{{ __('SubNews::common.ip') }}</td>
              <td>{{ __('SubNews::common.user_agent') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($mails as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->customer->name ?? '-' }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->ip }}</td>
                <td>{{ sub_string($item->user_agent, 68) }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $mails->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush