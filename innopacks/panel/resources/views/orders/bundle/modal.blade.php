<!-- Bundle Details Modal -->
<div class="modal fade" id="bundleDetailsModal" tabindex="-1" aria-labelledby="bundleDetailsModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title d-flex align-items-center" id="bundleDetailsModalLabel">
          <i class="bi bi-box-seam text-primary me-2"></i>
          <span>{{ __('panel/order.bundle_details') }}</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">


        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
            <tr>
              <th class="border-0">{{ __('panel/order.product') }}</th>
              <th class="border-0">{{ __('panel/order.sku_code') }}</th>
              <th class="border-0 text-center">{{ __('panel/order.quantity') }}</th>
            </tr>
            </thead>
            <tbody id="bundleItemsTable">
            <!-- Bundle items will be populated here -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>{{ __('panel/common.close') }}
        </button>
      </div>
    </div>
  </div>
</div>
