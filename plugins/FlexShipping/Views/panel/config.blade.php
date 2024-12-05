@extends('panel::layouts.app')
@section('body-class', 'flex-shipping')
@section('title', __('FlexShipping::setting.heading_title'))

@push('header')
 <link rel="stylesheet" href="{{ plugin_asset('flex_shipping', 'css/flex_shipping.css') }}">
@endpush

@php
 $taxClasses = \InnoShop\Common\Repositories\TaxClassRepo::getInstance()->all();
 $geoZones = \InnoShop\Common\Repositories\RegionRepo::getInstance()->all();
 $customerGroups = \InnoShop\Common\Repositories\Customer\GroupRepo::getInstance()->all();
 $currencies = \InnoShop\Common\Repositories\CurrencyRepo::getInstance()->enabledList();
 $currencyCode = current_currency_code();
 $localeCode = locale_code();
 $timeNow = date('Y-m-d H:i:s');
@endphp

@section('content')
 <div class="card h-min-600" id="app">
  <div class="card-header">
   <button type="button" class="btn btn-primary save-btn" @click="submit">{{ __('panel/common.btn_save') }}</button>
  </div>
  <div class="card-body">
   <div class="row mb-5">
    <div class="col-xxl-20 col-xl-3 col-lg-4 col-md-4 d-flex align-items-center">
     <label class="text-nowrap me-2">{{ __('FlexShipping::setting.entry_sort_order') }}</label>
     <input type="number" v-model.number="sort_order" placeholder="{{ __('FlexShipping::setting.entry_sort_order') }}"
      class="form-control wp-200" />
    </div>

    <div class="col-xxl-20 col-xl-3 col-lg-4 col-md-4 d-flex align-items-center">
     <label class="text-nowrap me-2">{{ __('FlexShipping::setting.entry_status') }}</label>
     <select v-model.number="status" class="form-control wp-200">
      <option value="0">{{ __('FlexShipping::setting.text_disabled') }}</option>
      <option value="1">{{ __('FlexShipping::setting.text_enabled') }}</option>
     </select>
    </div>

    <div class="col-xxl-20 col-xl-3 col-lg-4 col-md-4 d-flex align-items-center">
     <label class="text-nowrap me-2">{{ __('FlexShipping::setting.entry_debug') }}</label>
     <div class="wp-200">
      <select v-model.number="debug" class="form-control">
       <option value="0">{{ __('FlexShipping::setting.text_disabled') }}</option>
       <option value="1">{{ __('FlexShipping::setting.text_enabled') }}</option>
      </select>
      <div class="text-muted mt-2" v-if="debug">{{ __('FlexShipping::setting.help_debug') }}</div>
     </div>
    </div>
   </div>

   <div class="d-flex ">
    <div class="wp-200">
     <div id="quote-list" class="nav nav-pills list-group mb-4" v-if="quotes.length">
      <a class="list-group-item d-flex justify-content-between align-items-center" :href="'#tab-quote-' + quoteIndex"
       data-bs-toggle="tab" v-for="(quote, quoteIndex) in quotes">
       <span v-text="quote.title['{{ $localeCode }}'] || '{{ __('FlexShipping::setting.text_untitled') }}'"></span>
       <div @click="removeQuoteButtonClicked(quoteIndex)"><i class="bi bi-trash"></i></div>
      </a>
     </div>
     <button class="btn btn-outline-secondary btn-sm add-new" data-toggle="tooltip" type="button"
      data-placement="bottom" @click="addQuoteButtonClicked">
      <i class="bi bi-plus-square"></i> {{ __('FlexShipping::setting.button_add_quote') }}
     </button>
    </div>
    <div class=" ms-4">
     <div class="tab-content">
      <div v-for="(quote, quoteIndex) in quotes" class="tab-pane" v-bind:id="'tab-quote-' + quoteIndex"
       :key="quoteIndex">
       <ul class="nav nav-tabs mb-4">
        <li><a class="nav-link active" :href="'#tab-quote-general-' + quoteIndex"
          data-bs-toggle="tab">{{ __('FlexShipping::setting.tab_general') }}</a></li>
        <li><a class="nav-link" :href="'#tab-quote-common-' + quoteIndex"
          data-bs-toggle="tab">{{ __('FlexShipping::setting.tab_basic') }}</a></li>
        <li><a class="nav-link" :href="'#tab-quote-product-' + quoteIndex"
          data-bs-toggle="tab">{{ __('FlexShipping::setting.tab_product') }}</a></li>
        <li><a class="nav-link" :href="'#tab-quote-time-' + quoteIndex"
          data-bs-toggle="tab">{{ __('FlexShipping::setting.tab_time') }}</a></li>
        <li><a class="nav-link" :href="'#tab-quote-cost-' + quoteIndex"
          data-bs-toggle="tab">{{ __('FlexShipping::setting.tab_cost') }}</a></li>
       </ul>
       <div class="tab-content">
        <div class="tab-pane active" :id="'tab-quote-general-' + quoteIndex">
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_title') }}</label>
          <div class="col-auto wp-100-">
           <vue-input-lang v-model="quote.title"></vue-input-lang>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.description') }}</label>
          <div class="col-auto wp-400">
           <vue-input-lang :is-textarea="true" v-model="quote.description"></vue-input-lang>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.shipping_icon') }}</label>
          <div class="col-auto wp-400">
            <vue-image v-model="quote.icon"></vue-image>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end"
           for="input-sort-order">{{ __('FlexShipping::setting.entry_sort_order') }}</label>
          <div class="col-auto wp-100-">
           <input type="number" v-model.number="quote.sort_order" id="input-sort-order"
            placeholder="{{ __('FlexShipping::setting.entry_sort_order') }}" class="form-control wp-400" />
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_tax_class') }}</label>
          <div class="col-auto wp-100-">
           <select v-model.number="quote.tax_class_id" class="form-control wp-400">
            <option value="0">{{ __('FlexShipping::setting.text_none') }}</option>
            @foreach ($taxClasses as $tax_class)
             <option value="{{ $tax_class['id'] }}">{{ $tax_class['title'] }}</option>
            @endforeach
           </select>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end"
           for="input-status">{{ __('FlexShipping::setting.entry_status') }}</label>
          <div class="col-auto wp-100-">
           <select id="input-status" class="form-control wp-400" v-model.number="quote.status">
            <option value="0">{{ __('FlexShipping::setting.text_disabled') }}</option>
            <option value="1">{{ __('FlexShipping::setting.text_enabled') }}</option>
           </select>
          </div>
         </div>
        </div>
        <div class="tab-pane" :id="'tab-quote-common-' + quoteIndex">
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_geo_zone') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.geo_zone.type">
            <option value="all">{{ __('FlexShipping::setting.entry_geo_zone_all') }}</option>
            @if (count($geoZones))
             <option value="selected">{{ __('FlexShipping::setting.entry_geo_zone_selected') }}</option>
            @endif
           </select>
           <div v-show="quote.rules.geo_zone.type != 'all'" class="bg-light p-3 wp-400">
            @foreach ($geoZones as $geo_zone)
             <label class="checkbox-inline mb-1 me-1">
              <input type="checkbox" value="{{ $geo_zone->id }}" v-model.number="quote.rules.geo_zone.ids">
              {{ $geo_zone->name }}
             </label>
            @endforeach
           </div>
           <div v-if="quote.rules.geo_zone.type != 'all' && quote.rules.geo_zone.ids.length < 1" class="text-danger">
            {{ __('FlexShipping::setting.error_geo_zone') }}
           </div>
           <div class="text-muted mt-2">{{ __('FlexShipping::setting.text_click_to_page') }}
            <a target="_blank"
             href="{{ panel_route('regions.index') }}">{{ __('FlexShipping::setting.text_configure') }}</a>
           </div>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_customer_group') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.customer_group.type">
            <option value="all">{{ __('FlexShipping::setting.entry_customer_group_all') }}</option>
            <option value="selected">{{ __('FlexShipping::setting.entry_customer_group_selected') }}</option>
           </select>
           <div v-show="quote.rules.customer_group.type != 'all'" class="bg-light p-3 wp-400">
            @foreach ($customerGroups as $customer_group)
             <label class="checkbox-inline mb-1 me-1">
              <input type="checkbox" value="{{ $customer_group->id }}"
               v-model.number="quote.rules.customer_group.ids"> {{ $customer_group->translation->name }}
             </label>
            @endforeach
           </div>
           <div v-if="quote.rules.customer_group.type != 'all' && quote.rules.customer_group.ids.length < 1"
            class="text-danger">
            {{ __('FlexShipping::setting.error_customer_group') }}
           </div>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_currency') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.currency.type">
            <option value="all">{{ __('FlexShipping::setting.entry_currency_all') }}</option>
            <option value="selected">{{ __('FlexShipping::setting.entry_currency_selected') }}</option>
           </select>
           <div v-show="quote.rules.currency.type != 'all'" class="bg-light p-3 wp-400">
            @foreach ($currencies as $currency)
             <label class="checkbox-inline mb-1 me-1">
              <input type="checkbox" value="{{ $currency->id }}" v-model.number="quote.rules.currency.ids">
              {{ $currency->name }}
             </label>
            @endforeach
           </div>
           <div v-if="quote.rules.currency.type != 'all' && quote.rules.currency.ids.length < 1" class="text-danger">
            {{ __('FlexShipping::setting.error_currency') }}
           </div>
          </div>
         </div>
        </div>
        <div class="tab-pane" :id="'tab-quote-product-' + quoteIndex">
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_product') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.product.type">
            <option value="all">{{ __('FlexShipping::setting.entry_product_all') }}</option>
            <option value="only">{{ __('FlexShipping::setting.entry_product_only') }}</option>
            <option value="include">{{ __('FlexShipping::setting.entry_product_include') }}</option>
            <option value="exclude">{{ __('FlexShipping::setting.entry_product_exclude') }}</option>
           </select>
           <div v-if="quote.rules.product.type != 'all'">
            <vue-autocomplete type="product" :items="quote.rules.product.items" @update:items="updateItems">
            </vue-autocomplete>
            <div v-if="quote.rules.product.items.length < 1" class="text-danger">
             {{ __('FlexShipping::setting.error_product') }}
            </div>
           </div>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_category') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.category.type">
            <option value="all">{{ __('FlexShipping::setting.entry_category_all') }}</option>
            <option value="only">{{ __('FlexShipping::setting.entry_category_only') }}</option>
            <option value="include">{{ __('FlexShipping::setting.entry_category_include') }}</option>
            <option value="exclude">{{ __('FlexShipping::setting.entry_category_exclude') }}</option>
           </select>
           <div v-if="quote.rules.category.type != 'all'">
            <vue-autocomplete type="category" :items="quote.rules.category.items" @update:items="updateItems">
            </vue-autocomplete>
            <div v-if="quote.rules.category.items.length < 1" class="text-danger">
             {{ __('FlexShipping::setting.error_category') }}
            </div>
           </div>
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_manufacturer') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.rules.brand.type">
            <option value="all">{{ __('FlexShipping::setting.entry_manufacturer_all') }}</option>
            <option value="only">{{ __('FlexShipping::setting.entry_manufacturer_only') }}</option>
            <option value="include">{{ __('FlexShipping::setting.entry_manufacturer_include') }}</option>
            <option value="exclude">{{ __('FlexShipping::setting.entry_manufacturer_exclude') }}</option>
           </select>
           <div v-if="quote.rules.brand.type != 'all'">
            <vue-autocomplete type="brand" :items="quote.rules.brand.items" @update:items="updateItems">
            </vue-autocomplete>
            <div v-if="quote.rules.brand.items.length < 1" class="text-danger">
             {{ __('FlexShipping::setting.error_manufacturer') }}
            </div>
           </div>
          </div>
         </div>
        </div>
        <div class="tab-pane" :id="'tab-quote-time-' + quoteIndex">
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_weekday') }}</label>
          <div class="col-auto wp-100-">
           @for ($i = 1; $i <= 7; $i++)
            <label class="checkbox-inline mt-2 me-1">
             <input type="checkbox" value="{{ $i }}" v-model.number="quote.rules.weekdays">
             {{ __('FlexShipping::setting.entry_weekday_' . $i . '') }}
            </label>
           @endfor
          </div>
         </div>
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_time') }}</label>
          <div class="col-auto wp-100-">
           <div class="row">
            @foreach (['start', 'end'] as $segment)
             <div class="col-sm-3">
              <select class="form-control" v-model="quote.rules.time.{{ $segment }}">
               <option value="any">{{ __('FlexShipping::setting.entry_any') }}</option>
               @for ($i = 0; $i <= 23; $i++)
                <option value="{{ $i < 10 ? '0' . $i : $i }}:00">{{ $i < 10 ? '0' . $i : $i }} :00
                 {{ $i < 12 ? __('FlexShipping::setting.text_am') : __('FlexShipping::setting.text_pm') }}</option>
               @endfor
              </select>
             </div>
            @endforeach
           </div>
           <div class="text-muted mt-2">
            {{ __('FlexShipping::setting.text_system_time') }}: {{ $timeNow }}
           </div>
          </div>
         </div>
        </div>
        <div class="tab-pane" :id="'tab-quote-cost-' + quoteIndex">
         <div class="row g-3 mb-3">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_unit') }}</label>
          <div class="col-auto wp-100-">
           <select class="form-control wp-400" v-model="quote.cost.unit">
            <option value="flat">{{ __('FlexShipping::setting.entry_unit_flat') }}</option>
            <option value="subtotal">{{ __('FlexShipping::setting.entry_unit_subtotal') }}</option>
            <option value="weight">{{ __('FlexShipping::setting.entry_unit_weight') }}</option>
            {{-- <option value="volume">{{ __('FlexShipping::setting.entry_unit_volume') }}</option> --}}
            <option value="total_quantity">{{ __('FlexShipping::setting.entry_unit_total_quantity') }}</option>
            {{-- <option value="volume_weight">{{ __('FlexShipping::setting.entry_unit_volume_weight') }}</option> --}}
            {{-- <option value="volume_weight_max">{{ __('FlexShipping::setting.entry_unit_volume_weight_max') }}
           </option> --}}
           </select>
          </div>
         </div>
         <div v-show="quote.cost.unit == 'volume_weight' || quote.cost.unit == 'volume_weight_max'"
          class="row g-3 mb-3">
          <label
           class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_volume_weight_ratio') }}</label>
          <div class="col-auto wp-100-">
           <div class="row">
            <div class="col-sm-3">
             <select v-model="quote.cost.ratio.operator" class="form-control wp-400">
              <option value="add">+</option>
              <option value="subtract">-</option>
              <option value="multiply">x</option>
              <option value="divide">÷</option>
             </select>
            </div>
            <div class="col-sm-3">
             <input type="number" v-model="quote.cost.ratio.constant"
              placeholder="{{ __('FlexShipping::setting.entry_volume_weight_constant') }}"
              class="form-control wp-400">
            </div>
           </div>
          </div>
         </div>
         <div class="row g-3 mb-3" v-if="quote.cost.unit == 'flat'">
          <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_flat_cost') }}</label>
          <div class="col-auto wp-100-">
           <div class="input-group">
            <div class="input-group-text">{{ $currencyCode }}</div>
            <input type="number" class="form-control wp-400" v-model="quote.cost.flat_cost">
           </div>
          </div>
         </div>
         <div v-else>
          <div class="row g-3 mb-3">
           <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_cost_type') }}</label>
           <div class="col-auto wp-100-">
            <select v-model="quote.cost.type" class="form-control wp-400">
             <option value="range">{{ __('FlexShipping::setting.entry_cost_type_range') }}</option>
             <option value="cumulative">{{ __('FlexShipping::setting.entry_cost_type_cumulative') }}</option>
            </select>
           </div>
          </div>
          <div class="row g-3 mb-3">
           <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_cost_type') }}</label>
           <div class="col-auto wp-100-">
            <table class="table table-bordered">
             <thead>
              <tr>
               <th>{{ __('FlexShipping::setting.entry_cost_start') }}</th>
               <th>{{ __('FlexShipping::setting.entry_cost_end') }}</th>
               <th>{{ __('FlexShipping::setting.entry_cost') }}</th>
               <th style="width: 200px;">{{ __('FlexShipping::setting.entry_cumulative_number') }}</th>
               <th style="width: 100px;"></th>
              </tr>
             </thead>
             <tbody>
              <tr v-for="(range, rangeIndex) in quote.cost.ranges">
               <td>
                <div class="input-group">
                 <div class="input-group-text" v-if="rangeIndex == 0">&gt;=</div>
                 <div class="input-group-text" v-else>&nbsp;&gt;&nbsp;</div>
                 <input type="number" class="form-control" v-model.number="range.start">
                </div>
                <div v-if="range.start < 0" class="text-danger">
                 <span>{{ __('FlexShipping::setting.error_start_lt_0') }}</span>
                </div>
                <div v-if="rangeIndex > 0 && range.start < quote.cost.ranges[rangeIndex - 1]['end']"
                 class="text-danger">
                 <span>{{ __('FlexShipping::setting.entry_cost_start') }}: @{{ range.start }}
                  {{ __('FlexShipping::setting.error_start_lt_last_end') }}: @{{ quote.cost.ranges[rangeIndex - 1]['end'] }}</span>
                </div>
               </td>
               <td>
                <div class="input-group">
                 <div class="input-group-text">&lt;=</div>
                 <input type="number" class="form-control" v-model.number="range.end">
                </div>
                <div v-if="range.end < 0" class="text-danger">
                 <span>{{ __('FlexShipping::setting.error_end_lt_0') }}</span>
                </div>
                <div v-if="range.start > range.end" class="text-danger">
                 <span>{{ __('FlexShipping::setting.entry_cost_end') }}: @{{ range.end }}
                  {{ __('FlexShipping::setting.error_end_lt_start') }}: @{{ range.start }}</span>
                </div>
               </td>
               <td>
                <div class="input-group">
                 <div class="input-group-text">{{ $currencyCode }}</div>
                 <input type="number" class="form-control" v-model.number="range.cost">
                </div>
                <div v-if="range.cost < 0" class="text-danger">
                 <span>{{ __('FlexShipping::setting.error_cost_lt_0') }}</span>
                </div>
               </td>
               <td style="width: 200px;">
                <input type="number" class="form-control" v-model.number="range.block">
                <div v-if="range.block < 0" class="text-danger">
                 <span>{{ __('FlexShipping::setting.error_cumulative_number_lt_0') }}</span>
                </div>
               </td>
               <td>
                <button class="btn btn-outline-danger" type="button"
                 @click="removeCostRangeButtonClicked(quoteIndex, rangeIndex)">{{ __('panel/common.delete') }}</button>
               </td>
              </tr>
             </tbody>
             <tfoot>
              <tr>
               <td colspan="5">
                <button type="button" class="btn btn-outline-secondary btn-sm float-end"
                 @click="addCostRangeButtonClicked(quoteIndex)">
                 {{ __('FlexShipping::setting.button_add_cost') }}
                </button>
               </td>
              </tr>
             </tfoot>
            </table>
           </div>
          </div>

          <div class="row g-3 mb-3">
           <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_extra') }}</label>
           <div class="col-auto wp-100-">
            <div class="input-group wp-400">
             <div class="input-group-text">{{ $currencyCode }}</div>
             <input type="number" class="form-control" v-model.number="quote.cost.extra">
            </div>
           </div>
          </div>
          <div class="row g-3 mb-3">
           <label class="wp-100 col-form-label text-end">{{ __('FlexShipping::setting.entry_max') }}</label>
           <div class="col-auto wp-100-">
            <div class="input-group wp-400">
             <div class="input-group-text">{{ $currencyCode }}</div>
             <input type="number" class="form-control" v-model.number="quote.cost.max">
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
@endsection

