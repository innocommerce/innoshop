@extends('panel::layouts.app')
@section('body-class', 'page-home')

@section('title', __('panel::menu.locale'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <table class="table">
        <thead>
        <tr>
          <td>ID</td>
          <td>标识</td>
          <td>名称</td>
          <td>编码</td>
          <td>排序</td>
          <td>操作</td>
        </tr>
        </thead>
        @if ($locales)
          <tbody>
          @foreach($locales as $item)
            <tr>
              <td>{{ $item['id'] }}</td>
              <td><img src="{{ image_resize($item['image']) }}" style="width: 30px;"></td>
              <td>{{ $item['name'] }}</td>
              <td>{{ $item['code'] }}</td>
              <td>{{ $item['position'] }}</td>
              <td>
                @if ($item['id'])
                  <button type="button" class="btn btn-sm btn-outline-danger leng-unload" data-code="{{ $item['code'] }}">卸载</button>
                @else
                  <button type="button" class="btn btn-sm btn-outline-primary leng-install" data-code="{{ $item['code'] }}">安装</button>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        @else
          <tbody>
          <tr>
            <td colspan="5">
              <x-panel-no-data/>
            </td>
          </tr>
          </tbody>
        @endif
      </table>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    $(function () {
      $('.leng-install').click(function () {
        axios.post('panel/locales/install', {code: $(this).data('code')}).then(function (res) {
          window.location.reload()
        })
      });

      $('.leng-unload').click(function () {
        axios.post(`panel/locales/${$(this).data('code')}/uninstall`).then(function (res) {
          window.location.reload()
        })
      });
    });
  </script>
@endpush