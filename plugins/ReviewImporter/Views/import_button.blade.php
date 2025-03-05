<button href="{{ panel_route('products.create') }}" class="btn btn-primary" data-bs-toggle="modal"
        data-bs-target="#importModal">
  <i class="bi bi-plus-square"></i> {{ __('panel/common.import') }}
</button>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">{{ __('ReviewImporter::import.import_data') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="{{ panel_route('review_importer.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="fileInput" class="form-label">{{ __('ReviewImporter::import.select_file') }}</label>
            <input class="form-control" type="file" name="reviews">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <a href="{{ plugin_asset('review_importer', 'template/reviews.xlsx') }}" class="btn btn-primary me-2"
             download>{{ __('ReviewImporter::import.download_template') }}</a>
          <button type="submit" class="btn btn-primary">{{ __('ReviewImporter::import.upload') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>