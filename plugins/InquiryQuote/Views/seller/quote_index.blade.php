@extends('seller::seller.layouts.app')

@section('title', '询盘议价')

@section('content')
  <div class="card h-min-600">
    <div class="card-body">

      <x-panel-criteria :criteria="$criteria ?? []" :action="panel_route('quotes.index')"/>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>ID</td>
            <td>询价单号</td>
            <td>询盘对象</td>
            <td>客户姓名</td>
            <td>询盘金额</td>
            <td>状态</td>
            <td>下单时间</td>
            <td>操作</td>
          </tr>
          </thead>
          <tbody>
          @foreach ($quotes as $quote)
            <tr>
              <td>{{ $quote->id }}</td>
              <td>{{ $quote->number }}</td>
              <td>{{ $quote->based_format }}</td>
              <td>{{ $quote->customer->name }}</td>
              <td>{{ $quote->total_format }}</td>
              <td>{{ $quote->status_format }}</td>
              <td>{{ $quote->created_at->format('Y-m-d H:i') }}</td>
              <td>
                <a href="{{ panel_route('quotes.edit', $quote) }}"
                   class="btn btn-sm btn-outline-primary">查看</a>
              </td>
            </tr>
          @endforeach

          @if($quotes->isEmpty())
            <tr>
              <td colspan="10">{{ __('panel/common.no_data') }}</td>
            </tr>
          @endif
          </tbody>
        </table>
      </div>

    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush

