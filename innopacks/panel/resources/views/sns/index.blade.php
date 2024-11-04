@extends('panel::layouts.app')

@section('title', __('panel/menu.sns'))

<x-panel::form.right-btns />

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">三方登录配置</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" novalidate id="sns-form" action="{{ panel_route('sns.index') }}" method="POST">
        @csrf

      <div class="container mt-1">
        <table class="table table-bordered">
          <thead class="table-secondary">
            <tr>
              <th scope="col">类型</th>
              <th scope="col">状态</th>
              <th scope="col">Cilent Secret</th>
              <th scope="col">回调地址</th>
              <th scope="col">排序</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <t>
              <th scope="row">
                <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    id="dropdownButton">
                    Dropdown button
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" data-value="Action">Action</a></li>
                    <li><a class="dropdown-item" data-value="Another action">Another action</a></li>
                    <li><a class="dropdown-item" data-value="Something else here">Something else here</a></li>
                  </ul>
                </div>
              </th>
              <td>Mark</td>
              <td>Otto</td>
              <td>@mdo</td>
              <td></td>
              <td></td>
            </t>
            <tr>
              <th scope="row">2</th>
              <td>Jacob</td>
              <td>111</td> 
              <td>@fat</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <th scope="row">3</th>
              <td colspan="1">Larry the Bird</td>
              <td>11111</td>
              <td>@twitter</td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
    <script>
    document.querySelectorAll('.dropdown-item').forEach(function (item) {
    item.addEventListener('click', function (event) {
      event.preventDefault(); // 防止默认行为（例如跳转）
      var dropdownButton = document.getElementById('dropdownButton');
      dropdownButton.textContent = this.getAttribute('data-value'); // 更新按钮文本
    });
    });
    </script>
@endpush
<style>
   
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid red;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
    .btn-secondary{
       background-color: #ffffff !important;
       color: black !important;
       border:1px solid  #dedede !important ;

    }
    .table-secondary{
         background-color: #e8edf3 !important;
    }
</style>