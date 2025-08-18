<div class="tab-pane fade mt-3" id="related-articles-tab-pane" role="tabpanel" aria-labelledby="related-articles-tab" tabindex="0">
  <x-panel-form-autocomplete-list 
    name="related_article_ids[]" 
    :value="old('related_article_ids', $article->relatedArticles->pluck('relation_id')->toArray() ?? [])"
    :selectedItems="$selectedRelatedArticles"
    placeholder="{{ __('panel/article.search_related_articles') }}"
    title="{{ __('panel/article.related_articles') }}"
    api="{{ url('api/panel/articles') }}" />
</div>

@hookinsert('panel.article.edit.related_articles.bottom')