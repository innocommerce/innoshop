@extends('panel::layouts.app')

@section('title', __('ProductExporter::common.title'))

@section('content')
  <div class="card mb-3">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('ProductExporter::common.import') }}</h5>
    </div>
    <div class="card-body">
      <form id="app-import" action="{{ panel_route('exporter.import') }}" method="POST" enctype="multipart/form-data"
            class="no-load">
        @csrf
        <div class="row">
          <div class="col-md-3 mb-3">
            <div class="form-group">
              <label class="form-label" for="product-excel-file">{{ __('ProductExporter::common.select_file') }}</label>
              <input type="file" name="product_excel_file" id="product-excel-file" class="form-control">
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="form-check">
              <input class="form-check-input" name="clear-data" type="checkbox" value="1" id="flexCheckDefault">
              <label class="form-check-label" for="flexCheckDefault">
                {{ __('ProductExporter::common.clear_data') }}
              </label>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('ProductExporter::common.import') }}</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('ProductExporter::common.export') }}</h5>
    </div>
    <div class="card-body">
      <form id="app-export" action="{{ panel_route('exporter.export') }}" method="POST" class="no-load">
        @csrf
        <div class="row">
          <div class="col-md-3">
            <x-common-form-input name="name" title="{{ __('ProductExporter::common.name_filtering') }}" value="{{ old('name') }}"
                                 placeholder="{{ __('ProductExporter::common.product_name') }}"/>
          </div>
          <div class="col-md-3">
            <x-common-form-input name="quantity" title="{{ __('ProductExporter::common.export_quantity') }}" value="{{ old('quantity') }}"
                                 placeholder="{{ __('ProductExporter::common.export_quantity') }}"/>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('ProductExporter::common.export') }}</button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script></script>
@endpush