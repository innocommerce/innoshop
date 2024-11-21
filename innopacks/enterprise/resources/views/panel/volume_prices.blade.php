@extends('panel::layouts.app')

@section('title', '批发价')

@push('header')
@endpush

<x-panel::form.right-btns/>

@section('content')
  <div id="app">
    <div class="card">
      <div class="card-header d-none">批发价</div>
      <div class="card-body">
        <table class="table table-response">
          <tr>
            <th>产品</th>
            <th>购买数量</th>
            <th>原价</th>
            <th>批发价</th>
            <th>操作</th>
          </tr>
          <tr>
            <td>aa</td>
            <td>100</td>
            <td>399</td>
            <td>299</td>
            <td>删除</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush
