@extends('panel::layouts.app')
@section('body-class', 'page-product-form')
@section('title', __('panel/menu.products'))

<x-panel::form.right-btns formid="product-form" />

@php
  $weightClasses = [
      ['id' => 0, 'name' => 'g'],
      ['id' => 1, 'name' => 'kg'],
      ['id' => 2, 'name' => 'lb'],
      ['id' => 3, 'name' => 'oz'],
  ];
@endphp

@section('content')
  <form class="needs-validation no-load" novalidate
    action="{{ $product->id ? panel_route('products.update', [$product->id]) : panel_route('products.store') }}"
    method="POST" id="product-form">
    @csrf
    @method($product->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-12">
        <div class="card mb-3">
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                  type="button" role="tab" aria-controls="basic-tab-pane"
                  aria-selected="true">{{ __('panel/product.basic_information') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="translation-tab" data-bs-toggle="tab" data-bs-target="#translation-tab-pane"
                  type="button" role="tab" aria-controls="translation-tab-pane"
                  aria-selected="false">{{ __('panel/product.product_description') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="specification-tab" data-bs-toggle="tab"
                  data-bs-target="#specification-tab-pane" type="button" role="tab"
                  aria-controls="specification-tab-pane"
                  aria-selected="false">{{ __('panel/product.specification_attribute') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="addition-tab" data-bs-toggle="tab" data-bs-target="#addition-tab-pane"
                  type="button" role="tab" aria-controls="addition-tab-pane"
                  aria-selected="false">{{ __('panel/product.extend_information') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button"
                  role="tab" aria-controls="seo-tab-pane" aria-selected="false">{{ __('panel/product.seo') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="relation-tab" data-bs-toggle="tab" data-bs-target="#relation-tab-pane"
                  type="button" role="tab" aria-controls="relation-tab-pane"
                  aria-selected="false">{{ __('panel/product.related_products') }}</button>
              </li>
              @hookinsert('panel.product.edit.tab.nav.bottom')
            </ul>

            <div class="tab-content" id="myTabContent">
              @include('panel::products.form.tab_pane_basic', $product)

              @include('panel::products.form.tab_pane_content', $product)

              @include('panel::products.form.tab_pane_specification', $product)

              @include('panel::products.form.tab_pane_addition', $product)

              @include('panel::products.form.tab_pane_seo', $product)

              @include('panel::products.form.tab_pane_related', $product)

              @hookinsert('panel.product.edit.tab.pane.bottom')
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('footer')
  <script>
    $(document).ready(function() {
      $('#product-form').on('keypress', function(e) {
        if (e.which === 13) {
          e.preventDefault();
        }
      });

      $('.category-search input').on('input', function() {
        const val = $(this).val().trim();
        var lists = $('.category-select li');
        lists.each(function() {
          var text = $(this).find('.name').text();
          if (text.indexOf(val) > -1) {
            $(this).show();
            $(this).find('.name').html(text.replace(val, '<span style="color: red;">' + val + '</span>'));
          } else {
            $(this).hide();
          }
        });
      });

      $('#translate-button').click(function() {
        const source_locale = $('#source-locale').val();
        const input = $(`input[data-locale="${source_locale}"]`);
        const text = input.val();

        axios.post(`/panel/translations/translate`, {
          source: source_locale,
          target: $('#target-locale').val(),
          text: text
        }).then(function(res) {
          res.data.forEach(function(item) {
            const target_input = $(`input[name="translations[${item.locale}][name]"]`);
            target_input.val(item.result);
          });
        }).catch(function(err) {
          inno.alert({
            msg: err.response.data.message,
            type: 'danger'
          });
        });
      });

      $('#translate-tab').click(function() {
        const source_tab_code = $('#source-tab').val();
        const textarea = $(`textarea[name="translations[${source_tab_code}][content]"]`);
        const editor_id = textarea.attr('id');
        const editor = tinymce.get(editor_id);

        tinymce.triggerSave();
        let content = editor.getContent();
        axios.post(`/panel/translations/translate`, {
          source: source_tab_code,
          target: $('#target-tab').val(),
          text: content
        }).then(function(res) {
          res.data.forEach(item => {
            const inputs = $(`input[data-locale="${item.locale}"]`);
            inputs.each(function() {
              const rich_text_editor = tinymce.get(`content-${item.locale}`);
              if (rich_text_editor) {
                rich_text_editor.setContent(item.result);
              }
            });
          });
        }).catch(function(err) {
          inno.alert({
            msg: err.response.data.message,
            type: 'danger'
          });
        });
      });

      $('.translate-submit').click(function() {
        const localeCodeContainer = $(this).closest('.form-row');
        const selectElement = $(this).closest('div').next('div').find('select.form-select');
        const selectedOptionValue = selectElement.val();
        const textarea = localeCodeContainer.find('textarea');
        const current_source = $(this).closest('.locale-code').data('locale-code');
        let textareaValue;
        let currentTextareaName;
        if (textarea.is('textarea')) {
          textareaValue = textarea.val();
          currentTextareaName = textarea.attr('name');

        } else {
          const inputarea = localeCodeContainer.find('input');
          textareaValue = inputarea.val(); // 赋值
          currentTextareaName = inputarea.attr('name'); // 赋值
        }
        console.log(textareaValue);
        axios.post(`/panel/translations/translate`, {
          source: current_source,
          target: selectedOptionValue,
          text: textareaValue
        }).then(function(res) {
          res.data.forEach(function(item) {
            const targetInputSelector = currentTextareaName.replace(`[${current_source}]`,
              `[${item.locale}]`);
            if (textarea.is('textarea')) {
              $('textarea[name="' + targetInputSelector + '"]').val(item.result);
            } else {
              $('input[name="' + targetInputSelector + '"]').val(item.result);
            }

          });
        }).catch(function(err) {
          inno.alert({
            msg: err.response.data.message,
            type: 'danger'
          });
        });
      });
    });
  </script>
@endpush
