<div class="tab-pane fade mt-3 col-md-6" id="relation-tab-pane" role="tabpanel"
     aria-labelledby="relation-tab" tabindex="5">
  <x-panel-form-autocomplete-list name="related_ids[]"
                                  :value="old('related_ids', $product->relations->pluck('relation_id')->toArray() ?? [])"
                                  placeholder="{{ __('panel/product.searching_products') }}"
                                  title="{{ __('panel/product.related_products') }}" api="/api/panel/products"/>
</div>
