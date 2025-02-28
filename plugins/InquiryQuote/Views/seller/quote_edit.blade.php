@extends('seller::seller.layouts.app')

@section('title', '询盘议价')

@section('page-title-right')
 <div class="title-right-btns">
  <div class="status-wrap" id="status-app">
   @foreach ($next_statuses as $status)
    @if (!in_array($status['status'], $excluded))
     <button class="btn btn-primary status ms-2" data-status-code="{{ $status['status'] }}"
      data-quote-id="{{ $quote['id'] }}" @click="edit('{{ $status['status'] }}')">{{ $status['action'] }}</button>
    @endif
   @endforeach
   <el-dialog v-model="statusDialog" title="{{ __('panel/order.status') }}" width="500" class="d-none">
    <div class="mb-2">{{ __('panel/order.comment') }}</div>
    <textarea v-model="comment" class="form-control" placeholder="{{ __('panel/order.comment') }}" rows="3"></textarea>
    <template #footer>
     <div class="dialog-footer">
      <el-button @click="statusDialog = false">{{ __('panel/common.close') }}</el-button>
      <el-button type="primary" @click="submit">{{ __('panel/common.btn_save') }}</el-button>
     </div>
    </template>
   </el-dialog>
  </div>
 </div>
@endsection

@section('content')
 <div class=" h-min-600">
  <div class="card-body">

   <div class="card mb-4">
    <div class="m-3 d-flex justify-content-between mb-3">
     <h5 class="card-title mb-0">询价信息</h5>
    </div>
    <div class="card-body">
     <table class="table align-middle">
      <thead>
       <tr>
        <td>订单号</td>
        <td>创建时间</td>
        <td>订单金额</td>
        <td>配送代码</td>
        <td>询盘状态</td>
       </tr>
      </thead>
      <tbody>
       <tr>
        <td>{{ $quote['number'] }}</td>
        <td>{{ $quote['created_at'] }}</td>
        <td>{{ $quote['total_format'] }}</td>
        <td>{{ $quote['shipping_method_code'] }}</td>
        <td data-order-id="{{ $quote['id'] }}">{{ $quote['status_format'] }}</td>
       </tr>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card mb-4">
    <div class="card-header">
     <h5 class="card-title mb-0">询价商品</h5>
    </div>
    <div class="card-body">
     <table class="table products-table align-middle">
      <thead>
       <tr>
        <td>商品名称</td>
        <td>SKU</td>
        <td>产品原价</td>
        <td>期望价格</td>
        <td>数量</td>
        <td>小计</td>
       </tr>
      </thead>
      <tbody>
      <tbody>
       @foreach ($inquiry_list as $item)
        @foreach ($item['inquiries'] as $inquiry)
         <tr data-sku-code="{{ $inquiry['sku_code'] }}">
          <td class="d-flex gap-3 ">
           <img class="wh-50" src="{{ $inquiry['image'] }}" alt="">
           <div class="d-flex align-items-center"> {{ $inquiry['product_name'] }}</div>
          </td>
          <td>{{ $inquiry['sku_code'] }}</td>
          <td>{{ $inquiry['origin_price_format'] }}</td>
          <td class="col-lg-2">
           <input type="number" step="1" name="inquiries[{{ $inquiry['id'] }}][inquiry_price]"
            value="{{ $inquiry['inquiry_price'] }}" class="form-control inquiry-price"
            data-inquiry-id="{{ $inquiry['id'] }}">
          </td>
          <td class="col-lg-2">
           <input type="number" step="1" name="inquiries[{{ $inquiry['id'] }}][quantity]"
            value="{{ $inquiry['quantity'] }}" class="form-control quantity" data-inquiry-id="{{ $inquiry['id'] }}">
          </td>
          <td class="text-align">
           <div>
            {{ $inquiry['inquiry_subtotal_format'] }}
           </div>
          </td>
        @endforeach
       @endforeach
       @foreach ($quote_fees as $item)
      <tbody>
       <tr>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle">{{ $item['label'] }}({{ $item['origin_amount_format'] }})</td>
        @if ($item['code'] !== 'subtotal')
         <td id="{{ $item['code'] }}" class="align-middle"><input type="text" class="form-control"
           value="{{ $item['inquiry_amount'] }}"></td>
        @else
         <td>{{ $item['inquiry_amount'] }}</td>
        @endif
        <td class="align-middle"></td>
       </tr>
      </tbody>
      @endforeach
      </tr>
      </tbody>
      <tbody>
       <tr>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="align-middle"></td>
        <td class="mt-3">
         <button type="button" data-quote-id="{{ $quote['id'] }}" class="btn btn-primary" id="submission">提交</button>
        </td>
       </tr>
      </tbody>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card mb-4">
    <div class="card-header">
     <h5 class="card-title mb-0">{{ __('panel/order.history') }}</h5>
    </div>
    <div class="card-body">
     <div class="col-12 col-md-6 mb-4">
      <div class="checkout-select">
       <textarea class="form-control" rows="4" id="orderComment" placeholder="{{ __('front/checkout.order_comment') }}"></textarea>
      </div>
      <div class="mt-3 d-flex justify-content-end">
       <button class="btn btn-primary" id="submitButton" type="button" data-quote-id="{{ $quote['id'] }}">提交</button>
      </div>
     </div>
     <div class="border-top">
      <table class="mt-5  table table-response align-middle">
       <thead>
        <tr>
         <th>{{ __('panel/order.status') }}</th>
         <th>{{ __('panel/order.comment') }}</th>
         <th>{{ __('panel/order.date_time') }}</th>
        </tr>
       </thead>
       <tbody>
        @foreach ($quote->histories as $history)
         {{-- @dump($history) --}}
         <tr>
          <td data-title="State">{{ $history->status_format }}</td>
          <td data-title="Remark">{{ $history->comment }}</td>
          <td data-title="Update Time">{{ $history->created_at }}</td>
         </tr>
        @endforeach
       </tbody>
      </table>
     </div>
    </div>
   </div>

  </div>
 </div>
