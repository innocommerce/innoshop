@extends('panel::layouts.app')
@section('body-class', 'page-product')

@section('title', __('panel/menu.products'))

@section('page-title-right')
  <a href="{{ panel_route('products.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card mb-3">

      <div class="row m-3">
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterName">名称:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterName" placeholder="名称">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterPrice">价格:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterPrice" placeholder="价格">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterStock">库存:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterStock" placeholder="库存">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterDate">SKU:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterDate" placeholder="SKU">
          </div>
        </div>
      </div>

    <div class="row m-3">
      <!-- 隐藏的更多筛选项，默认不显示 -->
      <div class="row" id="moreFilters" style="display:none;">
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterCategory">分类:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterCategory" placeholder="分类">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group d-flex align-items-center">
            <label class="mb-0 me-2 fw-normal" for="filterBrand">品牌:</label>
            <input type="text" class="form-control form-control-sm w-75" id="filterBrand" placeholder="品牌">
          </div>
        </div>
        <!-- 可以继续添加更多的筛选项 -->
      </div>

      <!-- 按钮部分始终在底部 -->
      <div class="row mt-3">
        <div class="col-auto">
          <button type="button" class="btn btn-outline-primary btn-sm">筛选</button>
          <button type="button" class="btn btn-outline-secondary btn-sm">重置</button>
          <button type="button" class="btn btn-outline-danger btn-sm" id="toggleMoreFilters">
            展开 <i class="bi bi-chevron-down"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById("toggleMoreFilters").addEventListener("click", function() {
      var moreFilters = document.getElementById("moreFilters");
      var icon = this.querySelector("i");

      if (moreFilters.style.display === "none") {
        moreFilters.style.display = "";
        icon.classList.remove("bi-chevron-down");
        icon.classList.add("bi-chevron-up");
        this.innerHTML = '收起 <i class="bi bi-chevron-up"></i>';
      } else {
        moreFilters.style.display = "none";
        icon.classList.remove("bi-chevron-up");
        icon.classList.add("bi-chevron-down");
        this.innerHTML = '展开 <i class="bi bi-chevron-down"></i>';
      }
    });
  </script>



  <div class="card h-min-600">
    <div class="card-body">
      @if ($products->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th>{{ __('panel/common.id') }}</th>
              <th class="wp-100">{{ __('panel/common.image') }}</th>
              <th>{{ __('panel/common.name') }}</th>
              <th>{{ __('panel/product.price') }}</th>
              <th>{{ __('panel/product.quantity') }}</th>
              <th>{{ __('panel/common.created_at') }}</th>
              <th>{{ __('panel/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
              <tr>
                <td>{{ $product->id }}</td>
                <td>
                  <div class="d-flex align-items-center justify-content-center wh-50 border">
                    <a href="{{ $product->url }}" target="_blank">
                      <img src="{{ image_resize($product->images->first()->path ?? '') }}" class="img-fluid"
                           alt="{{ $product->translation->name ?? '' }}">
                    </a>
                  </div>
                </td>
                <td><a href="{{ $product->url }}" class="text-decoration-none"
                       target="_blank">{{ $product->translation->name ?? '' }}</a></td>
                <td>{{ currency_format($product->masterSku->price) }}</td>
                <td>{{ $product->masterSku->quantity }}</td>
                <td>{{ $product->created_at }}</td>
                <td>
                  <a href="{{ panel_route('products.edit', [$product->id]) }}"
                     class="btn btn-outline-primary btn-sm">{{ __('panel/common.edit')}}</a>
                  <form action="{{ panel_route('products.destroy', [$product->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('panel/common.delete')}}</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $products->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
  </script>
@endpush