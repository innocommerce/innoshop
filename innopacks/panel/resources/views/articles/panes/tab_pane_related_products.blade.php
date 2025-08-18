<div class="tab-pane fade mt-3" id="related-products-tab-pane" role="tabpanel" aria-labelledby="related-products-tab" tabindex="0">
  <x-panel-form-autocomplete-list 
    name="product_ids[]" 
    :value="old('product_ids', $article->products->pluck('id')->toArray() ?? [])"
    :selectedItems="$selectedRelatedProducts"
    placeholder="{{ __('panel/article.search_related_products') }}"
    title="{{ __('panel/article.related_products') }}"
    api="{{ url('api/panel/products') }}" />
</div>

@hookinsert('panel.article.edit.related_products.bottom')