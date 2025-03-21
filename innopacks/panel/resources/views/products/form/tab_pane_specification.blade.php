<div class="tab-pane fade mt-4" id="specification-tab-pane" role="tabpanel"
     aria-labelledby="specification-tab" tabindex="2">
  <div class="mt-2 skus-single-box {{ $product->isMultiple() ? 'd-none' : '' }}">
    <div class="alert alert-info mb-3">
      <i class="bi bi-info-circle me-2"></i>
      {{ __('panel/product.sku_notice') }}
    </div>
    <div class="row">
      <div class="col-12 col-md-4">
        <x-common-form-input :title="__('panel/product.price')" name="skus[0][price]"
                             value="{{ old('skus.0.price', $product->masterSku->price ?? '') }}"/>
      </div>
      <div class="col-12 col-md-4">
        <x-common-form-input :title="__('panel/product.quantity')" name="skus[0][quantity]"
                             value="{{ old('skus.0.quantity', $product->masterSku->quantity ?? '') }}"/>
      </div>
      <div class="col-12 col-md-4">
        <x-common-form-input title="SKU Code" name="skus[0][code]"
                             value="{{ old('skus.0.code', $product->masterSku->code ?? '') }}"/>
      </div>
    </div>

    <div class="row">
      <div class="col-12 col-md-4">
        <x-common-form-input :title="__('panel/product.model')" name="skus[0][model]"
                             value="{{ old('skus.0.model', $product->masterSku->model ?? '') }}"/>
      </div>
      <div class="col-12 col-md-4">
        <x-common-form-input :title="__('panel/product.origin_price')" name="skus[0][origin_price]"
                             value="{{ old('skus.0.origin_price', $product->masterSku->origin_price ?? '') }}"/>
      </div>
      @hookinsert('panel.product.edit.sku.single.input.item.price.after')
    </div>
  </div>
  @include('panel::products.form._form_variant')
  @hookinsert('panel.product.edit.form_variant.after')
  @include('panel::products.form._form_attribute')
</div>