@push('footer')
 @include('panel::components.vue.vue-autocomplete')
 @include('panel::components.vue.vue-image')
 @include('panel::components.vue.vue-input-lang')
 <script>
  const user_token = '';
  const button_confirm_delete = '{{ __('FlexShipping::setting.button_confirm_delete') }}';
  const button_cancel = '{{ __('FlexShipping::setting.button_cancel') }}';
  const text_confirm_delete = '{{ __('FlexShipping::setting.text_confirm_delete') }}';
  const text_save_success = '{{ __('FlexShipping::setting.text_save_success') }}';
  const error_network = '{{ __('FlexShipping::setting.error_network') }}';
  const text_untitled = '{{ __('FlexShipping::setting.text_untitled') }}';
  const i18n = {
   button_confirm_delete: button_confirm_delete,
   button_cancel: button_cancel,
   text_confirm_delete: text_confirm_delete,
   text_save_success: text_save_success,
   error_network: error_network,
   text_untitled: text_untitled,
  };


  function showToast(message) {
   layer.msg(message);
  }

  let _loading = null;

  function showLoading() {
   _loading = layer.load(1, {
    shade: [0.5, '#fff']
   });
  }

  function closeLoading() {
   layer.close(_loading);
  }

  function showConfirm(message, leftButton, rightButton, callback) {
   const deleteConfirmLayer = layer.confirm(message, {
    btn: [leftButton, rightButton]
   }, function() {
    layer.close(deleteConfirmLayer);
    callback();
   });
  }

  function newQuoteFactory() {
   return {
    title: {},
    icon: '',
    description: {},
    sort_order: 0,
    tax_class_id: 0,
    status: 0,
    rules: {
     store: {
      type: 'all',
      ids: []
     },
     geo_zone: {
      type: 'all',
      ids: []
     },
     country: {
      type: 'all',
      ids: []
     },
     customer_group: {
      type: 'all',
      ids: []
     },
     zone: {
      type: 'all',
      ids: []
     },
     currency: {
      type: 'all',
      ids: []
     },
     product: {
      type: 'all',
      items: []
     },
     category: {
      type: 'all',
      items: []
     },
     brand: {
      type: 'all',
      items: []
     },
     weekdays: [1, 2, 3, 4, 5, 6, 7],
     time: {
      start: 'any',
      end: 'any'
     }
    },
    cost: {
     unit: 'weight',
     type: 'range',
     ratio: {
      operator: 'divide',
      constant: null
     },
     ranges: [],
     flat_cost: null,
     max: null,
     extra: null,
    }
   }
  }

  function newCostRangeFactory() {
   return {
    start: null,
    end: null,
    cost: null,
    block: 0,
   }
  }
  const {
   ref,
   reactive,
   watch,
   createApp,
   computed
  } = Vue;

  const app = createApp({
   components: {
    VueAutocomplete,
    VueImage,
    VueInputLang,
   },
   setup() {
    const status = ref(@json($plugin->getSetting('setting.status') ?? 1));
    const debug = ref(@json($plugin->getSetting('setting.debug') ?? 0));
    const sort_order = ref(@json($plugin->getSetting('setting.sort_order') ?? 0));
    const quotes = reactive(@json($plugin->getSetting('setting.quotes') ?? []));

    const validateQuote = function() {};

    const submit = function() {
     const postData = {
      status: this.status,
      sort_order: this.sort_order,
      debug: this.debug,
      quotes: quotes
     };
     axios.put('{{ panel_route('flex_shipping.update') }}', postData).then((res) => {
      if (res.message) {
       layer.msg(res.message)
      }
     })
    };

    const addQuoteButtonClicked = function() {
     quotes.push(newQuoteFactory());
     setTimeout(() => {
      $('#quote-list').children("a:last-child")[0].click()
     }, 0)
    };

    const removeQuoteButtonClicked = function(quoteIndex) {
     showConfirm(i18n.text_confirm_delete, i18n.button_confirm_delete, i18n.button_cancel, function() {
      quotes.splice(quoteIndex, 1);
     });
    };
    const addCostRangeButtonClicked = function(quoteIndex) {
     var newRange = newCostRangeFactory();
     var rangeCount = quotes[quoteIndex].cost.ranges.length;
     if (rangeCount > 0) {
      newRange.start = quotes[quoteIndex].cost.ranges[rangeCount - 1].end;
     }
     quotes[quoteIndex].cost.ranges.push(newRange);
    };

    const removeCostRangeButtonClicked = function(quoteIndex, rangeIndex) {
     quotes[quoteIndex].cost.ranges.splice(rangeIndex, 1);
    };

    const updateItems = (newItems) => {
     quotes[0].rules.product.items.value = newItems;
    };

    return {
     status,
     debug,
     sort_order,
     quotes,
     validateQuote,
     submit,
     addQuoteButtonClicked,
     removeQuoteButtonClicked,
     addCostRangeButtonClicked,
     removeCostRangeButtonClicked,
     updateItems,
    };
   },

   watch: {
    quotes: {
     handler: function() {
      this.validateQuote();
     },
     deep: true
    }
   },

   mounted() {
    if (this.quotes.length) {
     $('#quote-list').children("a:first-child")[0].click()
    }
   },
  });

  app.use(ElementPlus);
  app.mount("#app");
 </script>

 <style>
  .nav-link {
   color: #1f1f1f;
  }
 </style>
@endpush