@endsection

@push('footer')
 <script>
  $('.status').on('click', function() {
   var quoteID = $(this).data('quote-id');
   var status = $(this).data('status-code')
   axios.put(`${urls.api_base}/quotes/${quoteID}/status`, {
    status: status
   }).then(function(res) {
    inno.msg(res.message);
    window.location.reload();
   }).catch(function(err) {
    layer.msg(err.response.data.message, {
     icon: 2
    });
   })
  });
  $(document).ready(function() {
   $('#submission').on('click', function() {
    const quoteId = $(this).data('quote-id');
    const inquiries = [];
    const shipping = $('#shipping input').val();
    const handling = $('#handling input').val();
    const tax = $('#tax input').val();

    $('table.products-table tbody tr').each(function() {
     const row = $(this);
     const price = parseFloat(row.find('.inquiry-price').val());
     const quantity = parseFloat(row.find('.quantity').val());
     const sku_code = row.data('sku-code');
     if (!isNaN(price) && !isNaN(quantity) && quantity > 0) {
      inquiries.push({
       sku_code:sku_code,
       inquiry_price: price,
       quantity: quantity,
      });
     }
    });
    const shipping_address_id = '{{ $quote['shipping_address_id'] }}';
    const shipping_method_code = '{{ $quote['shipping_method_code'] }}';
    const based = '{{ $quote['based'] }}';
    axios.put(`${urls.api_base}/quotes/${quoteId}`, {
     shipping_address_id,
     shipping_method_code,
     based,
     inquiries,
     fees:{shipping,
    handling,
    tax,}
    }).then(function(res) {
     inno.msg(res.message);
     window.location.reload();
    }).catch(function(err) {
     layer.msg(err.response.data.message, {
      icon: 2
     });
    });
   });
  })
  $(document).ready(function() {
   $('#submitButton').click(function() {
    var comment = $('#orderComment').val();
    var quoteId = $(this).data('quote-id');
    axios.post(`${urls.api_base}/quotes/${quoteId}/histories`, {
      comment,
     })
     .then(function(res) {
      inno.msg(res.message);
      window.location.reload();
     })
     .catch(function(err) {
      console.log(err.response.data.message)
     })
   });
  });
 </script>
@endpush
