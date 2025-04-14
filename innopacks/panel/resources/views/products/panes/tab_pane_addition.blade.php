<div class="tab-pane fade mt-3" id="addition-tab-pane" role="tabpanel" aria-labelledby="addition-tab"
     tabindex="3">
  <div class="d-flex flex-column flex-sm-row gap-3">
    <div class="col-12 col-sm-6">
      <x-common-form-select :title="__('panel/product.tax_class')" name="tax_class_id"
                            :value="old('tax_class_id', $product->tax_class_id ?? 0)" :options="$tax_classes"
                            key="id" label="name"/>

      <div class="row">
        <div class="col-md-6">
          <x-common-form-input :title="__('panel/product.weight')" name="weight"
                           :value="old('weight', $product->weight ?? '')" :placeholder="__('panel/product.weight')"/>
        </div>
        <div class="col-md-6">
          <x-common-form-select :title="__('panel/product.weight_class')" name="weight_class"
                             :value="old('weight_class', $product->weight_class ?? '')" :options="$weightClasses"
                             key="code" label="name" />
        </div>
      </div>

      <x-common-form-select :title="__('panel/product.brand')" name="brand_id"
                            :value="old('brand_id', $product->brand_id ?? 0)" :options="$brands"
                            key="id" label="name"/>
      <x-common-form-input :title="__('panel/product.spu_code')" name="spu_code" 
                           :value="old('spu_code', $product->spu_code ?? '')"
                           :placeholder="__('panel/product.spu_code')"/>
      <x-common-form-input :title="__('panel/product.position')" name="position"
                           :value="old('position', $product->position ?? '')"
                           :placeholder="__('panel/product.position')"/>
    </div>
    <div class="col-12 col-sm-6">
      <x-panel::form.row title="{{ __('panel/product.category') }}">
        <div class="category-select">
          @if ($categories->count())
            <div class="category-search">
              <input type="text" class="form-control" placeholder="{{ __('panel/common.filter') }}">
            </div>
            <ul>
              @foreach ($categories as $category)
                <li>
                  <label>
                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                      {{ in_array($category->id, old('categories', $product->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                    <span class="name">{{ $category->fallbackName() }}</span>
                  </label>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </x-panel::form.row>
      <x-common-form-input :title="__('panel/product.sales')" name="sales" :value="old('sales', $product->sales ?? '')"
                           :placeholder="__('panel/product.sales')"/>
      <x-common-form-input :title="__('panel/product.viewed')" name="viewed"
                           :value="old('viewed', $product->viewed ?? '')" :placeholder="__('panel/product.viewed')"/>
    </div>
  </div>
  <x-common-form-switch-radio :title="__('panel/product.is_virtual')" name="is_virtual"
                              :value="old('is_virtual', $product->is_virtual ?? false)"/>
</div>
